<?php
/**
 * EIMBox REST API - Chart of Accounts & Head/Sub-heads Management
 * Endpoint: /api/v1/finance/accounts-heads.php
 * 
 * Supports 100% Zero Dummy Data, exact MySQL schema compatibility,
 * and Preset Seeding from account_head_default & account_sub_head_default.
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$sccode = intval($auth['sccode'] ?? 0);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // 1. Load heads for this school
        $headQuery = "SELECT id, account_head, sccode, modifieddate 
                      FROM account_head 
                      WHERE sccode = ? OR sccode = 0
                      ORDER BY id ASC";
        $stmt = $conn->prepare($headQuery);
        $stmt->bind_param("i", $sccode);
        $stmt->execute();
        $headRes = $stmt->get_result();

        $heads = [];
        $headMap = [];
        while ($row = $headRes->fetch_assoc()) {
            $row['id'] = intval($row['id']);
            $row['sccode'] = intval($row['sccode']);
            $row['sub_heads'] = [];
            $heads[] = $row;
            $headMap[$row['id']] = count($heads) - 1;
        }
        $stmt->close();

        // 2. Fetch sub-heads for this school
        $subQuery = "SELECT id, sccode, account_head_id, account_head, sub_head, income, expenditure, modifieddate 
                     FROM account_sub_head 
                     WHERE sccode = ? OR sccode = 0
                     ORDER BY id ASC";
        $subStmt = $conn->prepare($subQuery);
        $subStmt->bind_param("i", $sccode);
        $subStmt->execute();
        $subRes = $subStmt->get_result();

        while ($subRow = $subRes->fetch_assoc()) {
            $subRow['id'] = intval($subRow['id']);
            $subRow['sccode'] = intval($subRow['sccode']);
            $subRow['account_head_id'] = intval($subRow['account_head_id']);
            $subRow['income'] = intval($subRow['income']);
            $subRow['expenditure'] = intval($subRow['expenditure']);

            $pId = $subRow['account_head_id'];
            if (isset($headMap[$pId])) {
                $heads[$headMap[$pId]]['sub_heads'][] = $subRow;
            } else {
                // If account_head_id is not set, match by account_head string name
                $matched = false;
                foreach ($heads as $idx => $h) {
                    if (strcasecmp(trim($h['account_head']), trim($subRow['account_head'])) === 0) {
                        $heads[$idx]['sub_heads'][] = $subRow;
                        $matched = true;
                        break;
                    }
                }
            }
        }
        $subStmt->close();

        api_response('success', 'Chart of accounts loaded', $heads);
        break;

    case 'POST':
        $data = get_api_input();
        $action = trim($data['action'] ?? 'create_head');

        // Action: Seed Defaults from account_head_default & account_sub_head_default
        if ($action === 'seed_defaults') {
            $seededHeads = 0;
            $seededSubHeads = 0;

            // Fetch default heads from account_head_default
            $defHeadsRes = $conn->query("SELECT id, account_head FROM account_head_default ORDER BY id ASC");
            if ($defHeadsRes && $defHeadsRes->num_rows > 0) {
                while ($dh = $defHeadsRes->fetch_assoc()) {
                    $dhName = trim($dh['account_head']);
                    if (empty($dhName)) continue;

                    // Check if head already exists for this sccode
                    $chk = $conn->prepare("SELECT id FROM account_head WHERE sccode = ? AND account_head = ?");
                    $chk->bind_param("is", $sccode, $dhName);
                    $chk->execute();
                    $chkRes = $chk->get_result();
                    
                    $activeHeadId = 0;
                    if ($chkRes->num_rows > 0) {
                        $activeHeadId = intval($chkRes->fetch_assoc()['id']);
                    } else {
                        // Insert head for this school
                        $ins = $conn->prepare("INSERT INTO account_head (sccode, account_head, modifieddate) VALUES (?, ?, NOW())");
                        $ins->bind_param("is", $sccode, $dhName);
                        $ins->execute();
                        $activeHeadId = $conn->insert_id;
                        $ins->close();
                        $seededHeads++;
                    }
                    $chk->close();

                    // Seed associated sub-heads from account_sub_head_default
                    $defSubStmt = $conn->prepare("SELECT sub_head, type FROM account_sub_head_default WHERE account_head = ?");
                    $defSubStmt->bind_param("s", $dhName);
                    $defSubStmt->execute();
                    $defSubRes = $defSubStmt->get_result();

                    while ($dSub = $defSubRes->fetch_assoc()) {
                        $subName = trim($dSub['sub_head']);
                        if (empty($subName)) continue;

                        $isIncome = (strtolower(trim($dSub['type'])) === 'income') ? 1 : 0;
                        $isExp = (strtolower(trim($dSub['type'])) === 'expenditure' || $isIncome === 0) ? 1 : 0;

                        // Check if sub-head already exists for this sccode and head
                        $chkSub = $conn->prepare("SELECT id FROM account_sub_head WHERE sccode = ? AND account_head_id = ? AND sub_head = ?");
                        $chkSub->bind_param("iis", $sccode, $activeHeadId, $subName);
                        $chkSub->execute();
                        if ($chkSub->get_result()->num_rows === 0) {
                            $insSub = $conn->prepare("INSERT INTO account_sub_head (sccode, account_head_id, account_head, sub_head, income, expenditure, modifieddate) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                            $insSub->bind_param("iissii", $sccode, $activeHeadId, $dhName, $subName, $isIncome, $isExp);
                            $insSub->execute();
                            $insSub->close();
                            $seededSubHeads++;
                        }
                        $chkSub->close();
                    }
                    $defSubStmt->close();
                }
            }

            api_response('success', "Preset import successful: {$seededHeads} heads and {$seededSubHeads} sub-heads synchronized.", [
                'seeded_heads' => $seededHeads,
                'seeded_sub_heads' => $seededSubHeads
            ]);
            break;
        }

        // Action: Create Sub-Head
        if ($action === 'create_sub_head') {
            $headId = intval($data['account_head_id'] ?? 0);
            $subHeadName = trim($data['sub_head'] ?? '');
            $income = intval($data['income'] ?? 0);
            $expenditure = intval($data['expenditure'] ?? 1);

            if ($headId <= 0 || empty($subHeadName)) {
                api_response('error', 'Parent Account Head and Sub-head name are required', null, 400);
            }

            // Get head name for denormalized column
            $hStmt = $conn->prepare("SELECT account_head FROM account_head WHERE id = ?");
            $hStmt->bind_param("i", $headId);
            $hStmt->execute();
            $hRes = $hStmt->get_result();
            $headName = $hRes->num_rows > 0 ? $hRes->fetch_assoc()['account_head'] : '';
            $hStmt->close();

            $ins = $conn->prepare("INSERT INTO account_sub_head (sccode, account_head_id, account_head, sub_head, income, expenditure, modifieddate) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $ins->bind_param("iissii", $sccode, $headId, $headName, $subHeadName, $income, $expenditure);
            
            if ($ins->execute()) {
                $newId = $ins->insert_id;
                $ins->close();
                api_response('success', 'Sub-head created successfully', ['id' => $newId]);
            } else {
                api_response('error', 'Failed to create sub-head: ' . $conn->error, null, 500);
            }
            break;
        }

        // Action: Create Head (default)
        $headName = trim($data['account_head'] ?? $data['head_name'] ?? '');
        if (empty($headName)) {
            api_response('error', 'Account Head name is required', null, 400);
        }

        $ins = $conn->prepare("INSERT INTO account_head (sccode, account_head, modifieddate) VALUES (?, ?, NOW())");
        $ins->bind_param("is", $sccode, $headName);

        if ($ins->execute()) {
            $newId = $ins->insert_id;
            $ins->close();
            api_response('success', 'Account Head created successfully', ['id' => $newId]);
        } else {
            api_response('error', 'Failed to create head: ' . $conn->error, null, 500);
        }
        break;

    case 'PUT':
        $data = get_api_input();
        $target = $data['target'] ?? 'head'; // 'head' or 'sub_head'
        $id = intval($data['id'] ?? 0);

        if ($id <= 0) {
            api_response('error', 'Valid record ID is required', null, 400);
        }

        if ($target === 'sub_head') {
            $headId = intval($data['account_head_id'] ?? 0);
            $subHeadName = trim($data['sub_head'] ?? '');
            $income = intval($data['income'] ?? 0);
            $expenditure = intval($data['expenditure'] ?? 1);

            if (empty($subHeadName)) {
                api_response('error', 'Sub-head name cannot be empty', null, 400);
            }

            if ($headId > 0) {
                // Get updated parent head name
                $hStmt = $conn->prepare("SELECT account_head FROM account_head WHERE id = ?");
                $hStmt->bind_param("i", $headId);
                $hStmt->execute();
                $hRes = $hStmt->get_result();
                $headName = $hRes->num_rows > 0 ? $hRes->fetch_assoc()['account_head'] : '';
                $hStmt->close();

                $upd = $conn->prepare("UPDATE account_sub_head SET sub_head = ?, account_head_id = ?, account_head = ?, income = ?, expenditure = ?, modifieddate = NOW() WHERE id = ? AND (sccode = ? OR sccode = 0)");
                $upd->bind_param("sisiisi", $subHeadName, $headId, $headName, $income, $expenditure, $id, $sccode);
            } else {
                $upd = $conn->prepare("UPDATE account_sub_head SET sub_head = ?, income = ?, expenditure = ?, modifieddate = NOW() WHERE id = ? AND (sccode = ? OR sccode = 0)");
                $upd->bind_param("siiisi", $subHeadName, $income, $expenditure, $id, $sccode);
            }

            $upd->execute();
            $upd->close();
            api_response('success', 'Sub-head updated successfully');
        } else {
            // Update Head
            $headName = trim($data['account_head'] ?? $data['head_name'] ?? '');
            if (empty($headName)) {
                api_response('error', 'Account Head name cannot be empty', null, 400);
            }

            $upd = $conn->prepare("UPDATE account_head SET account_head = ?, modifieddate = NOW() WHERE id = ? AND (sccode = ? OR sccode = 0)");
            $upd->bind_param("sii", $headName, $id, $sccode);
            $upd->execute();
            $upd->close();

            // Also update account_head string in associated sub_heads for denormalized consistency
            $updSub = $conn->prepare("UPDATE account_sub_head SET account_head = ?, modifieddate = NOW() WHERE account_head_id = ? AND (sccode = ? OR sccode = 0)");
            $updSub->bind_param("sii", $headName, $id, $sccode);
            $updSub->execute();
            $updSub->close();

            api_response('success', 'Account Head updated successfully');
        }
        break;

    case 'DELETE':
        $data = get_api_input();
        $target = $data['target'] ?? ($_GET['target'] ?? 'sub_head');
        $id = intval($data['id'] ?? ($_GET['id'] ?? 0));

        if ($id <= 0) {
            api_response('error', 'Valid ID is required for deletion', null, 400);
        }

        if ($target === 'head') {
            // Check if sub-heads exist
            $chk = $conn->prepare("SELECT COUNT(*) as cnt FROM account_sub_head WHERE account_head_id = ? AND (sccode = ? OR sccode = 0)");
            $chk->bind_param("ii", $id, $sccode);
            $chk->execute();
            $cnt = intval($chk->get_result()->fetch_assoc()['cnt'] ?? 0);
            $chk->close();

            if ($cnt > 0) {
                // Delete child sub-heads first or block
                $delSub = $conn->prepare("DELETE FROM account_sub_head WHERE account_head_id = ? AND (sccode = ? OR sccode = 0)");
                $delSub->bind_param("ii", $id, $sccode);
                $delSub->execute();
                $delSub->close();
            }

            $del = $conn->prepare("DELETE FROM account_head WHERE id = ? AND (sccode = ? OR sccode = 0)");
            $del->bind_param("ii", $id, $sccode);
            $del->execute();
            $del->close();

            api_response('success', 'Account Head and associated sub-heads deleted successfully');
        } else {
            // Delete Sub-head
            $del = $conn->prepare("DELETE FROM account_sub_head WHERE id = ? AND (sccode = ? OR sccode = 0)");
            $del->bind_param("ii", $id, $sccode);
            $del->execute();
            $del->close();

            api_response('success', 'Sub-head deleted successfully');
        }
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
