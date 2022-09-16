/* importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js'); */
firebase.initializeApp({apiKey: "AIzaSyBtcGSJHT2oMj4zco-WVgc-e_vsZGSm_XM",authDomain: "bee-arena-notifications.firebaseapp.com",projectId: "bee-arena-notifications",storageBucket: "bee-arena-notifications.appspot.com", messagingSenderId: "282217238269", appId: "1:282217238269:web:126c4a00a8728ff475b894"});
const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function (payload) { return self.registration.showNotification(payload.data.title, { body: payload.data.body ? payload.data.body : '', icon: payload.data.icon ? payload.data.icon : '' }); });
