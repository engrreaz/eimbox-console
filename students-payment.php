<?php require_once 'header.php'; ?>

<style>
    /* modal max height 90% */
    .modal-dialog {
        max-height: 90vh;
    }

    /* modal body scrollable */
    .modal-body {
        max-height: calc(90vh - 120px);
        /* header + padding adjust */
        overflow-y: auto;
    }
</style>


<div class="container-xxl flex-grow-1 container-p-y">

    <h3 class="">Student's Payment System</h3>
    <div id="eposlink" hidden>..............</div>






    <div class="row">
        <div class="col-md-4">
            <?php
            $chain_param = '-c 4 -t Choose Values -u -r -b View List';
            include 'components/slot-tree-ui.php';
            ?>
        </div>

        <div class="col-md-8 ">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="fw-bold  text-center text-info">Student Dues List</h5>
                </div>
                <div class="form-group row">
                    <div class="col-12 ">

                        <div class=" table-responsive ">
                            <table class="table table-stripe table-sm">
                                <thead>
                                    <tr class="text-primary">
                                        <td class="text-center font-weight-bold">Roll</td>
                                        <td class=" font-weight-bold">Name of Student</td>
                                        <td class="text-end ">Dues</td>
                                        <td class="text-right"></td>
                                    </tr>
                                </thead>

                                <tbody id="dues-body">

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>


    </div>



</div>

<style>
    #myModal1 {
        z-index: 2000 !important;
    }

    #myModal1+.modal-backdrop {
        z-index: 1999 !important;
    }
</style>

<!-- --------------------------------------- MODALS ------------------ -->
<div class="modal fade" id="myModal1" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Change Receipt Date</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="modalForm">
                <div class="modal-body">
                    <!-- Hidden fields to hold param1 & param2 -->
                    <input type="hidden" id="modalPrno">
                    <input type="hidden" id="modalType">

                    <!-- Date input field -->
                    <label for="modalDate" class="form-label">Choose Date</label>
                    <input type="date" class="form-control" id="modalDate" value="<?php echo $td; ?>" required>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Change Date</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="duesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Student Dues</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="getdata">
                <!-- AJAX content will load here -->
            </div>

        </div>
    </div>
</div>


<!-- Modal Structure -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content  border border-primary border-3">
            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Payment Split Window</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Body -->
            <div class="modal-body">
                Enter the amount to be split from the selected item.<br>
                <input type="text" class="form-control" id="spltid" placeholder="Enter ID" value="" hidden>
                <input type="text" class="form-control" id="spltamtpre" placeholder="Enter Amount" value="" hidden>
                <input type="text" class="form-control" id="spltamt" placeholder="Enter Amount" value="">
                <span class="text-muted text-small">The remaining amount will stay as dues in the original item.</span>
            </div>
            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="mybtn" onclick="splitable();">Split Now</button>
            </div>
        </div>
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable  modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history"></i> Payment History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="historyContent">
                <div class="text-center text-muted">
                    Loading...
                </div>
            </div>

        </div>
    </div>
</div>


<div class="modal fade" id="fineModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add Fine / জরিমানা যোগ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="fine_stid">

                <div class="mb-3">
                    <label class="form-label">Fine Amount</label>
                    <input type="number" id="fine_amount" class="form-control" min="1">
                </div>

                <div class="mb-3" hidden>
                    <label class="form-label">Note / Reason</label>
                    <textarea id="fine_note" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger btn-sm" onclick="saveFine()">Save Fine</button>
            </div>

        </div>
    </div>
</div>

<!-- Modal Structure -->

<?php require_once 'footer.php'; ?>

<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->


<script>
    var uri = window.location.href;
    document.getElementById('defbtn').innerHTML = "Today's Collection";
    document.getElementById('defmenu').innerHTML = '';
    function defbtn() {
        var cls = document.getElementById('cls').value;
        var sec = document.getElementById('sec').value;
        window.location.href = 'report-today-collection.php?&cls=' + cls + '&sec=' + sec;
    }
    function reload() {
        window.location.href = uri;
    }
    function resultentry(roll) {
        if (roll == 0) {
            document.getElementById('boardroll').value = '';
        } else {
            document.getElementById('boardroll').value = roll;
        }

        document.getElementById('ren').style.display = 'block';
        document.getElementById('boardroll').focus();
    }



</script>




<script>

    function getdues(stid, lastpr, datam, year, tdues) {

        var infor = "stid=" + stid + "&lastpr=" + lastpr + "&datam=" + datam + "&year=" + year + "&tdues=" + tdues;
        // alert(infor);
        $("#getdata").html("");

        $.ajax({
            type: "POST",
            url: "payments/fetch-indivisual-dues.php",
            data: infor,
            cache: false,
            beforeSend: function () {
                $('#getdata').html('<small>Processing...</small>');
            },
            success: function (html) {
                // let modal = new bootstrap.Modal(document.getElementById('duesModal'));
                let modal = new bootstrap.Modal(document.getElementById('duesModal'), {
                    backdrop: 'static',   // backdrop থাকবে
                    keyboard: false       // ESC চাপলে বন্ধ হবে না
                });

                modal.show();

                $("#getdata").html(html);
                document.getElementById("prdate").focus();
            }
        });

    }
</script>




<script>

    $(document).on('click', '.btn-change-date', function () {

        let prno = $(this).data('prno');
        let type = $(this).data('type');
        let prdate = $(this).data('prdate');

        $('#modalPrno').val(prno);
        $('#modalType').val(type);
        $('#modalDate').val(prdate);

        let modalEl = document.getElementById('myModal1');
        let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    });



    // মডাল ফর্ম সাবমিট
    document.getElementById('modalForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const param1 = document.getElementById('modalPrno').value;
        const param2 = document.getElementById('modalType').value;
        const dateValue = document.getElementById('modalDate').value;

        // আপনি এখানেই API কল, Ajax, বা অন্য JS এক্সিকিউশন করতে পারেন
        changedate(param1, param2, dateValue);

        // মডাল বন্ধ করা
        showToast('success', 'Date has been changed successfully.', 'Change Receipt Date');
        let modalEl = document.getElementById('myModal1');
        let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();

    });

</script>




<script>
    // এই ফাংশনেই সব ডেটা যাবে
    function changedate(p1, p2, date) {

        infor = "p1=" + p1 + "&date=" + date + "&p2=" + p2;

        $("#eposlink").html("");

        $.ajax({
            type: "POST",
            url: "payments/pr-change-del.php",
            data: infor,
            cache: false,
            beforeSend: function () {
                $("#eposlink").html('...');
            },
            success: function (html) {
                $("#eposlink").html(html);
                if (p2 == 2 || p2 == 3 || p2 == 5) {
                    window.location.reload();
                }

            }
        });
    }
</script>




<script>


    function epos() {
        let lastpr = document.getElementById("mylastpr").value;
        infor = "prno=" + lastpr;
        $("#eposlink").html("");

        $.ajax({
            type: "POST",
            url: "payments/getprinfo.php",
            data: infor,
            cache: false,
            beforeSend: function () {
                $("#eposlink").html('.....');
            },
            success: function (html) {
                $("#eposlink").html(html);
            }
        });
    }
</script>

<script>
    function openFineModal(stid) {

        $('#fine_stid').val(stid);
        $('#fine_amount').val('');
        $('#fine_note').val('');

        let modal = new bootstrap.Modal(document.getElementById('fineModal'));
        modal.show();
    }

    function saveFine() {
        let stid = $('#fine_stid').val();
        let amount = $('#fine_amount').val();
        let note = $('#fine_note').val();
        let slot = $('#slot-main').val();
        let session = $('#session-main').val();
        let roll = $('#cur-roll').val();

        if (!amount || amount <= 0) {
            showToast('error', 'Fine amount is required.', 'Validation Error');
            return;
        }

        $.post('payments/save-fine.php', {
            slot: slot,
            session: session,
            stid: stid,
            roll: roll,
            amount: amount,
            note: note
        })
            .done(function (res) {
                if (res) {
                    let modal = bootstrap.Modal.getInstance(document.getElementById('fineModal'));
                    modal.hide();
                    if (res.includes('Success')) {
                        showToast('success', 'Student Fine Added Successfully.', 'Add Fine');
                        setCookie('payment-stid', stid);
                        setTimeout(function () {
                            window.location.reload();
                        }, 800);
                    } else {
                        showToast('error', res, 'Error');
                    }

                } else {
                    showToast('error', res, 'Error');
                }
            })
            .fail(function () {
                showToast('error', 'An unexpected error occurred. Please try again.', 'Server Error');
            });
    }
</script>




<script>
    function save(stid, year) {

        let cnto = document.getElementById("cntp").value;
        cnto = parseInt(cnto) * 1;
        let chk = document.getElementById("chk").value;

        //chk, rollno, cls, sec, nameeng, nameben, mobile
        let prno = document.getElementById("prno").value;
        let prdate = document.getElementById("prdate").value;
        let aaa = parseInt(document.getElementById("amt").value);
        // alert('s');
        if (aaa >= 0) {
            let tail = "count=" + chk + "&stid=" + stid + "&prno=" + prno + "&prdate=" + prdate + "&year=" + year;
            let run = 0;
            for (let x = 0; x < cnto; x++) {
                let ch = document.getElementById("rex" + x).checked;
                if (ch === true) {
                    let fid = document.getElementById("fid" + x).innerHTML;
                    let amt = document.getElementById("amt" + x).innerHTML;
                    tail += "&fid" + run + "=" + fid + "&amt" + run + "=" + amt;
                    run++;
                }
            }

            var infor = tail;
            // alert(tail);

            $("#bbttnn").html("");

            $.ajax({
                type: "POST",
                url: "payments/save-pr.php",
                data: infor,
                cache: false,
                beforeSend: function () {
                    $("#bbttnn").html('<span class=""><center></span>');
                },
                success: function (html) {
                    $("#bbttnn").html(html);
                    $("#bbttnn")
                        .prop("disabled", true)
                        .removeClass("btn-success")
                        .addClass("btn-danger");
                    $('#item-list-table').html("");
                    showToast('success', 'BDT ' + aaa + ' has been paid to student', 'Payment Success');

                    let modal = bootstrap.Modal.getInstance(document.getElementById('duesModal'));

                    modal.hide();
                }
            });
        }

    }

</script>


<script>
    function sell(id) {
        let ch = document.getElementById("rex" + id).checked;
        if (ch === true) {
            document.getElementById("rex" + id).checked = false;
        } else {
            document.getElementById("rex" + id).checked = true;
        }
        sel(id);

    }


    function sel(id) {
        // alert(id + 'SEL');
        let ch = document.getElementById("rex" + id).checked;

        let amt = document.getElementById("amt" + id).innerHTML;
        amt = parseInt(amt) * 1;
        let amtt = parseInt(document.getElementById("amt").value) * 1;
        let chk = parseInt(document.getElementById("chk").value) * 1;

        if (ch === true) {
            //document.getElementById("rex"+id).checked = true;
            amtt = amtt + amt;
            chk++;
        } else {
            //document.getElementById("rex"+id).checked = false;
            amtt = amtt - amt;
            chk--
        }
        document.getElementById("amt").value = amtt + '.00';
        document.getElementById("chk").value = chk;
        diss();
    }
</script>

<script>
    function delpr(p1, p2, prno) {

        Swal.fire({
            title: 'Delete?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            backdrop: true,
            allowOutsideClick: false,
            customClass: {
                popup: 'swal-on-top'
            }
        }).then((res) => {
            if (res.isConfirmed) {
                changedate(p1, p2, prno);
                Swal.fire({
                    title: "Deleted!",
                    text: "Payment Receipt has been deleted.",
                    icon: "success"
                });
            }
        });


    }
</script>

<!-- ---------------------------- CHAIN -------------- -->
<script>

</script>
<!-- ---------------------------- CHAIN -------------- -->

<!-- ----------------------------------- -->


<script>
    function getlist() {

        let year = $('#session-main').val();
        let cls = $('#class-main').val();
        let sec = $('#section-main').val();

        var infor = "year=" + year + "&cls=" + cls + "&sec=" + sec;

        $("#dues-body").html("");

        $.ajax({
            type: "POST",
            url: "payments/fetch-dues-student-dues.php",
            data: infor,
            cache: false,
            beforeSend: function () {
                $('#dues-body').html('<tr><td colspan="4" class="text-center text-info pt-5">Retrieve Dues List ...</td></tr>');
            },
            success: function (html) {
                $("#dues-body").html(html);

                let payStid = getCookie('payment-stid');

                if (payStid) {
                    let row = document.querySelector('.click-row[data-stid="' + payStid + '"]');
                    if (row) {
                        row.click();
                        setCookie('payment-stid', '');
                    }
                }
            }
        });

    }
</script>

<script>

    function toggleRow(id) {
        let cb = document.getElementById('rex' + id);
        cb.checked = !cb.checked;
        sel(id);
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

    });

</script>

<script>
    $(function () {

        function tryGetList() {
            let cls = $('#class-main').val();
            let sec = $('#section-main').val();
            if (cls && sec) {
                getlist();


            }
        }

        $('#class-main').on('change', function () {
            setTimeout(tryGetList, 500);
        });

        $('#section-main').on('change', function () {
            tryGetList();
        });

    });

    $(document).on('click', '.btn-chain', function () {
        getlist();
    });
</script>



<!-- ----------------------------------- -->
</body>

</html>