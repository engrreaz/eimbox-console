<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between mb-2">
        <h3>Bank Details</h3>

        <button class="btn btn-inverse-success" onclick="addNewBank();">
            <i class="mdi mdi-library-plus"></i> Add New Account
        </button>
    </div>

    <div class="card">
        <div id="msg"></div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Acc. No.</th>
                        <th>Type</th>
                        <th>Bank / Branch</th>
                        <th class="text-right">Last Balance</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sl = 1;
                    $total = 0;

                    $sql = "SELECT * FROM bankinfo WHERE sccode='$sccode' ORDER BY id ASC";
                    $qr = mysqli_query($conn, $sql);

                    if (mysqli_num_rows($qr) > 0) {
                        while ($row = mysqli_fetch_assoc($qr)) {

                            $acc = $row['accno'];
                            $type = $row['acctype'];
                            $bank = $row['bankname'];
                            $branch = $row['branch'];
                            $id = $row['id'];
                            $cdate = $row['closingdate'] ?? '';




                            // Last Balance
                            $bls = 0;
                            $q2 = mysqli_query(
                                $conn,
                                "SELECT balance FROM banktrans 
                                 WHERE sccode='$sccode' AND accno='$acc'
                                 ORDER BY verifytime DESC, date DESC, id DESC LIMIT 1"
                            );
                            if (mysqli_num_rows($q2) > 0) {
                                $bls = mysqli_fetch_assoc($q2)['balance'];
                            }

                            $total += $bls;
                            ?>
                            <tr <?= (strtotime($cdate) !== false) ? 'class="table-danger"' : '' ?>>
                                <td><?= $sl++; ?></td>
                                <td><?= $acc; ?></td>
                                <td><?= $type; ?></td>
                                <td><?= $bank; ?><br><small><?= $branch; ?></small></td>
                                <td class="text-end fs-6 fw-bold">
                                    <?= number_format($bls, 2); ?>
                                </td>
                                <td class="text-right">
                                    <div class="row">
                                        <div class="col">
                                            <a href="bank-account.php?accno=<?= $acc ?>"  target="_blank"><i
                                                    class="bi bi-caret-right-fill tex"></i></a>

                                        </div>

                                        <div class="col text-primary" onclick="editBank('<?= $id ?>');">
                                            <i class="bi bi-pencil-square"></i>
                                        </div>

                                        <div class="col text-danger" onclick="removeBank('<?= $id ?>');">
                                            <i class="bi bi-trash3-fill"></i>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6'>No Data Found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>


<div class="modal fade" id="bankModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="modalTitle">Add New Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="bankForm">
                    <input type="hidden" id="bank_id">

                    <label>Account Number</label>
                    <input type="text" id="accno" class="form-control mb-2" required>

                    <label>Account Type</label>
                    <input type="text" id="acctype" class="form-control mb-2" required>

                    <label>Bank Name</label>
                    <input type="text" id="bankname" class="form-control mb-2" required>

                    <label>Branch</label>
                    <input type="text" id="branch" class="form-control mb-2" required>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" onclick="saveBank();">Save</button>
            </div>
        </div>
    </div>
</div>



<script>
    function addNewBank() {
        $("#modalTitle").text("Add New Account");
        $("#bankForm")[0].reset();
        $("#bank_id").val('');
        var myModal = new bootstrap.Modal(document.getElementById("bankModal"));
        myModal.show();
    }

    function editBank(id) {
        $.post("bank/bank-get.php", { id: id }, function (data) {
            let x = JSON.parse(data);

            $("#modalTitle").text("Edit Bank Account");
            $("#bank_id").val(x.id);
            $("#accno").val(x.accno);
            $("#acctype").val(x.acctype);
            $("#bankname").val(x.bankname);
            $("#branch").val(x.branch);

            var myModal = new bootstrap.Modal(document.getElementById("bankModal"));
            myModal.show();
        });
    }

    function saveBank() {
        let form = {
            id: $("#bank_id").val(),
            accno: $("#accno").val(),
            acctype: $("#acctype").val(),
            bankname: $("#bankname").val(),
            branch: $("#branch").val()
        };

        $.post("bank/bank-save.php", form, function (res) {
            $("#msg").html(res);
            setTimeout(() => location.reload(), 1000);
        });
    }

    function removeBank(id) {
        if (!confirm("Delete this account?")) return;

        $.post("bank/bank-delete.php", { id: id }, function (res) {
            $("#msg").html(res);
            setTimeout(() => location.reload(), 800);
        });
    }

</script>
</body>

</html>