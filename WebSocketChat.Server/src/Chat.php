<?php

namespace App;

use MessagePack\Exception\InvalidOptionException;
use MessagePack\Exception\UnpackingFailedException;
use MessagePack\MessagePack;
use Psr\Http\Message\RequestInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\Frame;

class Chat implements MessageComponentInterface {
    protected $history = [];
    protected $clients_logins = [];
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $object = MessagePack::unpack($msg);
        } catch (InvalidOptionException | UnpackingFailedException $exception) {
            return false;
        }
        
        if(!isset($object['Action']))
            return false;

        if($object['Action'] == 'SayHello')
            return $this->onSayHello($from, $msg, $object);

        if($object['Action'] == 'SendMessage')
            return $this->onSendMessage($from, $msg, $object);
        
        
        echo "Unknown action '{$object['Action']}': ".json_encode($object)."\n";
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";

        if(!isset($this->clients_logins[$conn->resourceId]))
            return;
        
        $response = MessagePack::pack([
            'From' => 'SYSTEM',
            'Message' => "User {$this->clients_logins[$conn->resourceId]} left the chat.",
            'Time' => (new \DateTime('NOW'))->format(DATE_ATOM)
        ]);

        $this->addFrameToHistory($response, $conn);
        
        unset($this->clients_logins[$conn->resourceId]);

        foreach ($this->clients as $client) {
            $client->send(new Frame($response, true, Frame::OP_BINARY));
        }

    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    private function onSayHello(ConnectionInterface $from, string $msg, array $object)
    {
        if(isset($this->clients_logins[$from->resourceId]) || !isset($object['Name']) || strlen($object['Name']) > 25 || $object['Name'] == 'SYSTEM')
            return;

        $numRecv = count($this->clients) - 1;

        echo "User \"{$object['Name']}\" (connection {$from->resourceId}) said hello to {$numRecv} other users!\n";

        $this->clients_logins[$from->resourceId] = $object['Name'];
        
        foreach ($this->history as $frame) {
            $from->send(new Frame($frame, true, Frame::OP_BINARY));
        }
        
        $response = MessagePack::pack([
            'From' => 'SYSTEM',
            'Message' => "User {$this->clients_logins[$from->resourceId]} joined the chat!",
            'Time' => (new \DateTime('NOW'))->format(DATE_ATOM)
        ]);
        
        $this->addFrameToHistory($response, $from);
        
        foreach ($this->clients as $client) {
                $client->send(new Frame($response, true, Frame::OP_BINARY));
        }

        $response = MessagePack::pack([
            'From' => 'SYSTEM',
            'Message' => "Currently online: ".implode($this->clients_logins, ', '),
            'Time' => (new \DateTime('NOW'))->format(DATE_ATOM)
        ]);
        $from->send(new Frame($response, true, Frame::OP_BINARY));
    }

    private function onSendMessage(ConnectionInterface $from, string $msg, $object)
    {
        if(!isset($this->clients_logins[$from->resourceId]) || !isset($object['Message']) || strlen($object['Message']) > 100 || $object['Message'] == "")
            return;
        
        if ($object['Message'] == '~'){
            $response = MessagePack::pack([
                'From' => 'SYSTEM',
                'Message' => "Currently online: ".implode($this->clients_logins, ', '),
                'Time' => (new \DateTime('NOW'))->format(DATE_ATOM)
            ]);
            $from->send(new Frame($response, true, Frame::OP_BINARY));
            
            return;
        }
        
        if ($object['Message'] == '~!'){
            exit;
        }

        $numRecv = count($this->clients) - 1;

        echo "New Message from \"{$this->clients_logins[$from->resourceId]}\" (connection {$from->resourceId}) was sent to {$numRecv} other users: {$object['Message']}\n";
        
        $response = MessagePack::pack([
            'From' => $this->clients_logins[$from->resourceId],
            'Message' => $object['Message'],
            'Time' => (new \DateTime('NOW'))->format(DATE_ATOM)
        ]);

        $this->addFrameToHistory($response, $from);
        
        foreach ($this->clients as $client) {
            if($client == $from)
                continue;
            
            if(!isset($this->clients_logins[$from->resourceId]))
                continue;
            
            $client->send(new Frame($response, true, Frame::OP_BINARY));
        }
    }
    
    private function addFrameToHistory(string $frame, ConnectionInterface $from) {
        $object = MessagePack::unpack($frame);
        file_put_contents(__DIR__.'/../.log', date("Y-m-d H:i:s")."\t".$from->remoteAddress."\t".$object['From']."\t".$object['Message']."\n", FILE_APPEND);
        
        $this->history[] = $frame;
        
        while (count($this->history) > 50)
            array_shift($this->history);
    }
}