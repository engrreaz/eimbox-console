// save-token.php
<?php
require_once('../core/config.php');
require_once('../core/db.php');

$data = json_decode(file_get_contents("php://input"), true);
$userId = $data["userId"];
$token = $data["token"];

$conn->query("REPLACE INTO user_tokens (user_id, token) VALUES ('$userId', '$token')");

echo json_encode(["status" => "success"]);
?>
