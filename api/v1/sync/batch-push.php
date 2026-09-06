<?php
/**
 * EIMBox REST API — Batch Push Offline Sync Queue Endpoint
 * Route: POST /api/v1/sync/batch-push.php
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

// Authenticate Bearer Token
$user = authenticate_token($conn);

$input = get_api_input();

$sccode = intval($input['sccode'] ?? $user['sccode'] ?? 0);
$transactions = $input['transactions'] ?? [];
$entryby = $user['profilename'] ?? $user['username'] ?? $user['email'] ?? 'Studio Offline Batch Engine';

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

if (!is_array($transactions) || empty($transactions)) {
    api_response('error', 'Transactions queue array cannot be empty.', null, 400);
}

$allowedTables = [
    'gpa', 'examroutine', 'examlist', 'subjects', 'subsetup', 'areas', 
    'students', 'sessioninfo', 'stmark', 'stattnd', 'stfinance', 'stpr', 
    'slots', 'teacher', 'classschedule', 'clsroutine', 'syllabus', 
    'lesson_tracking', 'sessionyear', 'settings', 'scinfo', 'ben_address',
    'tickets', 'ticket_messages', 'events', 'notice', 'notice_category',
    'usersapp', 'permissions_role', 'user_custom_permissions',
    'account_head', 'account_sub_head', 'bankinfo', 'banktrans', 'cashbook',
    'account_head_default', 'account_sub_head_default',
    'app_releases', 'app_roadmap', 'faq_desktop',
    'tabulatingsheet', 'tabulatingsheetex', 'tabulatingsheetpibi'
];

$results = [];
$syncedCount = 0;
$failedCount = 0;

foreach ($transactions as $tx) {
    $queueId = $tx['queue_id'] ?? null;
    $module = strtolower(trim($tx['module_name'] ?? $tx['module'] ?? ''));
    $action = strtoupper(trim($tx['action_type'] ?? $tx['action'] ?? ''));
    $localId = trim($tx['local_id'] ?? '');
    $payload = $tx['payload'] ?? [];

    if (is_string($payload)) {
        $payload = json_decode($payload, true) ?: [];
    }

    $conn->begin_transaction();
    $assignedServerId = null;

    try {
        if ($module === 'finance' || str_contains($action, 'FEE') || str_contains($action, 'PAYMENT')) {
            // Process Fee Collection
            $stid = trim($payload['stid'] ?? '');
            $sessionyear = intval($payload['sessionyear'] ?? date('Y'));
            $items = $payload['items'] ?? [];
            $prdate = trim($payload['prdate'] ?? date('Y-m-d'));
            $collectionMedia = trim($payload['collection_media'] ?? 'Cash');

            if (empty($stid) || empty($items)) {
                throw new Exception('Missing stid or items in fee transaction.');
            }

            // Fetch Student Details
            $stStmt = $conn->prepare("SELECT s.stnameeng, s.stnameben, s.guarmobile, si.classname, si.sectionname, si.rollno 
                FROM students s 
                LEFT JOIN sessioninfo si ON si.stid = s.stid AND si.sccode = s.sccode 
                WHERE s.sccode = ? AND s.stid = ? 
                ORDER BY si.sessionyear DESC LIMIT 1");
            $stStmt->bind_param('is', $sccode, $stid);
            $stStmt->execute();
            $student = $stStmt->get_result()->fetch_assoc();
            $stStmt->close();

            if (!$student) {
                throw new Exception("Student ID {$stid} not found.");
            }

            $cls = $student['classname'] ?? '';
            $sec = $student['sectionname'] ?? '';
            $rollno = intval($student['rollno'] ?? 0);
            $mobile = $student['guarmobile'] ?? '';

            // PR Number
            $prStmt = $conn->prepare("SELECT MAX(prno) AS max_pr FROM stpr WHERE sccode = ?");
            $prStmt->bind_param('i', $sccode);
            $prStmt->execute();
            $prRow = $prStmt->get_result()->fetch_assoc();
            $prStmt->close();
            $prno = ($prRow && $prRow['max_pr'] > 0) ? intval($prRow['max_pr']) + 1 : intval(date('y') . sprintf("%04d", intval(substr($stid, -4)) ?: 1) . '01');

            $totalPaid = 0;
            $partNamesEng = [];
            $partNamesBen = [];

            foreach ($items as $itm) {
                $fid = intval($itm['id'] ?? $itm['itemid'] ?? 0);
                $paidAmt = floatval($itm['paid'] ?? $itm['paid_amount'] ?? 0);
                if ($fid <= 0 || $paidAmt <= 0) continue;

                $fStmt = $conn->prepare("SELECT id, partid, particulareng, particularben, pr1 FROM stfinance WHERE id = ? AND sccode = ? AND stid = ? LIMIT 1");
                $fStmt->bind_param('iis', $fid, $sccode, $stid);
                $fStmt->execute();
                $fRow = $fStmt->get_result()->fetch_assoc();
                $fStmt->close();

                if (!$fRow) continue;

                $isPr1Used = intval($fRow['pr1']) > 0;
                $fld = $isPr1Used ? 'pr2' : 'pr1';
                $flddt = $isPr1Used ? 'pr2date' : 'pr1date';
                $fldby = $isPr1Used ? 'pr2by' : 'pr1by';
                $fldno = $isPr1Used ? 'pr2no' : 'pr1no';

                $upStmt = $conn->prepare("UPDATE stfinance 
                    SET $fld = ?, $fldno = ?, $flddt = ?, $fldby = ?, paid = paid + ?, dues = dues - ?, modifieddate = NOW() 
                    WHERE id = ?");
                $upStmt->bind_param('dissddi', $paidAmt, $prno, $prdate, $entryby, $paidAmt, $paidAmt, $fid);
                $upStmt->execute();
                $upStmt->close();

                $totalPaid += $paidAmt;
                $partNamesEng[] = $fRow['particulareng'];
                $partNamesBen[] = $fRow['particularben'];
            }

            if ($totalPaid <= 0) {
                throw new Exception('No payable items updated.');
            }

            $pengStr = implode(', ', array_slice($partNamesEng, 0, 3));
            $pbenStr = implode(', ', array_slice($partNamesBen, 0, 3));
            $emptyTxt = '';
            $zeroVal = 0;

            $insPr = $conn->prepare("INSERT INTO stpr (
                sessionyear, sccode, classname, sectionname, stid, rollno, prno, prdate, partid, peng, pben, amount, entryby, entrytime, smstxt, smscnt, mobileno, smsstatus, statusvalue, collection_media
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)");
            $insPr->bind_param('iisssisissdsisssis', 
                $sessionyear, $sccode, $cls, $sec, $stid, $rollno, $prno, $prdate, 
                $zeroVal, $pengStr, $pbenStr, $totalPaid, $entryby, 
                $emptyTxt, $zeroVal, $mobile, $zeroVal, $emptyTxt, $collectionMedia
            );
            $insPr->execute();
            $assignedServerId = $conn->insert_id;
            $insPr->close();

            $upSess = $conn->prepare("UPDATE sessioninfo SET lastpr = ? WHERE sccode = ? AND stid = ? AND sessionyear LIKE ?");
            $sessLike = "%$sessionyear%";
            $upSess->bind_param('siss', $prno, $sccode, $stid, $sessLike);
            $upSess->execute();
            $upSess->close();

        } elseif ($module === 'exams' || str_contains($action, 'MARK')) {
            // Process Marks Entry
            $sessionyear = intval($payload['sessionyear'] ?? date('Y'));
            $exam = trim($payload['exam'] ?? '');
            $slot = trim($payload['slot'] ?? 'School');
            $classname = trim($payload['classname'] ?? '');
            $sectionname = trim($payload['sectionname'] ?? '');
            $subcode = intval($payload['subcode'] ?? $payload['subject'] ?? 0);
            $fullmark = intval($payload['fullmark'] ?? 100);
            $marks = $payload['marks'] ?? [];

            foreach ($marks as $m) {
                $stid = trim($m['stid'] ?? '');
                if (empty($stid)) continue;

                $subj = floatval($m['subj'] ?? 0);
                $obj = floatval($m['obj'] ?? 0);
                $pra = floatval($m['pra'] ?? $m['prac'] ?? 0);
                $ca = floatval($m['ca'] ?? 0);

                $markobt = $subj + $obj + $pra + $ca;
                $on100 = $fullmark > 0 ? round(($markobt / $fullmark) * 100, 2) : $markobt;
                
                $gp = 0.0; $gl = 'F';
                if ($on100 >= 80) { $gp = 5.0; $gl = 'A+'; }
                elseif ($on100 >= 70) { $gp = 4.0; $gl = 'A'; }
                elseif ($on100 >= 60) { $gp = 3.5; $gl = 'A-'; }
                elseif ($on100 >= 50) { $gp = 3.0; $gl = 'B'; }
                elseif ($on100 >= 40) { $gp = 2.0; $gl = 'C'; }
                elseif ($on100 >= 33) { $gp = 1.0; $gl = 'D'; }

                $chkStmt = $conn->prepare("SELECT id FROM stmark 
                    WHERE sccode = ? AND sessionyear = ? AND exam = ? AND classname = ? AND sectionname = ? AND subject = ? AND stid = ? 
                    LIMIT 1");
                $chkStmt->bind_param('iisssis', $sccode, $sessionyear, $exam, $classname, $sectionname, $subcode, $stid);
                $chkStmt->execute();
                $chkRes = $chkStmt->get_result()->fetch_assoc();
                $chkStmt->close();

                if ($chkRes) {
                    $upStmt = $conn->prepare("UPDATE stmark SET 
                        slot = ?, fullmark = ?, subj = ?, obj = ?, pra = ?, ca = ?, markobt = ?, on100 = ?, gp = ?, gl = ?, modifieddate = NOW(), entryby = ?
                        WHERE id = ?");
                    $upStmt->bind_param('sdddddddsdsi', $slot, $fullmark, $subj, $obj, $pra, $ca, $markobt, $on100, $gp, $gl, $entryby, $chkRes['id']);
                    $upStmt->execute();
                    $assignedServerId = $chkRes['id'];
                    $upStmt->close();
                } else {
                    $insStmt = $conn->prepare("INSERT INTO stmark (
                        slot, sessionyear, sccode, exam, classname, sectionname, subject, fullmark, stid, subj, obj, pra, ca, markobt, on100, gp, gl, entrydate, entryby
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
                    $insStmt->bind_param('siisssiisddddddsss', 
                        $slot, $sessionyear, $sccode, $exam, $classname, $sectionname, $subcode, $fullmark, $stid, 
                        $subj, $obj, $pra, $ca, $markobt, $on100, $gp, $gl, $entryby
                    );
                    $insStmt->execute();
                    $assignedServerId = $conn->insert_id;
                    $insStmt->close();
                }
            }

        } elseif ($module === 'attendance' || str_contains($action, 'ATTEND')) {
            // Process Attendance Push
            $sessionyear = intval($payload['sessionyear'] ?? date('Y'));
            $adate = trim($payload['date'] ?? $payload['adate'] ?? date('Y-m-d'));
            $records = $payload['records'] ?? [];

            foreach ($records as $rec) {
                $stid = trim($rec['stid'] ?? '');
                if (empty($stid)) continue;

                $yn = isset($rec['status']) ? intval($rec['status']) : (isset($rec['yn']) ? intval($rec['yn']) : 1);
                $intime = !empty($rec['in_time']) ? trim($rec['in_time']) : null;
                $outtime = !empty($rec['out_time']) ? trim($rec['out_time']) : null;
                $rollno = intval($rec['rollno'] ?? 0);
                $recClass = trim($rec['classname'] ?? '');
                $recSec = trim($rec['sectionname'] ?? '');

                $chkStmt = $conn->prepare("SELECT id FROM stattnd WHERE sccode = ? AND stid = ? AND adate = ? LIMIT 1");
                $chkStmt->bind_param('iss', $sccode, $stid, $adate);
                $chkStmt->execute();
                $chkRes = $chkStmt->get_result()->fetch_assoc();
                $chkStmt->close();

                if ($chkRes) {
                    $upStmt = $conn->prepare("UPDATE stattnd SET 
                        yn = ?, intime = COALESCE(?, intime), outtime = COALESCE(?, outtime), entryby = ?, modifieddate = NOW()
                        WHERE id = ?");
                    $upStmt->bind_param('isssi', $yn, $intime, $outtime, $entryby, $chkRes['id']);
                    $upStmt->execute();
                    $assignedServerId = $chkRes['id'];
                    $upStmt->close();
                } else {
                    $insStmt = $conn->prepare("INSERT INTO stattnd (
                        sccode, sessionyear, stid, adate, yn, intime, outtime, classname, sectionname, rollno, entryby, entrytime
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    $insStmt->bind_param('iississssds', 
                        $sccode, $sessionyear, $stid, $adate, $yn, $intime, $outtime, $recClass, $recSec, $rollno, $entryby
                    );
                    $insStmt->execute();
                    $assignedServerId = $conn->insert_id;
                    $insStmt->close();
                }
            }

        } elseif ($module === 'catchment' || str_contains($action, 'ADDRESS')) {
            // Process Catchment Area & Address Translations Push
            $eng_str = trim($payload['eng_str'] ?? $payload['eng'] ?? '');
            $ben_str = trim($payload['ben_str'] ?? $payload['str'] ?? '');
            $field_type = trim($payload['field_type'] ?? 'previll');
            $quota_pct = intval($payload['quota_pct'] ?? 0);
            $remarks = trim($payload['remarks'] ?? '');

            if (!empty($eng_str)) {
                $stmt = $conn->prepare("
                    INSERT INTO ben_address (sccode, eng_str, ben_str, field_type, quota_pct, remarks, modifieddate)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE
                        ben_str = VALUES(ben_str),
                        field_type = VALUES(field_type),
                        quota_pct = VALUES(quota_pct),
                        remarks = VALUES(remarks),
                        modifieddate = NOW()
                ");
                $stmt->bind_param('isssis', $sccode, $eng_str, $ben_str, $field_type, $quota_pct, $remarks);
                $stmt->execute();
                $assignedServerId = $conn->insert_id;
                $stmt->close();
            }

        } elseif (str_contains($action, 'TABLE_') || in_array($module, $allowedTables)) {
            // Generic Table Upsert Handler
            $targetTable = in_array($module, $allowedTables) ? $module : preg_replace('/[^a-zA-Z0-9_]/', '', strtolower(trim($payload['table'] ?? '')));
            if (!in_array($targetTable, $allowedTables)) {
                throw new Exception("Unauthorized table for generic push: {$targetTable}");
            }

            $rowRecord = $payload['record'] ?? $payload['data'] ?? $payload;
            unset($rowRecord['table'], $rowRecord['local_id'], $rowRecord['sync_status']);

            if (!in_array($targetTable, ['notice_category', 'ben_address', 'permissions_role', 'account_head_default', 'app_releases', 'app_roadmap', 'faq_desktop'])) {
                $rowRecord['sccode'] = $sccode;
            }

            // Enforce 401-800 code range strictly for subjects table
            if ($targetTable === 'subjects') {
                $subcodeVal = intval($rowRecord['subcode'] ?? 0);
                if ($subcodeVal < 401 || $subcodeVal > 800) {
                    throw new Exception("Custom subject code must strictly be between 401 and 800. Subcode ({$subcodeVal}) is outside allowed range.");
                }
            }

            $rowRecord['modifieddate'] = date('Y-m-d H:i:s');

            $existingId = intval($rowRecord['id'] ?? 0);
            $fields = array_keys($rowRecord);
            $placeholders = implode(', ', array_fill(0, count($fields), '?'));
            $fieldList = '`' . implode('`, `', $fields) . '`';
            $updateList = implode(', ', array_map(fn($f) => "`{$f}` = VALUES(`{$f}`)", $fields));

            $sql = "INSERT INTO `{$targetTable}` ({$fieldList}) VALUES ({$placeholders}) ON DUPLICATE KEY UPDATE {$updateList}";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed on {$targetTable}: " . $conn->error);
            }

            $types = str_repeat('s', count($fields));
            $vals = array_values($rowRecord);
            $stmt->bind_param($types, ...$vals);
            $stmt->execute();
            $assignedServerId = $existingId > 0 ? $existingId : $conn->insert_id;
            $stmt->close();

        } else {
            throw new Exception("Unknown module or action: {$module} / {$action}");
        }

        $conn->commit();

        $syncedCount++;
        $results[] = [
            'queue_id' => $queueId,
            'local_id' => $localId,
            'server_id' => $assignedServerId,
            'module' => $module,
            'status' => 'synced',
            'error' => null
        ];

    } catch (Exception $e) {
        $conn->rollback();
        $failedCount++;
        $results[] = [
            'queue_id' => $queueId,
            'local_id' => $localId,
            'server_id' => null,
            'module' => $module,
            'status' => 'failed',
            'error' => $e->getMessage()
        ];
    }
}

api_response('success', 'Batch synchronization processed.', [
    'total_processed' => count($transactions),
    'synced_count' => $syncedCount,
    'failed_count' => $failedCount,
    'results' => $results,
    'server_time' => date('Y-m-d H:i:s')
]);
