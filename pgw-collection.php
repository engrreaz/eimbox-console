<?php require_once 'header.php'; ?>

<style>
    #detailsModal_pgw .modal-dialog {
        max-width: 800px;
        margin: 1.75rem auto;
    }

    #detailsModal_pgw .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    /* Info টেক্সটের স্পেস বাড়ানো */
    #pgwTable td,
    #pgwTable th {
        text-align: center;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <h4 class="mb-4"> Collection Through Payment Gateways</h4>

    <!-- Table -->
    <div class="card">

        <table id="pgwTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>SC Code</th>
                    <th>Session</th>
                    <th>STID</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Gateway</th>
                    <th>TrxID</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM payment_pgw where sccode='$sccode' ORDER BY id DESC";
                $q = $conn->query($sql);
                while ($row = $q->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['sccode'] ?></td>
                        <td><?= $row['sessionyear'] ?></td>
                        <td><?= $row['stid'] ?></td>
                        <td><?= date('d/m/Y', strtotime($row['paydate'])) ?></td>
                        <td><?= $row['amount'] ?></td>
                        <td><?= $row['gateway'] ?></td>
                        <td><?= $row['trxID'] ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary btnDetails" data-id="<?= $row['id'] ?>"
                                data-stid="<?= $row['stid'] ?>" data-sccode="<?= $row['sccode'] ?>"
                                data-session="<?= $row['sessionyear'] ?>"
                                data-tokenid="<?= $row['token_id'] ?>">Details</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>


    <!-- Modal -->
    <div class="modal fade" id="detailsModal_pgw" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary  text-white pb-3">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsBody">
                    Loading...
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>


<!-- ----------------------------------- -->
<script>

    $(document).ready(function () {


        $("#pgwTable").DataTable({ "pageLength": 10 });

        $(document).on("click", ".btnDetails", function () {

            const id = $(this).data("id");
            const stid = $(this).data("stid");
            const sccode = $(this).data("sccode");
            const session = $(this).data("session");
            const tokenid = $(this).data("tokenid");

            // Loading text first
            $("#detailsBody").html("<div class='text-center p-3'>⏳ Loading...</div>");

            // Modal instance ready
            const modalEl = document.getElementById("detailsModal_pgw");
            let detailsModal = bootstrap.Modal.getInstance(modalEl);
            if (!detailsModal) detailsModal = new bootstrap.Modal(modalEl);
            detailsModal.show();

            // AJAX NOW
            $.ajax({
                url: "ajax/pgw-details-ajax.php",
                type: "POST",
                data: {
                    id: id,
                    stid: stid,
                    sccode: sccode,
                    sessionyear: session,
                    tokenid: tokenid
                },
                success: function (data) {
                    $("#detailsBody").html(data);
                },
                error: function () {
                    $("#detailsBody").html("<div class='text-danger'>❌ Failed to load data.</div>");
                }
            });
        });

    });



</script>
<!-- ----------------------------------- -->

</body>

</html>