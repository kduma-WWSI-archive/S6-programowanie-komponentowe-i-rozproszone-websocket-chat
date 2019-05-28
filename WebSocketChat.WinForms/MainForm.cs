using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using WebSocketChat.Shared;
using WebSocketChat.Shared.Objects;

namespace WebSocketChat.WinForms
{
    public partial class MainForm : Form
    {
        private static Chat _chat;
        public MainForm()
        {
            InitializeComponent();
        }

        private void Button1_Click(object sender, EventArgs e)
        {
            SendMessage();
        }

        private void SendMessage()
        {
            _chat.Send(textBox1.Text);
            listBox1.Items.Add($"[{_chat.Name}] {textBox1.Text}");
            textBox1.Text = "";
        }

        private void Form1_Load(object sender, EventArgs e)
        {
            _chat = new Chat(Prompt.ShowDialog("My Poor Chat", "Enter Your Name:"));
            _chat.OnMessage += MessageReceived;
            _chat.Connect();
        }

        void MessageReceived(ChatMessage obj)
        {
            this.Invoke(
                (MethodInvoker) delegate { listBox1.Items.Add($"[{obj.From}] {obj.Message}"); }
            );
        }
    }
}
