<?php
require_once '../core/config.php';
require_once '../core/db.php';

header('Content-Type: application/json');

$sccode = $_POST['sccode'] ?? null;
$type = $_POST['type'] ?? '';

if (!$sccode || strlen($sccode) !== 6) {
    echo json_encode(['error' => 'Invalid Institute Code.']);
    exit;
}

$response = [];

switch ($type) {
    case 'get_initial_data':
        // 1. Get Slots
        $stmt = $conn->prepare("SELECT slotname FROM slots WHERE sccode = ?");
        $stmt->bind_param("s", $sccode);
        $stmt->execute();
        $result = $stmt->get_result();
        $slots = [];
        while ($row = $result->fetch_assoc()) {
            $slots[] = $row['slotname'];
        }
        $response['slots'] = $slots;

        // 2. Get Sessions
        $stmt = $conn->prepare("SELECT syear FROM sessionyear WHERE active=1 AND sccode = ?");
        $stmt->bind_param("s", $sccode);
        $stmt->execute();
        $result = $stmt->get_result();
        $sessions = [];
        while ($row = $result->fetch_assoc()) {
            $sessions[] = $row['syear'];
        }
        $response['sessions'] = $sessions;
        break;

    case 'get_exams':
        $sessionyear = $_POST['sessionyear'] ?? null;
        $slot = $_POST['slot'] ?? null;
        if (!$sessionyear || !$slot) {
            echo json_encode(['error' => 'Session and Slot are required.']);
            exit;
        }

        $stmt = $conn->prepare("SELECT DISTINCT examtitle FROM examlist WHERE sccode = ? AND sessionyear = ? AND slot = ?");
        $stmt->bind_param("sss", $sccode, $sessionyear, $slot);
        $stmt->execute();
        $result = $stmt->get_result();
        $exams = [];
        while ($row = $result->fetch_assoc()) {
            $exams[] = $row['examtitle'];
        }
        $response['exams'] = $exams;
        break;

    case 'get_classes':
        $sessionyear = $_POST['sessionyear'] ?? null;
        $slot = $_POST['slot'] ?? null;
        if (!$sessionyear || !$slot) {
            echo json_encode(['error' => 'Session and Slot are required.']);
            exit;
        }

        $stmt = $conn->prepare("SELECT DISTINCT areaname FROM areas WHERE sccode = ? AND sessionyear = ? AND slot = ? ORDER BY idno");
        $stmt->bind_param("sss", $sccode, $sessionyear, $slot);
        $stmt->execute();
        $result = $stmt->get_result();
        $classes = [];
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row['areaname'];
        }
        $response['classes'] = $classes;
        break;

    case 'get_sections':
        $sessionyear = $_POST['sessionyear'] ?? null;
        $slot = $_POST['slot'] ?? null;
        $classname = $_POST['classname'] ?? null;
        if (!$sessionyear || !$slot || !$classname) {
            echo json_encode(['error' => 'Session, Slot, and Class are required.']);
            exit;
        }

        $stmt = $conn->prepare("SELECT DISTINCT subarea FROM areas WHERE sccode = ? AND sessionyear = ? AND slot = ? AND areaname = ? ");
        $stmt->bind_param("ssss", $sccode, $sessionyear, $slot, $classname);
        $stmt->execute();
        $result = $stmt->get_result();
        $sections = [];
        while ($row = $result->fetch_assoc()) {
            $sections[] = $row['subarea'];
        }
        $response['sections'] = $sections;
        break;

    default:
        $response['error'] = 'Invalid request type.';
        break;
}

echo json_encode($response);

?>