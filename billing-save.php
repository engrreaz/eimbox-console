<?php
require_once 'core/config.php'; 
require_once 'core/db.php'; 
require_once 'core/global_values.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sccode = $_POST['sccode'];
    $subtotal = floatval($_POST['subtotal']);
    $discount = floatval($_POST['discount']);
    $grandtotal = floatval($_POST['grandtotal']);
    $invoice_date = date('Y-m-d');

    $conn->begin_transaction();

    try {
        // ✅ Step 1: ইনভয়েস তৈরি
        $stmt = $conn->prepare("
            INSERT INTO billing_invoices 
            (sccode, invoice_date, subtotal, discount, grandtotal, status)
            VALUES (?, ?, ?, ?, ?, 'unpaid')
        ");
        $stmt->bind_param("ssddd", $sccode, $invoice_date, $subtotal, $discount, $grandtotal);
        $stmt->execute();
        $invoice_id = $stmt->insert_id;
        $stmt->close();

        // ✅ Step 2: প্রতিটি আইটেম ইনসার্ট করা
        $item_names = $_POST['item_name'];
        $quantities = $_POST['quantity'];
        $rates = $_POST['rate'];
        $totals = $_POST['total'];

        $item_stmt = $conn->prepare("
            INSERT INTO billing_items (invoice_id, item_name, quantity, rate, total)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($item_names as $i => $item_name) {
            $qty = floatval($quantities[$i]);
            $rate = floatval($rates[$i]);
            $total = floatval($totals[$i]);
            $item_stmt->bind_param("isddd", $invoice_id, $item_name, $qty, $rate, $total);
            $item_stmt->execute();
        }

        $item_stmt->close();

        // ✅ Step 3: ট্রানজেকশন সম্পন্ন
        $conn->commit();

        echo "<script>
            alert('✅ ইনভয়েস সফলভাবে তৈরি হয়েছে!');
            window.location = 'billing-list.php';
        </script>";

    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>
            alert('❌ ইনভয়েস তৈরিতে সমস্যা হয়েছে!');
            // window.history.back();
        </script>";
    }
}
?>