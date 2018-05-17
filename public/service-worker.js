var body = '';
self.addEventListener('push', function(event) {


  var title = 'Tiekimo atnaujinimas';

  var icon = '/media/logo.png';
  var tag = 'tiekimas-update-notification';

     event.waitUntil(
       self.registration.showNotification(title, {
         body: body,
         icon: icon,
         tag: tag,
         vibrate: [200, 100, 200, 100, 200, 100, 200],
         sound: 'default',
       })
     );
});

self.addEventListener('message', function (evt) {
  body = evt.data['action'];
  console.log('postMessage received', body);
})

self.addEventListener('notificationclick', function(event) {

  event.notification.close();

  event.waitUntil(clients.matchAll({
    type: 'window'
  }).then(function(clientList) {
    for (var i = 0; i < clientList.length; i++) {
      var client = clientList[i];
      if (client.url === '/' && 'focus' in client) {
        return client.focus();
      }
    }
    if (clients.openWindow) {
      return clients.openWindow('/');
    }
  }));
});
