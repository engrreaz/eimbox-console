<?php
/**
 * display_teacher_report.php
 *
 * Fetches data from `analytics_teacher_performance` and renders it as a custom HTML block.
 * This is called via AJAX from the main report page.
 */

require_once '../core/config.php';
require_once '../core/db.php';

$dataset_id = filter_input(INPUT_GET, 'dataset_id', FILTER_VALIDATE_INT);
$sccode = $_SESSION['sccode'] ?? null;

if (!$dataset_id || !$sccode) {
    echo '<div class="alert alert-danger">Invalid Parameters.</div>';
    exit;
}

try {
    // Fetch the data, ordered by TIA score
    $stmt = $conn->prepare("
        SELECT
            t.tname, t.position, atp.*
        FROM analytics_teacher_performance AS atp
        JOIN teacher AS t ON atp.tid = t.tid AND atp.sccode = t.sccode
        WHERE atp.dataset_id = ? AND atp.sccode = ?
        ORDER BY atp.teacher_rank ASC, atp.teacher_impact_adjustment DESC
    ");
    if (!$stmt) {
        throw new Exception("Database query preparation failed: " . $conn->error);
    }
    $stmt->bind_param("is", $dataset_id, $sccode);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($data)) {
        echo '<div class="alert alert-warning">No teacher performance data available for this report.</div>';
        exit;
    }

    // --- Custom HTML Rendering Starts Here ---
    echo "<div class='row g-4'>";

    foreach ($data as $teacher) {
        $rank = $teacher['teacher_rank'] ?? 'N/A';
        $avg_marks = (float)($teacher['overall_avg_marks'] ?? 0);
        $pass_rate = (float)($teacher['overall_pass_rate'] ?? 0);
        $tpi = (float)($teacher['tpi'] ?? 0);
        $tia = (float)($teacher['tia'] ?? 0);
        $tci = (float)($teacher['tci_score'] ?? 0);
        $tsi = (float)($teacher['tsi_score'] ?? 0);

        $tci_color = $tci > 0 ? 'text-success' : 'text-danger';
        $tsi_color = $tsi > 0 ? 'text-success' : 'text-danger';

        echo "
        <div class='col-md-6'>
            <div class='card h-100 shadow-sm'>
                <div class='card-header d-flex justify-content-between align-items-center'>
                    <div>
                        <h6 class='card-title mb-0'>{$teacher['tname']}</h6>
                        <small class='text-muted'>{$teacher['position']}</small>
                    </div>
                    <span class='badge bg-primary rounded-pill fs-5'>Rank: {$rank}</span>
                </div>
                <div class='card-body'>
                    <div class='row text-center'>
                        <div class='col'><strong>TPI</strong><br>{$tpi}</div>
                        <div class='col'><strong>TIA</strong><br><span class='fw-bold fs-5'>{$tia}</span></div>
                        <div class='col {$tci_color}'><strong>TCI</strong><br>{$tci}</div>
                        <div class='col {$tsi_color}'><strong>TSI</strong><br>{$tsi}</div>
                    </div>
                    <hr>
                    <div class='mb-2'>
                        <span>Avg. Marks: " . number_format($avg_marks, 2) . "%</span>
                        <div class='progress' style='height: 10px;'><div class='progress-bar bg-info' style='width: {$avg_marks}%'></div></div>
                    </div>
                    <div>
                        <span>Pass Rate: " . number_format($pass_rate, 2) . "%</span>
                        <div class='progress' style='height: 10px;'><div class='progress-bar bg-success' style='width: {$pass_rate}%'></div></div>
                    </div>
                </div>
            </div>
        </div>
        ";
    }

    echo "</div>"; // End .row

} catch (Exception $e) {
    echo '<div class="alert alert-danger">An error occurred: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>