<?php
$serverKey = "BPgA04Spud38OURxlO1WoWzwQBVcXs9OAUdsmPSxa4pdqfQvLPsEKvwZ2YWZuqufdwWBnrD9glZSzVT-nSp5jbs"; // Firebase project settings -> Cloud Messaging -> Server key
$token = "TARGET_USER_TOKEN";   // DB থেকে টেনে আনো

$notification = [
  "title" => "Hello",
  "body" => "This is a test notification!"
];

$data = [
  "to" => $token,
  "notification" => $notification
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: key=" . $serverKey,
  "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$result = curl_exec($ch);
curl_close($ch);

echo $result;
?>