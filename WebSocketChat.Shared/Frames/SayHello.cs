using MsgPack;

namespace WebSocketChat.Shared.Frames
{
    public class SayHello : IPackable
    {
        public string Name { get; set; }

        public void PackToMessage(Packer packer, PackingOptions options)
        {
            packer.PackMapHeader(2);

            packer.PackString("Action");
            packer.PackString("SayHello");

            packer.PackString("Name");
            packer.PackString(Name);
        }
    }
}