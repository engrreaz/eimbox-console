<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row d-flex justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Search Student Result</h5>
                </div>
                <div class="card-body">
                    <form id="resultSearchForm">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="sccode" class="form-label">Institute Code (EIIN/sccode)</label>
                                <input type="text" id="sccode" name="sccode" class="form-control"
                                    placeholder="Enter 6-digit code" maxlength="6">
                            </div>

                            <div class="col-md-6">
                                <label for="slot" class="form-label">Slot</label>
                                <select id="slot" name="slot" class="form-select" disabled>
                                    <option value="">Select Slot</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="sessionyear" class="form-label">Session</label>
                                <select id="sessionyear" name="sessionyear" class="form-select" disabled>
                                    <option value="">Select Session</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="exam" class="form-label">Examination</label>
                                <select id="exam" name="exam" class="form-select" disabled>
                                    <option value="">Select Examination</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="classname" class="form-label">Class</label>
                                <select id="classname" name="classname" class="form-select" disabled>
                                    <option value="">Select Class</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="sectionname" class="form-label">Section</label>
                                <select id="sectionname" name="sectionname" class="form-select" disabled>
                                    <option value="">Select Section</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="rollno" class="form-label">Roll No</label>
                                <input type="text" id="rollno" name="rollno" class="form-control"
                                    placeholder="Enter Roll Number" disabled>
                            </div>

                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100">Search Result</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>


<?php require_once 'footer.php'; ?>

<script>
    $(document).ready(function () {
        const sccodeInput = $('#sccode');
        const slotSelect = $('#slot');
        const sessionSelect = $('#sessionyear');
        const examSelect = $('#exam');
        const classSelect = $('#classname');
        const sectionSelect = $('#sectionname');
        const rollInput = $('#rollno');

        // Reset and disable function
        function resetSelect(selector, defaultText) {
            selector.html(`<option value="">${defaultText}</option>`).prop('disabled', true);
        }

        function populateSelect(selector, data, defaultText) {
            selector.html(`<option value="">${defaultText}</option>`);
            if (data && data.length > 0) {
                $.each(data, function (index, value) {
                    selector.append($('<option>', {
                        value: value,
                        text: value
                    }));
                });
                selector.prop('disabled', false);
            } else {
                selector.html(`<option value="">No data found</option>`).prop('disabled', true);
            }
        }

        // 1. On sccode input
        sccodeInput.on('keyup', function () {
            let sccode = $(this).val();
            resetSelect(slotSelect, 'Select Slot');
            resetSelect(sessionSelect, 'Select Session');
            resetSelect(examSelect, 'Select Examination');
            resetSelect(classSelect, 'Select Class');
            resetSelect(sectionSelect, 'Select Section');
            rollInput.val('').prop('disabled', true);

            if (sccode.length === 6) {
                slotSelect.prop('disabled', false).html('<option>Loading...</option>');
                sessionSelect.prop('disabled', false).html('<option>Loading...</option>');

                $.post('get_data.php', { type: 'get_initial_data', sccode: sccode }, function (data) {
                    populateSelect(slotSelect, data.slots, 'Select Slot');
                    populateSelect(sessionSelect, data.sessions, 'Select Session');
                }, 'json').fail(function() {
                    alert('Error fetching initial data.');
                    resetSelect(slotSelect, 'Select Slot');
                    resetSelect(sessionSelect, 'Select Session');
                });
            }
        });

        // 2. On Session or Slot change -> load Exam and Class
        sessionSelect.add(slotSelect).on('change', function () {
            let sccode = sccodeInput.val();
            let slot = slotSelect.val();
            let session = sessionSelect.val();

            resetSelect(examSelect, 'Select Examination');
            resetSelect(classSelect, 'Select Class');
            resetSelect(sectionSelect, 'Select Section');
            rollInput.val('').prop('disabled', true);

            if (sccode.length === 6 && slot && session) {
                examSelect.prop('disabled', false).html('<option>Loading...</option>');
                classSelect.prop('disabled', false).html('<option>Loading...</option>');

                // Get Exams
                $.post('get_data.php', { type: 'get_exams', sccode: sccode, slot: slot, sessionyear: session }, function (data) {
                    populateSelect(examSelect, data.exams, 'Select Examination');
                }, 'json');

                // Get Classes
                $.post('get_data.php', { type: 'get_classes', sccode: sccode, slot: slot, sessionyear: session }, function (data) {
                    populateSelect(classSelect, data.classes, 'Select Class');
                }, 'json');
            }
        });

        // 3. On Class change -> load Section
        classSelect.on('change', function () {
            let sccode = sccodeInput.val();
            let slot = slotSelect.val();
            let session = sessionSelect.val();
            let classname = $(this).val();

            resetSelect(sectionSelect, 'Select Section');
            rollInput.val('').prop('disabled', true);

            if (sccode.length === 6 && slot && session && classname) {
                sectionSelect.prop('disabled', false).html('<option>Loading...</option>');
                $.post('get_data.php', { type: 'get_sections', sccode: sccode, slot: slot, sessionyear: session, classname: classname }, function (data) {
                    populateSelect(sectionSelect, data.sections, 'Select Section');
                }, 'json');
            }
        });

        // 4. On Section change -> enable roll
        sectionSelect.on('change', function () {
            if ($(this).val()) {
                rollInput.prop('disabled', false);
            } else {
                rollInput.prop('disabled', true);
            }
        });

        // Form submission
        $('#resultSearchForm').on('submit', function (e) {
            e.preventDefault();
            const formData = $(this).serialize();
            // Example: Open marksheet in a new tab
            window.open('marksheet.php?' + formData, '_blank');
        });
    });
</script>


</body>

</html>