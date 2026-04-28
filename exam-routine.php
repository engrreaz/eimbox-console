<?php
require_once 'header.php';

$slot = $_COOKIE['chain-slot'] ?? null;
$sessionyear = $_COOKIE['chain-session'] ?? null;
$classname = $_COOKIE['chain-class'] ?? null;
$sectionname = $_COOKIE['chain-section'] ?? null;
$exam = $_COOKIE['chain-exam'] ?? null;


$subjectList = [];

$sqlSub = "SELECT subcode, subject 
           FROM subjects
           WHERE sccategory='$sctype'
           AND (sccode='0' OR sccode='$sccode')
           ORDER BY subcode";

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

        <div class="mb-2">
          <label>Date</label>
          <input type="date" id="m_date" class="form-control">
        </div>

        <div class="mb-2">
          <label>Time</label>
          <input type="time" id="m_time" class="form-control">
        </div>

        <div class="mb-2">
          <label>Subject</label>
          <select id="m_subcode" class="form-control">
            <option value="">Select</option>

            <?php foreach ($subjectList as $sub): ?>
              <option value="<?= $sub['subcode'] ?>">
                <?= $sub['subcode'] ?> - <?= $sub['subject'] ?>
              </option>
            <?php endforeach; ?>

          </select>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-success" onclick="saveSubject()">Save</button>
      </div>

    </div>
  </div>
</div>


<div class="modal fade" id="cloneModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5>Clone Exam Routine</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <div class="row">

          <div class="col-md-6 mb-2">
            <label>Session</label>
            <input type="text" id="clone_session" class="form-control">
          </div>

          <div class="col-md-6 mb-2">
            <label>Exam</label>
            <input type="text" id="clone_exam" class="form-control">
          </div>

          <div class="col-md-6 mb-2">
            <label>Class</label>
            <input type="text" id="clone_class" class="form-control">
          </div>

          <div class="col-md-6 mb-2">
            <label>Section</label>
            <input type="text" id="clone_section" class="form-control">
          </div>

        </div>

        <button class="btn btn-info btn-sm mt-2" onclick="previewClone()">
          Preview
        </button>

        <hr>

        <div id="clonePreview"></div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-success" onclick="doClone()">
          Clone Now
        </button>
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
    addModalInstance = new bootstrap.Modal(document.getElementById('addModalRoutine'));
    cloneModalInstance = new bootstrap.Modal(document.getElementById('cloneModal'));

    function openAddModal() {
      const el = document.getElementById('addModalRoutine');
      if (!el) {
        console.log('Add modal not found');
        return;
      }
      const modal = new bootstrap.Modal(el);
      modal.show();
    }

    function closeAddModal() {
      const el = document.getElementById('addModalRoutine');
      const modal = bootstrap.Modal.getInstance(el);
      if (modal) {
        modal.hide();
      }
    }

  });



  function openCloneModal() {
    cloneModalInstance.show();
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
        $('#result-area').html('<div class="text-center">Loading...</div>');
      },
      success: function (res) {
        $('#result-area').html(res);
      },
      error: function () {
        $('#result-area').html('<div class="text-danger">Failed to load data</div>');
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

      if (res.status == 'success') {
        console.log('Updated');
        showToast('success', 'Updated successfully', 'Update Routine');

      } else {
        sweetAlert('Error', 'Update failed', 'error');
      }

    }, 'json');

  });

  $(document).on('click', '.btnDelete', function () {

    if (!confirm('Delete this subject?')) return;

    let tr = $(this).closest('tr');
    let id = tr.data('id');

    $.post('exam/exam-routine-action.php', {
      action: 'delete',
      id: id
    }, function (res) {

      if (res.status == 'success') {
        tr.remove();
        showToast('success', 'Deleted successfully', 'Delete Routine');
      } else {
        sweetAlert('Error', 'Delete failed', 'error');
      }

    }, 'json');

  });
</script>





<script>
  function saveSubject() {

    let date = $('#m_date').val();
    let time = $('#m_time').val();
    let subcode = $('#m_subcode').val();

    if (!subcode) {
      alert('Select subject');
      return;
    }

    $.post('exam/exam-routine-action.php', {
      action: 'insert',
      sccode: params.sccode,
      sessionyear: params.sessionyear,
      examname: params.examname,
      clsname: params.clsname,
      secname: params.secname,
      date: date,
      time: time,
      subcode: subcode
    }, function (res) {

      if (res.status == 'success') {

        addModalInstance.hide();   // ✅ FIXED

        loadRoutine();

      } else {
        sweetAlert('Error', 'Failed to add subject', 'error');
      }

    }, 'json');
  }
</script>


<script>

  function previewClone() {

    $.post('exam/exam-routine-action.php', {
      action: 'preview_clone',
      sessionyear: $('#clone_session').val(),
      examname: $('#clone_exam').val(),
      clsname: $('#clone_class').val(),
      secname: $('#clone_section').val(),
      sccode: params.sccode
    }, function (res) {

      let html = '';

      if (res.data.length === 0) {
        html = '<div class="text-danger">No data found</div>';
      } else {

        html += `<table class="table table-bordered">`;

        res.data.forEach(row => {
          html += `
                    <tr>
                        <td>${row.date ?? ''}</td>
                        <td>${row.time ?? ''}</td>
                        <td>${row.subcode}</td>
                        <td>${row.subject}</td>
                    </tr>
                `;
        });

        html += `</table>`;
      }

      $('#clonePreview').html(html);

    }, 'json');
  }


  function doClone() {

    if (!confirm('Clone routine from selected source?')) return;

    $.post('exam/exam-routine-action.php', {
      action: 'clone',

      from_session: $('#clone_session').val(),
      from_exam: $('#clone_exam').val(),
      from_class: $('#clone_class').val(),
      from_section: $('#clone_section').val(),

      sccode: params.sccode,
      sessionyear: params.sessionyear,
      examname: params.examname,
      clsname: params.clsname,
      secname: params.secname

    }, function (res) {

      if (res.status == 'success') {

        cloneModalInstance.hide();
        loadRoutine();

      } else {
        sweetAlert('Error', 'Failed to clone routine', 'error');
      }

    }, 'json');
  }
</script>
<!-- ----------------------------------- -->
</body>

</html>