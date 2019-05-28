using MsgPack;

namespace WebSocketChat.Shared.Frames
{
    public class ChatMessage : IPackable
    {
        public string Message { get; set; }

        public void PackToMessage(Packer packer, PackingOptions options)
        {
            packer.PackMapHeader(2);

            packer.PackString("Action");
            packer.PackString("SendMessage");

            packer.PackString("Message");
            packer.PackString(Message);
        }
    }
}