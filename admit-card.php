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
      height: 297mm;
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
  function chainBtnFunc(mode = 'preview') {

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
            mode: mode, // 🔥 add this
            slot,
            sessionyear,
            classname,
            sectionname,
            exam
        },
        success: function (res) {
            $('#result-area').html(res);
        }
    });
}
</script>

<script>
  function waitForImagesAndPrint(win) {
    let images = win.document.images;
    let total = images.length;
    let loaded = 0;

    if (total === 0) {
        win.print();
        return;
    }

    for (let img of images) {
        if (img.complete) {
            loaded++;
        } else {
            img.onload = img.onerror = function () {
                loaded++;
                if (loaded === total) win.print();
            };
        }
    }

    if (loaded === total) win.print();
}
</script>

<script>
 function printAdmit() {
    // 🔥 load FULL data first
    chainBtnFunc('print');

    setTimeout(() => {
        let content = document.getElementById('routineTable').innerHTML;

        let win = window.open('', '', 'width=900,height=700');

        win.document.write(`<html><body>${content}</body></html>`);
        win.document.close();

        win.onload = function () {
            waitForImagesAndPrint(win);
        };

    }, 1000); // wait for ajax
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


  let settingsModal;

  $(document).ready(function () {
    settingsModal = new bootstrap.Modal(document.getElementById('settingsModal'));
  });
  function openSettings() {
    settingsModal.show();
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