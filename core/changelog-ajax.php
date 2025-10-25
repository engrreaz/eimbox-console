<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

// -------------------- Badge Color Mapping --------------------
function badgeColor($action)
{
    return match (strtolower($action)) {
        'implement' => 'success',
        'update' => 'info',
        'bug_fix' => 'danger',
        'refactor' => 'warning',
        'optimize' => 'primary',
        'security_patch' => 'dark',
        default => 'secondary',
    };
}

// -------------------- Get offset --------------------
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = 20;

// -------------------- Fetch Timeline Data --------------------
$sql = "
SELECT dt.*, mm.nav_title, mm.module_name
FROM dev_timeline dt
LEFT JOIN modulemanager mm ON dt.page_name = mm.related_pages
ORDER BY dt.created_at DESC
LIMIT $limit OFFSET $offset
";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $currentDate = '';
    while ($row = $result->fetch_assoc()) {
        $date = date('Y-m-d', strtotime($row['created_at']));
        if ($currentDate !== $date) {
            $currentDate = $date;
            echo '<li class="timeline-item timeline-item-transparent">
                    <div class="timeline-event">
                        <h5 class="text-muted mb-3 mt-4 text-center">
                            <i class="bx bx-calendar me-1"></i>'
                . date("F d, Y", strtotime($date)) .
                '</h5>
                <hr class="my-2 p-0"/>
                    </div>
                  </li>';
        }

        $color = badgeColor($row['action_type']);
        echo '<li class="timeline-item timeline-item-transparent">
                <span class="timeline-point timeline-point-' . $color . '"></span>
                <div class="timeline-event">
                    <div class="timeline-header mb-2">
                        <h6 class="mb-0">[' . htmlspecialchars($row['nav_title'] ?: $row['page_name']) . '] ' . htmlspecialchars($row['feature_name']) . '</h6>
                        <small class="text-body-secondary">' . timeAgo($row['created_at']) . '</small>
                    </div>
                    <p class="mb-2">' . nl2br(htmlspecialchars($row['description'])) . '</p>
                    <div>
                        <span class="badge bg-' . $color . ' me-1">' . ucfirst($row['action_type']) . '</span>
                        <span class="badge bg-label-secondary me-1">' . ucfirst($row['status']) . '</span>
                        <small class="text-muted ms-2"><i class="bx bx-user"></i> ' . htmlspecialchars($row['logged_by']) . '</small>
                    </div>
                </div>
              </li>';
    }
}
?>