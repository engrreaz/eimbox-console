<?php
/**
 * EIMBox REST API - Table Row Pull Synchronization Endpoint
 * Route: GET /api/v1/sync/table-pull.php
 * Params: ?table={table_name}&sccode={sccode}&since={timestamp}&session={sessionyear}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate Request
$user = api_authenticate_request();
$sccode = (int)($user['sccode'] ?? 0);

$conn = api_get_db_connection();
$tableName = preg_replace('/[^a-zA-Z0-9_]/', '', trim($_GET['table'] ?? ''));
$activeSccode = isset($_GET['sccode']) && (int)$_GET['sccode'] > 0 ? (int)$_GET['sccode'] : $sccode;
$since = trim($_GET['since'] ?? '');
$session = trim($_GET['session'] ?? '');

$allowedTables = [
    'gpa', 'examroutine', 'examlist', 'subjects', 'subsetup', 'areas', 
    'students', 'sessioninfo', 'stmark', 'stattnd', 'stfinance', 'stpr', 
    'slots', 'teacher', 'classschedule', 'clsroutine', 'syllabus', 
    'lesson_tracking', 'sessionyear', 'settings', 'scinfo', 'ben_address'
];

if (empty($tableName) || !in_array($tableName, $allowedTables)) {
    api_send_response(422, false, "Invalid or unauthorized table: " . htmlspecialchars($tableName));
}

// Build query based on table schema peculiarities
$hasSccode = true;
$hasModified = true;
$hasSession = false;

// Tables with global sccode=0 fallback
$supportsGlobal = in_array($tableName, ['gpa', 'subjects', 'examlist', 'slots', 'settings', 'classschedule']);

$where = [];
$params = [];
$types = "";

if ($supportsGlobal) {
    $where[] = "(sccode = ? OR sccode = 0)";
    $params[] = $activeSccode;
    $types .= "i";
} else {
    $where[] = "sccode = ?";
    $params[] = $activeSccode;
    $types .= "i";
}

if (!empty($session) && in_array($tableName, ['examroutine', 'examlist', 'areas', 'sessioninfo', 'subsetup', 'stmark', 'stattnd', 'classschedule', 'clsroutine', 'syllabus', 'lesson_tracking'])) {
    $where[] = "(sessionyear = ? OR sessionyear IS NULL OR sessionyear = '')";
    $params[] = $session;
    $types .= "s";
}

if (!empty($since) && strtotime($since)) {
    $where[] = "(modifieddate >= ? OR modifieddate IS NULL)";
    $params[] = $since;
    $types .= "s";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$sql = "SELECT * FROM `{$tableName}` {$whereClause} ORDER BY id ASC LIMIT 5000";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();

api_send_response(200, true, "Table rows pulled successfully.", [
    'table' => $tableName,
    'sccode' => $activeSccode,
    'count' => count($rows),
    'rows' => $rows,
    'server_time' => date('Y-m-d H:i:s')
]);
