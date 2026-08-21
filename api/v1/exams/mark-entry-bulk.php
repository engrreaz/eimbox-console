<?php
/**
 * EIMBox REST API — Bulk Marks Entry Endpoint (IDE Grid Fast Entry)
 * Route: POST /api/v1/exams/mark-entry-bulk.php
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

// Authenticate Bearer Token
$user = authenticate_token($conn);

$input = get_api_input();

$sccode = intval($input['sccode'] ?? $user['sccode'] ?? 0);
$sessionyear = intval($input['sessionyear'] ?? date('Y'));
$exam = trim($input['exam'] ?? '');
$slot = trim($input['slot'] ?? 'School');
$classname = trim($input['classname'] ?? '');
$sectionname = trim($input['sectionname'] ?? '');
$subcode = intval($input['subcode'] ?? $input['subject'] ?? 0);
$fullmark = intval($input['fullmark'] ?? 100);
$marks = $input['marks'] ?? [];
$entryby = $user['profilename'] ?? $user['username'] ?? $user['email'] ?? 'Studio Marks IDE';

if ($sccode <= 0 || empty($exam) || empty($classname) || $subcode <= 0) {
    api_response('error', 'sccode, exam, classname and subcode are required.', null, 400);
}

if (!is_array($marks) || empty($marks)) {
    api_response('error', 'Marks array cannot be empty.', null, 400);
}

/**
 * Grade Calculator Helper
 */
function calculate_grade($on100) {
    if ($on100 >= 80) return ['gp' => 5.0, 'gl' => 'A+'];
    if ($on100 >= 70) return ['gp' => 4.0, 'gl' => 'A'];
    if ($on100 >= 60) return ['gp' => 3.5, 'gl' => 'A-'];
    if ($on100 >= 50) return ['gp' => 3.0, 'gl' => 'B'];
    if ($on100 >= 40) return ['gp' => 2.0, 'gl' => 'C'];
    if ($on100 >= 33) return ['gp' => 1.0, 'gl' => 'D'];
    return ['gp' => 0.0, 'gl' => 'F'];
}

$conn->begin_transaction();

try {
    $processedCount = 0;

    foreach ($marks as $m) {
        $stid = trim($m['stid'] ?? '');
        if (empty($stid)) continue;

        $subj = floatval($m['subj'] ?? 0);
        $obj = floatval($m['obj'] ?? 0);
        $pra = floatval($m['pra'] ?? $m['prac'] ?? 0);
        $ca = floatval($m['ca'] ?? 0);

        $markobt = $subj + $obj + $pra + $ca;
        $on100 = $fullmark > 0 ? round(($markobt / $fullmark) * 100, 2) : $markobt;
        $grade = calculate_grade($on100);
        $gp = $grade['gp'];
        $gl = $grade['gl'];

        // Check if existing record
        $chkStmt = $conn->prepare("SELECT id FROM stmark 
            WHERE sccode = ? AND sessionyear = ? AND exam = ? AND classname = ? AND sectionname = ? AND subject = ? AND stid = ? 
            LIMIT 1");
        $chkStmt->bind_param('iisssis', $sccode, $sessionyear, $exam, $classname, $sectionname, $subcode, $stid);
        $chkStmt->execute();
        $chkRes = $chkStmt->get_result()->fetch_assoc();
        $chkStmt->close();

        if ($chkRes) {
            // Update
            $upStmt = $conn->prepare("UPDATE stmark SET 
                slot = ?, fullmark = ?, subj = ?, obj = ?, pra = ?, ca = ?, markobt = ?, on100 = ?, gp = ?, gl = ?, modifieddate = NOW(), entryby = ?
                WHERE id = ?");
            $upStmt->bind_param('sdddddddsdsi', 
                $slot, $fullmark, $subj, $obj, $pra, $ca, $markobt, $on100, $gp, $gl, $entryby, $chkRes['id']
            );
            $upStmt->execute();
            $upStmt->close();
        } else {
            // Insert
            $insStmt = $conn->prepare("INSERT INTO stmark (
                slot, sessionyear, sccode, exam, classname, sectionname, subject, fullmark, stid, subj, obj, pra, ca, markobt, on100, gp, gl, entrydate, entryby
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
            $insStmt->bind_param('siisssiisddddddsss', 
                $slot, $sessionyear, $sccode, $exam, $classname, $sectionname, $subcode, $fullmark, $stid, 
                $subj, $obj, $pra, $ca, $markobt, $on100, $gp, $gl, $entryby
            );
            $insStmt->execute();
            $insStmt->close();
        }

        $processedCount++;
    }

    $conn->commit();

    api_response('success', 'Bulk marks processed successfully.', [
        'processed_count' => $processedCount,
        'sccode' => $sccode,
        'sessionyear' => $sessionyear,
        'exam' => $exam,
        'classname' => $classname,
        'sectionname' => $sectionname,
        'subcode' => $subcode
    ]);

} catch (Exception $e) {
    $conn->rollback();
    api_response('error', 'Bulk marks entry failed: ' . $e->getMessage(), null, 500);
}
