<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-4">📑 সকল প্রতিষ্ঠানের ইনভয়েস তালিকা</h4>

    <div class="card">
        <div class="card-body">
            <table id="invoiceTable" class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>প্রতিষ্ঠান</th>
                        <th>ইনভয়েস নং</th>
                        <th>তারিখ</th>
                        <th>মোট</th>
                        <th>পেমেন্ট</th>
                        <th>ডিউ</th>
                        <th>স্ট্যাটাস</th>
                        <th>অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>



<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="paymentForm">
                <div class="modal-header">
                    <h5 class="modal-title">💰 ইনভয়েস পেমেন্ট</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="invoice_id" name="invoice_id">

                    <div class="mb-3">
                        <label class="form-label">পেমেন্টের তারিখ</label>
                        <input type="date" name="payment_date" class="form-control" required
                            value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">পেমেন্ট এমাউন্ট</label>
                        <input type="number" name="amount" class="form-control" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">পদ্ধতি</label>
                        <select name="method" class="form-select">
                            <option>Cash</option>
                            <option>Bank</option>
                            <option>Mobile</option>
                            <option>Adjustment</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">নোট (ঐচ্ছিক)</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">✅ Save Payment</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">❌ Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>




<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script>

    if (typeof bootstrap !== 'undefined') {
        let version = bootstrap.Tooltip.toString().match(/v\d+\.\d+\.\d+/);
        console.log("Bootstrap version:", version ? version[0] : "unknown");
    } else {
        console.log("Bootstrap loaded নয়");
    }


    function loadInvoices() {
        $.ajax({
            url: "ajax/load_invoices.php",
            type: "GET",
            dataType: "json",
            success: function (res) {
                console.log(res);
                let rows = "";
                let i = 1;
                res.forEach(row => {
                    let statusClass = {
                        "Pending": "warning",
                        "Partial": "info",
                        "Paid": "success",
                        "Cancelled": "secondary"
                    }[row.status] || "light";

                    rows += `
            <tr>
                <td>${i++}</td>
                <td>${row.scname}</td>
                <td>${row.invoice_no}</td>
                <td>${row.invoice_date}</td>
                <td>${row.grand_total}</td>
                <td>${row.paid_amount}</td>
                <td>${row.due_amount}</td>
                <td><span class="badge bg-${statusClass}">${row.status}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary viewBtn" data-id="${row.id}">দেখুন</button>
                    <button class="btn btn-sm btn-success payBtn" data-id="${row.id}">পেমেন্ট</button>
                    <button class="btn btn-sm btn-danger delBtn" data-id="${row.id}">ডিলিট</button>
                </td>
            </tr>`;
                });
                $("#invoiceTable tbody").html(rows);
            },
            error: function (xhr) {
                console.error("Ajax error:", xhr.responseText);
            }
        });
    }

    $(document).ready(function () {
        console.log("Document ready"); // এটা দেখো
        loadInvoices();
    });
</script>

<script>
    // Payment button
    $(document).on("click", ".payBtn", function () {
        const id = $(this).data("id");
        $("#invoice_id").val(id);
        // $("#paymentModal").modal("show");

        const modalEl = document.getElementById("paymentModal");
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    });

    // View button
    $(document).on("click", ".viewBtn", function () {
        const id = $(this).data("id");
        // Example: view invoice in a modal or new page
        window.open("billing-view.php?id=" + id, "_blank");
    });

    // Delete button
    $(document).on("click", ".delBtn", function () {
        const id = $(this).data("id");
        if (confirm("Are you sure you want to delete this invoice?")) {
            $.post("ajax/delete_invoice.php", { id: id }, function (res) {
                if (res.success) {
                    alert("Invoice deleted successfully!");
                    loadInvoices();
                } else {
                    alert("Error: " + res.message);
                }
            }, "json");
        }
    });




    // পেমেন্ট সাবমিট
    $("#paymentForm").on("submit", function (e) {
        e.preventDefault();

        $.ajax({
            url: "ajax/add_payment.php",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (res) {
                if (res.success) {
                    alert("✅ পেমেন্ট সফলভাবে সংরক্ষিত হয়েছে!");
                    $("#paymentModal").modal("hide");
                    loadInvoices(); // টেবিল রিফ্রেশ
                } else {
                    alert("❌ " + res.message);
                }
            }
        });
    });



</script>
<!-- ----------------------------------- -->
</body>

</html>