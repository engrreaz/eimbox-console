<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Analytics /</span> Final Reports</h4>

    <!-- Section for selecting the analysis report -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">View Analysis Report</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="slot" class="form-label">Slot</label>
                    <select id="slot" name="slot" class="form-select">
                        <option value="">Select Slot</option>
                        <?php
                        $sccode = $_SESSION['sccode'];
                        $slots_query = $conn->prepare("SELECT slotname FROM slots WHERE sccode = ? ORDER BY slotname");
                        $slots_query->bind_param("s", $sccode);
                        $slots_query->execute();
                        $slots_result = $slots_query->get_result();
                        while ($slot_row = $slots_result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($slot_row['slotname']) . "'>" . htmlspecialchars($slot_row['slotname']) . "</option>";
                        }
                        $slots_query->close();
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sessionYear" class="form-label">Session Year</label>
                    <select id="sessionYear" name="sessionyear" class="form-select">
                        <option value="">Select Session</option>
                        <?php
                        $sessions_query = $conn->prepare("SELECT syear FROM sessionyear WHERE sccode = ? AND active = 1 ORDER BY syear DESC");
                        $sessions_query->bind_param("s", $sccode);
                        $sessions_query->execute();
                        $sessions_result = $sessions_query->get_result();
                        while ($session_row = $sessions_result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($session_row['syear']) . "'>" . htmlspecialchars($session_row['syear']) . "</option>";
                        }
                        $sessions_query->close();
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="examId" class="form-label">Examination</label>
                    <select id="examId" name="examid[]" class="form-select" multiple disabled>
                        <!-- Options will be loaded by JS -->
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" id="viewReportBtn" class="btn btn-primary w-100 p-3" disabled>
                        <i class="bi bi-eye me-2"></i>View Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Section for displaying reports -->
    <div class="card" id="reportContainer" style="display: none;">
        <div class="card-header p-0">
            <div class="nav-align-top">
                <ul class="nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a id="printAllBtn" href="#" target="_blank" class="nav-link" style="background-color: #e7e7ff;"><i class="tf-icons ri-printer-line me-1"></i> Print All</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-institute" aria-controls="tab-institute" aria-selected="true">
                            <i class="tf-icons ri-building-line me-1"></i> Institute Overview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-teacher" aria-controls="tab-teacher" aria-selected="false">
                            <i class="tf-icons ri-user-star-line me-1"></i> Teacher Performance
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-class" aria-controls="tab-class" aria-selected="false">
                            <i class="tf-icons ri-group-line me-1"></i> Class Report
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-subject" aria-controls="tab-subject" aria-selected="false">
                            <i class="tf-icons ri-book-2-line me-1"></i> Subject Report
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-student" aria-controls="tab-student" aria-selected="false">
                            <i class="tf-icons ri-user-line me-1"></i> Student Merit
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <div class="tab-content p-0">
                <div class="tab-pane fade show active" id="tab-institute" role="tabpanel">
                    <div id="instituteReportContent">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5 class="card-title text-white">Total Students</h5>
                                        <p class="card-text fs-3 fw-bold" id="totalStudents"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title text-white">Overall Pass Rate</h5>
                                        <p class="card-text fs-3 fw-bold" id="overallPassRate"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5 class="card-title text-white">Overall Avg. Marks</h5>
                                        <p class="card-text fs-3 fw-bold" id="overallAvgMarks"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Gender-based Performance</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Males:</strong> <span id="malePassRate"></span>% Pass Rate, <span id="maleAvgMarks"></span> Avg. Marks</p>
                                        <p><strong>Females:</strong> <span id="femalePassRate"></span>% Pass Rate, <span id="femaleAvgMarks"></span> Avg. Marks</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Areas for Improvement</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>At-Risk Students:</strong> <span id="atRiskStudentsCount"></span></p>
                                        <h6>Weakest Subjects (Highest Failure Rate):</h6>
                                        <ul id="weakestSubjectsList"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Top 10 Students</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead><tr><th>Rank</th><th>Student</th><th>Class</th><th>GPA</th></tr></thead>
                                                <tbody id="topStudentsTableBody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Top 3 Classes (by CPI)</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead><tr><th>Rank</th><th>Class</th><th>CPI Score</th></tr></thead>
                                                <tbody id="topClassesTableBody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Grade Distribution</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="gradeDistributionChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="tab-teacher" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Teacher</th>
                                    <th>Designation</th>
                                    <th>Avg. Marks</th>
                                    <th>Pass Rate (%)</th>
                                    <th>TPI Score</th>
                                    <th>TIA Score</th>
                                    <th>TCI Score</th>
                                    <th>TSI Score</th>
                                </tr>
                            </thead>
                            <tbody id="teacherReportTableBody">
                                <tr><td colspan="9" class="text-center text-muted">Select report criteria and click "View Report".</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="tab-class" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Class</th>
                                    <th>Students</th>
                                    <th>Avg. Marks (%)</th>
                                    <th>CPI Score</th>
                                    <th>Difficulty (CDF)</th>
                                </tr>
                            </thead>
                            <tbody id="classReportTableBody">
                                <tr><td colspan="6" class="text-center text-muted">Select report criteria and click "View Report".</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="tab-subject" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Students</th>
                                    <th>Avg. Marks</th>
                                    <th>Pass %</th>
                                    <th>Fail %</th>
                                    <th>Difficulty (SDF)</th>
                                </tr>
                            </thead>
                            <tbody id="subjectReportTableBody">
                                <tr><td colspan="6" class="text-center text-muted">Select report criteria and click "View Report".</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="tab-student" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Class Rank</th>
                                    <th>Section Rank</th>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Roll</th>
                                    <th>Total Marks</th>
                                    <th>Percentage</th>
                                    <th>GPA</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody id="studentReportTableBody">
                                <tr><td colspan="9" class="text-center text-muted">Select report criteria and click "View Report".</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    // Helper functions to set and get cookies
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    $(document).ready(function () {
        const slotSelect = $('#slot');
        const sessionSelect = $('#sessionYear');
        const examSelect = $('#examId');
        const viewReportBtn = $('#viewReportBtn');

        function fetchExams() {
            const slot = slotSelect.val();
            const session = sessionSelect.val();

            if (slot && session) {
                examSelect.prop('disabled', true).html('<option value="">Loading...</option>');
                $.ajax({
                    url: 'analytics/get_exams_by_slot_session.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { slot: slot, sessionyear: session },
                    success: function (data) {
                        examSelect.prop('disabled', false).html(''); // Clear options
                        if (data && data.exams && data.exams.length > 0) {
                            $.each(data.exams, function (index, exam) {
                                examSelect.append($('<option>', { value: exam.id, text: exam.examtitle }));
                            });
                            const savedExam = getCookie('analytics_report_exam');
                            if (savedExam) {
                                examSelect.val(savedExam.split(','));
                            }
                        } else {
                            examSelect.html('<option value="">No exams found</option>');
                        }
                    }
                });
            } else {
                examSelect.prop('disabled', true).html('');
            }
        }

        function checkSelections() {
            const slot = slotSelect.val();
            const session = sessionSelect.val();
            const exams = examSelect.val();
            viewReportBtn.prop('disabled', !(slot && session && exams && exams.length > 0));
        }

        // Restore selections from cookies
        slotSelect.val(getCookie('analytics_report_slot'));
        sessionSelect.val(getCookie('analytics_report_session'));
        fetchExams();

        // Event listeners
        slotSelect.on('change', function() { setCookie('analytics_report_slot', $(this).val(), 7); fetchExams(); checkSelections(); });
        sessionSelect.on('change', function() { setCookie('analytics_report_session', $(this).val(), 7); fetchExams(); checkSelections(); });
        examSelect.on('change', function() { setCookie('analytics_report_exam', $(this).val(), 7); checkSelections(); });

        viewReportBtn.on('click', function() {
            $('#reportContainer').slideDown();
            // Here you will add AJAX calls to load data for each tab
            loadReports();
        });

        checkSelections();
    });

    let currentDatasetId = null;

    async function loadReports() {
        const slot = $('#slot').val();
        const sessionyear = $('#sessionYear').val();
        const examids = $('#examId').val(); // This is an array
        const examid_list_str = examids.join(','); // Convert to comma-separated string

        if (!slot || !sessionyear || !examids || examids.length === 0) {
            alert('Please select Slot, Session, and Examination.');
            return;
        }

        // Fetch the dataset_id first
        try {
            const datasetIdResponse = await $.ajax({
                url: 'analytics/get_latest_dataset.php',
                type: 'GET', // Use GET for fetching data
                dataType: 'json',
                data: { examid: examid_list_str, slot: slot, sessionyear: sessionyear }
            });

            currentDatasetId = datasetIdResponse.dataset_id;

            if (currentDatasetId) {
                $('#printAllBtn').attr('href', `analytics-print-all.php?dataset_id=${currentDatasetId}`);
                // Load individual reports using the fetched dataset_id
                loadInstituteReport(currentDatasetId);
                loadTeacherReport(currentDatasetId);
                loadStudentReport(currentDatasetId);
                loadClassReport(currentDatasetId);
                loadSubjectReport(currentDatasetId);
            } else {
                alert('No analysis report found for the selected criteria. Please run analysis first.');
                $('#printAllBtn').attr('href', '#');
            }
        } catch (error) {
            console.error("Error fetching dataset ID:", error);
            alert('Failed to retrieve analysis report. Please try again.');
        }
    }

    async function loadTeacherReport(dataset_id) {
        const teacherTableBody = $('#teacherReportTableBody');
        teacherTableBody.html('<tr><td colspan="9" class="text-center"><span class="spinner-border spinner-border-sm"></span> Loading Teacher Report...</td></tr>');
        try {
            const response = await $.ajax({ url: 'analytics/get_teacher_report.php', data: { dataset_id: dataset_id } });
            if (response.status === 'success' && response.data.length > 0) {
                let tableContent = '';
                response.data.forEach((row, index) => {
                    tableContent += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${row.tname}</td>
                            <td>${row.position || ''}</td>
                            <td>${parseFloat(row.overall_avg_marks).toFixed(2)}</td>
                            <td>${parseFloat(row.overall_pass_rate).toFixed(2)}</td>
                            <td>${parseFloat(row.tpi).toFixed(2)}</td>
                            <td class="fw-bold">${parseFloat(row.tia).toFixed(2)}</td>
                            <td>${parseFloat(row.tci_score).toFixed(2)}</td>
                            <td>${parseFloat(row.tsi_score).toFixed(2)}</td>
                        </tr>
                    `;
                });
                teacherTableBody.html(tableContent);
            } else {
                teacherTableBody.html('<tr><td colspan="9" class="text-center text-muted">No teacher data found for this report.</td></tr>');
            }
        } catch (error) {
            console.error("Failed to fetch teacher report:", error);
            teacherTableBody.html(`<tr><td colspan="9" class="text-center text-danger">Failed to load teacher report: ${error.message || error.statusText}</td></tr>`);
        }
    }

    async function loadStudentReport(dataset_id) {
        const studentTableBody = $('#studentReportTableBody');
        studentTableBody.html('<tr><td colspan="9" class="text-center"><span class="spinner-border spinner-border-sm"></span> Loading Student Merit List...</td></tr>');
        try {
            const response = await $.ajax({ url: 'analytics/get_student_report.php', data: { dataset_id: dataset_id } });
            if (response.status === 'success' && response.data.length > 0) {
                let tableContent = '';
                response.data.forEach(row => {
                    const rankClass = row.failed_subjects > 0 ? 'text-danger' : 'fw-bold';
                    const classRank = row.failed_subjects > 0 ? 'F' : row.class_rank;
                    const sectionRank = row.failed_subjects > 0 ? 'F' : row.section_rank;

                    tableContent += `
                        <tr>
                            <td class="${rankClass}">${classRank}</td>
                            <td>${sectionRank}</td>
                            <td>${row.stnameeng}</td>
                            <td>${row.classname} - ${row.sectionname}</td>
                            <td>${row.rollno}</td>
                            <td>${parseFloat(row.total_marks_obtained).toFixed(2)}</td>
                            <td>${parseFloat(row.percentage).toFixed(2)}%</td>
                            <td>${parseFloat(row.gpa).toFixed(2)}</td>
                            <td>${row.grade}</td>
                        </tr>
                    `;
                });
                studentTableBody.html(tableContent);
            } else {
                studentTableBody.html('<tr><td colspan="9" class="text-center text-muted">No student data found for this report.</td></tr>');
            }
        } catch (error) {
            console.error("Failed to fetch student report:", error);
            studentTableBody.html(`<tr><td colspan="9" class="text-center text-danger">Failed to load student report: ${error.message || error.statusText}</td></tr>`);
        }
    }

    async function loadClassReport(dataset_id) {
        const classTableBody = $('#classReportTableBody');
        classTableBody.html('<tr><td colspan="6" class="text-center"><span class="spinner-border spinner-border-sm"></span> Loading Class Report...</td></tr>');
        try {
            const response = await $.ajax({ url: 'analytics/get_class_report.php', data: { dataset_id: dataset_id } });
            if (response.status === 'success' && response.data.length > 0) {
                let tableContent = '';
                response.data.forEach(row => {
                    tableContent += `
                        <tr>
                            <td class="fw-bold">${row.class_rank}</td>
                            <td>${row.classname} - ${row.sectionname}</td>
                            <td>${row.total_students_appeared}</td>
                            <td>${parseFloat(row.overall_marks_percentage).toFixed(2)}%</td>
                            <td>${parseFloat(row.cpi_score).toFixed(2)}</td>
                            <td>${parseFloat(row.difficulty_factor).toFixed(2)}</td>
                        </tr>
                    `;
                });
                classTableBody.html(tableContent);
            } else {
                classTableBody.html('<tr><td colspan="6" class="text-center text-muted">No class data found for this report.</td></tr>');
            }
        } catch (error) {
            console.error("Failed to fetch class report:", error);
            classTableBody.html(`<tr><td colspan="6" class="text-center text-danger">Failed to load class report.</td></tr>`);
        }
    }

    async function loadSubjectReport(dataset_id) {
        const subjectTableBody = $('#subjectReportTableBody');
        subjectTableBody.html('<tr><td colspan="6" class="text-center"><span class="spinner-border spinner-border-sm"></span> Loading Subject Report...</td></tr>');
        try {
            const response = await $.ajax({ url: 'analytics/get_subject_report.php', data: { dataset_id: dataset_id } });
            if (response.status === 'success' && response.data.length > 0) {
                let tableContent = '';
                response.data.forEach(row => {
                    tableContent += `
                        <tr>
                            <td>${row.subject_code} &mdash; ${row.subject_name}</td>
                            <td>${row.total_students_appeared}</td>
                            <td>${parseFloat(row.overall_avg_marks).toFixed(2)}</td>
                            <td>${parseFloat(100 - row.failure_rate).toFixed(2)}%</td>
                            <td>${parseFloat(row.failure_rate).toFixed(2)}%</td>
                            <td class="fw-bold">${parseFloat(row.sdf).toFixed(2)}</td>
                        </tr>
                    `;
                });
                subjectTableBody.html(tableContent);
            } else {
                subjectTableBody.html('<tr><td colspan="6" class="text-center text-muted">No subject data found for this report.</td></tr>');
            }
        } catch (error) {
            console.error("Failed to fetch subject report:", error);
            subjectTableBody.html(`<tr><td colspan="6" class="text-center text-danger">Failed to load subject report.</td></tr>`);
        }
    }

    let gradeChartInstance = null; // To store Chart.js instance

    async function loadInstituteReport(dataset_id) {
        const instituteReportContent = $('#instituteReportContent');
        instituteReportContent.find('.card-text, .card-title, ul, tbody').html('<span class="spinner-border spinner-border-sm"></span> Loading...');
        try {
            const response = await $.ajax({ url: 'analytics/get_institute_report.php', data: { dataset_id: dataset_id } });
            if (response.status === 'success' && response.data) {
                const data = response.data;

                // Summary
                $('#totalStudents').closest('.card-body').find('.card-title').text(`Total Students (${data.summary.total_students})`);
                $('#totalStudents').text(data.summary.total_students);
                $('#overallPassRate').closest('.card-body').find('.card-title').text(`Pass Rate (${data.summary.total_passed_students}/${data.summary.total_students})`);
                $('#overallPassRate').text(data.summary.pass_rate.toFixed(2) + '%');
                $('#overallAvgMarks').closest('.card-body').find('.card-title').text('Overall Average Marks');
                $('#overallAvgMarks').text(data.summary.overall_avg_marks_percentage.toFixed(2));

                // Gender-based Performance
                $('#malePassRate').closest('.card').find('.card-title').text(`Gender Performance (M: ${data.gender_performance.total_males}, F: ${data.gender_performance.total_females})`);
                $('#malePassRate').text(data.gender_performance.male_pass_rate.toFixed(2));
                $('#maleAvgMarks').text(data.gender_performance.avg_male_marks.toFixed(2));
                $('#femalePassRate').text(data.gender_performance.female_pass_rate.toFixed(2));
                $('#femaleAvgMarks').text(data.gender_performance.avg_female_marks.toFixed(2));

                // Areas for Improvement
                $('#atRiskStudentsCount').text(data.at_risk_count);
                $('#atRiskStudentsCount').closest('.card').find('.card-title').text(`Areas for Improvement`);
                let weakestSubjectsHtml = '';
                data.weakest_subjects.forEach(sub => {
                    weakestSubjectsHtml += `<li>${sub.subject_name} (${sub.failure_rate.toFixed(2)}% Failure)</li>`;
                });
                $('#weakestSubjectsList').html(weakestSubjectsHtml || '<li>N/A</li>');

                // Top 10 Students
                $('#topStudentsTableBody').closest('.card').find('.card-header .card-title').text('Top 10 Students');
                let topStudentsHtml = '';
                data.top_students.forEach((student, index) => {
                    topStudentsHtml += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${student.stnameeng}</td>
                            <td>${student.classname} - ${student.sectionname}</td>
                            <td>${student.gpa.toFixed(2)}</td>
                        </tr>
                    `;
                });
                $('#topStudentsTableBody').html(topStudentsHtml || '<tr><td colspan="4" class="text-center text-muted">No top students found.</td></tr>');

                // Top 3 Classes
                $('#topClassesTableBody').closest('.card').find('.card-header .card-title').text('Top 3 Classes (by CPI)');
                let topClassesHtml = '';
                data.top_classes.forEach(cls => {
                    topClassesHtml += `
                        <tr>
                            <td>${cls.class_rank}</td>
                            <td>${cls.classname} - ${cls.sectionname}</td>
                            <td>${cls.cpi_score.toFixed(2)}</td>
                        </tr>
                    `;
                });
                $('#topClassesTableBody').html(topClassesHtml || '<tr><td colspan="3" class="text-center text-muted">No top classes found.</td></tr>');

                // Grade Distribution Chart
                $('#gradeDistributionChart').closest('.card').find('.card-header .card-title').text('Grade Distribution');
                const grades = data.grade_distribution.map(item => item.grade);
                const studentCounts = data.grade_distribution.map(item => item.student_count);

                if (gradeChartInstance) {
                    gradeChartInstance.destroy(); // Destroy existing chart before creating a new one
                }
                const ctx = document.getElementById('gradeDistributionChart').getContext('2d');
                gradeChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: grades,
                        datasets: [{
                            label: 'Number of Students',
                            data: studentCounts,
                            backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#fd7e14', '#6c757d', '#dc3545', '#000000'], // Example colors
                            borderColor: ['#28a745', '#17a2b8', '#ffc107', '#fd7e14', '#6c757d', '#dc3545', '#000000'],
                            borderWidth: 1
                        }]
                    },
                    options: { scales: { y: { beginAtZero: true } } }
                });

            } else {
                instituteReportContent.html('<p class="text-center text-muted">No institute overview data found for this report.</p>');
            }
        } catch (error) {
            console.error("Failed to fetch institute report:", error);
            instituteReportContent.html(`<p class="text-center text-danger">Failed to load institute report: ${error.message || error.statusText}</p>`);
        }
    }
</script>

</body>
</html>