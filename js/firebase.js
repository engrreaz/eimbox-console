import { initializeApp } from "https://www.gstatic.com/firebasejs/12.3.0/firebase-app.js";
import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/12.3.0/firebase-messaging.js";

const firebaseConfig = {
  apiKey: "AIzaSyDwgeH-yW1J1vYtBZcJBNob-Sep3NRlgaA",
  authDomain: "eimbox-2ca37.firebaseapp.com",
  projectId: "eimbox-2ca37",
  storageBucket: "eimbox-2ca37.appspot.com",
  messagingSenderId: "854269097307",
  appId: "1:854269097307:web:ca0cc3f2cb6a91655c967e"
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

export { messaging, getToken, onMessage };
