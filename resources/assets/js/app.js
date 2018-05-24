
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

Echo.channel('chat')
  .listen('ChatMessageSent', (e) => {
    if(user.role === 'Tiekejas' || user.name === e.order.whoAdded){
      loadChatMessages(e.order.id);
      if (!document.hasFocus()) {
        sendChatNotification();
      }
    }
  });
