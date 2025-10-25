import { messaging, getToken, onMessage } from "./firebase.js";

// Register Service Worker
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('./firebase-messaging-sw.js')
    .then(registration => console.log('SW registered:', registration.scope))
    .catch(err => console.error('SW registration failed:', err));
}

// Request permission & get FCM token
async function requestPermission(userId) {
  const permission = await Notification.requestPermission();
  if (permission === "granted") {
    const registration = await navigator.serviceWorker.ready;
    const token = await getToken(messaging, {
      vapidKey: "BPgA04Spud38OURxlO1WoWzwQBVcXs9OAUdsmPSxa4pdqfQvLPsEKvwZ2YWZuqufdwWBnrD9glZSzVT-nSp5jbs",
      serviceWorkerRegistration: registration
    });
    console.log("FCM Token:", token);

    // Example: send token to backend
    await fetch("/backend/save-token.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ userId, token })
    });
  } else {
    console.log("Notification permission denied");
  }
}

window.requestPermission = requestPermission;

// Foreground notifications
onMessage(messaging, (payload) => {
  console.log("Foreground message:", payload);
  alert(`${payload.notification.title} - ${payload.notification.body}`);
});