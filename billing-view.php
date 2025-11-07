<?php
require_once 'header.php';

$id = intval($_GET['id'] ?? 0);
if(!$id){
    echo "<div class='alert alert-danger'>Invalid Invoice ID</div>";
    exit;
}

// ইনভয়েস ডিটেইল
$invoiceQ = $conn->prepare("
    SELECT bi.*, sc.scname 
    FROM billing_invoices bi
    JOIN scinfo sc ON sc.sccode = bi.sccode
    WHERE bi.id = ?
");
$invoiceQ->bind_param("i", $id);
$invoiceQ->execute();
$invoice = $invoiceQ->get_result()->fetch_assoc();
$invoiceQ->close();

if(!$invoice){
    echo "<div class='alert alert-danger'>Invoice not found</div>";
    exit;
}

// ইনভয়েস আইটেমস
$itemsQ = $conn->prepare("SELECT * FROM billing_items WHERE invoice_id=?");
$itemsQ->bind_param("i", $id);
$itemsQ->execute();
$items = $itemsQ->get_result()->fetch_all(MYSQLI_ASSOC);
$itemsQ->close();

// পেমেন্ট হিস্ট্রি
$paymentsQ = $conn->prepare("SELECT * FROM billing_payments WHERE invoice_id=? ORDER BY payment_date ASC");
$paymentsQ->bind_param("i", $id);
$paymentsQ->execute();
$payments = $paymentsQ->get_result()->fetch_all(MYSQLI_ASSOC);
$paymentsQ->close();
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-4">📄 ইনভয়েস #<?= $invoice['invoice_no'] ?: $invoice['id'] ?></h4>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>প্রতিষ্ঠান:</strong> <?= $invoice['scname'] ?></p>
            <p><strong>তারিখ:</strong> <?= $invoice['invoice_date'] ?></p>
            <p><strong>স্ট্যাটাস:</strong> <span class="badge bg-<?= $invoice['status']=='Paid'?'success':($invoice['status']=='Partial'?'info':'warning')?>"><?= $invoice['status'] ?></span></p>
        </div>
    </div>

    <!-- Invoice Items -->
    <div class="card mb-4">
        <div class="card-header">📝 ইনভয়েস আইটেমস</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>আইটেম নাম</th>
                        <th>পরিমাণ</th>
                        <th>দর</th>
                        <th>মোট</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; foreach($items as $it): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $it['item_name'] ?></td>
                        <td><?= $it['quantity'] ?></td>
                        <td><?= $it['rate'] ?></td>
                        <td><?= $it['total'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p class="mt-3"><strong>মোট:</strong> <?= $invoice['subtotal'] ?></p>
            <p><strong>ডিসকাউন্ট:</strong> <?= $invoice['discount'] ?></p>
            <p><strong>গ্র্যান্ড টোটাল:</strong> <?= $invoice['grandtotal'] ?></p>
            <p><strong>পেমেন্ট:</strong> <?= $invoice['paid_amount'] ?></p>
            <p><strong>বাকি:</strong> <?= $invoice['due_amount'] ?></p>
        </div>
    </div>

    <!-- Payments History -->
    <div class="card mb-4">
        <div class="card-header">💰 পেমেন্ট হিস্ট্রি</div>
        <div class="card-body">
            <?php if(count($payments)): ?>
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>তারিখ</th>
                        <th>পরিমাণ</th>
                        <th>পদ্ধতি</th>
                        <th>নোট</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; foreach($payments as $p): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $p['payment_date'] ?></td>
                        <td><?= $p['amount'] ?></td>
                        <td><?= $p['method'] ?></td>
                        <td><?= $p['note'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>কোনো পেমেন্ট পাওয়া যায়নি।</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-4">
        <button class="btn btn-info" onclick="window.open('billing-pdf.php?id=<?= $invoice['id'] ?>','_blank')">PDF ডাউনলোড / প্রিন্ট</button>
        <a href="billing-list.php" class="btn btn-secondary">🔙 ফিরে যাও</a>
    </div>
</div>

<?php require_once 'footer.php'; ?>
