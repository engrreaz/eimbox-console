<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/global_values.php';
require_once __DIR__ . '/../core/inventory_db.php';

header('Content-Type: application/json; charset=utf-8');

// Ensure DB tables exist
init_inventory_database($conn);

$response = [
    'status' => 'error',
    'message' => 'Invalid Request'
];

if (empty($_SESSION['user_id']) || empty($sccode)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access. Please login again.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

try {
    // ==========================================
    // 1. SEARCH ITEMS BY BARCODE OR KEYWORD (FOR POS / PURCHASE / ISSUE)
    // ==========================================
    if ($action === 'search_items') {
        $query = trim($_GET['q'] ?? '');
        $type = trim($_GET['type'] ?? '');

        $sql = "SELECT i.*, 
                       COALESCE(c.category_name, 'General') AS category_name, 
                       COALESCE(u.unit_symbol, 'Pcs') AS unit_symbol 
                FROM inv_items i 
                LEFT JOIN inv_categories c ON i.category_id = c.id 
                LEFT JOIN inv_units u ON i.unit_id = u.id 
                WHERE i.sccode = ? AND i.status = 1";
        
        $params = [$sccode];
        $types = "i";

        if (!empty($type)) {
            $sql .= " AND i.item_type = ?";
            $params[] = $type;
            $types .= "s";
        }

        if (!empty($query)) {
            $sql .= " AND (i.item_name LIKE ? OR i.item_code LIKE ? OR i.barcode = ?)";
            $q_like = "%$query%";
            $params[] = $q_like;
            $params[] = $q_like;
            $params[] = $query;
            $types .= "sss";
        }

        $sql .= " ORDER BY i.item_name ASC LIMIT 50";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        $items = [];
        while ($row = $res->fetch_assoc()) {
            $items[] = $row;
        }

        echo json_encode(['status' => 'success', 'data' => $items]);
        exit;
    }

    // ==========================================
    // 2. SEARCH STUDENT FOR POS (BY STID / ROLL / NAME / MOBILE)
    // ==========================================
    if ($action === 'search_student') {
        $q = trim($_GET['q'] ?? $_POST['q'] ?? '');
        if (empty($q)) {
            echo json_encode(['status' => 'success', 'data' => []]);
            exit;
        }

        $q_like = "%$q%";
        $cur_session = $sessionyear ?? date('Y');

        // Multi-tenant student search with sessioninfo and students join
        $sql = "SELECT si.stid, si.rollno, si.classname, si.sectionname, si.sessionyear,
                       s.stnameeng, s.stnameben, s.guarmobile, s.photo_id
                FROM sessioninfo si 
                LEFT JOIN students s ON (si.stid = s.stid AND (s.sccode = si.sccode OR s.sccode = 0))
                WHERE si.sccode = ? 
                  AND (
                      si.stid = ? 
                      OR si.rollno = ? 
                      OR s.stnameeng LIKE ? 
                      OR s.stnameben LIKE ? 
                      OR s.guarmobile LIKE ?
                  )
                ORDER BY (si.sessionyear = ?) DESC, si.id DESC, si.rollno ASC 
                LIMIT 15";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $sccode, $q, $q, $q_like, $q_like, $q_like, $cur_session);
        $stmt->execute();
        $res = $stmt->get_result();

        $students = [];
        $seen = [];
        while ($row = $res->fetch_assoc()) {
            if (!isset($seen[$row['stid']])) {
                $seen[$row['stid']] = true;
                $students[] = [
                    'stid' => $row['stid'],
                    'rollno' => $row['rollno'],
                    'classname' => $row['classname'] ?? '',
                    'sectionname' => $row['sectionname'] ?? '',
                    'sessionyear' => $row['sessionyear'] ?? '',
                    'stnameeng' => $row['stnameeng'] ?? '',
                    'stnameben' => $row['stnameben'] ?? '',
                    'guarmobile' => $row['guarmobile'] ?? '',
                    'photo_id' => $row['photo_id'] ?? ''
                ];
            }
        }

        // Fallback: If no records found in sessioninfo, search directly in students table
        if (empty($students)) {
            $stmt_fb = $conn->prepare("SELECT stid, stnameeng, stnameben, guarmobile, photo_id 
                FROM students 
                WHERE sccode = ? 
                  AND (stid = ? OR stnameeng LIKE ? OR stnameben LIKE ? OR guarmobile LIKE ?) 
                ORDER BY id DESC LIMIT 15");
            $stmt_fb->bind_param("issss", $sccode, $q, $q_like, $q_like, $q_like);
            $stmt_fb->execute();
            $res_fb = $stmt_fb->get_result();
            while ($row = $res_fb->fetch_assoc()) {
                if (!isset($seen[$row['stid']])) {
                    $seen[$row['stid']] = true;
                    $students[] = [
                        'stid' => $row['stid'],
                        'rollno' => 'N/A',
                        'classname' => 'General',
                        'sectionname' => '',
                        'sessionyear' => '',
                        'stnameeng' => $row['stnameeng'] ?? '',
                        'stnameben' => $row['stnameben'] ?? '',
                        'guarmobile' => $row['guarmobile'] ?? '',
                        'photo_id' => $row['photo_id'] ?? ''
                    ];
                }
            }
        }

        echo json_encode(['status' => 'success', 'data' => $students]);
        exit;
    }

    // ==========================================
    // 3. SAVE / EDIT ITEM MASTER
    // ==========================================
    if ($action === 'save_item') {
        $item_id = intval($_POST['item_id'] ?? 0);
        $item_name = trim($_POST['item_name'] ?? '');
        $item_code = trim($_POST['item_code'] ?? '');
        $barcode = trim($_POST['barcode'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $unit_id = intval($_POST['unit_id'] ?? 0);
        $item_type = in_array($_POST['item_type'] ?? '', ['SaleItem', 'Consumable', 'Asset']) ? $_POST['item_type'] : 'SaleItem';
        $purchase_price = floatval($_POST['purchase_price'] ?? 0);
        $sale_price = floatval($_POST['sale_price'] ?? 0);
        $current_stock = intval($_POST['current_stock'] ?? 0);
        $reorder_level = intval($_POST['reorder_level'] ?? 5);
        $location_rack = trim($_POST['location_rack'] ?? '');

        if (empty($item_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Item name is required!']);
            exit;
        }

        if (empty($item_code)) {
            $item_code = 'ITM-' . date('ymd') . '-' . rand(100, 999);
        }

        if ($item_id > 0) {
            $stmt = $conn->prepare("UPDATE inv_items SET 
                item_name = ?, item_code = ?, barcode = ?, category_id = ?, unit_id = ?, 
                item_type = ?, purchase_price = ?, sale_price = ?, reorder_level = ?, location_rack = ?
                WHERE id = ? AND sccode = ?");
            $stmt->bind_param("sssiisddisii", $item_name, $item_code, $barcode, $category_id, $unit_id, $item_type, $purchase_price, $sale_price, $reorder_level, $location_rack, $item_id, $sccode);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Item updated successfully.']);
            exit;
        } else {
            $stmt = $conn->prepare("INSERT INTO inv_items 
                (sccode, item_code, barcode, item_name, category_id, unit_id, item_type, purchase_price, sale_price, current_stock, reorder_level, location_rack, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssiisddiiss", $sccode, $item_code, $barcode, $item_name, $category_id, $unit_id, $item_type, $purchase_price, $sale_price, $current_stock, $reorder_level, $location_rack, $usr);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'New item created successfully.', 'id' => $conn->insert_id]);
            exit;
        }
    }

    // ==========================================
    // 4. DELETE ITEM
    // ==========================================
    if ($action === 'delete_item') {
        $item_id = intval($_POST['item_id'] ?? 0);
        if ($item_id > 0) {
            $stmt = $conn->prepare("UPDATE inv_items SET status = 0 WHERE id = ? AND sccode = ?");
            $stmt->bind_param("ii", $item_id, $sccode);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Item removed from inventory.']);
            exit;
        }
    }

    // ==========================================
    // 5. STOCK ADJUSTMENT (DAMAGE / AUDIT)
    // ==========================================
    if ($action === 'adjust_stock') {
        $item_id = intval($_POST['item_id'] ?? 0);
        $adjust_type = trim($_POST['adjustment_type'] ?? 'Reduction');
        $qty = intval($_POST['qty'] ?? 0);
        $reason = trim($_POST['reason'] ?? 'Inventory audit adjustment');

        if ($item_id <= 0 || $qty <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid item or quantity.']);
            exit;
        }

        // Insert adjustment log
        $stmt_adj = $conn->prepare("INSERT INTO inv_stock_adjustments (sccode, item_id, adjustment_type, qty, reason, adjusted_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_adj->bind_param("iisiss", $sccode, $item_id, $adjust_type, $qty, $reason, $usr);
        $stmt_adj->execute();

        // Update item stock
        if ($adjust_type === 'Addition') {
            $up_stmt = $conn->prepare("UPDATE inv_items SET current_stock = current_stock + ? WHERE id = ? AND sccode = ?");
        } else {
            $up_stmt = $conn->prepare("UPDATE inv_items SET current_stock = GREATEST(0, current_stock - ?) WHERE id = ? AND sccode = ?");
        }
        $up_stmt->bind_param("iii", $qty, $item_id, $sccode);
        $up_stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'Stock adjusted successfully.']);
        exit;
    }

    // ==========================================
    // 6. PROCESS POS SALES (SALE INVOICE + AUTO STOCK DECREMENT + CASHBOOK INCOME)
    // ==========================================
    if ($action === 'process_pos_sale') {
        $raw_cart = $_POST['cart'] ?? '[]';
        $cart = json_decode($raw_cart, true);

        if (empty($cart) || !is_array($cart)) {
            echo json_encode(['status' => 'error', 'message' => 'Cart is empty. Please add items to sale.']);
            exit;
        }

        $sale_date = trim($_POST['sale_date'] ?? date('Y-m-d'));
        $student_id = intval($_POST['student_id'] ?? 0);
        $student_stid = trim($_POST['student_stid'] ?? '');
        $customer_name = trim($_POST['customer_name'] ?? 'Counter Customer');
        $customer_phone = trim($_POST['customer_phone'] ?? '');
        $payment_mode = trim($_POST['payment_mode'] ?? 'Cash');
        $discount_amount = floatval($_POST['discount_amount'] ?? 0);
        $paid_amount = floatval($_POST['paid_amount'] ?? 0);

        // Generate Invoice Number
        $invoice_no = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        // Calculate totals
        $total_amount = 0;
        $items_to_insert = [];

        foreach ($cart as $c) {
            $itm_id = intval($c['id']);
            $itm_qty = intval($c['qty']);
            $itm_price = floatval($c['price']);

            if ($itm_id > 0 && $itm_qty > 0) {
                // Fetch current purchase rate
                $chk_itm = $conn->prepare("SELECT purchase_price, current_stock, item_name FROM inv_items WHERE id = ? AND sccode = ? LIMIT 1");
                $chk_itm->bind_param("ii", $itm_id, $sccode);
                $chk_itm->execute();
                $r_chk = $chk_itm->get_result()->fetch_assoc();
                $pr_rate = floatval($r_chk['purchase_price'] ?? 0);
                $subtot = $itm_price * $itm_qty;
                $total_amount += $subtot;

                $items_to_insert[] = [
                    'item_id' => $itm_id,
                    'item_name' => $r_chk['item_name'] ?? 'Item',
                    'qty' => $itm_qty,
                    'unit_price' => $itm_price,
                    'purchase_rate' => $pr_rate,
                    'subtotal' => $subtot
                ];
            }
        }

        if (empty($items_to_insert)) {
            echo json_encode(['status' => 'error', 'message' => 'No valid items found in cart.']);
            exit;
        }

        $net_payable = max(0, $total_amount - $discount_amount);
        $change_amount = max(0, $paid_amount - $net_payable);

        // 1. Insert into inv_sales
        $stmt_sale = $conn->prepare("INSERT INTO inv_sales 
            (sccode, invoice_no, sale_date, student_id, student_stid, customer_name, customer_phone, total_amount, discount_amount, net_payable, paid_amount, change_amount, payment_mode, sale_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_sale->bind_param("ississsdddddss", 
            $sccode, $invoice_no, $sale_date, $student_id, $student_stid, 
            $customer_name, $customer_phone, $total_amount, $discount_amount, 
            $net_payable, $paid_amount, $change_amount, $payment_mode, $usr
        );
        $stmt_sale->execute();
        $sale_id = $conn->insert_id;

        // 2. Insert items and decrement stock
        $stmt_item = $conn->prepare("INSERT INTO inv_sale_items (sccode, sale_id, item_id, qty, unit_price, purchase_rate, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_dec = $conn->prepare("UPDATE inv_items SET current_stock = GREATEST(0, current_stock - ?) WHERE id = ? AND sccode = ?");

        foreach ($items_to_insert as $it) {
            $stmt_item->bind_param("iiiiddd", $sccode, $sale_id, $it['item_id'], $it['qty'], $it['unit_price'], $it['purchase_rate'], $it['subtotal']);
            $stmt_item->execute();

            $stmt_dec->bind_param("iii", $it['qty'], $it['item_id'], $sccode);
            $stmt_dec->execute();
        }

        // 3. Post to Cashbook (if paid by Cash/Online)
        $cashbook_entry_id = null;
        if ($paid_amount > 0 && in_array($payment_mode, ['Cash', 'bKash', 'Nagad', 'Card'])) {
            try {
                $cur_month = intval(date('n', strtotime($sale_date)));
                $cur_year = intval(date('Y', strtotime($sale_date)));
                $target_session = $sessionyear ?? date('Y');
                $particulars = "Stationery / Store Sales - Inv #$invoice_no ($customer_name)";

                // Check if head exists or default to 0
                $cb_head = 0;
                $h_chk = $conn->query("SELECT id FROM account_head WHERE sccode = '$sccode' AND (account_head LIKE '%Store%' OR account_head LIKE '%Stationery%' OR account_head LIKE '%Sales%') LIMIT 1");
                if ($h_chk && $hr = $h_chk->fetch_assoc()) {
                    $cb_head = intval($hr['id']);
                }

                $cb_stmt = $conn->prepare("INSERT INTO cashbook 
                    (sccode, sessionyear, month, year, date, slots, account_head, account_sub_head, partid, particulars, amount, income, expenditure, type, memono, entryby, entrytime, status) 
                    VALUES (?, ?, ?, ?, ?, 'General', ?, 0, 0, ?, ?, ?, 0.00, 'Income', ?, ?, NOW(), 1)");
                $cb_stmt->bind_param("isiisisddss", 
                    $sccode, $target_session, $cur_month, $cur_year, $sale_date, 
                    $cb_head, $particulars, $paid_amount, $paid_amount, 
                    $sale_id, $usr
                );
                if ($cb_stmt->execute()) {
                    $cashbook_entry_id = $conn->insert_id;
                    $conn->query("UPDATE inv_sales SET cashbook_entry_id = $cashbook_entry_id WHERE id = $sale_id AND sccode = $sccode");
                }
            } catch (Exception $e_cb) {
                // Cashbook error handled gracefully
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Sale processed successfully.',
            'invoice' => [
                'sale_id' => $sale_id,
                'invoice_no' => $invoice_no,
                'sale_date' => $sale_date,
                'customer_name' => $customer_name,
                'student_stid' => $student_stid,
                'customer_phone' => $customer_phone,
                'total_amount' => $total_amount,
                'discount_amount' => $discount_amount,
                'net_payable' => $net_payable,
                'paid_amount' => $paid_amount,
                'change_amount' => $change_amount,
                'payment_mode' => $payment_mode,
                'items' => $items_to_insert
            ]
        ]);
        exit;
    }

    // ==========================================
    // 6.1. GET SALE INVOICE DETAILS FOR REPRINT
    // ==========================================
    if ($action === 'get_sale_details') {
        $sale_id = intval($_GET['sale_id'] ?? $_POST['sale_id'] ?? 0);
        $invoice_no = trim($_GET['invoice_no'] ?? $_POST['invoice_no'] ?? '');

        if ($sale_id <= 0 && empty($invoice_no)) {
            echo json_encode(['status' => 'error', 'message' => 'Please provide a valid Sale ID or Invoice Number.']);
            exit;
        }

        if ($sale_id > 0) {
            $stmt_s = $conn->prepare("SELECT * FROM inv_sales WHERE id = ? AND sccode = ? LIMIT 1");
            $stmt_s->bind_param("ii", $sale_id, $sccode);
        } else {
            $stmt_s = $conn->prepare("SELECT * FROM inv_sales WHERE invoice_no = ? AND sccode = ? LIMIT 1");
            $stmt_s->bind_param("si", $invoice_no, $sccode);
        }

        $stmt_s->execute();
        $sale = $stmt_s->get_result()->fetch_assoc();

        if (!$sale) {
            echo json_encode(['status' => 'error', 'message' => 'Sale invoice record not found.']);
            exit;
        }

        $sid = intval($sale['id']);
        $stmt_si = $conn->prepare("SELECT si.*, COALESCE(i.item_name, 'Store Product') AS item_name, COALESCE(i.item_code, '') AS item_code, COALESCE(u.unit_symbol, 'Pcs') AS unit_sym 
            FROM inv_sale_items si 
            LEFT JOIN inv_items i ON si.item_id = i.id 
            LEFT JOIN inv_units u ON i.unit_id = u.id 
            WHERE si.sale_id = ? AND si.sccode = ? 
            ORDER BY si.id ASC");
        $stmt_si->bind_param("ii", $sid, $sccode);
        $stmt_si->execute();
        $res_si = $stmt_si->get_result();

        $items = [];
        while ($row = $res_si->fetch_assoc()) {
            $items[] = $row;
        }

        echo json_encode([
            'status' => 'success',
            'sale' => $sale,
            'items' => $items,
            'invoice' => [
                'sale_id' => $sale['id'],
                'invoice_no' => $sale['invoice_no'],
                'sale_date' => $sale['sale_date'],
                'customer_name' => $sale['customer_name'],
                'student_stid' => $sale['student_stid'],
                'customer_phone' => $sale['customer_phone'] ?? '',
                'total_amount' => $sale['total_amount'],
                'discount_amount' => $sale['discount_amount'],
                'net_payable' => $sale['net_payable'],
                'paid_amount' => $sale['paid_amount'],
                'change_amount' => $sale['change_amount'],
                'payment_mode' => $sale['payment_mode'],
                'items' => $items
            ]
        ]);
        exit;
    }

    // ==========================================
    // 6.2. GET RECENT SALES LIST (FOR POS & DASHBOARD)
    // ==========================================
    if ($action === 'get_recent_sales') {
        $limit = intval($_GET['limit'] ?? $_POST['limit'] ?? 30);
        if ($limit <= 0 || $limit > 100) $limit = 30;

        $stmt = $conn->prepare("SELECT s.*, COUNT(si.id) AS item_count 
            FROM inv_sales s 
            LEFT JOIN inv_sale_items si ON s.id = si.sale_id 
            WHERE s.sccode = ? 
            GROUP BY s.id 
            ORDER BY s.id DESC 
            LIMIT ?");
        $stmt->bind_param("ii", $sccode, $limit);
        $stmt->execute();
        $res = $stmt->get_result();

        $sales = [];
        while ($row = $res->fetch_assoc()) {
            $sales[] = $row;
        }

        echo json_encode(['status' => 'success', 'data' => $sales]);
        exit;
    }

    // ==========================================
    // 7. SAVE PURCHASE (STOCK IN + CASHBOOK EXPENSE)
    // ==========================================
    if ($action === 'save_purchase') {
        $purchase_no = trim($_POST['purchase_no'] ?? '');
        $supplier_id = intval($_POST['supplier_id'] ?? 0);
        $purchase_date = trim($_POST['purchase_date'] ?? date('Y-m-d'));
        $payment_method = trim($_POST['payment_method'] ?? 'Cash');
        $discount_amount = floatval($_POST['discount_amount'] ?? 0);
        $paid_amount = floatval($_POST['paid_amount'] ?? 0);
        $remarks = trim($_POST['remarks'] ?? '');
        $raw_items = $_POST['items'] ?? '[]';
        $items = json_decode($raw_items, true);

        if (empty($purchase_no)) {
            $purchase_no = 'PUR-' . date('Ymd') . '-' . rand(100, 999);
        }

        if (empty($items) || !is_array($items)) {
            echo json_encode(['status' => 'error', 'message' => 'Please add at least one item to purchase.']);
            exit;
        }

        $total_amount = 0;
        foreach ($items as $it) {
            $total_amount += (floatval($it['unit_price']) * intval($it['qty']));
        }
        $net_amount = max(0, $total_amount - $discount_amount);
        $due_amount = max(0, $net_amount - $paid_amount);

        // 1. Insert Purchase Header
        $stmt_p = $conn->prepare("INSERT INTO inv_purchases 
            (sccode, purchase_no, supplier_id, purchase_date, total_amount, discount_amount, net_amount, paid_amount, due_amount, payment_method, remarks, entryby) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_p->bind_param("isisdddddsss", $sccode, $purchase_no, $supplier_id, $purchase_date, $total_amount, $discount_amount, $net_amount, $paid_amount, $due_amount, $payment_method, $remarks, $usr);
        $stmt_p->execute();
        $purchase_id = $conn->insert_id;

        // 2. Insert items and increment stock
        $stmt_p_item = $conn->prepare("INSERT INTO inv_purchase_items (sccode, purchase_id, item_id, qty, unit_price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_inc = $conn->prepare("UPDATE inv_items SET current_stock = current_stock + ?, purchase_price = ? WHERE id = ? AND sccode = ?");

        foreach ($items as $it) {
            $itm_id = intval($it['item_id']);
            $qty = intval($it['qty']);
            $rate = floatval($it['unit_price']);
            $subtot = $qty * $rate;

            $stmt_p_item->bind_param("iiiidd", $sccode, $purchase_id, $itm_id, $qty, $rate, $subtot);
            $stmt_p_item->execute();

            $stmt_inc->bind_param("idii", $qty, $rate, $itm_id, $sccode);
            $stmt_inc->execute();
        }

        // 3. Post to Cashbook (if paid)
        if ($paid_amount > 0 && in_array($payment_method, ['Cash', 'Bank', 'Cheque'])) {
            try {
                $cur_month = intval(date('n', strtotime($purchase_date)));
                $cur_year = intval(date('Y', strtotime($purchase_date)));
                $target_session = $sessionyear ?? date('Y');
                $particulars = "Store Purchase - Challan #$purchase_no";

                $cb_head = 0;
                $h_chk = $conn->query("SELECT id FROM account_head WHERE sccode = '$sccode' AND (account_head LIKE '%Purchase%' OR account_head LIKE '%Store%') LIMIT 1");
                if ($h_chk && $hr = $h_chk->fetch_assoc()) {
                    $cb_head = intval($hr['id']);
                }

                $cb_stmt = $conn->prepare("INSERT INTO cashbook 
                    (sccode, sessionyear, month, year, date, slots, account_head, account_sub_head, partid, particulars, amount, income, expenditure, type, memono, entryby, entrytime, status) 
                    VALUES (?, ?, ?, ?, ?, 'General', ?, 0, 0, ?, ?, 0.00, ?, 'Expenditure', ?, ?, NOW(), 1)");
                $cb_stmt->bind_param("isiisisddss", 
                    $sccode, $target_session, $cur_month, $cur_year, $purchase_date, 
                    $cb_head, $particulars, $paid_amount, $paid_amount, 
                    $purchase_id, $usr
                );
                if ($cb_stmt->execute()) {
                    $cb_id = $conn->insert_id;
                    $conn->query("UPDATE inv_purchases SET cashbook_entry_id = $cb_id WHERE id = $purchase_id AND sccode = $sccode");
                }
            } catch (Exception $e_cb) {
                // Cashbook error handled gracefully
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Purchase recorded and stock added successfully.', 'purchase_id' => $purchase_id]);
        exit;
    }

    // ==========================================
    // 7.1. GET PURCHASE DETAILS
    // ==========================================
    if ($action === 'get_purchase_details') {
        $purchase_id = intval($_POST['purchase_id'] ?? $_GET['purchase_id'] ?? 0);
        if ($purchase_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid purchase ID.']);
            exit;
        }

        $stmt_p = $conn->prepare("SELECT p.*, COALESCE(s.supplier_name, 'Direct Local Market / Cash') AS supplier_name, s.company_name, s.phone AS supplier_phone, s.email AS supplier_email, s.address AS supplier_address 
            FROM inv_purchases p 
            LEFT JOIN inv_suppliers s ON p.supplier_id = s.id 
            WHERE p.id = ? AND p.sccode = ? LIMIT 1");
        $stmt_p->bind_param("ii", $purchase_id, $sccode);
        $stmt_p->execute();
        $purchase = $stmt_p->get_result()->fetch_assoc();

        if (!$purchase) {
            echo json_encode(['status' => 'error', 'message' => 'Purchase record not found.']);
            exit;
        }

        $stmt_it = $conn->prepare("SELECT pi.*, i.item_name, i.item_code, COALESCE(u.unit_symbol, 'Pcs') AS unit_sym 
            FROM inv_purchase_items pi 
            LEFT JOIN inv_items i ON pi.item_id = i.id 
            LEFT JOIN inv_units u ON i.unit_id = u.id 
            WHERE pi.purchase_id = ? AND pi.sccode = ? 
            ORDER BY pi.id ASC");
        $stmt_it->bind_param("ii", $purchase_id, $sccode);
        $stmt_it->execute();
        $res_it = $stmt_it->get_result();
        $items = [];
        while ($row = $res_it->fetch_assoc()) {
            $items[] = $row;
        }

        echo json_encode(['status' => 'success', 'purchase' => $purchase, 'items' => $items]);
        exit;
    }

    // ==========================================
    // 7.2. DELETE / CANCEL PURCHASE (SAFE STOCK REVERSAL & CASHBOOK VOUCHER REMOVAL)
    // ==========================================
    if ($action === 'delete_purchase') {
        $purchase_id = intval($_POST['purchase_id'] ?? 0);
        if ($purchase_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid purchase ID.']);
            exit;
        }

        // 1. Fetch Purchase Header
        $stmt_chk = $conn->prepare("SELECT * FROM inv_purchases WHERE id = ? AND sccode = ? LIMIT 1");
        $stmt_chk->bind_param("ii", $purchase_id, $sccode);
        $stmt_chk->execute();
        $purchase = $stmt_chk->get_result()->fetch_assoc();

        if (!$purchase) {
            echo json_encode(['status' => 'error', 'message' => 'Purchase record not found.']);
            exit;
        }

        if (isset($purchase['status']) && intval($purchase['status']) === 0) {
            echo json_encode(['status' => 'error', 'message' => 'This purchase record is already cancelled.']);
            exit;
        }

        // 2. Fetch items and validate current stock
        $stmt_pitems = $conn->prepare("SELECT pi.*, i.item_name, i.current_stock 
            FROM inv_purchase_items pi 
            JOIN inv_items i ON pi.item_id = i.id 
            WHERE pi.purchase_id = ? AND pi.sccode = ?");
        $stmt_pitems->bind_param("ii", $purchase_id, $sccode);
        $stmt_pitems->execute();
        $res_pitems = $stmt_pitems->get_result();
        $items_to_revert = [];

        while ($pitem = $res_pitems->fetch_assoc()) {
            $curr_stock = intval($pitem['current_stock'] ?? 0);
            $pur_qty = intval($pitem['qty'] ?? 0);
            $item_name = $pitem['item_name'] ?? 'Item #' . $pitem['item_id'];

            if ($curr_stock < $pur_qty) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => "Cannot cancel purchase. Item '{$item_name}' currently has only {$curr_stock} in stock, but this challan added {$pur_qty} (items have already been sold, issued or deducted)."
                ]);
                exit;
            }
            $items_to_revert[] = $pitem;
        }

        // 3. Rollback Stock
        $stmt_deduct = $conn->prepare("UPDATE inv_items SET current_stock = GREATEST(0, current_stock - ?) WHERE id = ? AND sccode = ?");
        foreach ($items_to_revert as $rv) {
            $r_qty = intval($rv['qty']);
            $r_item_id = intval($rv['item_id']);
            $stmt_deduct->bind_param("iii", $r_qty, $r_item_id, $sccode);
            $stmt_deduct->execute();
        }

        // 4. Reverse Cashbook Voucher if present
        $cb_id = intval($purchase['cashbook_entry_id'] ?? 0);
        if ($cb_id > 0) {
            $conn->query("UPDATE cashbook SET status = 0 WHERE id = $cb_id AND sccode = $sccode");
        } else {
            // Also check by memono if matching purchase_id
            $conn->query("UPDATE cashbook SET status = 0 WHERE memono = '$purchase_id' AND sccode = $sccode AND type = 'Expenditure'");
        }

        // 5. Mark purchase as Cancelled (status = 0)
        $cancel_note = " [Cancelled by " . $usr . " on " . date('Y-m-d H:i') . "]";
        $stmt_up_p = $conn->prepare("UPDATE inv_purchases SET status = 0, remarks = CONCAT(COALESCE(remarks, ''), ?) WHERE id = ? AND sccode = ?");
        $stmt_up_p->bind_param("sii", $cancel_note, $purchase_id, $sccode);
        $stmt_up_p->execute();

        echo json_encode(['status' => 'success', 'message' => 'Purchase challan cancelled successfully. Stock has been rolled back and cashbook entry voided.']);
        exit;
    }

    // ==========================================
    // 8. SAVE DEPARTMENTAL ISSUE (INTERNAL CONSUMPTION)
    // ==========================================
    if ($action === 'save_issue') {
        $issue_no = 'ISS-' . date('Ymd') . '-' . rand(100, 999);
        $issue_date = trim($_POST['issue_date'] ?? date('Y-m-d'));
        $issued_to_type = trim($_POST['issued_to_type'] ?? 'Department');
        $issued_to_name = trim($_POST['issued_to_name'] ?? '');
        $purpose = trim($_POST['purpose'] ?? '');
        $raw_items = $_POST['items'] ?? '[]';
        $items = json_decode($raw_items, true);

        if (empty($issued_to_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Recipient name or department is required!']);
            exit;
        }

        if (empty($items) || !is_array($items)) {
            echo json_encode(['status' => 'error', 'message' => 'Please select at least one item to issue.']);
            exit;
        }

        $stmt_iss = $conn->prepare("INSERT INTO inv_issues (sccode, issue_no, issue_date, issued_to_type, issued_to_name, issued_by, purpose) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_iss->bind_param("issssss", $sccode, $issue_no, $issue_date, $issued_to_type, $issued_to_name, $usr, $purpose);
        $stmt_iss->execute();
        $issue_id = $conn->insert_id;

        $stmt_iss_item = $conn->prepare("INSERT INTO inv_issue_items (sccode, issue_id, item_id, qty) VALUES (?, ?, ?, ?)");
        $stmt_dec = $conn->prepare("UPDATE inv_items SET current_stock = GREATEST(0, current_stock - ?) WHERE id = ? AND sccode = ?");

        foreach ($items as $it) {
            $itm_id = intval($it['item_id']);
            $qty = intval($it['qty']);

            $stmt_iss_item->bind_param("iiii", $sccode, $issue_id, $itm_id, $qty);
            $stmt_iss_item->execute();

            $stmt_dec->bind_param("iii", $qty, $itm_id, $sccode);
            $stmt_dec->execute();
        }

        echo json_encode(['status' => 'success', 'message' => 'Items issued successfully.', 'issue_no' => $issue_no]);
        exit;
    }

    // ==========================================
    // 9. SUPPLIER MANAGEMENT (SAVE & DELETE)
    // ==========================================
    if ($action === 'save_supplier') {
        $supplier_id = intval($_POST['supplier_id'] ?? 0);
        $supplier_name = trim($_POST['supplier_name'] ?? '');
        $company_name = trim($_POST['company_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $opening_balance = floatval($_POST['opening_balance'] ?? 0);

        if (empty($supplier_name) || empty($phone)) {
            echo json_encode(['status' => 'error', 'message' => 'Supplier name and phone number are required.']);
            exit;
        }

        if ($supplier_id > 0) {
            $stmt = $conn->prepare("UPDATE inv_suppliers SET supplier_name = ?, company_name = ?, phone = ?, email = ?, address = ?, opening_balance = ? WHERE id = ? AND sccode = ?");
            $stmt->bind_param("sssssdii", $supplier_name, $company_name, $phone, $email, $address, $opening_balance, $supplier_id, $sccode);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Supplier updated successfully.']);
            exit;
        } else {
            $stmt = $conn->prepare("INSERT INTO inv_suppliers (sccode, supplier_name, company_name, phone, email, address, opening_balance) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssd", $sccode, $supplier_name, $company_name, $phone, $email, $address, $opening_balance);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Supplier added successfully.']);
            exit;
        }
    }

    if ($action === 'delete_supplier') {
        $supplier_id = intval($_POST['supplier_id'] ?? 0);
        if ($supplier_id > 0) {
            $stmt = $conn->prepare("UPDATE inv_suppliers SET status = 0 WHERE id = ? AND sccode = ?");
            $stmt->bind_param("ii", $supplier_id, $sccode);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Supplier removed.']);
            exit;
        }
    }

    // ==========================================
    // 10. FIXED ASSETS MANAGEMENT (SAVE & DELETE)
    // ==========================================
    if ($action === 'save_asset') {
        $asset_id = intval($_POST['asset_id'] ?? 0);
        $asset_tag_code = trim($_POST['asset_tag_code'] ?? '');
        $asset_name = trim($_POST['asset_name'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $purchase_date = trim($_POST['purchase_date'] ?? date('Y-m-d'));
        $purchase_cost = floatval($_POST['purchase_cost'] ?? 0);
        $location_room = trim($_POST['location_room'] ?? 'General Room');
        $custodian_name = trim($_POST['custodian_name'] ?? '');
        $warranty_expiry = !empty($_POST['warranty_expiry']) ? $_POST['warranty_expiry'] : null;
        $asset_condition = in_array($_POST['asset_condition'] ?? '', ['Good', 'Under Repair', 'Damaged', 'Disposed']) ? $_POST['asset_condition'] : 'Good';
        $depreciation_rate_pct = floatval($_POST['depreciation_rate_pct'] ?? 0);
        $current_valuation = floatval($_POST['current_valuation'] ?? $purchase_cost);
        $remarks = trim($_POST['remarks'] ?? '');

        if (empty($asset_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Asset name is required.']);
            exit;
        }

        if (empty($asset_tag_code)) {
            $asset_tag_code = 'AST-' . date('Y') . '-' . rand(1000, 9999);
        }

        if ($asset_id > 0) {
            $stmt = $conn->prepare("UPDATE fixed_assets SET 
                asset_tag_code = ?, asset_name = ?, category_id = ?, purchase_date = ?, purchase_cost = ?, 
                location_room = ?, custodian_name = ?, warranty_expiry = ?, asset_condition = ?, 
                depreciation_rate_pct = ?, current_valuation = ?, remarks = ?
                WHERE id = ? AND sccode = ?");
            $stmt->bind_param("ssisdssssddsii", $asset_tag_code, $asset_name, $category_id, $purchase_date, $purchase_cost, $location_room, $custodian_name, $warranty_expiry, $asset_condition, $depreciation_rate_pct, $current_valuation, $remarks, $asset_id, $sccode);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Fixed asset record updated.']);
            exit;
        } else {
            $stmt = $conn->prepare("INSERT INTO fixed_assets 
                (sccode, asset_tag_code, asset_name, category_id, purchase_date, purchase_cost, location_room, custodian_name, warranty_expiry, asset_condition, depreciation_rate_pct, current_valuation, remarks) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issisdssssdds", $sccode, $asset_tag_code, $asset_name, $category_id, $purchase_date, $purchase_cost, $location_room, $custodian_name, $warranty_expiry, $asset_condition, $depreciation_rate_pct, $current_valuation, $remarks);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'New asset registered successfully.']);
            exit;
        }
    }

    if ($action === 'delete_asset') {
        $asset_id = intval($_POST['asset_id'] ?? 0);
        if ($asset_id > 0) {
            $stmt = $conn->prepare("DELETE FROM fixed_assets WHERE id = ? AND sccode = ?");
            $stmt->bind_param("ii", $asset_id, $sccode);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Asset record deleted.']);
            exit;
        }
    }

    // ==========================================
    // 11. CATEGORY & UNIT MANAGEMENT (GLOBAL sccode=0 & TENANT sccode)
    // ==========================================
    if ($action === 'get_units') {
        $u_res = $conn->query("SELECT *, (sccode = 0) AS is_global FROM inv_units WHERE (sccode = '$sccode' OR sccode = 0) AND status = 1 ORDER BY (sccode = '$sccode') DESC, id DESC");
        $units = [];
        while ($u = $u_res->fetch_assoc()) {
            $units[] = $u;
        }
        echo json_encode(['status' => 'success', 'data' => $units]);
        exit;
    }

    if ($action === 'save_unit') {
        $unit_name = trim($_POST['unit_name'] ?? '');
        $unit_symbol = trim($_POST['unit_symbol'] ?? '');
        if (!empty($unit_name) && !empty($unit_symbol)) {
            $stmt = $conn->prepare("INSERT INTO inv_units (sccode, unit_name, unit_symbol) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $sccode, $unit_name, $unit_symbol);
            $stmt->execute();
            $new_id = $conn->insert_id;
            echo json_encode([
                'status' => 'success', 
                'message' => "Unit '{$unit_name}' ({$unit_symbol}) saved successfully.", 
                'id' => $new_id,
                'unit_name' => $unit_name,
                'unit_symbol' => $unit_symbol
            ]);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Please provide both Unit Name and Symbol.']);
            exit;
        }
    }

    if ($action === 'delete_unit') {
        $unit_id = intval($_POST['unit_id'] ?? 0);
        if ($unit_id > 0) {
            // Check if global
            $chk = $conn->query("SELECT sccode FROM inv_units WHERE id = $unit_id LIMIT 1")->fetch_assoc();
            if ($chk && intval($chk['sccode']) === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Common system units (Global) cannot be deleted.']);
                exit;
            }
            $stmt = $conn->prepare("UPDATE inv_units SET status = 0 WHERE id = ? AND sccode = ?");
            $stmt->bind_param("ii", $unit_id, $sccode);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Custom unit deleted successfully.']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid unit ID.']);
            exit;
        }
    }

    if ($action === 'get_categories') {
        $c_res = $conn->query("SELECT *, (sccode = 0) AS is_global FROM inv_categories WHERE (sccode = '$sccode' OR sccode = 0) AND status = 1 ORDER BY (sccode = '$sccode') DESC, id DESC");
        $categories = [];
        while ($c = $c_res->fetch_assoc()) {
            $categories[] = $c;
        }
        echo json_encode(['status' => 'success', 'data' => $categories]);
        exit;
    }

    if ($action === 'save_category') {
        $cat_name = trim($_POST['category_name'] ?? '');
        $cat_type = trim($_POST['category_type'] ?? 'SaleItem');
        if (!empty($cat_name)) {
            $stmt = $conn->prepare("INSERT INTO inv_categories (sccode, category_name, category_type) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $sccode, $cat_name, $cat_type);
            $stmt->execute();
            $new_id = $conn->insert_id;
            echo json_encode([
                'status' => 'success', 
                'message' => "Category '{$cat_name}' saved successfully.", 
                'id' => $new_id,
                'category_name' => $cat_name,
                'category_type' => $cat_type
            ]);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Please provide Category Name.']);
            exit;
        }
    }

    if ($action === 'delete_category') {
        $category_id = intval($_POST['category_id'] ?? 0);
        if ($category_id > 0) {
            $chk = $conn->query("SELECT sccode FROM inv_categories WHERE id = $category_id LIMIT 1")->fetch_assoc();
            if ($chk && intval($chk['sccode']) === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Common system categories (Global) cannot be deleted.']);
                exit;
            }
            $stmt = $conn->prepare("UPDATE inv_categories SET status = 0 WHERE id = ? AND sccode = ?");
            $stmt->bind_param("ii", $category_id, $sccode);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Custom category deleted successfully.']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid category ID.']);
            exit;
        }
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

echo json_encode($response);
