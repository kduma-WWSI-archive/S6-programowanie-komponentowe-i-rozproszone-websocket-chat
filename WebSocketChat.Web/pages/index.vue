<template>
  <main class="main-content">
    <section class="nes-container with-title">
      <h3 class="title">WebSocketChat</h3>
      <div id="init" class="item" v-if="!init">
        <div class="nes-field">
          <form @submit.prevent="initialize">
            <label for="host">Server Address</label>
            <input type="text" id="host" class="nes-input" v-model="host">

            <br>

            <label for="username">Your Username</label>
            <input type="text" id="username" class="nes-input" v-model="username">

            <br>

            <button type="submit" class="nes-btn is-primary">Start Chat</button> <a class="nes-btn" href="win.zip">WinForms</a> <a class="nes-btn" href="cli.zip">Mono CLI</a>
          </form>
        </div>
      </div>
      <div id="inputs" class="item" v-if="init">
        <div class="nes-field">
          <form @submit.prevent="send_message">
            <label for="message">Your Message</label>
            <input type="text" id="message" class="nes-input" v-model="new_message">
          </form>
        </div>
      </div>
      <div id="balloons" class="item" v-if="init">
        <section class="message-list">

          <template v-for="message in messages_list">
            <section :class="['message', message.From === username ? ' -right':'-left']">
              <i class="nes-bcrikko" v-if="message.From !== username"></i>
              <!-- Balloon -->
              <div :class="['nes-balloon', message.From === username ? ' from-right':'from-left']">
                <span class="nes-badge">
                  <span :class="[message.From === 'SYSTEM' ? 'is-error':(message.From === username ? 'is-success':'is-dark')]">{{ message.From }}</span>
                </span>
                <span class="nes-text is-disabled">
                  <timeago :datetime="message.Time" :auto-update="5" :converter-options="{includeSeconds: true}"></timeago>
                </span>
                <p>{{ message.Message }}</p>
              </div>
              <i class="nes-bcrikko" v-if="message.From === username"></i>
            </section>
          </template>
        </section>
      </div>
    </section>
  </main>
</template>

<script>
import msgpack from "msgpack-lite";

export default {
  data() {
    return {
      init: false,
      new_message: "",
      connection: null,
      host: 'wss://'+window.location.hostname+":2096/",
      username: "WEB"+this.getRandomInt(1000, 9999),
      messages_list: [],
    }
  },
  methods: {
    send_message: function(event) {
      this.connection.send(msgpack.encode({
        "Action": "SendMessage",
        "Message": this.new_message
      }));

      this.messages_list.unshift({
        "From": this.username,
        "Message": this.new_message,
        "Time": new Date()
      });

      this.new_message = "";

      while (this.messages_list.length > 50)
        this.messages_list.pop();
    },
    message_received: function(event) {
      var raw_binary_data = new Uint8Array(event.data);
      var message = msgpack.decode(raw_binary_data);
      this.messages_list.unshift(message);


      while (this.messages_list.length > 50)
        this.messages_list.pop();
    },
    connection_opened: function(event) {
      this.connection.send(msgpack.encode({
        "Action": "SayHello",
        "Name": this.username
      }));

      localStorage.setItem("username", this.username);
      localStorage.setItem("host", this.host);
    },
    connection_closed: function(event) {
      this.init = false;
    },
    getRandomInt: function(min, max) {
      return Math.floor(Math.random() * (max - min + 1) + min);
    },
    initialize: function() {
      this.init = true;
      this.connection = new WebSocket(this.host);
      this.connection.binaryType = 'arraybuffer';
      this.connection.onclose = this.connection_closed;
      this.connection.onopen = this.connection_opened;
      this.connection.onmessage = this.message_received;
    }
  },
  mounted() {
    if(localStorage.getItem("username"))
      this.username = localStorage.getItem("username");

    if(localStorage.getItem("host"))
      this.host = localStorage.getItem("host");
  }
}
</script>
