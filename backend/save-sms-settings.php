<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$valid_columns = [
    'sms_gateway',
    'sms_in',
    'sms_out',
    'sms_absent',
    'sms_payment',
    'sms_dues',
    'sms_month_report'
];

$blockbox = $_POST['blockbox'] ?? '';
$sms_settings_raw = $_POST['sms_settings'] ?? '{}';

if (!in_array($blockbox, $valid_columns)) {
    echo "<span class='text-danger'>Invalid configuration target!</span>";
    exit;
}

// Decode to validate JSON structure
$decoded = json_decode($sms_settings_raw, true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
    echo "<span class='text-danger'>Invalid JSON data!</span>";
    exit;
}

// Re-encode cleanly (JSON_UNESCAPED_UNICODE prevents Bengali characters from turning into unicode escapes)
$clean_json = json_encode($decoded, JSON_UNESCAPED_UNICODE);
$escaped_json = mysqli_real_escape_string($conn, $clean_json);

$upd = "UPDATE scinfo SET $blockbox='$escaped_json' WHERE sccode='$sccode'";
if ($conn->query($upd)) {
    echo "<span class='text-success fw-bold'><i class='bi bi-check-circle'></i> Saved successfully</span>";
} else {
    echo "<span class='text-danger'>Failed to update settings!</span>";
}