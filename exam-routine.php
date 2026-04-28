<?php
require_once 'header.php';

$slot = $_COOKIE['chain-slot'] ?? null;
$sessionyear = $_COOKIE['chain-session'] ?? null;
$classname = $_COOKIE['chain-class'] ?? null;
$sectionname = $_COOKIE['chain-section'] ?? null;
$exam = $_COOKIE['chain-exam'] ?? null;




?>

<div class="container-xxl flex-grow-1 container-p-y">
  <?php
  $chain_param = '-c 12 -t Choose Values -u -r -b View Routine -h exam';
  include 'components/slot-tree-ui.php';
  ?>


  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">

        </div>
        <div class="card-body">

        </div>
      </div>
    </div>

  </div>

</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script>
function chainBtnFunc() {

    let slot = $('#chain-slot').val();
    let sessionyear = $('#chain-session').val();
    let classname = $('#chain-class').val();
    let sectionname = $('#chain-section').val();
    let exam = $('#chain-exam').val();

    $.ajax({
        url: 'exam/exam-routine-view.php',
        type: 'POST',
        data: {
            slot: slot,
            sessionyear: sessionyear,
            classname: classname,
            sectionname: sectionname,
            exam: exam
        },
        beforeSend: function () {
            $('#resultArea').html('<div class="text-center">Loading...</div>');
        },
        success: function (res) {
            $('#resultArea').html(res);
        },
        error: function () {
            $('#resultArea').html('<div class="text-danger">Failed to load data</div>');
        }
    });

}
</script>
<!-- ----------------------------------- -->
</body>

</html>
