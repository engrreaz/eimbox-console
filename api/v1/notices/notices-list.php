<?php
/**
 * EIMBox REST API — Notice Board & Announcements Endpoint
 * Route: GET /api/v1/notices/notices-list.php
 * Query Params: ?sccode={sccode}&limit={limit}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$limit = intval($_GET['limit'] ?? 20);
if ($limit <= 0 || $limit > 100) $limit = 20;

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

$stmt = $conn->prepare("SELECT id, title, descrip, entryby, entrytime 
FROM notice 
WHERE sccode = ? 
ORDER BY id DESC LIMIT ?");
$stmt->bind_param('ii', $sccode, $limit);
$stmt->execute();
$res = $stmt->get_result();

$notices = [];
while ($row = $res->fetch_assoc()) {
    $notices[] = [
        'id'          => intval($row['id']),
        'title'       => $row['title'],
        // SQLite-compatible column names (used by desktop sync engine)
        'descrip'     => $row['descrip'] ?? '',
        'entryby'     => $row['entryby'] ?? '',
        'entrytime'   => $row['entrytime'] ?? null,
        // Legacy / web-dashboard-friendly aliases
        'description' => $row['descrip'] ?? '',
        'published_by' => $row['entryby'] ?? '',
        'date'        => isset($row['entrytime']) ? date('Y-m-d', strtotime($row['entrytime'])) : null,
        'timestamp'   => $row['entrytime'] ?? null,
    ];
}
$stmt->close();


api_response('success', 'Notices loaded successfully.', [
    'sccode' => $sccode,
    'total_count' => count($notices),
    'notices' => $notices
]);
