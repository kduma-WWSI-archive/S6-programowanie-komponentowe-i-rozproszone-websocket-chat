using System;
using MsgPack.Serialization;
using WebSocketChat.Shared.Frames;
using WebSocketSharp;

namespace WebSocketChat.Shared
{
    public class Chat
    {
        private readonly WebSocket _ws;

        public event Action<Objects.ChatMessage> OnMessage;
        public event Action OnDisconnect;

        public string Name { get; }

        public Chat(string name, string endpoint = "ws://localhost:9267/")
        {
            _ws = new WebSocket(endpoint);
            _ws.OnMessage += MessageHandler;
            _ws.OnClose += CloseHandler;
            Name = name;
        }

        private void CloseHandler(object sender, CloseEventArgs e)
        {
            OnDisconnect?.Invoke();
        }

        public void Connect()
        {
            _ws.Connect();
            
            var serializer = MessagePackSerializer.Get<SayHello>();
            var frame = serializer.PackSingleObject(new SayHello
            {
                Name = Name
            });

            _ws.Send(frame);
        }

        public void Send(string message)
        {
            var serializer = MessagePackSerializer.Get<ChatMessage>();
            var frame = serializer.PackSingleObject(new ChatMessage
            {
                Message = message
            });

            _ws.Send(frame);
        }

        private void MessageHandler(object sender, MessageEventArgs e)
        {
            var serializer = MessagePackSerializer.Get<Objects.ChatMessage>();
            var message = serializer.UnpackSingleObject(e.RawData);

            OnMessage?.Invoke(message);
        }

    }
}
