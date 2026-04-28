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
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5>Clone Exam Routine</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <div class="row">
          <div class="col-md-4">
            <div class="row">

              <div class="col-12 mb-2">
                <label>Session</label>
                <input type="text" id="clone_session" class="form-control form-control-sm">
              </div>

              <div class="col-12 mb-2">
                <label>Exam</label>
                <input type="text" id="clone_exam" class="form-control form-control-sm">
              </div>

              <div class="col-12 mb-2">
                <label>Class</label>
                <input type="text" id="clone_class" class="form-control form-control-sm">
              </div>

              <div class="col-12 mb-2">
                <label>Section</label>
                <input type="text" id="clone_section" class="form-control form-control-sm">
              </div>

              <div class="col-12 mb-2">
                <button class="btn btn-info btn-sm mt-2" onclick="previewClone()">
                  Preview
                </button>
              </div>


            </div>
          </div>
          <div class="col-md-8">

            <div id="clonePreview" class="">
              <div class="alert alert-info text-center small">
                if found, routine will display here as preview before cloning.
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
    addModalInstance = new bootstrap.Modal(document.getElementById('addModalRoutine'));
    cloneModalInstance = new bootstrap.Modal(document.getElementById('cloneModal'));
  });

  function openAddModalRoutine() {
    addModalInstance.show();
  }


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
      url: 'exam/exam-routine-admit-card.php',
      type: 'POST',
      data: {
        action: 'admit',
        slot: slot,
        sessionyear: sessionyear,
        classname: classname,
        sectionname: sectionname,
        exam: exam
      },
      beforeSend: function () {
        $('#result-area').html('<div class="alert alert-primary text-center">Loading...</div>');
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








<!-- ----------------------------------- -->
</body>

</html>