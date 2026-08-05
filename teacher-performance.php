<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Section for starting the analysis job -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Run Teacher Performance Analysis</h5>
        </div>
        <div class="card-body">
            <form id="analysisForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="sessionYear" class="form-label">Session Year</label>
                        <select id="sessionYear" name="sessionyear" class="form-select">
                            <!-- Options will be loaded dynamically -->
                            <?php
                            $current_year = date('Y');
                            for ($i = $current_year; $i >= $current_year - 5; $i--) {
                                echo "<option value='$i'>$i</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="examId" class="form-label">Examination</label>
                        <select id="examId" name="examid" class="form-select" disabled>
                            <option value="">Select Session First</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-play-circle me-2"></i>Start Analysis
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <!-- Progress Bar Section -->
        <div class="card-footer" id="progressSection" style="display: none;">
            <h6 id="progressTitle">Processing...</h6>
            <div class="progress" style="height: 20px;">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
            <div id="progressStatus" class="mt-2 small text-muted"></div>
        </div>
    </div>

    <!-- Section for displaying results -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Performance Report</h5>
        </div>
        <div class="card-body">
            <!-- Filters for viewing results (Only for Admins) -->
            <?php if (($_SESSION['is_admin'] ?? 0) >= 4): ?>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="reportSessionYear" class="form-label">Session Year</label>
                    <select id="reportSessionYear" class="form-select">
                        <?php
                        $current_year = date('Y');
                        for ($i = $current_year; $i >= $current_year - 5; $i--) {
                            echo "<option value='$i'>$i</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="reportExamId" class="form-label">Examination</label>
                    <select id="reportExamId" class="form-select" disabled>
                        <option value="">Select Session First</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" id="loadReportBtn" class="btn btn-info w-100">
                        <i class="bi bi-eye me-2"></i>View Report
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Chart Section -->
            <div id="chartContainer" class="mb-4" style="display: none;">
                <canvas id="performanceChart"></canvas>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Pass Rate</th>
                            <th>Average GPA</th>
                            <th>A+ Count</th>
                        </tr>
                    </thead>
                    <tbody id="reportTableBody">
                        <tr>
                            <td colspan="6" class="text-center text-muted">No data to display. Run an analysis to see the report.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sessionSelect = document.getElementById('sessionYear');
        const examSelect = document.getElementById('examId');
        const analysisForm = document.getElementById('analysisForm');
        const progressSection = document.getElementById('progressSection');
        const progressBar = document.getElementById('progressBar');
        const progressStatus = document.getElementById('progressStatus');
        const progressTitle = document.getElementById('progressTitle');
        const reportSessionSelect = document.getElementById('reportSessionYear');
        const reportExamSelect = document.getElementById('reportExamId');
        const loadReportBtn = document.getElementById('loadReportBtn');
        const reportTableBody = document.getElementById('reportTableBody');

        let pollInterval;
        const isAdmin = <?php echo (($_SESSION['is_admin'] ?? 0) >= 4) ? 'true' : 'false'; ?>;
        const defaultSession = '<?php echo date('Y'); ?>';

        let performanceChartInstance; // To hold the chart instance

        // Load exams when session changes
        sessionSelect.addEventListener('change', function() {
            const session = this.value;
            examSelect.disabled = true;
            examSelect.innerHTML = '<option value="">Loading...</option>';

            if (!session) {
                examSelect.innerHTML = '<option value="">Select Session First</option>';
                return;
            }

            // AJAX call to get exams for the selected session
            // You need to create this PHP file: api/get_exams.php
            fetch(`api/get_exams.php?sessionyear=${session}`)
                .then(response => response.json())
                .then(data => {
                    examSelect.innerHTML = '<option value="">Select Examination</option>';
                    if (data.status === 'success') {
                        data.exams.forEach(exam => {
                            examSelect.innerHTML += `<option value="${exam.id}">${exam.examtitle}</option>`;
                        });
                        examSelect.disabled = false;
                    } else {
                        examSelect.innerHTML = `<option value="">${data.message}</option>`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching exams:', error);
                    examSelect.innerHTML = '<option value="">Error loading exams</option>';
                });
        });

        // Trigger change on page load to populate exams for the default session
        sessionSelect.dispatchEvent(new Event('change'));

        // Load exams for the report section
        reportSessionSelect.addEventListener('change', function() {
            const session = this.value;
            reportExamSelect.disabled = true;
            reportExamSelect.innerHTML = '<option value="">Loading...</option>';

            if (!session) {
                reportExamSelect.innerHTML = '<option value="">Select Session First</option>';
                return;
            }

            fetch(`api/get_exams.php?sessionyear=${session}`)
                .then(response => response.json())
                .then(data => {
                    reportExamSelect.innerHTML = '<option value="">Select Examination</option>';
                    if (data.status === 'success') {
                        data.exams.forEach(exam => {
                            reportExamSelect.innerHTML += `<option value="${exam.id}">${exam.examtitle}</option>`;
                        });
                        reportExamSelect.disabled = false;
                    } else {
                        reportExamSelect.innerHTML = `<option value="">${data.message}</option>`;
                    }
                });
        });

        // Trigger change for report section on page load
        reportSessionSelect.dispatchEvent(new Event('change'));

        // Handle form submission to create a job
        analysisForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            progressSection.style.display = 'block';
            progressTitle.textContent = 'Creating job...';
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';
            progressStatus.textContent = 'Please wait while the analysis job is being created.';

            fetch('api/create_job.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.jobid) {
                        progressTitle.textContent = `Processing Job #${data.jobid}`;
                        progressStatus.textContent = 'Job has been queued. Starting process...';
                        // Start polling for job status
                        pollJobStatus(data.jobid, formData.get('sessionyear'), formData.get('examid'));

                        // **NEW:** Trigger the background runner asynchronously
                        fetch('api/run_job_async.php');

                    }else {
                        progressTitle.textContent = 'Error';
                        progressStatus.textContent = data.message || 'Failed to create job.';
                        progressBar.classList.add('bg-danger');
                    }
                })
                .catch(error => {
                    console.error('Error creating job:', error);
                    progressTitle.textContent = 'Error';
                    progressStatus.textContent = 'A network error occurred.';
                    progressBar.classList.add('bg-danger');
                });
        });

        // Poll for job status
        function pollJobStatus(jobid, session, examid) {
            pollInterval = setInterval(() => {
                fetch(`api/get_job_status.php?jobid=${jobid}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success' && data.job) {
                            const job = data.job;
                            const progress = parseFloat(job.progress).toFixed(2);

                            progressBar.style.width = `${progress}%`;
                            progressBar.textContent = `${progress}%`;
                            progressStatus.textContent = `Status: ${job.status}`;

                            if (job.status === 'Completed') {
                                clearInterval(pollInterval);
                                progressBar.classList.remove('progress-bar-animated');
                                progressBar.classList.add('bg-success');
                                progressTitle.textContent = 'Analysis Completed!';
                                // Automatically load the results now
                                loadReportData(session, examid);
                            } else if (job.status === 'Failed') {
                                clearInterval(pollInterval);
                                progressBar.classList.remove('progress-bar-animated');
                                progressBar.classList.add('bg-danger');
                                progressTitle.textContent = 'Analysis Failed';
                                progressStatus.textContent = `Error: ${job.errmsg}`;
                            }
                        } else {
                            clearInterval(pollInterval);
                            progressStatus.textContent = 'Could not get job status.';
                        }
                    })
                    .catch(error => {
                        clearInterval(pollInterval);
                        console.error('Error polling job status:', error);
                        progressStatus.textContent = 'Error checking job status.';
                    });
            }, 3000); // Poll every 3 seconds
        }

        // Render performance chart
        function renderPerformanceChart(data) {
            // Destroy previous chart instance if it exists
            if (performanceChartInstance) {
                performanceChartInstance.destroy();
            }

            // Process data for the chart: Calculate average pass rate per teacher
            const teacherData = {};
            data.forEach(row => {
                const teacherName = row.teacher_name || 'Unknown Teacher';
                if (!teacherData[teacherName]) {
                    teacherData[teacherName] = {
                        totalPassRate: 0,
                        count: 0
                    };
                }
                teacherData[teacherName].totalPassRate += parseFloat(row.pass_rate);
                teacherData[teacherName].count++;
            });

            const labels = Object.keys(teacherData);
            const avgPassRates = labels.map(teacher => {
                return (teacherData[teacher].totalPassRate / teacherData[teacher].count).toFixed(2);
            });

            const ctx = document.getElementById('performanceChart').getContext('2d');
            performanceChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Average Pass Rate (%)',
                        data: avgPassRates,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: { scales: { y: { beginAtZero: true, max: 100 } } }
            });
        }

        // Load report data via AJAX
        function loadReportData(session, examid) {
            if (!session || !examid) {
                alert('Please select Session and Examination to view the report.');
                return;
            }

            reportTableBody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Loading report...</td></tr>`;

            fetch(`api/get_teacher_performance.php?sessionyear=${session}&examid=${examid}`)
                .then(response => response.json())
                .then(result => {
                    // Hide chart container initially
                    const chartContainer = document.getElementById('chartContainer');
                    chartContainer.style.display = 'none';

                    if (result.status === 'success' && result.data.length > 0) {
                        reportTableBody.innerHTML = ''; // Clear loading message
                        result.data.forEach(row => {
                            const tr = `
                                <tr>
                                    <td>${row.teacher_name || 'N/A'}</td>
                                    <td>${row.subject_name || 'N/A'} (${row.subjectid})</td>
                                    <td>${row.classname} (${row.sectionname})</td>
                                    <td><span class="badge bg-label-success">${parseFloat(row.pass_rate).toFixed(2)}%</span></td>
                                    <td><span class="badge bg-label-info">${parseFloat(row.average_gpa).toFixed(2)}</span></td>
                                    <td>${row.a_plus_count}</td>
                                </tr>
                            `;
                            reportTableBody.innerHTML += tr;
                        });

                        // Render the chart with the new data
                        chartContainer.style.display = 'block';
                        renderPerformanceChart(result.data);
                    } else {
                        reportTableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${result.message || 'No data found.'}</td></tr>`;
                    }
                })
                .catch(error => {
                    console.error('Error loading report:', error);
                    reportTableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">An error occurred while loading the report.</td></tr>`;
                });
        }

        // Event listener for the load report button
        if (isAdmin) {
            loadReportBtn.addEventListener('click', () => loadReportData(reportSessionSelect.value, reportExamSelect.value));
        } else {
            // For non-admins, load the report automatically for the current year and latest exam
            fetch(`api/get_exams.php?sessionyear=${defaultSession}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success' && data.exams.length > 0) {
                        loadReportData(defaultSession, data.exams[data.exams.length - 1].id); // সর্বশেষ পরীক্ষা
                    }
                });
        }
    });
</script>
<!-- ----------------------------------- -->
</body>

</html>