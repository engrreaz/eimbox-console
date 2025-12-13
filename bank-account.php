<?php
require_once 'header.php';

$accno = isset($_GET['accno']) ? intval($_GET['accno']) : 0;
$exid = isset($_GET['addnew']) ? intval($_GET['addnew']) : 0;
$today = date("Y-m-d");

/* ===========================================================
   1. CASHBOOK SYNC (only when ?cashbook-refno=1 )
=========================================================== */
if (isset($_GET['cashbook-refno'])) {

    $sql = "SELECT * FROM banktrans WHERE sccode=? AND verified=1 ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $sccode);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($bx = $result->fetch_assoc()) {
        $id = $bx['id'];
        $type = $bx['transtype'];
        $amt = $bx['amount'];
        $date = $bx['date'];
        $pid = $bx['partid'];
        $etime = $bx['entrytime'];

        $refno = $sccode . date("YmdHis", strtotime($etime));

        $u = $conn->prepare("UPDATE banktrans SET refno=? WHERE id=?");
        $u->bind_param("si", $refno, $id);
        $u->execute();

        /* TYPE DECISION */
        switch ($type) {
            case "Deposit":
                $tipe = "Expenditure";
                $pid2 = 1;
                $income = 0;
                $ex = $amt;
                break;
            case "Deduction":
                $tipe = "Expenditure";
                $pid2 = 4;
                $income = 0;
                $ex = $amt;
                break;
            case "Interest":
                $tipe = "Income";
                $pid2 = 3;
                $income = $amt;
                $ex = 0;
                break;
            default:
                $tipe = "Income";
                $pid2 = 2;
                $income = $amt;
                $ex = 0;
                break;
        }

        /* Prevent duplicate cashbook entry */
        $ck = $conn->prepare("SELECT id FROM cashbook WHERE sccode=? AND refno=?");
        $ck->bind_param("ss", $sccode, $refno);
        $ck->execute();
        if ($ck->get_result()->num_rows == 0) {

            $ins = $conn->prepare("
                INSERT INTO cashbook (
                    sccode, sessionyear, month, year, slots, date, type, refno, partid,
                    category, memono, particulars, income, expenditure, amount, 
                    entryby, entrytime, module, status
                )
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),'BANK',1)
            ");

            $cat = $type;
            $slot = "School";
            $month = date("m", strtotime($date));
            $year = date("Y", strtotime($date));
            $memo = "";
            $particular = "from Bank Transaction";

            $ins->bind_param(
                "ssssssssissssddd",
                $sccode,
                $year,
                $month,
                $year,
                $slot,
                $date,
                $tipe,
                $refno,
                $pid2,
                $cat,
                $memo,
                $particular,
                $income,
                $ex,
                $amt,
                $entryby,
                $cur
            );
            $ins->execute();

            /* Extra for Withdrawal */
            if (($type == "Withdraw" || $type == "Withdrawal") && $pid != 5) {
                $ins2 = $conn->prepare("
                    INSERT INTO cashbook (
                        sccode, sessionyear, month, year, slots, date, type, refno, partid,
                        category, memono, particulars, income, expenditure, amount, 
                        entryby, entrytime, module, status
                    )
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),'BANK',1)
                ");
                $ins2->bind_param(
                    "ssssssssissssddd",
                    $sccode,
                    $year,
                    $month,
                    $year,
                    $slot,
                    $date,
                    $tipe,
                    $refno,
                    $pid,
                    $cat,
                    $memo,
                    $particular,
                    $income,
                    $ex,
                    $amt,
                    $entryby,
                    $cur
                );
                $ins2->execute();
            }
        }
    }

    echo "<div class='alert alert-success'>Cashbook Sync Complete</div>";
}

/* ===========================================================
   2. FETCH BANK INFORMATION
=========================================================== */
$stmt = $conn->prepare("SELECT * FROM bankinfo WHERE sccode=? AND accno=?");
$stmt->bind_param("si", $sccode, $accno);
$stmt->execute();
$bankData = $stmt->get_result()->fetch_assoc();

$acctype = $bankData['acctype'] ?? '';
$bank = $bankData['bankname'] ?? '';
$branch = $bankData['branch'] ?? '';
$cdate = $bankData['closingdate'] ?? '';

/* ===========================================================
   3. FETCH SINGLE TRANSACTION FOR EDIT
=========================================================== */
$trx = [];
if ($exid > 0) {
    $t = $conn->prepare("SELECT * FROM banktrans WHERE id=?");
    $t->bind_param("i", $exid);
    $t->execute();
    $trx = $t->get_result()->fetch_assoc();
}
?>

<style>
    /* assets/css/bank-account.css */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.55);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1200;
    }

    .modal-box {
        background: #fff;
        width: 480px;
        max-width: 92%;
        padding: 18px;
        border-radius: 8px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
    }

    .modal-box h4 {
        margin-top: 0;
        margin-bottom: 12px;
        font-size: 18px;
    }

    .modal-box .form-control {
        margin-bottom: 8px;
    }

    .table-sm td,
    .table-sm th {
        vertical-align: middle;
        padding: 8px;
    }

    .badge.bg-success {
        background-color: #28a745 !important;
    }

    .btn {
        border-radius: 6px;
    }

    table.dataTable>tbody>tr>th,
    table.dataTable>tbody>tr>td {
        padding: 3px 5px;
    }
</style>


<!-- Bank Account Selector Modal -->
<div class="modal-overlay" id="bankSelectModal" style="display:none;">
    <div class="modal-box">
        <h5>Select Bank Account</h5>

        <?php
        $q = $conn->prepare("SELECT accno, bankname, branch, acctype FROM bankinfo WHERE sccode=? ORDER BY bankname, accno");
        $q->bind_param("s", $sccode);
        $q->execute();
        $res = $q->get_result();
        ?>

        <select id="modalAcc" class="form-control mb-2">
            <option value="">-- Select account --</option>
            <?php while ($b = $res->fetch_assoc()): ?>
                <option value="<?= intval($b['accno']) ?>">
                    <?= htmlspecialchars($b['bankname']) ?> - <?= htmlspecialchars($b['branch']) ?>
                    (<?= htmlspecialchars($b['acctype']) ?>) - <?= intval($b['accno']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="chooseAccount()">Open</button>
            <button class="btn btn-secondary"
                onclick="document.getElementById('bankSelectModal').style.display='none'">Close</button>
        </div>
    </div>
</div>

<script>
    function showBankSelect() { document.getElementById('bankSelectModal').style.display = 'flex'; }
    function chooseAccount() {
        let acc = document.getElementById('modalAcc').value;
        if (!acc) { alert('Please select a bank account'); return; }
        window.location = "bank-account.php?accno=" + acc;
    }
</script>



<div class="modal-overlay" id="trxModal_bank">
    <div class="modal-box">
        <h4 id="modalTitle"></h4>
        <input type="hidden" id="trx_id">

        <label>Date</label>
        <input type="date" id="trx_date" class="form-control mb-2">

        <label>Type</label>
        <select id="trx_type" class="form-control mb-2">
            <option>Deposit</option>
            <option>Withdraw</option>
            <option>Interest</option>
            <option>Deduction</option>
        </select>

        <label>Cheque No</label>
        <input type="text" id="trx_chq" class="form-control mb-2">

        <label>Amount</label>
        <input type="number" id="trx_amt" class="form-control mb-3">

        <div class="d-flex gap-2">
            <button onclick="saveTransaction(1)" id="btnSave" class="btn btn-primary">Save</button>
            <button onclick="saveTransaction(2)" id="btnVerify" class="btn btn-success">Update & Verify</button>
            <button onclick="saveTransaction(3)" id="btnDelete" class="btn btn-danger">Delete</button>
            <button onclick="closeTrxModal()" class="btn btn-secondary">Close</button>
        </div>
        <div id="msg" class="mt-2"></div>
    </div>
</div>

<div class="container-xxl mt-3">
   
    <div class="d-flex justify-content-between ">
        <div></div>
        <button class="btn btn-success" onclick="openAdd()" <?= (strtotime($cdate) !== false) ? 'disabled' : '' ?> >+ Add Transaction</button>
    </div>

    <div class="card mt-2">
        <div class="card-body">
            <b>Account:</b> <?= $accno ?><br>
            <b>Type:</b> <?= $acctype ?><br>
            <b>Bank:</b> <?= $bank ?><br>
            <b>Branch:</b> <?= $branch ?>
        </div>
    </div>

    <div class="card mt-3">
        <div class="table-responsive">
            <div class="text-center fs-4 fw-bold mt-4">Transactions</div>
            <table class="table table-bordered table-sm" id="trnxTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Opening</th>
                        <th>Type</th>
                        <th>Cheque</th>
                        <th>Credit</th>
                        <th>Debit</th>
                        <th>Balance</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q = $conn->prepare("SELECT * FROM banktrans WHERE sccode=? AND accno=? ORDER BY verified desc, slno , date");
                    $q->bind_param("si", $sccode, $accno);
                    $q->execute();
                    $res = $q->get_result();
                    $i = 1;

                    while ($r = $res->fetch_assoc()):
                        $credit = in_array($r['transtype'], ['Deposit', 'Interest']) ? number_format($r['amount'], 2) : "";
                        $debit = $credit == "" ? number_format($r['amount'], 2) : "";

                        if ($r['transtype'] == 'Withdrawal' || $r['transtype'] == 'Withdraw') {
                            $row_clr = 'danger';
                        } else if ($r['transtype'] == 'Deposit') {
                            $row_clr = 'success';
                        } else if ($r['transtype'] == 'Interest') {
                            $row_clr = 'info';
                        } else if ($r['transtype'] == 'Deduction') {
                            $row_clr = 'warning';
                        } else {
                            $row_clr = 'secondary';
                        }

                        ?>
                        <tr class="table-<?= $row_clr ?>">
                            <td><?= $i++ ?></td>
                            <td><?= $r['date'] ?></td>
                            <td><?= number_format($r['transopening'], 2) ?></td>
                            <td><?= $r['transtype'] ?></td>
                            <td><?= $r['chqno'] ?></td>
                            <td><?= $credit ?></td>
                            <td><?= $debit ?></td>
                            <td><?= number_format($r['balance'], 2) ?></td>
                            <td class="text-end">
                                <?php if (!$r['verified']): ?>
                                    <button class="btn btn-sm btn-primary py-1 my-0" id="trbtn<?= $r['id'] ?>"
                                        onclick="openEdit(<?= $r['id'] ?>)">Edit</button>
                                <?php else: ?>
                                    <span class="badge bg-success">Verified</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="bank/bank-sync-cashbook.php" class="btn btn-warning mt-3">Sync Cashbook</a>
</div>



<script></script>
<script>
    const BANK_ACCOUNT = "<?= $accno ?>";

    /* ===== unified modal functions (no name conflicts) ===== */
    function openTrxModal() {
        document.getElementById("trxModal_bank").style.display = "flex";
    }
    function closeTrxModal() {
        document.getElementById("trxModal_bank").style.display = "none";
    }

    function openBankModal() {
        document.getElementById("bankSelectModal").style.display = "flex";
    }
    function closeBankModal() {
        document.getElementById("bankSelectModal").style.display = "none";
    }

    /* ===== open Add Transaction (uses trx modal) ===== */
    function openAdd() {
        document.getElementById("modalTitle").innerText = "Add Transaction";
        document.getElementById("trx_id").value = "";
        document.getElementById("trx_date").value = "<?= $today ?>";
        document.getElementById("trx_type").value = "Withdraw";
        document.getElementById("trx_chq").value = "";
        document.getElementById("trx_amt").value = "";
        document.getElementById("btnVerify").style.display = "none";
        document.getElementById("btnDelete").style.display = "none";
        openTrxModal();
    }

    /* ===== open Edit Transaction (fetch then show trx modal) ===== */
    function openEdit(id) {
        fetch("bank/bank-get-trx.php?id=" + id)
            .then(r => r.json())
            .then(d => {
                if (d.error) {
                    alert(d.error);
                    return;
                }
                document.getElementById("modalTitle").innerText = "Edit Transaction";
                document.getElementById("trx_id").value = d.id || "";
                document.getElementById("trx_date").value = d.date || "<?= $today ?>";
                document.getElementById("trx_type").value = d.transtype || "Withdraw";
                document.getElementById("trx_chq").value = d.chqno || "";
                document.getElementById("trx_amt").value = d.amount || "";

                // show buttons appropriate for edit
                document.getElementById("btnVerify").style.display = "inline-block";
                document.getElementById("btnDelete").style.display = "inline-block";

                openTrxModal();
            })
            .catch(err => {
                console.error(err);
                alert('Failed to fetch transaction.');
            });
    }

    /* ===== save / update / delete via bank-save-trx.php ===== */
    function saveTransaction(mode) {
        let fd = new FormData();
        fd.append("mode", mode);
        fd.append("id", document.getElementById("trx_id").value || "");
        fd.append("accno", BANK_ACCOUNT || "<?= $accno ?>");
        fd.append("date", document.getElementById("trx_date").value);
        fd.append("type", document.getElementById("trx_type").value);
        fd.append("chq", document.getElementById("trx_chq").value);
        fd.append("amt", document.getElementById("trx_amt").value);

        // disable buttons during request
        const btnSave = document.getElementById("btnSave");
        const btnVerify = document.getElementById("btnVerify");
        const btnDelete = document.getElementById("btnDelete");
        if (btnSave) btnSave.disabled = true;
        if (btnVerify) btnVerify.disabled = true;
        if (btnDelete) btnDelete.disabled = true;

        fetch("bank/bank-save-trx.php", { method: "POST", body: fd })
            .then(r => r.text())
            .then(msg => {
                document.getElementById("msg").innerHTML = msg;
                setTimeout(() => location.reload(), 500);
            })
            .catch(err => {
                console.error(err);
                alert('Save failed.');
            })
            .finally(() => {
                if (btnSave) btnSave.disabled = false;
                if (btnVerify) btnVerify.disabled = false;
                if (btnDelete) btnDelete.disabled = false;

                if (mode == 2) {
                    btnbtn = document.getElementById("trbtn" + document.getElementById("trx_id").value);
                    btnDelete.disabled = true;
                }
            });
    }

    /* ===== Bank selector helpers ===== */
    function chooseAccount() {
        const acc = document.getElementById('modalAcc').value;
        if (!acc) { alert('Please select a bank account'); return; }
        window.location = "bank-account.php?accno=" + acc;
    }

    /* ===== On page load: show bank selector only if BANK_ACCOUNT is empty/0 ===== */
    document.addEventListener('DOMContentLoaded', () => {
        try {
            const acc = (typeof BANK_ACCOUNT !== 'undefined') ? String(BANK_ACCOUNT) : (new URLSearchParams(window.location.search)).get('accno') || '';
            if (!acc || acc === "0" || acc === "") {
                // small delay to ensure UI ready
                setTimeout(() => openBankModal(), 150);
            }
        } catch (e) {
            console.error(e);
        }
    });


</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("trnxTable");

        if (table) {
            new DataTable(table, {
                paging: true,
                searching: true,
                info: true,
                ordering: true,
                lengthChange: true,
                pageLength: 50,
                order: [[0, "desc"]], // ID desc
            });
        }
    });
</script>


<?php require_once 'footer.php'; ?>

</body>

</html>