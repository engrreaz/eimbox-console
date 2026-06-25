<?php require_once 'header.php'; ?>

<style>
    tr th {
        padding: 2px;
    }

    .full-height {
        height: 100%;
    }

    .vr-full {
        width: 1px;
        background-color: #ccc;
        height: 100%;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <div id="eposlink" hidden>..............</div>

    <?php
    $chain_param = '-c 12 -t Payments Collection Report -u -r -b View List';
    include 'components/slot-tree-ui.php';
    ?>

    <div class="card mb-3 card-border-shadow-primary">
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <label class="form-label text-small" for="date-from-main">Date From</label>
                    <input type="date" class="form-control form-control-sm" id="date-from-main">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-small" for="date-to-main">Date To</label>
                    <input type="date" class="form-control form-control-sm" id="date-to-main">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-small" for="collector-main">Collected By</label>
                    <select class="form-select form-select-sm" id="collector-main">
                        <option value="">Select Collector</option>
                        <?php
                        $query = "SELECT email, profilename FROM usersapp WHERE sccode = '$sccode'";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<option class="text-danger fw-bold" value="' . htmlspecialchars($row['email']) . '">' . htmlspecialchars($row['profilename'] ?? $row['email']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-small" for="date-from-main">Total Collection</label>
                    <input type="text"
                        class="form-control form-control-sm text-center  text-white fs-6 fw-bold "
                        id="totalAmont" style="font-size:24px; padding:0; background:teal;" disabled>
                </div>


                <div class="col-md-4 text-center row pt-2 full-height">

                    <!-- Left -->
                    <div class="col text-end d-flex flex-column justify-content-end">
                        <div class="row text-end">
                            <div class="col">
                                <button class="btn btn-sm btn-outline-primary border-0 p-0 shadow-none me-3"
                                    onclick="printSelected()" title="Print Receipt">
                                    <i class="bi bi-printer icon-24px"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger border-0 p-0 shadow-none"
                                    onclick="downloadSelected()" title="Download Receipt">
                                    <i class="bi bi-file-pdf icon-24px"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row mt-3 text-end">
                            <div class="col">
                                Receipt
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="col-auto d-flex justify-content-center">
                        <div class="vr-full"></div>
                    </div>

                    <!-- Right -->
                    <div class="col text-end d-flex flex-column justify-content-start">
                        <div class="row text-start">
                            <div class="col">
                                <button class="btn btn-sm btn-outline-secondary border-0 p-0 shadow-none me-3"
                                    onclick="printSelectedReport()" title="Print Collection Report">
                                    <i class="bi bi-printer-fill icon-24px"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary border-0 p-0 shadow-none"
                                    onclick="downloadSelectedReport()" title="Download Collection Report">
                                    <i class="bi bi-file-pdf-fill icon-24px"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row mt-3 text-start">
                            <div class="col">
                                Report
                            </div>
                        </div>
                    </div>

                      <!-- Divider -->
                    <div class="col-auto d-flex justify-content-center">
                        <div class="vr-full"></div>
                    </div>

                    <!-- Right -->
                    <div class="col text-end d-flex flex-column justify-content-start">
                        <div class="row text-start">
                            <div class="col">
                                <button class="btn btn-sm btn-outline-secondary border-0 p-0 shadow-none me-3"
                                    onclick="printSelectedReport()" title="Print Collection Report">
                                    <i class="bi bi-chat-square-text icon-24px"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary border-0 p-0 shadow-none"
                                    onclick="downloadSelectedReport()" title="Download Collection Report">
                                    <i class="bi bi-envelope icon-24px"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row mt-3 text-start">
                            <div class="col">
                                Message
                            </div>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>


    <div class="row">

        <div class="col-md-12 ">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="fw-bold  text-center text-info">Student Collection List</h5>
                </div>
                <div class="form-group row">
                    <div class="col-12 ">
                        <div class=" table-responsive ">
                            <table class="table table-stripe table-sm data-table">
                                <thead>
                                    <tr class="text-primary ">
                                        <th><input type="checkbox" class="form-check-input form-check-danger"
                                                id="checkAll" onclick="toggleCheckAll(this)"></th>
                                        <th>Roll</th>
                                        <th colspan="2">Name </th>
                                        <th>PR No</th>
                                        <th>Date</th>
                                        <th class="text-end">Amount</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody id="data-body-main">

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
<!-- Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Receipt Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Structure -->


<?php require_once 'footer.php'; ?>

<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->



<script>
    function toggleCheckAll(checkbox) {
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.checked = checkbox.checked;
        });
    }

    function viewReceipt(stid, prno, prdate) {
        let syd = $('#session-main').val();

        fetch('payments/get-receipt.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `stid=${stid}&prno=${prno}&prdate=${prdate}&session=${syd}`
        })
            .then(r => r.text())
            .then(data => {
                document.getElementById('modalBody').innerHTML = data;
                new bootstrap.Modal(document.getElementById('receiptModal')).show();
            })
            .catch(error => console.error('Error:', error));
    }

    function printReceipt(stid, prno, prdate) {
         window.open(`payments/selected-receipts.php?prs=${prno}`);
    }

    function downloadReceipt(stid, prno, prdate) {
        window.open(`payments/selected-receipts.php?prs=${prno}&mode=pdf`);
    }



    function printSelected() {
        const selected = [...document.querySelectorAll('.row-checkbox:checked')].map(cb => cb.value);
        if (!selected.length) return alert('কোনো রেকর্ড নির্বাচন করুন');
        window.open(`payments/selected-receipts.php?prs=${selected.join(',')}`);
    }

    function downloadSelected() {
        const selected = [...document.querySelectorAll('.row-checkbox:checked')].map(cb => cb.value);
        if (!selected.length) return alert('কোনো রেকর্ড নির্বাচন করুন');
        window.open(`payments/selected-receipts.php?prs=${selected.join(',')}&mode=pdf`);
    }
</script>


<!-- ---------------------------- CHAIN -------------- -->
<script>

    function defaultLoadScript() {

        // Get all filter values
        let slot = $('#slot-main').val();
        let year = $('#session-main').val();
        let cls = $('#class-main').val();
        let sec = $('#section-main').val();
        let dateFrom = $('#date-from-main').val();
        let dateTo = $('#date-to-main').val();
        let collector = $('#collector-main').val();

        // Use object instead of query string
        let infor = {
            slot: slot,
            year: year,
            cls: cls,
            sec: sec,
            dateFrom: dateFrom,
            dateTo: dateTo,
            collector: collector
        };


        $("#data-body-main").html('<tr><td colspan="4" class="text-center text-info pt-5">Retrieve Dues List ...</td></tr>');

        $.ajax({
            type: "POST",
            url: "payments/fetch-collection-list.php",
            data: infor,
            cache: false,
            success: function (html) {
                $("#data-body-main").html(html);
                let a = $('#motmot').text();
                $('#totalAmont').val(a);
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
                $("#data-body-main").html('<tr><td colspan="4" class="text-center text-danger pt-5">Failed to retrieve data!</td></tr>');
            }
        });
    }


    $(function () {
        function tryGetList() {
            let cls = $('#class-main').val();
            let sec = $('#section-main').val();
            if (cls && sec) {
                defaultLoadScript();
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
        defaultLoadScript();
    });
</script>
<script>
    $(document).ready(function () {
        $('.data-table').DataTable({
            pageLength: 10,          // প্রতি পেজে কয়টা row
            lengthChange: true,      // dropdown show
            searching: true,         // search box
            ordering: true,          // sorting
            info: true,              // "Showing x to y"
            paging: true,            // pagination
            responsive: true,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ rows",
                info: "Showing _START_ to _END_ of _TOTAL_ rows",
                paginate: {
                    previous: "Prev",
                    next: "Next"
                }
            }
        });
    });
</script>



<!-- ---------------------------- CHAIN -------------- -->



<!-- ----------------------------------- -->
</body>

</html>