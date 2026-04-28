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

<style>
  @media print {
    .admit-card {
      width: 210mm;
      height: auto;
    }
  }

  .admit-card {
    page-break-after: always;
  }
</style>
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



<div class="modal fade" id="settingsModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5>Admit Card Settings</h5>
      </div>

      <div class="modal-body">

        <label>Padding (mm)</label>
        <input type="number" id="pad" class="form-control" value="10">

        <label class="mt-2">Font Size</label>
        <input type="number" id="fontSize" class="form-control" value="12">

      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" onclick="applySettings()">Apply</button>
      </div>

    </div>
  </div>
</div>



<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->


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


<script>
  function printAdmit() {
    let content = document.getElementById('routineTable').innerHTML;

    let win = window.open('', '', 'width=900,height=700');
    win.document.write(`
        <html>
        <head>
            <title>Print Admit</title>
            <link rel="stylesheet" href="assets/css/bootstrap.min.css">
        </head>
        <body>${content}</body>
        </html>
    `);

    win.document.close();
    win.print();
  }
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
  function downloadPDF() {
    let element = document.getElementById('routineTable');

    html2pdf().from(element).save('admit-card.pdf');
  }
</script>

<script>
  function openSettings() {
    $('#settingsModal').modal('show');
  }
</script>

<script>
  function applySettings() {

    let pad = $('#pad').val();
    let font = $('#fontSize').val();

    $('.admit-card').css({
      'padding': pad + 'mm',
      'font-size': font + 'px'
    });

    $('#settingsModal').modal('hide');
  }
</script>
<!-- ----------------------------------- -->
</body>

</html>