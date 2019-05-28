using System;
using System.Threading;
using WebSocketChat.Shared;
using WebSocketChat.Shared.Objects;

namespace WebSocketChat.CLI
{
    class MainClass
    {
        private static Chat _chat;

        public static void Main(string[] args)
        {
            Console.WriteLine("Enter Your Name: ");
            var name = Console.ReadLine();
            _chat = new Chat(name);
            _chat.OnMessage += MessageReceived;
            _chat.OnDisconnect += ChatDisconnected;
            _chat.Connect();

            while(true)
            {
                var message = Console.ReadLine();
                _chat.Send(message);
                Console.WriteLine($"[{name}] {message}");
            }
        }

        private static void ChatDisconnected()
        {
            Console.WriteLine($"CHAT DISCONNECTED - RECONNECTING!");
            Thread.Sleep(2000);
            _chat.Connect();
        }

        private static void MessageReceived(ChatMessage obj) => Console.WriteLine($"[{obj.From}] {obj.Message}");

    }
}
