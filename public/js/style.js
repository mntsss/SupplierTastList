var activeurl = window.location;
$('a[href="'+activeurl+'"]').parent('li').addClass('active');



function togglePegMeniu(id)
{
  $('#'+id).fadeIn("slow");
}

'use strict';

var API_KEY = window.GoogleSamples.Config.gcmAPIKey;
var GCM_ENDPOINT = 'https://android.googleapis.com/gcm/send';

var curlCommandDiv = document.querySelector('.js-curl-command');
var isPushEnabled = false;

var processStatus = function (response) {
    // status "0" to handle local files fetching (e.g. Cordova/Phonegap etc.)
    if (response.status === 200 || response.status === 0) {
        return Promise.resolve(response)
    } else {
        return Promise.reject(alert(response.statusText))
    }
};
var parseJson = function (response) {
    return response.json();
};


function endpointWorkaround(pushSubscription) {

  if (pushSubscription.endpoint.indexOf('https://android.googleapis.com/gcm/send') !== 0) {
    return pushSubscription.endpoint;
  }

  var mergedEndpoint = pushSubscription.endpoint;

  if (pushSubscription.subscriptionId &&
    pushSubscription.endpoint.indexOf(pushSubscription.subscriptionId) === -1) {

    mergedEndpoint = pushSubscription.endpoint + '/' +
      pushSubscription.subscriptionId;
  }
  return mergedEndpoint;
}

function sendSubscriptionToServer(subscription) {

  var mergedEndpoint = endpointWorkaround(subscription);

  var endpointSections = mergedEndpoint.split('/');
  var subscriptionId = endpointSections[endpointSections.length - 1];
  updateUserSubscription(subscriptionId);
}



function unsubscribe() {
  var pushButton = document.querySelector('.js-on-off-notifications');
  pushButton.disabled = true;


  navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {

    serviceWorkerRegistration.pushManager.getSubscription().then(
      function(pushSubscription) {

        if (!pushSubscription) {

          isPushEnabled = false;
          pushButton.disabled = false;
          pushButton.textContent = 'Įjungti notifikacijas';
          return;
        }

        updateUserSubscription();
        pushSubscription.unsubscribe().then(function() {
          pushButton.disabled = false;
          pushButton.textContent = 'Įjungti notifikacijas';
          isPushEnabled = false;
        }).catch(function(e) {

          console.log('Unsubscription error: ', e);
          pushButton.disabled = false;
        });
      }).catch(function(e) {
        console.log('Error thrown while unsubscribing from ' +
          'push messaging.', e);
      });
  });
}

function subscribe() {

  var pushButton = document.querySelector('.js-on-off-notifications');
  pushButton.disabled = true;

  navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
    serviceWorkerRegistration.pushManager.subscribe({userVisibleOnly: true})
      .then(function(subscription) {

        isPushEnabled = true;
        pushButton.textContent = 'Išjungti notifikacijas';
        pushButton.disabled = false;

        return sendSubscriptionToServer(subscription);
      })
      .catch(function(e) {
        if (Notification.permission === 'denied') {

          console.log('Permission for Notifications was denied');
          pushButton.disabled = true;
        } else {

          console.log('Unable to subscribe to push.', e);
          pushButton.disabled = false;
          pushButton.textContent = 'Įjungti notifikacijas';
        }
      });
  });
}


function initialiseState() {

  if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
    console.log('Notifications aren\'t supported.');
    return;
  }

  if (Notification.permission === 'denied') {
    console.log('The user has blocked notifications.');
    return;
  }


  if (!('PushManager' in window)) {
    console.log('Push messaging isn\'t supported.');
    return;
  }

  navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {

    serviceWorkerRegistration.pushManager.getSubscription()
      .then(function(subscription) {

        var pushButton = document.querySelector('.js-on-off-notifications');
        pushButton.disabled = false;

        if (!subscription) {

          return;
        }


        sendSubscriptionToServer(subscription);

        pushButton.textContent = 'Išjungti notifikacijas';
        isPushEnabled = true;
      })
      .catch(function(err) {
        console.log('Error during getSubscription()', err);
      });
  });
}

window.addEventListener('load', function() {
  var pushButton = document.querySelector('.js-on-off-notifications');
  pushButton.addEventListener('click', function() {
    if (isPushEnabled) {
      unsubscribe();
    } else {
      subscribe();
    }
  });

  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('./service-worker.js')
    .then(initialiseState);
  } else {
    console.log('Browser doesnt support service-worker...');
  }
});
