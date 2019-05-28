<template>
  <div id="nescss">
    <div class="container">
      <link href="https://unpkg.com/nes.css@2.1.0/css/nes.min.css" rel="stylesheet" />
      <link href="https://fonts.googleapis.com/css?family=Press+Start+2P" rel="stylesheet">
      <nuxt />
    </div>
  </div>
</template>

<style>
  html, body, pre, code, kbd, samp {
    font-family: "Press Start 2P";
  }
  body {
    padding: 0 2rem;
    margin: 0 2rem;
  }

  #nescss > .container {
    max-width: 980px;
    margin: 0 auto;
    margin-top: 50px;
  }

  .main-content {
    margin-bottom: 4rem;
  }

  @media screen and (max-width: 768px) {
    body {
      margin: 2rem 0.5rem;
      padding: 0;
    }

    header > .container {
      margin: 0 0.5rem;
    }

    .message-list > .message > .nes-balloon {
      max-width: 60%;
    }
  }

  @media screen and (max-width: 580px) {
    #nescss > .container {
      margin-top: 20px;
    }
  }


  /* Balloons sample */
  section.message-list {
    display: flex;
    flex-direction: column;
  }
  .message-list > .message {
    display: flex;
    margin-top: 2rem;
  }
  .message-list > .message > .nes-balloon {
    max-width: 650px;
  }
  .message-list > .message i {
    align-self: flex-end;
  }
  .message-list > .message.-left {
    align-self: flex-start;
  }
  .message-list > .message.-right {
    align-self: flex-end;
  }
  .message-list > .message.-left i {
    margin-right: 2rem;
  }
  .message-list > .message.-right i {
    margin-left: 2rem;
  }

</style>
