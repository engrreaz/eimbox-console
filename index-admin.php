<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card mb-3" id="exam-stat">

    </div>

    <div class="row g-6">
        <div class="col-md-8">
            <!-- Card Border Shadow -->
            <div class="row g-3">

                <!-- Students Attendance -->
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card card-border-shadow-warning ">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="bi bi-person icon-24px"></i>
                                    </span>
                                </div>
                                <h4 class="mb-0" id="present-count"></h4>
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
                                <h4 class="mb-0" id="student-bunk"></h4>
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
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card card-border-shadow-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="bi bi-person-badge icon-24px"></i>
                                    </span>
                                </div>
                                <h4 class="mb-0" id="teacher-attnd"></h4>
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
                                <h4 class="mb-0" id="techer-cl" >1</h4>
                            </div>
                            <h6 class="mb-0 fw-normal">Teacher Absent | CL</h6>
                            <p class="mb-0">
                                <span class="me-1 fw-medium">+2.3%</span>
                                <small class="text-body-secondary">than yesterday</small>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Collection Today -->
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card card-border-shadow-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="bi bi-cash-stack icon-24px"></i>
                                    </span>
                                </div>
                                <h4 class="mb-0" id="st-payment"></h4>
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
                                <h4 class="mb-0" id="spend-amount"></h4>
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
        </div>


        <div class="col-md-4 ">

            <div class="col-12" id="dashboard-stat"></div>


            <div class="col-12 mb-3" id="todo-block">
                <div class="card card-border-shadow-primary">
                    <div class="card-body">
                        <h6 class="mb-0 fw-normal">Todo List</h6>
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
            </div>

            <div class="col-12 mb-3" id="schedule-block">
                <div class="card card-border-shadow-primary">
                    <div class="card-body">
                        <h6 class="mb-0 fw-normal">Schedule</h6>
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
            </div>

            <div class="col-12 mb-3" id="notice-block">
                <div class="card card-border-shadow-primary">
                    <div class="card-body">
                        <h6 class="mb-0 fw-normal">Notice Board</h6>
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
            </div>



        </div>
    </div>


</div>
<!-- / Content -->


<div hidden>
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
</div>


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





<script>
    $(document).ready(function () {
        $("#exam-stat").html("<div class='text-center p-3'>Loading...</div>");

        $.post("index/exam-stat.php", {}, function (data) {
            $("#exam-stat").html(data);
        });


        $.post("index/dashboard-stat.php", {}, function (res) {
            $("#today-date").text(res.today);
            $("#present-count").text(res.present);
            $("#student-bunk").text(res.bunk);
            $("#teacher-attnd").text(res.teacher);
            $("#teacher-cl").text(res.cl);
            $("#st-payment").text(res.collection);
            $("#spend-amount").text(res.expense);
        }, "json");


        // --------RIGHT PANEL-------------
        $.post("index/todo-block.php", {}, function (data) {
            $("#todo-block").html(data);
        });
        $.post("index/notice-block.php", {}, function (data) {
            $("#notice-block").html(data);
        });
        $.post("index/schedule-block.php", {}, function (data) {
            $("#schedule-block").html(data);
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


<script>
    /**
     * টাস্ক সম্পন্ন করার ফাংশন (AJAX)
     */

    function markAsDone(todoId) {
        const row = document.getElementById(`todo-row-${todoId}`);

        row.style.opacity = '0.5';
        row.style.pointerEvents = 'none';

        fetch('index/update_todo_status.php', {
            method: 'POST',
            body: new URLSearchParams({
                id: todoId,
                status: 1
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    row.style.transform = 'translateX(20px)';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        updateBadgeCount();
                    }, 300);
                } else {
                    alert('Error updating status');
                    row.style.opacity = '1';
                    row.style.pointerEvents = 'all';
                    document.getElementById(`check-${todoId}`).checked = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                row.style.opacity = '1';
                row.style.pointerEvents = 'all';
            });
    }

    function updateBadgeCount() {
        const badge = document.getElementById('todo-badge');
        let currentCount = parseInt(badge.innerText);
        if (currentCount > 0) {
            badge.innerText = currentCount - 1;
        }
    }

</script>