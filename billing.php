<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <h4 class="fw-bold py-3">Invoices</h4>

    <div class="table-responsive">
        <table class="table table-striped table-bordered" id="invoiceTable">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Grand Total</th>
                    <th>Paid Amount</th>
                    <th>Due Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // DB include + connection assumed in header.php
                $sccode = $_SESSION['sccode']; // অথবা যেখান থেকে আসবে
                
                $stmt = $conn->prepare("SELECT * FROM billing_invoices WHERE sccode = ? ORDER BY invoice_date DESC");
                $stmt->bind_param("s", $sccode);
                $stmt->execute();
                $result = $stmt->get_result();
                $i = 1;

                while ($row = $result->fetch_assoc()) {
                    $statusClass = '';
                    switch ($row['payment_status']) {
                        case 'paid':
                            $statusClass = 'badge bg-success';
                            break;
                        case 'partial':
                            $statusClass = 'badge bg-warning text-dark';
                            break;
                        case 'unpaid':
                            $statusClass = 'badge bg-danger';
                            break;
                        case 'cancelled':
                            $statusClass = 'badge bg-secondary';
                            break;
                    }
                    echo "<tr>
                        <td>{$i}</td>
                        <td>{$row['invoice_no']}</td>
                        <td>{$row['invoice_date']}</td>
                        <td>{$row['customer_name']}</td>
                        <td>{$row['grand_total']}</td>
                        <td>{$row['paid_amount']}</td>
                        <td>{$row['due_amount']}</td>
                        <td><span class='{$statusClass}'>{$row['payment_status']}</span></td>
                        <td>
                            <a href='invoice_pdf.php?id={$row['id']}' class='btn btn-sm btn-primary' target='_blank'>Download PDF</a>
                            <button onclick='printInvoice({$row['id']})' class='btn btn-sm btn-secondary'>Print</button>
                        </td>
                    </tr>";
                    $i++;
                }

                $stmt->close();
                ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script>
    function printInvoice(id) {
        var url = 'invoice_pdf.php?id=' + id + '&print=1';
        var w = window.open(url, '_blank');
        w.focus();
    }
</script>
<!-- ----------------------------------- -->
</body>

</html>