<?php
/**
 * EIMBox REST API - Chart of Accounts & Head/Sub-heads Management
 * Endpoint: /api/v1/finance/accounts-heads.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$sccode = $auth['sccode'] ?? 0;
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Load hierarchical chart of accounts for school
        $headQuery = "SELECT id, sccode, head_code, head_name, head_name_bn, head_type, is_system_default, status, display_order 
                      FROM account_head 
                      WHERE (sccode = ? OR sccode = '0' OR sccode = '')
                      ORDER BY display_order ASC, id ASC";
        $stmt = $conn->prepare($headQuery);
        $sccodeStr = (string)$sccode;
        $stmt->bind_param("s", $sccodeStr);
        $stmt->execute();
        $headRes = $stmt->get_result();

        $heads = [];
        $headMap = [];
        while ($row = $headRes->fetch_assoc()) {
            $row['id'] = (int)$row['id'];
            $row['status'] = (int)$row['status'];
            $row['display_order'] = (int)$row['display_order'];
            $row['is_system_default'] = (int)$row['is_system_default'];
            $row['sub_heads'] = [];
            $heads[] = $row;
            $headMap[$row['id']] = count($heads) - 1;
        }

        // Fetch sub-heads
        $subQuery = "SELECT id, sccode, account_head_id, sub_head_code, sub_head, sub_head_bn, income, expenditure, default_amount, status, display_order 
                     FROM account_sub_head 
                     WHERE (sccode = ? OR sccode = '0' OR sccode = '')
                     ORDER BY display_order ASC, id ASC";
        $subStmt = $conn->prepare($subQuery);
        $subStmt->bind_param("s", $sccodeStr);
        $subStmt->execute();
        $subRes = $subStmt->get_result();

        while ($subRow = $subRes->fetch_assoc()) {
            $subRow['id'] = (int)$subRow['id'];
            $subRow['account_head_id'] = (int)$subRow['account_head_id'];
            $subRow['income'] = (int)$subRow['income'];
            $subRow['expenditure'] = (int)$subRow['expenditure'];
            $subRow['default_amount'] = (float)$subRow['default_amount'];
            $subRow['status'] = (int)$subRow['status'];
            $subRow['display_order'] = (int)$subRow['display_order'];

            $pId = $subRow['account_head_id'];
            if (isset($headMap[$pId])) {
                $heads[$headMap[$pId]]['sub_heads'][] = $subRow;
            }
        }

        api_response('success', 'Chart of accounts loaded', $heads);
        break;

    case 'POST':
        $data = get_api_input();
        $action = $data['action'] ?? 'create_head';

        if ($action === 'seed_defaults') {
            // Seed defaults from account_head_default / account_sub_head_default if available
            $defaultHeads = [
                ['head_code' => '1000', 'head_name' => 'Tuition & Academic Fees', 'head_name_bn' => 'শিক্ষার্থী বেতন ও সেশন আয়', 'head_type' => 'income'],
                ['head_code' => '1100', 'head_name' => 'Government Grants & MPO', 'head_name_bn' => 'সরকারি বরাদ্দ ও অনুদান', 'head_type' => 'income'],
                ['head_code' => '1200', 'head_name' => 'Donations & Endowments', 'head_name_bn' => 'কমিটি ও শুভানুধ্যায়ী অনুদান', 'head_type' => 'income'],
                ['head_code' => '1300', 'head_name' => 'Institutional Property & Rental Income', 'head_name_bn' => 'নিজস্ব সম্পত্তি ও ইজারা আয়', 'head_type' => 'income'],
                ['head_code' => '1400', 'head_name' => 'Bank Interest & Financial Inflows', 'head_name_bn' => 'ব্যাংক সুদ ও আর্থিক লভ্যাংশ', 'head_type' => 'income'],
                ['head_code' => '3000', 'head_name' => 'Salary & Allowances', 'head_name_bn' => 'শিক্ষক-কর্মচারী বেতন ও ভাতাদি', 'head_type' => 'expense'],
                ['head_code' => '3100', 'head_name' => 'Administrative & Office Expenses', 'head_name_bn' => 'দাপ্তরিক ও প্রশাসনিক ব্যয়', 'head_type' => 'expense'],
                ['head_code' => '3200', 'head_name' => 'Utilities & Bills', 'head_name_bn' => 'ইউটিলিটি ও বিলসমূহ', 'head_type' => 'expense'],
                ['head_code' => '3300', 'head_name' => 'Examination Expenses', 'head_name_bn' => 'পরীক্ষা সংক্রান্ত ব্যয়', 'head_type' => 'expense'],
                ['head_code' => '3400', 'head_name' => 'Maintenance & Repairs', 'head_name_bn' => 'মেরামত, উন্নয়ন ও সংস্কার', 'head_type' => 'expense'],
                ['head_code' => '3500', 'head_name' => 'Sports & Cultural Activities', 'head_name_bn' => 'ক্রীড়া ও সহ-শিক্ষা কার্যক্রম', 'head_type' => 'expense'],
                ['head_code' => '3600', 'head_name' => 'Conveyance & Hospitality', 'head_name_bn' => 'যাতায়াত ও আপ্যায়ন', 'head_type' => 'expense'],
                ['head_code' => '5000', 'head_name' => 'Capital Assets & Purchases', 'head_name_bn' => 'স্থায়ী সম্পদ ক্রয়', 'head_type' => 'asset']
            ];

            $seededCount = 0;
            foreach ($defaultHeads as $dh) {
                $check = $conn->prepare("SELECT id FROM account_head WHERE sccode = ? AND head_name = ?");
                $sccodeStr = (string)$sccode;
                $check->bind_param("ss", $sccodeStr, $dh['head_name']);
                $check->execute();
                if ($check->get_result()->num_rows === 0) {
                    $ins = $conn->prepare("INSERT INTO account_head (sccode, head_code, head_name, head_name_bn, head_type, is_system_default, status) VALUES (?, ?, ?, ?, ?, 1, 1)");
                    $ins->bind_param("sssss", $sccodeStr, $dh['head_code'], $dh['head_name'], $dh['head_name_bn'], $dh['head_type']);
                    $ins->execute();
                    $seededCount++;
                }
            }
            api_response('success', "$seededCount default account heads synchronized");
            break;
        }

        if ($action === 'create_sub_head') {
            $accountHeadId = (int)($data['account_head_id'] ?? 0);
            $subHead = trim($data['sub_head'] ?? '');
            $subHeadBn = trim($data['sub_head_bn'] ?? '');
            $subHeadCode = trim($data['sub_head_code'] ?? '');
            $income = (int)($data['income'] ?? 0);
            $expenditure = (int)($data['expenditure'] ?? 0);
            $defaultAmount = (float)($data['default_amount'] ?? 0);
            $status = isset($data['status']) ? (int)$data['status'] : 1;

            if (!$accountHeadId || empty($subHead)) {
                api_response('error', 'Parent Account Head and Sub-head title are required', null, 400);
            }

            $sccodeStr = (string)$sccode;
            $ins = $conn->prepare("INSERT INTO account_sub_head (sccode, account_head_id, sub_head_code, sub_head, sub_head_bn, income, expenditure, default_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->bind_param("sisssiidi", $sccodeStr, $accountHeadId, $subHeadCode, $subHead, $subHeadBn, $income, $expenditure, $defaultAmount, $status);
            
            if ($ins->execute()) {
                api_response('success', 'Sub-head created successfully', ['id' => $ins->insert_id]);
            } else {
                api_response('error', 'Failed to create sub-head: ' . $conn->error, null, 500);
            }
            break;
        }

        // Default action: create_head
        $headName = trim($data['head_name'] ?? '');
        $headNameBn = trim($data['head_name_bn'] ?? '');
        $headCode = trim($data['head_code'] ?? '');
        $headType = strtolower(trim($data['head_type'] ?? 'income'));
        $displayOrder = (int)($data['display_order'] ?? 0);
        $status = isset($data['status']) ? (int)$data['status'] : 1;

        if (empty($headName)) {
            api_response('error', 'Account Head name is required', null, 400);
        }

        $sccodeStr = (string)$sccode;
        $ins = $conn->prepare("INSERT INTO account_head (sccode, head_code, head_name, head_name_bn, head_type, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ins->bind_param("sssssii", $sccodeStr, $headCode, $headName, $headNameBn, $headType, $displayOrder, $status);

        if ($ins->execute()) {
            api_response('success', 'Account Head created successfully', ['id' => $ins->insert_id]);
        } else {
            api_response('error', 'Failed to create head: ' . $conn->error, null, 500);
        }
        break;

    case 'PUT':
        $data = get_api_input();
        $target = $data['target'] ?? 'head'; // 'head' or 'sub_head'
        $id = (int)($data['id'] ?? 0);

        if (!$id) {
            api_response('error', 'Valid record ID is required', null, 400);
        }

        $sccodeStr = (string)$sccode;
        if ($target === 'sub_head') {
            $subHead = trim($data['sub_head'] ?? '');
            $subHeadBn = trim($data['sub_head_bn'] ?? '');
            $subHeadCode = trim($data['sub_head_code'] ?? '');
            $income = (int)($data['income'] ?? 0);
            $expenditure = (int)($data['expenditure'] ?? 0);
            $defaultAmount = (float)($data['default_amount'] ?? 0);
            $status = (int)($data['status'] ?? 1);

            $upd = $conn->prepare("UPDATE account_sub_head SET sub_head = ?, sub_head_bn = ?, sub_head_code = ?, income = ?, expenditure = ?, default_amount = ?, status = ? WHERE id = ? AND (sccode = ? OR sccode = '0')");
            $upd->bind_param("sssiidis", $subHead, $subHeadBn, $subHeadCode, $income, $expenditure, $defaultAmount, $status, $id, $sccodeStr);
            $upd->execute();
            api_response('success', 'Sub-head updated successfully');
        } else {
            $headName = trim($data['head_name'] ?? '');
            $headNameBn = trim($data['head_name_bn'] ?? '');
            $headCode = trim($data['head_code'] ?? '');
            $headType = strtolower(trim($data['head_type'] ?? 'income'));
            $status = (int)($data['status'] ?? 1);

            $upd = $conn->prepare("UPDATE account_head SET head_name = ?, head_name_bn = ?, head_code = ?, head_type = ?, status = ? WHERE id = ? AND (sccode = ? OR sccode = '0')");
            $upd->bind_param("ssssiis", $headName, $headNameBn, $headCode, $headType, $status, $id, $sccodeStr);
            $upd->execute();
            api_response('success', 'Account Head updated successfully');
        }
        break;

    case 'DELETE':
        $data = get_api_input();
        $target = $data['target'] ?? 'sub_head';
        $id = (int)($data['id'] ?? ($_GET['id'] ?? 0));

        if (!$id) {
            api_response('error', 'Valid ID is required for deletion', null, 400);
        }

        $sccodeStr = (string)$sccode;
        if ($target === 'head') {
            // Check if subheads exist
            $chk = $conn->prepare("SELECT COUNT(*) as cnt FROM account_sub_head WHERE account_head_id = ?");
            $chk->bind_param("i", $id);
            $chk->execute();
            $cnt = $chk->get_result()->fetch_assoc()['cnt'] ?? 0;
            if ($cnt > 0) {
                api_response('error', 'Cannot delete Account Head while sub-heads exist. Remove sub-heads first.', null, 400);
            }

            $del = $conn->prepare("DELETE FROM account_head WHERE id = ? AND (sccode = ? OR sccode = '0')");
            $del->bind_param("is", $id, $sccodeStr);
            $del->execute();
            api_response('success', 'Account Head deleted successfully');
        } else {
            $del = $conn->prepare("DELETE FROM account_sub_head WHERE id = ? AND (sccode = ? OR sccode = '0')");
            $del->bind_param("is", $id, $sccodeStr);
            $del->execute();
            api_response('success', 'Sub-head deleted successfully');
        }
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
