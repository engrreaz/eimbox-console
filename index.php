<?php
require_once 'header.php';
// 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

// array(6) { [0]=> string(1) "1" [1]=> string(20) "tNrdSSziORSgTc85sDxJ" [2]=> string(16) "bc82b09ea156c49a" [3]=> string(13) "8809617618425" [4]=> string(8) "a0d66743" [5]=> string(31) "http://bulksmsbd.net/api/smsapi" }


// echo '<pre>' . print_r($_SESSION) . '</pre>';


?>

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card mb-3">
        <div class="card-body" id="exam-stat"></div>
    </div>

    <!-- Card Border Shadow -->
    <div class="row g-6">




        <?php

        // global_send_sms('01919629672', 'Test message from Eimbox SMS Gateway Integration. This is a test message to verify the SMS sending functionality. Please ignore this message. Thank you!', 'Regular', 'Test', '0');
        ?>


        <!-- Students Attendance -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-border-shadow-warning ">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bi bi-person icon-24px"></i>
                            </span>
                        </div>
                        <h4 class="mb-0">500</h4>
                    </div>
                    <h6 class="mb-0 fw-normal">Student's Attendance</h6>
                    <p class="mb-0">
                        <span class="me-1 fw-medium">-8.7%</span>
                        <small class="text-body-secondary">than yesterday</small>
                    </p>
                </div>
            </div>

            <div class="card card-border-shadow-warning mt-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bi bi-person icon-24px"></i>
                            </span>
                        </div>
                        <h4 class="mb-0">30</h4>
                    </div>
                    <h6 class="mb-0 fw-normal">Absent Last 3 Days</h6>
                    <p class="mb-0">
                        <span class="me-1 fw-medium">-8.7%</span>
                        <small class="text-body-secondary">than yesterday</small>
                    </p>
                </div>
            </div>
        </div>

        <!-- Teachers Attendance -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-border-shadow-info">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="bi bi-person-badge icon-24px"></i>
                            </span>
                        </div>
                        <h4 class="mb-0">50</h4>
                    </div>
                    <h6 class="mb-0 fw-normal">Teacher's Attendance</h6>
                    <p class="mb-0">
                        <span class="me-1 fw-medium">+2.3%</span>
                        <small class="text-body-secondary">than yesterday</small>
                    </p>
                </div>
            </div>

            <div class="card card-border-shadow-info mt-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="bi bi-person-badge icon-24px"></i>
                            </span>
                        </div>
                        <h4 class="mb-0">1</h4>
                    </div>
                    <h6 class="mb-0 fw-normal">Teacher Absent | CL</h6>
                    <p class="mb-0">
                        <span class="me-1 fw-medium">+2.3%</span>
                        <small class="text-body-secondary">than yesterday</small>
                    </p>
                </div>
            </div>
        </div>

        <!-- Active Users Today -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-border-shadow-success">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bi bi-people icon-24px"></i>
                            </span>
                        </div>
                        <h4 class="mb-0">320</h4>
                    </div>
                    <h6 class="mb-0 fw-normal">Active Users Today</h6>
                    <p class="mb-0">
                        <span class="me-1 fw-medium">+5.1%</span>
                        <small class="text-body-secondary">than yesterday</small>
                    </p>
                </div>
            </div>
            <div class="card card-border-shadow-success mt-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bi bi-people icon-24px"></i>
                            </span>
                        </div>
                        <h4 class="mb-0">320</h4>
                    </div>
                    <h6 class="mb-0 fw-normal">Active Users Today....</h6>
                    <p class="mb-0">
                        <span class="me-1 fw-medium">+5.1%</span>
                        <small class="text-body-secondary">than yesterday</small>
                    </p>
                </div>
            </div>
        </div>

        <!-- Collection Today -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-border-shadow-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bi bi-cash-stack icon-24px"></i>
                            </span>
                        </div>
                        <h4 class="mb-0">৳ 12,500</h4>
                    </div>
                    <h6 class="mb-0 fw-normal">Collection Today</h6>
                    <p class="mb-0">
                        <span class="me-1 fw-medium">+3.7%</span>
                        <small class="text-body-secondary">than yesterday</small>
                    </p>
                </div>
            </div>

            <div class="card card-border-shadow-danger mt-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="bi bi-wallet icon-24px"></i>
                            </span>
                        </div>
                        <h4 class="mb-0">৳ 7,800</h4>
                    </div>
                    <h6 class="mb-0 fw-normal">Expanse Today</h6>
                    <p class="mb-0">
                        <span class="me-1 fw-medium">-1.2%</span>
                        <small class="text-body-secondary">than yesterday</small>
                    </p>
                </div>
            </div>
        </div>



    </div>







    <!--/ Card Border Shadow -->

    <!--/ On route vehicles Table -->

</div>
<!-- / Content -->





<!-- Modal -->
<div class="modal fade" id="examDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl" style="max-width:80%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exam Detailed Statistics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="examDetailBody"
                style=" height: 80vh; overflow-y: auto;   overflow-x: hidden;  ">
                <div class="p-3 text-center">Loading...</div>
            </div>
        </div>
    </div>
</div>


<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script></script>
<!-- ----------------------------------- -->






<script>
    $(document).ready(function () {
        $("#exam-stat").html("<div class='text-center p-3'>Loading...</div>");

        $.post("index/exam-stat.php", {}, function (data) {
            $("#exam-stat").html(data);
        });
    });

   $(document).on("click", ".details", function () {

    let dtype = $(this).data("type");   // student / clssec / teacher
    let url = "index/exam-stat-details.php?type=" + dtype;

    $("#examDetailBody").html("<div class='p-3 text-center'>Loading...</div>");

    $.post(url, {}, function (data) {

        $("#examDetailBody").html(data);

        if (typeof renderExamChart === "function") {
            renderExamChart();
        }

        let modalEl = document.getElementById("examDetailModal");
        let modal = new bootstrap.Modal(modalEl);
        modal.show();
    });
});

</script>
</body>

</html>