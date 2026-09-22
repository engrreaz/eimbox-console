<?php
require_once 'core/config.php';
require_once 'core/db.php';

// Check if is_locked exists
$check = $conn->query("SHOW COLUMNS FROM cashbook LIKE 'is_locked'");
if ($check && $check->num_rows === 0) {
    $sql1 = "ALTER TABLE cashbook ADD COLUMN is_locked TINYINT NOT NULL DEFAULT 0 AFTER status";
    if ($conn->query($sql1)) {
        echo "Added is_locked column successfully.\n";
    } else {
        echo "Error adding is_locked: " . $conn->error . "\n";
    }
} else {
    echo "is_locked column already exists.\n";
}

// Check if locked_at exists
$check2 = $conn->query("SHOW COLUMNS FROM cashbook LIKE 'locked_at'");
if ($check2 && $check2->num_rows === 0) {
    $sql2 = "ALTER TABLE cashbook ADD COLUMN locked_at DATETIME DEFAULT NULL AFTER is_locked";
    if ($conn->query($sql2)) {
        echo "Added locked_at column successfully.\n";
    } else {
        echo "Error adding locked_at: " . $conn->error . "\n";
    }
} else {
    echo "locked_at column already exists.\n";
}
