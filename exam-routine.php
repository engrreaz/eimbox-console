<?php
require_once 'header.php';

$slot = $_COOKIE['chain-slot'] ?? null;
$sessionyear = $_COOKIE['chain-session'] ?? null;
$classname = $_COOKIE['chain-class'] ?? null;
$sectionname = $_COOKIE['chain-section'] ?? null;
$exam = $_COOKIE['chain-exam'] ?? null;


$subjectList = [];

$sqlSub = "SELECT s.subcode, s.subject
FROM subjects s
INNER JOIN subsetup ss ON s.subcode = ss.subject
WHERE s.sccategory = '$sctype'
AND (s.sccode = '0' OR s.sccode = '$sccode')
AND ss.slot = '$slot'
AND ss.sessionyear = '$sessionyear'
AND ss.sccode = '$sccode'
AND ss.classname = '$classname'
AND ss.sectionname = '$sectionname'
ORDER BY s.subcode";

$resSub = mysqli_query($conn, $sqlSub);

while ($rowSub = mysqli_fetch_assoc($resSub)) {
  $subjectList[] = $rowSub;
}

?>

<div class="container-xxl flex-grow-1 container-p-y">
  <?php
  $chain_param = '-c 12 -t Choose Values -u -r -b View Routine -h exam';
  include 'components/slot-tree-ui.php';
  ?>


  <div class="row">
    <div class="col-12">
      <div class="card" id="result-area">
        <div class="card-header">

        </div>
        <div class="card-body">

        </div>
      </div>
    </div>

  </div>

</div>

<div class="modal fade" id="addModalRoutine">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5>Add Subject</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <div class="row">
          <div class="col-md-6 mb-2">
            <label>Date</label>
            <input type="date" id="m_date" class="form-control form-control-sm">
          </div>

          <div class="col-md-6 mb-2">
            <label>Time</label>
            <input type="time" id="m_time" class="form-control form-control-sm">
          </div>
        </div>

        <div class="row">
          <div class="col-md-12 mb-2">
            <label>Subject</label>
            <select id="m_subcode" class="form-control form-control-sm">
              <option value="">Select</option>

              <?php foreach ($subjectList as $sub): ?>
                <option value="<?= $sub['subcode'] ?>">
                  <?= $sub['subcode'] ?> - <?= $sub['subject'] ?>
                </option>
              <?php endforeach; ?>

            </select>
          </div>
        </div>



      </div>

      <div class="modal-footer">
        <button class="btn btn-success btn-sm" onclick="saveSubject()">Save</button>
      </div>

    </div>
  </div>
</div>


<div class="modal fade" id="cloneModal">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-copy me-2 text-primary"></i>Clone / Import Exam Routine</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-md-5 border-end">
            <h6 class="text-muted mb-3"><i class="bi bi-box-arrow-in-right me-1"></i>Select Source Routine</h6>
            <div class="row g-2">
              <div class="col-12">
                <label class="form-label small fw-semibold">Source Session Year</label>
                <select id="clone_session" class="form-select form-select-sm" onchange="loadCloneExamsAndClasses()"></select>
              </div>

              <div class="col-12">
                <label class="form-label small fw-semibold">Source Exam</label>
                <select id="clone_exam" class="form-select form-select-sm"></select>
              </div>

              <div class="col-12">
                <label class="form-label small fw-semibold">Source Class</label>
                <select id="clone_class" class="form-select form-select-sm" onchange="loadCloneSections()"></select>
              </div>

              <div class="col-12">
                <label class="form-label small fw-semibold">Source Section</label>
                <select id="clone_section" class="form-select form-select-sm">
                  <option value="">All Sections</option>
                </select>
              </div>

              <div class="col-12 mt-3">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="clone_overwrite" value="1">
                  <label class="form-check-label small" for="clone_overwrite">Overwrite existing subjects</label>
                </div>
              </div>

              <div class="col-12 mt-3">
                <button type="button" class="btn btn-primary btn-sm w-100" onclick="previewClone()">
                  <i class="bi bi-search me-1"></i> Preview Source Routine
                </button>
              </div>
            </div>
          </div>

          <div class="col-md-7">
            <h6 class="text-muted mb-3"><i class="bi bi-table me-1"></i>Routine Preview</h6>
            <div id="clonePreview" class="border rounded p-3 bg-light" style="min-height: 250px; max-height: 380px; overflow-y: auto;">
              <div class="text-center text-muted small py-5">
                <i class="bi bi-info-circle display-6 d-block mb-2 text-secondary"></i>
                Select source parameters and click <strong>Preview Source Routine</strong> to inspect before cloning.
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->

<script>
  let addModalInstance;
  let cloneModalInstance;

  $(document).ready(function () {
    const addEl = document.getElementById('addModalRoutine');
    if (addEl) addModalInstance = new bootstrap.Modal(addEl);

    const cloneEl = document.getElementById('cloneModal');
    if (cloneEl) cloneModalInstance = new bootstrap.Modal(cloneEl);
  });

  function openAddModalRoutine() {
    if (!addModalInstance) {
      addModalInstance = new bootstrap.Modal(document.getElementById('addModalRoutine'));
    }
    addModalInstance.show();
  }

  function openCloneModal() {
    if (!cloneModalInstance) {
      cloneModalInstance = new bootstrap.Modal(document.getElementById('cloneModal'));
    }

    // Reset preview
    $('#clonePreview').html(`
      <div class="text-center text-muted small py-5">
        <i class="bi bi-info-circle display-6 d-block mb-2 text-secondary"></i>
        Select source parameters and click <strong>Preview Source Routine</strong> to inspect before cloning.
      </div>
    `);

    // Immediate baseline population from current page state
    let currentYear = $('#session-main').val() || new Date().getFullYear().toString();
    $('#clone_session').html(`<option value="${currentYear}">${currentYear}</option>`);
    
    // Load available source session years from server
    $.post('exam/exam-routine-action.php', { action: 'get_clone_sessions' }, function (res) {
      if (res.status === 'success' && res.sessions && res.sessions.length > 0) {
        let opt = '';
        res.sessions.forEach(y => {
          opt += `<option value="${y}" ${y == currentYear ? 'selected' : ''}>${y}</option>`;
        });
        $('#clone_session').html(opt);
      }
      loadCloneExamsAndClasses();
    }, 'json').fail(function() {
      loadCloneExamsAndClasses();
    });

    cloneModalInstance.show();
  }

  function loadCloneExamsAndClasses() {
    let sy = $('#clone_session').val() || $('#session-main').val() || new Date().getFullYear().toString();
    let currentExam = $('#exam-main').val() || '';
    let currentClass = $('#class-main').val() || '';

    // Show loading indicators
    $('#clone_exam').html('<option value="">Loading Exams...</option>');
    $('#clone_class').html('<option value="">Loading Classes...</option>');
    $('#clone_section').html('<option value="">All Sections</option>');

    // 1. Load exams for this session
    $.post('exam/exam-routine-action.php', { action: 'get_clone_exams', session: sy }, function (res) {
      let opt = '<option value="">-- Select Exam --</option>';
      if (res.status === 'success' && res.exams && res.exams.length > 0) {
        if (!currentExam || !res.exams.includes(currentExam)) {
          currentExam = res.exams[0];
        }
        res.exams.forEach(ex => {
          let isSel = (ex == currentExam);
          opt += `<option value="${ex}" ${isSel ? 'selected' : ''}>${ex}</option>`;
        });
      }
      $('#clone_exam').html(opt);
    }, 'json');

    // 2. Load classes for this session
    $.post('exam/exam-routine-action.php', { action: 'get_clone_classes', session: sy }, function (res) {
      let opt = '<option value="">-- Select Class --</option>';
      if (res.status === 'success' && res.classes && res.classes.length > 0) {
        if (!currentClass || !res.classes.includes(currentClass)) {
          currentClass = res.classes[0];
        }
        res.classes.forEach(c => {
          let isSel = (c == currentClass);
          opt += `<option value="${c}" ${isSel ? 'selected' : ''}>${c}</option>`;
        });
      }
      $('#clone_class').html(opt);
      loadCloneSections();
    }, 'json');
  }

  function loadCloneSections() {
    let sy = $('#clone_session').val() || $('#session-main').val() || '';
    let cls = $('#clone_class').val();
    let currentSection = $('#section-main').val() || '';

    if (!cls) {
      $('#clone_section').html('<option value="">All Sections</option>');
      return;
    }

    $('#clone_section').html('<option value="">Loading Sections...</option>');

    $.post('exam/exam-routine-action.php', { action: 'get_clone_sections', session: sy, class: cls }, function (res) {
      let opt = '<option value="">All Sections</option>';
      if (res.status === 'success' && res.sections && res.sections.length > 0) {
        res.sections.forEach(s => {
          opt += `<option value="${s}" ${s == currentSection ? 'selected' : ''}>${s}</option>`;
        });
      }
      $('#clone_section').html(opt);
    }, 'json').fail(function() {
      $('#clone_section').html('<option value="">All Sections</option>');
    });
  }
</script>

<script>
  function chainBtnFunc() {
    let slot = $('#slot-main').val();
    let sessionyear = $('#session-main').val();
    let classname = $('#class-main').val();
    let sectionname = $('#section-main').val();
    let exam = $('#exam-main').val();

    $.ajax({
      url: 'exam/exam-routine-view.php',
      type: 'POST',
      data: {
        action: 'fetch',
        slot: slot,
        sessionyear: sessionyear,
        classname: classname,
        sectionname: sectionname,
        exam: exam
      },
      beforeSend: function () {
        $('#result-area').html('<div class="p-4 text-center text-primary"><div class="spinner-border spinner-border-sm me-2"></div> Loading Routine...</div>');
      },
      success: function (res) {
        $('#result-area').html(res);
      },
      error: function () {
        $('#result-area').html('<div class="alert alert-danger text-center">Failed to load exam routine data.</div>');
      }
    });
  }
</script>

<script>
  $(document).on('change', '.updateField', function () {
    let tr = $(this).closest('tr');
    let id = tr.data('id');
    let field = $(this).data('field');
    let value = $(this).val();

    $.post('exam/exam-routine-action.php', {
      action: 'update',
      id: id,
      field: field,
      value: value
    }, function (res) {
      if (res.status === 'success') {
        if (typeof showToast === 'function') {
          showToast('success', res.message || 'Updated successfully', 'Update Routine');
        }
      } else {
        Swal.fire('Error', res.message || 'Update failed', 'error');
      }
    }, 'json');
  });

  $(document).on('click', '.btnDelete', function () {
    let tr = $(this).closest('tr');
    let id = tr.data('id');

    Swal.fire({
      title: 'Remove Subject?',
      text: "This subject will be deleted from the routine!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, remove it!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('exam/exam-routine-action.php', {
          action: 'delete',
          id: id
        }, function (res) {
          if (res.status === 'success') {
            tr.fadeOut(300, function() { $(this).remove(); });
            if (typeof showToast === 'function') {
              showToast('success', res.message || 'Deleted successfully', 'Delete Routine');
            }
          } else {
            Swal.fire('Error', res.message || 'Delete failed', 'error');
          }
        }, 'json');
      }
    });
  });
</script>

<script>
  function saveSubject() {
    let date = $('#m_date').val();
    let time = $('#m_time').val();
    let subcode = $('#m_subcode').val();

    let slot = $('#slot-main').val();
    let sessionyear = $('#session-main').val();
    let classname = $('#class-main').val();
    let sectionname = $('#section-main').val();
    let exam = $('#exam-main').val();

    if (!subcode) {
      Swal.fire('Subject Required', 'Please select a subject to add.', 'warning');
      return;
    }
    if (!exam || !classname) {
      Swal.fire('Selection Incomplete', 'Please select Exam and Class from the top filter first.', 'warning');
      return;
    }

    $.post('exam/exam-routine-action.php', {
      action: 'insert',
      sessionyear: sessionyear,
      examname: exam,
      clsname: classname,
      secname: sectionname,
      date: date,
      time: time,
      subcode: subcode
    }, function (res) {
      if (res.status === 'success') {
        if (addModalInstance) addModalInstance.hide();
        Swal.fire({
          icon: 'success',
          title: 'Subject Added',
          text: res.message || 'Subject added to routine successfully!',
          timer: 1500,
          showConfirmButton: false
        });
        chainBtnFunc(); // Refresh routine view
      } else {
        Swal.fire('Error', res.message || 'Failed to add subject', 'error');
      }
    }, 'json');
  }
</script>

<script>
  function previewClone() {
    let srcSession = $('#clone_session').val();
    let srcExam = $('#clone_exam').val();
    let srcClass = $('#clone_class').val();
    let srcSection = $('#clone_section').val();

    if (!srcSession || !srcExam || !srcClass) {
      Swal.fire('Source Required', 'Please select Source Session, Exam, and Class to preview.', 'warning');
      return;
    }

    $('#clonePreview').html('<div class="text-center text-primary py-5"><div class="spinner-border spinner-border-sm me-2"></div> Fetching source routine...</div>');

    $.post('exam/exam-routine-action.php', {
      action: 'preview_clone',
      sessionyear: srcSession,
      examname: srcExam,
      clsname: srcClass,
      secname: srcSection
    }, function (res) {
      let html = '';

      if (res.status !== 'success' || !res.data || res.data.length === 0) {
        html = '<div class="alert alert-warning text-center my-3"><i class="bi bi-exclamation-triangle me-1"></i> No routine found for selected source criteria.</div>';
      } else {
        html += `
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge bg-success">${res.data.length} Subject(s) Found</span>
            <small class="text-muted">${srcExam} - ${srcClass} (${srcSession})</small>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-sm table-striped bg-white">
              <thead class="table-light">
                <tr>
                  <th class="small py-1">Date</th>
                  <th class="small py-1">Time</th>
                  <th class="small py-1">Code</th>
                  <th class="small py-1">Subject Name</th>
                </tr>
              </thead>
              <tbody>`;

        res.data.forEach(row => {
          let benName = row.subben ? `<br><small class="text-muted">${row.subben}</small>` : '';
          html += `
            <tr>
              <td class="small py-1 text-nowrap">${row.date || '—'}</td>
              <td class="small py-1 text-nowrap">${row.time || '—'}</td>
              <td class="small py-1 font-monospace">${row.subcode}</td>
              <td class="small py-1 fw-semibold">${row.subject}${benName}</td>
            </tr>`;
        });

        html += `
              </tbody>
            </table>
          </div>
          <div class="mt-3 text-end">
            <button class="btn btn-success btn-sm px-3 shadow-sm" onclick="doClone()">
              <i class="bi bi-cloud-arrow-down me-1"></i> Clone & Import (${res.data.length} Subjects)
            </button>
          </div>`;
      }

      $('#clonePreview').html(html);
    }, 'json').fail(function() {
      $('#clonePreview').html('<div class="alert alert-danger text-center my-3">Error loading preview data.</div>');
    });
  }

  function doClone() {
    let targetSession = $('#session-main').val();
    let targetExam = $('#exam-main').val();
    let targetClass = $('#class-main').val();
    let targetSection = $('#section-main').val();

    if (!targetSession || !targetExam || !targetClass) {
      Swal.fire('Target Required', 'Please select Target Session, Exam, and Class from the main top filters.', 'warning');
      return;
    }

    let srcSession = $('#clone_session').val();
    let srcExam = $('#clone_exam').val();
    let srcClass = $('#clone_class').val();
    let srcSection = $('#clone_section').val();
    let overwrite = $('#clone_overwrite').is(':checked') ? 1 : 0;

    let overwriteWarning = overwrite ? '<br><strong class="text-danger">Warning: Existing routine subjects for this target will be overwritten!</strong>' : '';

    Swal.fire({
      title: 'Confirm Routine Import?',
      html: `Import routine from <strong>${srcExam} (${srcClass}) [${srcSession}]</strong> into <strong>${targetExam} (${targetClass}) [${targetSession}]</strong>?${overwriteWarning}`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#198754',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, Clone Now!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Importing Routine...',
          text: 'Please wait while subjects are being cloned.',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        $.post('exam/exam-routine-action.php', {
          action: 'clone',
          from_session: srcSession,
          from_exam: srcExam,
          from_class: srcClass,
          from_section: srcSection,
          sessionyear: targetSession,
          examname: targetExam,
          clsname: targetClass,
          secname: targetSection,
          overwrite: overwrite
        }, function (res) {
          if (res.status === 'success') {
            if (cloneModalInstance) cloneModalInstance.hide();
            Swal.fire({
              icon: 'success',
              title: 'Routine Cloned!',
              text: res.message || 'Routine cloned successfully',
              timer: 2000,
              showConfirmButton: true
            });
            chainBtnFunc(); // Refresh routine view
          } else {
            Swal.fire('Import Failed', res.message || 'Failed to clone routine', 'error');
          }
        }, 'json').fail(function() {
          Swal.fire('Error', 'Server communication failure during cloning.', 'error');
        });
      }
    });
  }
</script>
<!-- ----------------------------------- -->
</body>

</html>