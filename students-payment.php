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
    <div id="eposlink">..............</div>






    <div class="row">
        <div class="col-md-4">
            <?php
            $chain_param = '-t Choose Values -u -r -b View List';
            include 'components/slot-tree-ui.php';
            ?>
        </div>

        <div class="col-md-8 ">
            <div class="card h-100">

                <div class="form-group row">
                    <div class="col-12 ">
                        <div class=" table-responsive">
                            <table class="table table-stripe">
                                <thead>
                                    <tr>
                                        <td class="text-right font-weight-bold">Roll</td>
                                        <td class=" font-weight-bold">Name of Student</td>
                                        <td class="text-right  font-weight-bold">Dues</td>
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



<!-- --------------------------------------- MODALS ------------------ -->
<div class="modal fade" id="myModal1" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Change Receipt Date</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="modalForm">
                <div class="modal-body">
                    <!-- Hidden fields to hold param1 & param2 -->
                    <input type="hidden" id="modalParam1">
                    <input type="hidden" id="modalParam2">

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


<?php require_once 'footer.php'; ?>



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
                let modal = new bootstrap.Modal(document.getElementById('duesModal'));
                modal.show();

                $("#getdata").html(html);
                document.getElementById("prdate").focus();
            }
        });

    }
</script>




<script>

    const modal = document.getElementById('myModal1');

    // যখন মডাল ওপেন হবে
    modal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const param1 = button.getAttribute('data-param1');
        const param2 = button.getAttribute('data-param2');

        // hidden ইনপুটে সেট করা
        document.getElementById('modalParam1').value = param1;
        document.getElementById('modalParam2').value = param2;
    });

    // মডাল ফর্ম সাবমিট
    document.getElementById('modalForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const param1 = document.getElementById('modalParam1').value;
        const param2 = document.getElementById('modalParam2').value;
        const dateValue = document.getElementById('modalDate').value;

        // আপনি এখানেই API কল, Ajax, বা অন্য JS এক্সিকিউশন করতে পারেন
        changedate(param1, param2, dateValue);

        // মডাল বন্ধ করা
        const modalInstance = bootstrap.Modal.getInstance(modal);
        modalInstance.hide();
    });

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
                $("#eposlink").html('.....');
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
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
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
                $('#dues-body').html('<small>Retrieve Dues List ...</small>');
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