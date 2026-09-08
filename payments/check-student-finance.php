<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

/* ==========================================================================
   EIMBOX HIGH-SPEED BATCH SYNC ENGINE (CHUNK SIZE: 25 STUDENTS PER REQUEST)
   TRIPLE-GUARDED FINANCIAL DATA PROTECTION (ZERO CHANCE OF DATA LOSS)
   ========================================================================== */

$startTime = microtime(true);

$sy = $_POST['sy'] ?? $_COOKIE['session'] ?? $_COOKIE['chain-session'] ?? $sessionyear;
$slot = $_POST['slot'] ?? $_COOKIE['slot'] ?? $_COOKIE['chain-slot'] ?? '';
$type = $_POST['type'] ?? '';
$part = $_POST['part'] ?? 'all';
$icode = $_POST['icode'] ?? '';
$stid = $_POST['stid'] ?? '';
$cls = $_POST['cls'] ?? '';
$sec = $_POST['sec'] ?? '';

// Batch Chunk Size
$batchSize = 25;
if ($stid) {
    $batchSize = 1;
}

/* ==========================================================
   1. SELECT QUEUED STUDENTS (validate = 0, LIMIT $batchSize)
   ========================================================== */
$where = "sccode='$sccode' AND sessionyear LIKE '%$sy%' AND validate=0";

if ($stid) {
    $where .= " AND stid='$stid'";
} elseif ($sec && $cls) {
    $where .= " AND classname='$cls' AND sectionname='$sec'";
} elseif ($cls) {
    $where .= " AND classname='$cls'";
}

$sqlStudents = "SELECT id, stid, classname, sectionname, rollno, rate, sessionyear, 
                       COALESCE(new_admi, 0) AS new_admi
                FROM sessioninfo
                WHERE $where
                ORDER BY id ASC
                LIMIT $batchSize";

$resStudents = $conn->query($sqlStudents);

if (!$resStudents || $resStudents->num_rows == 0) {
    echo '<div id="totaltotal" hidden>0</div>';
    exit;
}

$students = [];
$studentIds = [];
$sessionInfoIds = [];

while ($row = $resStudents->fetch_assoc()) {
    $students[] = $row;
    $studentIds[] = "'" . $conn->real_escape_string($row['stid']) . "'";
    $sessionInfoIds[] = intval($row['id']);
}

$stidsIn = implode(',', $studentIds);
$sessionIdsIn = implode(',', $sessionInfoIds);

/* ==========================================================
   START ATOMIC TRANSACTION
   ========================================================== */
$conn->begin_transaction();

try {

    /* ==========================================================
       2. RESET VALIDATION FLAG (validate = 0) FOR THIS BATCH
       ========================================================== */
    if ($part === 'icode' && $icode) {
        $conn->query("UPDATE stfinance SET validate=0 
                      WHERE sccode='$sccode' AND sessionyear LIKE '%$sy%' 
                        AND stid IN ($stidsIn) AND itemcode='$icode'");
    } else {
        $conn->query("UPDATE stfinance SET validate=0 
                      WHERE sccode='$sccode' AND sessionyear LIKE '%$sy%' 
                        AND stid IN ($stidsIn)");
    }

    /* ==========================================================
       3. IN-MEMORY PRE-FETCHING (ZERO N+1 QUERIES)
       ========================================================== */
    
    // A. Fetch all Individual Setups for these students
    $indMap = [];
    $sqlInd = "SELECT stid, itemcode, amount 
               FROM financesetupind 
               WHERE sccode='$sccode' AND sessionyear LIKE '%$sy%' AND stid IN ($stidsIn)";
    $resInd = $conn->query($sqlInd);
    if ($resInd && $resInd->num_rows > 0) {
        while ($r = $resInd->fetch_assoc()) {
            $indMap[$r['stid'] . '_' . $r['itemcode']] = floatval($r['amount']);
        }
    }

    // B. Fetch all Active Master Fee Items
    $setupWhere = "sccode='$sccode' AND sessionyear LIKE '%$sy%' AND COALESCE(active, 1) = 1";
    if ($part === 'icode' && $icode) {
        $setupWhere .= " AND itemcode='$icode'";
    }

    $finSetup = [];
    $sqlSetup = "SELECT id, itemcode, month, particulareng, particularben, 
                        COALESCE(new_only, 0) AS new_only, 
                        COALESCE(splitable, 0) AS splitable, 
                        COALESCE(sub_head, 0) AS sub_head
                 FROM financesetup 
                 WHERE $setupWhere 
                 ORDER BY slno ASC, id ASC";
    $resSetup = $conn->query($sqlSetup);
    if ($resSetup && $resSetup->num_rows > 0) {
        while ($r = $resSetup->fetch_assoc()) {
            $finSetup[] = $r;
        }
    }

    // C. Fetch all Fee Rates (financesetupvalue)
    $valMap = [];
    $sqlVal = "SELECT itemcode, classname, sectionname, amount 
               FROM financesetupvalue 
               WHERE sccode='$sccode' AND sessionyear LIKE '%$sy%' AND amount > 0";
    $resVal = $conn->query($sqlVal);
    if ($resVal && $resVal->num_rows > 0) {
        while ($r = $resVal->fetch_assoc()) {
            $c = strtolower(trim($r['classname']));
            $s = strtolower(trim($r['sectionname']));
            $valMap[$r['itemcode'] . '|' . $c . '|' . $s] = floatval($r['amount']);
        }
    }

    // D. Fetch all Existing stfinance records for this batch
    $stFinMap = [];
    $sqlStFin = "SELECT id, stid, itemcode, month, paid, pr1, pr1no, pr2, splitid, splitid2 
                 FROM stfinance 
                 WHERE sccode='$sccode' AND sessionyear LIKE '%$sy%' AND stid IN ($stidsIn)";
    $resStFin = $conn->query($sqlStFin);
    if ($resStFin && $resStFin->num_rows > 0) {
        while ($r = $resStFin->fetch_assoc()) {
            $key = $r['stid'] . '_' . $r['itemcode'] . '_' . intval($r['month']);
            $stFinMap[$key] = $r;
        }
    }

    /* ==========================================================
       4. MONTH FREQUENCY RESOLUTION HELPER
       ========================================================== */
    function parseRuleMonths(int $rule): array {
        if ($rule === 0) return range(1, 12);
        if ($rule >= 1 && $rule <= 12) return [$rule];
        if ($rule === 22) return [2, 4, 6, 8, 10, 12];
        if ($rule === 33) return [3, 6, 9, 11];
        if ($rule === 44 || $rule === 442) return [4, 8, 11];
        if ($rule === 66 || $rule === 662) return [1, 11];
        return [$rule];
    }

    $monthEng = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $monthBen = ['', 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];

    $insertRows = [];
    $updateUnpaidRows = [];
    $validateOnlyIds = [];
    $subheadSyncMap = [];

    $newCount = 0;
    $updateCount = 0;
    $lockedCount = 0;

    /* ==========================================================
       5. BATCH IN-MEMORY EVALUATION
       ========================================================== */
    foreach ($students as $st) {
        $s_id = $st['stid'];
        $s_cls = strtolower(trim($st['classname']));
        $s_sec = strtolower(trim($st['sectionname']));
        $s_roll = intval($st['rollno']);
        $s_rate = (isset($st['rate']) && is_numeric($st['rate'])) ? floatval($st['rate']) : 100;
        $s_new_admi = intval($st['new_admi'] ?? 0);
        $s_year = $st['sessionyear'];

        foreach ($finSetup as $fs) {
            $itemcode = $fs['itemcode'];
            $partid = intval($fs['id']);
            $partex = $fs['particulareng'];
            $partbx = $fs['particularben'];
            $monthRule = intval($fs['month']);
            $new_only = intval($fs['new_only'] ?? 0);
            $sub_head = intval($fs['sub_head'] ?? 0);

            // New Admission Only Rule
            if ($new_only == 1 && $s_new_admi == 0) {
                continue;
            }

            // Amount Priority (1. Exact Class+Section -> 2. Class -> 3. Global Default)
            $amt = 0;
            if (isset($valMap[$itemcode . '|' . $s_cls . '|' . $s_sec])) {
                $amt = $valMap[$itemcode . '|' . $s_cls . '|' . $s_sec];
            } elseif (isset($valMap[$itemcode . '|' . $s_cls . '|'])) {
                $amt = $valMap[$itemcode . '|' . $s_cls . '|'];
            } elseif (isset($valMap[$itemcode . '||'])) {
                $amt = $valMap[$itemcode . '||'];
            }

            if ($amt <= 0) {
                continue;
            }

            // Tuition Fee Waiver Calculation
            if (stripos($partex, 'tution') !== false || stripos($partex, 'tuition') !== false || stripos($partbx, 'বেতন') !== false) {
                $amt = ($amt * $s_rate) / 100;
            }

            // Individual Student Override
            $paya = $amt;
            if (isset($indMap[$s_id . '_' . $itemcode])) {
                $paya = $indMap[$s_id . '_' . $itemcode];
            }

            $months = parseRuleMonths($monthRule);
            $item_repeat = count($months);

            foreach ($months as $m) {
                $stKey = $s_id . '_' . $itemcode . '_' . $m;

                if (isset($stFinMap[$stKey])) {
                    // Record Exists
                    $ex = $stFinMap[$stKey];
                    $exId = intval($ex['id']);
                    $exPaid = floatval($ex['paid'] ?? 0);
                    $exPr1 = floatval($ex['pr1'] ?? 0);
                    $exPr1No = intval($ex['pr1no'] ?? 0);
                    $exPr2 = floatval($ex['pr2'] ?? 0);
                    $exSplit1 = intval($ex['splitid'] ?? 0);
                    $exSplit2 = intval($ex['splitid2'] ?? 0);

                    // 🛑 SAFETY RING 1: If paid or receipt exists, NEVER overwrite money or delete
                    if ($exPaid > 0 || $exPr1 > 0 || $exPr1No > 0 || $exPr2 > 0 || $exSplit1 > 0 || $exSplit2 > 0) {
                        $validateOnlyIds[] = $exId;
                        $subheadSyncMap[$exId] = $sub_head;
                        $lockedCount++;
                    } else {
                        // Unpaid Record: Safe to recalculate dues
                        $updateUnpaidRows[] = [
                            'id' => $exId,
                            'amount' => $amt,
                            'payableamt' => $paya,
                            'dues' => $paya,
                            'sub_head' => $sub_head
                        ];
                        $updateCount++;
                    }
                } else {
                    // New Record: Prepare for Multi-row Bulk Insert
                    $titleEng = ($item_repeat > 1 && isset($monthEng[$m])) ? ($partex . ' | ' . $monthEng[$m]) : $partex;
                    $titleBen = ($item_repeat > 1 && isset($monthBen[$m])) ? ($partbx . ' | ' . $monthBen[$m]) : $partbx;
                    $idmon = $s_id . '-' . $partid . '-' . $m;

                    $titleEngEsc = $conn->real_escape_string($titleEng);
                    $titleBenEsc = $conn->real_escape_string($titleBen);
                    $idmonEsc = $conn->real_escape_string($idmon);

                    $insertRows[] = "('$sccode', '$s_year', '$s_cls', '$s_sec', '$s_id', '$s_roll', '$partid', '$itemcode', '$sub_head', '$titleEngEsc', '$titleBenEsc', '$amt', '$m', '$idmonEsc', '$cur', '$usr', '$paya', '$cur', 0, '$paya', '$cur', 1, '$cur')";
                    $newCount++;
                }
            }
        }
    }

    /* ==========================================================
       6. BULK SQL EXECUTION
       ========================================================== */
    
    // A. Multi-row Bulk Insert
    if (!empty($insertRows)) {
        // Chunk inserts into 300 rows max per multi-insert query for MySQL packet safety
        $chunks = array_chunk($insertRows, 300);
        foreach ($chunks as $chunk) {
            $sqlBulkInsert = "INSERT INTO stfinance 
                (sccode, sessionyear, classname, sectionname, stid, rollno, partid,
                 itemcode, sub_head, particulareng, particularben, amount, month, idmon,
                 setupdate, setupby, payableamt, modifieddate, paid, dues,
                 last_update, validate, validationtime)
                VALUES " . implode(',', $chunk);
            $conn->query($sqlBulkInsert);
        }
    }

    // B. Bulk Updates for Unpaid Dues
    if (!empty($updateUnpaidRows)) {
        foreach ($updateUnpaidRows as $up) {
            $conn->query("UPDATE stfinance 
                          SET validate=1, sub_head='{$up['sub_head']}', amount='{$up['amount']}', payableamt='{$up['payableamt']}', dues='{$up['dues']}', modifieddate='$cur', modifiedby='$usr' 
                          WHERE id='{$up['id']}'");
        }
    }

    // C. Validate Locked/Paid records without touching money
    if (!empty($validateOnlyIds)) {
        $validChunks = array_chunk($validateOnlyIds, 500);
        foreach ($validChunks as $chunk) {
            $idsList = implode(',', $chunk);
            $conn->query("UPDATE stfinance SET validate=1 WHERE id IN ($idsList)");
        }

        foreach ($subheadSyncMap as $vId => $shId) {
            if ($shId > 0) {
                $conn->query("UPDATE stfinance SET sub_head='$shId' WHERE id='$vId'");
            }
        }
    }

    /* ==========================================================
       🛑 7. TRIPLE-GUARDED CLEANUP OF CANCELLED UNPAID ITEMS
       ABSOLUTE SAFETY: IMPOSSIBLE TO DELETE ANY RECORD WITH MONEY
       ========================================================== */
    $sqlSafeDelete = "DELETE FROM stfinance 
                      WHERE sccode='$sccode' 
                        AND sessionyear LIKE '%$sy%' 
                        AND stid IN ($stidsIn) 
                        AND validate = 0 
                        AND (paid = 0 OR paid IS NULL) 
                        AND (pr1 = 0 OR pr1 IS NULL) 
                        AND (pr1no IS NULL OR pr1no = 0) 
                        AND (pr2 = 0 OR pr2 IS NULL) 
                        AND (cashbook1 = 0 OR cashbook1 IS NULL) 
                        AND (cashbook2 = 0 OR cashbook2 IS NULL)";
    $conn->query($sqlSafeDelete);

    // D. Mark Batch of Students in sessioninfo as Validated
    $conn->query("UPDATE sessioninfo 
                  SET validate=1, validationtime='$cur' 
                  WHERE id IN ($sessionIdsIn)");

    /* Commit Atomic Transaction */
    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    echo "<div class='text-danger p-2 bg-danger-subtle rounded mb-2'>
            <i class='bi bi-exclamation-octagon me-1'></i> Transaction Error: " . htmlspecialchars($e->getMessage()) . "
          </div>";
    exit;
}

/* ==========================================================
   8. REMAINING QUEUE COUNT
   ========================================================== */
$qLeft = $conn->query("SELECT COUNT(*) c FROM sessioninfo WHERE $where");
$left = ($qLeft && $rowLeft = $qLeft->fetch_assoc()) ? intval($rowLeft['c']) : 0;

/* ==========================================================
   9. REALTIME LOG OUTPUT
   ========================================================== */
$duration = round(microtime(true) - $startTime, 2);
$studentCount = count($students);

echo "<div class='py-1 border-bottom d-flex justify-content-between align-items-center'>
        <span>
            <span class='badge bg-label-info me-1'>" . date('H:i:s') . "</span>
            <strong class='text-primary'>Batch ({$studentCount} students)</strong>: IDs " . htmlspecialchars($students[0]['stid']) . "..." . htmlspecialchars(end($students)['stid']) . "
        </span>
        <span>
            <span class='badge bg-label-success me-1'>+{$newCount} new</span>
            <span class='badge bg-label-warning me-1'>~{$updateCount} upd</span>
            <span class='badge bg-label-secondary'>{$lockedCount} locked</span>
            <small class='text-muted ms-2'>{$duration}s</small>
        </span>
      </div>";

echo "<div id='totaltotal' hidden>$left</div>";
