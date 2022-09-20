importScripts("https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js");
importScripts(
  "https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js"
);
firebase.initializeApp({
  /* apiKey: "AIzaSyBQIgeM53O54ZUbAMIcC6Cx1ezuhoKQcWI",
  authDomain: "wassalley-94b5a.firebaseapp.com",
  projectId: "wassalley-94b5a",
  storageBucket: "wassalley-94b5a.appspot.com",
  messagingSenderId: "304607168594",
  appId: "1:304607168594:web:10ca2abaf305d90217378b", */
  apiKey: "AIzaSyBQIgeM53O54ZUbAMIcC6Cx1ezuhoKQcWI",
  authDomain: "wassalley-94b5a.firebaseapp.com",
  projectId: "wassalley-94b5a",
  storageBucket: "wassalley-94b5a.appspot.com",
  messagingSenderId: "304607168594",
  appId: "1:304607168594:web:10ca2abaf305d90217378b",
  measurementId: "G-H3KQTSDMKZ"
});
const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function (payload) {
  console.log(payload)
  return self.registration.showNotification(payload.data.title, {
    body: payload.data.body ? payload.data.body : "",
    icon: payload.data.icon ? payload.data.icon : "",
  });
});
