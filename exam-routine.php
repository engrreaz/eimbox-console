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
  function btnchain(){
    let slot = document.getElementById('chain-slot').value;
    let sessionyear = document.getElementById('chain-session').value;
    let classname = document.getElementById('chain-class').value;
    let sectionname = document.getElementById('chain-section').value;
    let exam = document.getElementById('chain-exam').value;

    if(slot && sessionyear && classname && sectionname && exam){
      window.location.href = `exam-routine-view.php?slot=${slot}&sessionyear=${sessionyear}&classname=${classname}&sectionname=${sectionname}&exam=${exam}`;
    }else{
      alert('Please select all values.');
    }
  }
</script>
<!-- ----------------------------------- -->
</body>

</html>
