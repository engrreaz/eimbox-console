<?php
function db_connect() {
    static $conn;

    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_errno) {
            error_log("DB connection failed: " . $conn->connect_error);
            die("Database connection problem, please try again later."); 
        }

        $conn->set_charset('utf8mb4');
    }

    return $conn;
}

$conn = db_connect();
