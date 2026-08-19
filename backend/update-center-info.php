<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['sccode'])) {
        $sccode = $_SESSION['sccode'];
        $center_code = $_POST['center_code'] ?? '';
        $center_name = $_POST['center_name'] ?? '';
        $ed_board = $_POST['ed_board'] ?? '';

        $stmt = $conn->prepare("UPDATE scinfo SET center_code = ?, center_name = ?, ed_board = ? WHERE sccode = ?");
        if ($stmt) {
            $stmt->bind_param("ssss", $center_code, $center_name, $ed_board, $sccode);
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Center information updated successfully.';
            } else {
                $response['message'] = 'Failed to execute update.';
            }
            $stmt->close();
        } else {
            $response['message'] = 'Failed to prepare statement.';
        }
    }
}

echo json_encode($response);