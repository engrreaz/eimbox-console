<?php
/**
 * display_teacher_report.php
 *
 * Fetches data from `analytics_teacher_performance` and renders it as a custom HTML block.
 * This is called via AJAX from the main report page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../core/config.php';
require_once '../core/db.php';

$dataset_id = filter_input(INPUT_GET, 'dataset_id', FILTER_VALIDATE_INT);

if (!$dataset_id) {
    echo '<div class="alert alert-danger">Invalid Dataset ID.</div>';
    exit;
}

try {
    // Fetch the data, ordered by teacher rank and TIA score
    $query = "
        SELECT
            COALESCE(t.tname, 'Unassigned Teacher') AS tname,
            COALESCE(t.position, '') AS position,
            atp.*
        FROM analytics_teacher_performance AS atp
        LEFT JOIN teacher AS t ON atp.tid = t.tid AND (t.sccode = atp.sccode OR t.sccode = '0')
        WHERE atp.dataset_id = ?
        ORDER BY atp.teacher_rank ASC, atp.teacher_impact_adjustment DESC
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database query preparation failed: " . $conn->error);
    }
    $stmt->bind_param("i", $dataset_id);
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
        $tpi = (float)($teacher['teacher_performance_index'] ?? $teacher['tpi'] ?? 0);
        $tia = (float)($teacher['teacher_impact_adjustment'] ?? $teacher['tia'] ?? 0);
        $tci = (float)($teacher['tci_score'] ?? 0);
        $tsi = (float)($teacher['tsi_score'] ?? 0);

        $tci_color = $tci > 0 ? 'text-success' : ($tci < 0 ? 'text-danger' : 'text-muted');
        $tsi_color = $tsi > 0 ? 'text-success' : ($tsi < 0 ? 'text-danger' : 'text-muted');

        $tci_sign = $tci > 0 ? '+' : '';
        $tsi_sign = $tsi > 0 ? '+' : '';

        $tname = htmlspecialchars($teacher['tname']);
        $position = !empty($teacher['position']) ? htmlspecialchars($teacher['position']) : 'Teacher';

        echo "
        <div class='col-md-6 col-lg-6'>
            <div class='card h-100 shadow-sm border'>
                <div class='card-header py-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center'>
                    <div>
                        <h6 class='card-title mb-0 fw-bold text-dark'>{$tname}</h6>
                        <small class='text-muted'>{$position}</small>
                    </div>
                    <span class='badge bg-primary fs-6 px-3 py-1'>Rank: #{$rank}</span>
                </div>
                <div class='card-body p-3'>
                    <div class='row text-center g-2 mb-3'>
                        <div class='col-3'>
                            <div class='p-2 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 11px;'>TPI (Base)</small>
                                <strong class='text-dark fs-6'>" . number_format($tpi, 1) . "</strong>
                            </div>
                        </div>
                        <div class='col-3'>
                            <div class='p-2 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 11px;'>TIA (Final)</small>
                                <strong class='text-primary fs-5'>" . number_format($tia, 1) . "</strong>
                            </div>
                        </div>
                        <div class='col-3'>
                            <div class='p-2 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 11px;'>TCI (Class)</small>
                                <strong class='{$tci_color} fs-6'>{$tci_sign}" . number_format($tci, 1) . "</strong>
                            </div>
                        </div>
                        <div class='col-3'>
                            <div class='p-2 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 11px;'>TSI (Subject)</small>
                                <strong class='{$tsi_color} fs-6'>{$tsi_sign}" . number_format($tsi, 1) . "</strong>
                            </div>
                        </div>
                    </div>

                    <div class='mb-2'>
                        <div class='d-flex justify-content-between mb-1' style='font-size: 11px;'>
                            <span class='text-muted'>Average Marks:</span>
                            <span class='fw-bold text-info'>" . number_format($avg_marks, 2) . "%</span>
                        </div>
                        <div class='progress' style='height: 12px; min-height: 12px;'>
                            <div class='progress-bar bg-info' role='progressbar' style='width: {$avg_marks}%;'></div>
                        </div>
                    </div>

                    <div>
                        <div class='d-flex justify-content-between mb-1' style='font-size: 11px;'>
                            <span class='text-muted'>Pass Rate:</span>
                            <span class='fw-bold text-success'>" . number_format($pass_rate, 2) . "%</span>
                        </div>
                        <div class='progress' style='height: 12px; min-height: 12px;'>
                            <div class='progress-bar bg-success' role='progressbar' style='width: {$pass_rate}%;'></div>
                        </div>
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