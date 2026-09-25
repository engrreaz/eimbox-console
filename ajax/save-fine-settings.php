<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

try {
    $sccode = (int)($_POST['sccode'] ?? $_SESSION['sccode'] ?? 0);
    if ($sccode <= 0) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Session expired or invalid institution code. Please log in again.']);
        exit;
    }

$sessionyear = trim($_POST['sessionyear'] ?? date('Y'));
$slot = trim($_POST['slot'] ?? 'School');
$absent_rate = floatval($_POST['absent_rate'] ?? 0);
$bunk_rate = floatval($_POST['bunk_rate'] ?? 0);
$bunk_rule_type = in_array($_POST['bunk_rule_type'] ?? '', ['flat_daily', 'per_period']) ? $_POST['bunk_rule_type'] : 'flat_daily';
$posting_mode = in_array($_POST['posting_mode'] ?? '', ['daily', 'monthly', 'manual']) ? $_POST['posting_mode'] : 'manual';
$daily_run_time = trim($_POST['daily_run_time'] ?? '18:00:00');
$monthly_run_day = max(1, min(28, intval($_POST['monthly_run_day'] ?? 1)));
$itemcode = trim($_POST['itemcode'] ?? '');
if (empty($itemcode) || $itemcode === 'FINE01') {
    $itemcode = uniqid();
}
$particulareng = trim($_POST['particulareng'] ?? 'Absence / Bunk Fine');
$particularben = trim($_POST['particularben'] ?? 'Absence / Bunk Fine');
$class_rates = $_POST['class_rates'] ?? [];

$updated_by = $_SESSION['user_id'] ?? 'Admin';

// 1. Ensure Table Exists
$conn->query("CREATE TABLE IF NOT EXISTS `fine_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `sessionyear` varchar(10) NOT NULL,
  `slot` varchar(50) DEFAULT 'School',
  `scope` enum('global','class') NOT NULL DEFAULT 'global',
  `classname` varchar(50) DEFAULT NULL,
  `absent_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bunk_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bunk_rule_type` enum('flat_daily','per_period') NOT NULL DEFAULT 'flat_daily',
  `posting_mode` enum('daily','monthly','manual') NOT NULL DEFAULT 'manual',
  `daily_run_time` time DEFAULT '18:00:00',
  `monthly_run_day` tinyint(2) DEFAULT 1,
  `itemcode` varchar(30) DEFAULT 'FINE01',
  `particulareng` varchar(150) DEFAULT 'Absence / Bunk Fine',
  `particularben` varchar(200) DEFAULT 'Absence / Bunk Fine',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `last_generated_date` date DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_fine_rule` (`sccode`, `sessionyear`, `slot`, `scope`, `classname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci");

// 2. Save Global Rule
$chkStmt = $conn->prepare("SELECT id FROM fine_settings WHERE sccode = ? AND sessionyear = ? AND slot = ? AND scope = 'global' LIMIT 1");
$chkStmt->bind_param('iss', $sccode, $sessionyear, $slot);
$chkStmt->execute();
$chkRes = $chkStmt->get_result();

if ($row = $chkRes->fetch_assoc()) {
    $upStmt = $conn->prepare("UPDATE fine_settings SET 
        absent_rate = ?, bunk_rate = ?, bunk_rule_type = ?, posting_mode = ?, 
        daily_run_time = ?, monthly_run_day = ?, itemcode = ?, particulareng = ?, 
        particularben = ?, updated_by = ?, updated_at = NOW() 
        WHERE id = ? AND sccode = ?");
    $upStmt->bind_param('ddsssissssii', 
        $absent_rate, $bunk_rate, $bunk_rule_type, $posting_mode, 
        $daily_run_time, $monthly_run_day, $itemcode, $particulareng, 
        $particularben, $updated_by, $row['id'], $sccode);
    $upStmt->execute();
    $upStmt->close();
} else {
    $inStmt = $conn->prepare("INSERT INTO fine_settings 
        (sccode, sessionyear, slot, scope, classname, absent_rate, bunk_rate, bunk_rule_type, 
         posting_mode, daily_run_time, monthly_run_day, itemcode, particulareng, particularben, updated_by)
        VALUES (?, ?, ?, 'global', NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $inStmt->bind_param('issddsssissss', 
        $sccode, $sessionyear, $slot, $absent_rate, $bunk_rate, $bunk_rule_type, 
        $posting_mode, $daily_run_time, $monthly_run_day, $itemcode, $particulareng, 
        $particularben, $updated_by);
    $inStmt->execute();
    $inStmt->close();
}
$chkStmt->close();

// 3. Save / Update Class-wise Custom Rates
if (is_array($class_rates)) {
    foreach ($class_rates as $cName => $cData) {
        $cName = trim($cName);
        if ($cName === '') continue;

        $isCustom = !empty($cData['enabled']) ? 1 : 0;
        $cAbsent = floatval($cData['absent_rate'] ?? 0);
        $cBunk = floatval($cData['bunk_rate'] ?? 0);

        if ($isCustom === 1) {
            $cChk = $conn->prepare("SELECT id FROM fine_settings WHERE sccode = ? AND sessionyear = ? AND slot = ? AND scope = 'class' AND classname = ? LIMIT 1");
            $cChk->bind_param('isss', $sccode, $sessionyear, $slot, $cName);
            $cChk->execute();
            $cRes = $cChk->get_result();

            if ($cRow = $cRes->fetch_assoc()) {
                $cUp = $conn->prepare("UPDATE fine_settings SET absent_rate = ?, bunk_rate = ?, status = 1, updated_by = ?, updated_at = NOW() WHERE id = ? AND sccode = ?");
                $cUp->bind_param('ddsii', $cAbsent, $cBunk, $updated_by, $cRow['id'], $sccode);
                $cUp->execute();
                $cUp->close();
            } else {
                $cIn = $conn->prepare("INSERT INTO fine_settings 
                    (sccode, sessionyear, slot, scope, classname, absent_rate, bunk_rate, status, updated_by)
                    VALUES (?, ?, ?, 'class', ?, ?, ?, 1, ?)");
                $cIn->bind_param('isssdds', $sccode, $sessionyear, $slot, $cName, $cAbsent, $cBunk, $updated_by);
                $cIn->execute();
                $cIn->close();
            }
            $cChk->close();
        } else {
            $delStmt = $conn->prepare("DELETE FROM fine_settings WHERE sccode = ? AND sessionyear = ? AND slot = ? AND scope = 'class' AND classname = ?");
            $delStmt->bind_param('isss', $sccode, $sessionyear, $slot, $cName);
            $delStmt->execute();
            $delStmt->close();
        }
    }
}

    ob_clean();
    echo json_encode([
        'status' => 'success',
        'message' => 'Fine policy and settings have been saved successfully.'
    ]);
    exit;

} catch (Throwable $e) {
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}
