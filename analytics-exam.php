<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Section for starting the analysis job -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Run Exam Performance Analysis</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
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
                <div class="col-md-2">
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
                <div class="col-md-3">
                    <label for="examId" class="form-label">Examination</label>
                    <select id="examId" name="examid[]" class="form-select" multiple disabled>
                        <!-- Options will be loaded by JS -->
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" id="startAnalysisBtn" class="btn btn-primary w-100 p-3" disabled>
                        <i class="bi bi-play-circle me-2"></i>Start Analysis
                    </button>
                </div>
                <div class="col-md-1">
                    <button type="button" id="checkValidityBtn" class="btn btn-outline-info w-100 p-3" disabled title="Check Data Validity"><i class="bi bi-shield-check"></i></button>
                </div>
                <div class="col-md-2">
                    <button type="button" id="viewHistoryBtn" class="btn btn-outline-secondary w-100 p-3 fs-5" data-bs-toggle="modal" data-bs-target="#historyModal" disabled title="View Analysis History">
                        <i class="bi bi-clock-history"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Section for displaying validation results -->
    <div class="card mb-4" id="validationResultsSection" style="display: none;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-clipboard2-data-fill me-2"></i>Data Validity Report</h5>
            <button type="button" class="btn-close" id="closeValidationReport" aria-label="Close"></button>
        </div>
        <div class="card-body">
            <div id="validationStatus" class="text-center p-4">
                <!-- Validation results will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Section for displaying analysis progress -->
    <div class="card mb-4" id="progressSection" style="display: none;">
        <div class="card-header">
            <h5 class="card-title mb-0">Analysis in Progress...</h5>
        </div>
        <div class="card-body">
            <div class="progress mb-3" style="height: 25px;">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
            <div id="progressStatus" class="d-flex align-items-center text-muted">
                <i id="statusIcon" class="bi bi-hourglass-split me-2"></i>
                <span id="statusText">Waiting to start...</span>
            </div>
        </div>
    </div>

    <!-- Section for displaying results -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Teacher Performance Report</h5>
        </div>
        <div class="card-body">
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
                        </tr>
                    </thead>
                    <tbody id="reportTableBody">
                        <tr>
                            <td colspan="7" class="text-center text-muted">Run analysis to see the report.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Analysis History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="historyModalLabel">Analysis History</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">List of previously generated reports for the selected examination. Click on any version to view it.</p>
        <div id="historyList" class="list-group">
          <!-- History items will be loaded here by JavaScript -->
          <div class="text-center text-muted p-4">Loading history...</div>
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

        // --- Automatically load latest report on selection change ---
        let changeTimeout;

        function fetchExams() {
            const slot = slotSelect.val();
            const session = sessionSelect.val();

            if (slot && session) {
                examSelect.prop('disabled', true).html('<option value="">Loading...</option>');

                $.ajax({
                    url: 'analytics/get_exams_by_slot_session.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        slot: slot,
                        sessionyear: session
                    },
                    success: function (data) {
                        examSelect.prop('disabled', false).html('<option value="">Select Examination</option>');
                        if (data && data.exams && data.exams.length > 0) {
                            $.each(data.exams, function (index, exam) {
                                examSelect.append($('<option>', {
                                    value: exam.id,
                                    text: exam.examtitle
                                }));
                            });
                            // Restore selected exam from cookie after options are loaded
                            const savedExam = getCookie('analytics_exam');
                            if (savedExam) {
                                examSelect.val(savedExam);
                                // Trigger report load if exam is pre-selected
                                if (examSelect.val()) {
                                    $('#startAnalysisBtn').prop('disabled', false);
                                    $('#checkValidityBtn').prop('disabled', false);
                                    loadLatestReport();
                                }
                            }
                        }
                    }
                });
            } else {
                examSelect.prop('disabled', true).html('<option value="">Select Slot & Session</option>');
            }
        }

        // Restore selections from cookies on page load
        const savedSlot = getCookie('analytics_slot');
        const savedSession = getCookie('analytics_session');

        if (savedSlot) {
            slotSelect.val(savedSlot);
        }
        if (savedSession) {
            sessionSelect.val(savedSession);
        }

        // Fetch exams if both slot and session are restored
        if (savedSlot && savedSession) {
            fetchExams();
        }

        // Save selections and trigger report loading
        function handleSelectionChange() {
            setCookie('analytics_slot', slotSelect.val(), 7);
            setCookie('analytics_session', sessionSelect.val(), 7);
            setCookie('analytics_exam', examSelect.val(), 7);

            clearTimeout(changeTimeout);
            changeTimeout = setTimeout(() => {
                if (examSelect.val()) {
                    $('#startAnalysisBtn').prop('disabled', false);
                    $('#checkValidityBtn').prop('disabled', false);
                    loadLatestReport();
                }
            }, 500); // Debounce to avoid rapid firing
        }

        slotSelect.on('change', () => { fetchExams(); handleSelectionChange(); });
        sessionSelect.on('change', () => { fetchExams(); handleSelectionChange(); });
        examSelect.on('change', handleSelectionChange);

        // --- Validity Check Logic ---
        $('#checkValidityBtn').on('click', function() {
            const slot = $('#slot').val();
            const sessionyear = $('#sessionYear').val();
            const examid = $('#examId').val();

            $('#validationResultsSection').slideDown();
            const validationStatusDiv = $('#validationStatus');
            validationStatusDiv.html('<div class="d-flex align-items-center justify-content-center p-3"><span class="spinner-border text-primary me-2"></span> <span>Checking data validity...</span></div>');

            $.ajax({
                url: 'analytics/check_data_validity.php',
                type: 'POST',
                dataType: 'json',
                data: { slot, sessionyear, examid },
                success: function(response) {
                    if (response.status === 'success') {
                        let html = '<ul class="list-group list-group-flush">';
                        response.issues.forEach(issue => { 
                            let icon = issue.type === 'error' ? 'bi-x-octagon-fill text-danger' : (issue.type === 'warning' ? 'bi-exclamation-triangle-fill text-warning' : 'bi-check-circle-fill text-success');
                            let linkHtml = '';
                            if (issue.url && issue.url_text) {
                                linkHtml = `<a href="${issue.url}" target="_blank" class="btn btn-sm btn-outline-primary ms-auto">${issue.url_text} <i class="bi bi-box-arrow-up-right"></i></a>`;
                            }
                            html += `<li class="list-group-item d-flex align-items-center"><i class="bi ${icon} me-3 fs-4"></i> <div class="me-3">${issue.message}</div> ${linkHtml}</li>`;
                        });
                        html += '</ul>';
                        validationStatusDiv.html(html);
                    } else {
                        validationStatusDiv.html(`<div class="alert alert-danger">${response.message}</div>`);
                    }
                },
                error: function() {
                    validationStatusDiv.html('<div class="alert alert-danger">An error occurred while checking data validity.</div>');
                }
            });
        });

        $('#closeValidationReport').on('click', function() {
            $('#validationResultsSection').slideUp();
        });
    });

    // --- Analysis Process Logic ---
    $('#startAnalysisBtn').on('click', async function () {
        const slot = $('#slot').val();
        const sessionyear = $('#sessionYear').val();
        const examid = $('#examId').val();

        if (!slot || !sessionyear || !examid) {
            alert('Please select Slot, Session, and Examination before starting.');
            return;
        }

        // UI setup
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Working...');
        $('#progressSection').slideDown();
        
        let currentProgress = 0;
        updateProgress(currentProgress, 'bi-hourglass-split', 'Fetching analysis steps...');

        try {
            // 1. Fetch the steps from JSON file
            // Cache busting: Add a unique query parameter to prevent the browser from using a cached version of the JSON file.
            // Since analysis_steps.json is not provided, I am defining the steps array directly here.
            // In your actual code, you would modify the JSON file.
            const steps = [
                { "file": "analytics-exam-step-0.php", "title": "Initializing Analysis", "increment": 5, "icon": "bi-power" },
                { "file": "analytics-exam-step-1.php", "title": "Analyzing Subject Performance", "increment": 15, "icon": "bi-journal-text" },
                { "file": "analytics-exam-step-2.php", "title": "Calculating Class Academic Index (CAI)", "increment": 10, "icon": "bi-building" },
                { "file": "analytics-exam-step-3.php", "title": "Calculating Subject Performance Index (SPI)", "increment": 10, "icon": "bi-book" },
                { "file": "analytics-exam-step-4.php", "title": "Calculating Class Ranks (CPI)", "increment": 5, "icon": "bi-trophy" },
                { "file": "analytics-exam-step-5.php", "title": "Aggregating Teacher Data", "increment": 10, "icon": "bi-person-video3" },
                { "file": "analytics-exam-step-6.php", "title": "Calculating Subject Difficulty (SDF)", "increment": 5, "icon": "bi-reception-3" },
                { "file": "analytics-exam-step-7.php", "title": "Calculating Combined Difficulty (CDI)", "increment": 5, "icon": "bi-puzzle" },
                { "file": "analytics-exam-step-8.php", "title": "Calculating Teacher Scores (TPI & TIA)", "increment": 5, "icon": "bi-calculator" },
                { "file": "analytics-exam-step-8a.php", "title": "Calculating Teacher Subject Index (TSPI)", "increment": 5, "icon": "bi-card-checklist" },
                { "file": "analytics-exam-step-9.php", "title": "Aggregating Student Performance", "increment": 10, "icon": "bi-people" },
                { "file": "analytics-exam-step-11.php", "title": "Calculating Student GPA & Grades", "increment": 5, "icon": "bi-mortarboard" },
                { "file": "analytics-exam-step-12.php", "title": "Calculating Teacher Impact (TCI & TSI)", "increment": 5, "icon": "bi-graph-up-arrow" },
                { "file": "analytics-exam-step-13.php", "title": "Calculating Student Risk Score (SRS)", "increment": 5, "icon": "bi-heart-pulse" },
                { "file": "analytics-exam-step-10.php", "title": "Identifying At-Risk Students", "increment": 5, "icon": "bi-exclamation-triangle" },
                { "file": "analytics-exam-step-15.php", "title": "Calculating Teacher Ranks", "increment": 3, "icon": "bi-award" },
                { "file": "analytics-exam-step-14.php", "title": "Finalizing Dataset Summary", "increment": 2, "icon": "bi-archive" }
            ];
            if (!response.ok) throw new Error('Could not load analysis_steps.json');
            const steps = await response.json();
            
            // 2. Execute each step sequentially
            for (const step of steps) {
                updateProgress(currentProgress, step.icon || 'bi-gear-wide-connected', `Starting: ${step.title}...`);

                const result = await runStep(step.file, { slot, sessionyear, examid });
                
                if (result.status !== 'success') {
                    throw new Error(`Step '${step.title}' failed: ${result.message}`);
                }
                
                // Add the increment to the current progress
                currentProgress += step.increment || 0;
                updateProgress(currentProgress, step.icon || 'bi-gear-wide-connected', `Completed: ${step.title}`);
            }

            // 3. Finalize
            updateProgress(100, 'bi-check-circle-fill text-success', 'Analysis complete!');
            await fetchAndDisplayReport(examid); // Fetch and display the report
            $('#startAnalysisBtn').prop('disabled', false).html('<i class="bi bi-play-circle me-2"></i>Start Analysis');

        } catch (error) {
            // এররটি কনসোলে লগ করুন, যাতে ডিবাগ করা সহজ হয়
            console.error("An error occurred during analysis:", error);
            updateProgress(100, 'bi-x-circle-fill text-danger', `Error: ${error.message}`);
            $('#progressBar').addClass('bg-danger');
            $('#startAnalysisBtn').prop('disabled', false).html('<i class="bi bi-play-circle me-2"></i>Start Analysis');
        }
    });

    function runStep(stepFile, params) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: 'analytics/run_analysis_step.php',
                type: 'POST',
                dataType: 'json',
                data: { step_file: stepFile, ...params },
                success: resolve,
                error: (xhr) => {
                    const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'A server error occurred.';
                    reject(new Error(errorMsg));
                }
            });
        });
    }

    function updateProgress(percentage, iconClass, text) {
        $('#progressBar').css('width', percentage + '%').attr('aria-valuenow', percentage).text(percentage + '%');
        $('#statusIcon').attr('class', `bi ${iconClass} me-2`);
        $('#statusText').text(text);
    }

    // --- Report Display Logic ---

    async function loadLatestReport() {
        const examId = $('#examId').val();
        const slot = $('#slot').val();
        const sessionyear = $('#sessionYear').val();
        if (!examId || !slot || !sessionyear) return;

        try {
            const datasetIdResponse = await $.ajax({ 
                url: 'analytics/get_latest_dataset.php', 
                data: { examid: examId, slot: slot, sessionyear: sessionyear } 
            });
            const dataset_id = datasetIdResponse.dataset_id;

            if (dataset_id) {
                $('#viewHistoryBtn').prop('disabled', false);
                await fetchAndDisplayReport(dataset_id);
            } else {
                $('#viewHistoryBtn').prop('disabled', true);
                $('#reportTableBody').html('<tr><td colspan="7" class="text-center text-muted">No analysis has been run for this exam yet.</td></tr>');
            }
        } catch (error) {
            console.error("Error loading latest report:", error);
            $('#reportTableBody').html(`<tr><td colspan="7" class="text-center text-danger">Failed to check for previous reports.</td></tr>`);
        }
    }

    async function fetchAndDisplayReport(dataset_id) {
        const reportTableBody = $('#reportTableBody');
        reportTableBody.html('<tr><td colspan="7" class="text-center"><span class="spinner-border spinner-border-sm"></span> Loading report...</td></tr>');

        try {
            if (!dataset_id) throw new Error('Could not retrieve analysis dataset ID.');

            const response = await $.ajax({
                url: 'analytics/get_teacher_report.php',
                data: { dataset_id: dataset_id }
            });

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
                        </tr>
                    `;
                });
                reportTableBody.html(tableContent);
            } else {
                reportTableBody.html('<tr><td colspan="7" class="text-center text-muted">No data found for this report.</td></tr>');
            }
        } catch (error) {
            console.error("Failed to fetch report:", error);
            reportTableBody.html(`<tr><td colspan="7" class="text-center text-danger">Failed to load report: ${error.message}</td></tr>`);
        }
    }

    // --- History Modal Logic ---
    $('#viewHistoryBtn').on('click', async function() {
        const examId = $('#examId').val();
        const slot = $('#slot').val();
        const sessionyear = $('#sessionYear').val();
        const historyList = $('#historyList');
        historyList.html('<div class="text-center text-muted p-4">Loading history...</div>');

        try {
            const response = await $.ajax({
                url: 'analytics/get_analysis_history.php',
                data: { examid: examId, slot: slot, sessionyear: sessionyear }
            });

            if (response.status === 'success' && response.data.length > 0) {
                let listContent = '';
                response.data.forEach((item, index) => {
                    const date = new Date(item.created_at).toLocaleString('en-US', {
                        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                    });
                    const isLatest = index === 0 ? '<span class="badge bg-primary ms-2">Latest</span>' : '';
                    listContent += `
                        <a href="#" class="list-group-item list-group-item-action" data-dataset-id="${item.id}">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${item.dataset_name}</h6>
                                <small>${date}</small>
                            </div>
                            <p class="mb-1 small text-muted">Version ID: ${item.datasetid} ${isLatest}</p>
                        </a>
                    `;
                });
                historyList.html(listContent);
            } else {
                historyList.html('<div class="list-group-item text-center text-muted">No history found.</div>');
            }
        } catch (error) {
            historyList.html('<div class="list-group-item text-center text-danger">Failed to load history.</div>');
        }
    });

    $(document).on('click', '#historyList a', function(e) {
        e.preventDefault();
        const datasetId = $(this).data('dataset-id');
        fetchAndDisplayReport(datasetId);
        $('#historyModal').modal('hide');
    });
</script>

</body>

</html>