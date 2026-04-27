<?php require_once 'header.php';

$date = $_COOKIE['report-date'] ?? date('Y-m-d');
$slot = $_COOKIE['chain-slot'] ?? 'School';
?>

<style>
  .action-btn {
    cursor: pointer;
    transition: .2s;
  }

  .action-btn:hover {
    transform: scale(1.2);
  }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

  <div class="card mb-4">
    <div class="card-body">

      <form id="reportForm" class="row g-3">

        <div class="col-md-3">
          <label class="form-label">Slot</label>
          <select name="slot" class="form-control form-control-sm">
            <?php
            $qr = "SELECT DISTINCT slotname FROM slots WHERE sccode='$sccode'";
            $res = $conn->query($qr);

            while ($row = $res->fetch_assoc()) {
              $selected = ($row['slotname'] == $slot) ? 'selected' : '';
              echo "<option value='" . $row['slotname'] . "' $selected>" . $row['slotname'] . "</option>";
            }
            ?>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Date</label>
          <input type="date" name="date" value="<?= $date ?>" class="form-control form-control-sm" required>
        </div>

        <div class="col-md-2 d-flex align-items-end">
          <button class="btn btn-primary btn-sm w-100">
            <i class="bi bi-search"></i> Generate
          </button>
        </div>

        <!-- Action Icons -->
        <div class="col-md-4 d-flex align-items-end justify-content-end gap-3">

          <i class="bi bi-printer fs-5 text-primary action-btn" id="printBtn" title="Print"></i>

          <i class="bi bi-file-earmark-pdf fs-5 text-danger action-btn" id="pdfBtn" title="Download PDF"></i>

          <i class="bi bi-envelope fs-5 text-success action-btn" id="mailBtn" title="Send Email"></i>

        </div>

      </form>

    </div>
  </div>


  <!-- REPORT AREA -->
  <div id="reportArea"></div>

</div>

<?php require_once 'footer.php'; ?>



<script>
  $('#reportForm').submit(function (e) {
    e.preventDefault();

    let date = $(this).find('input[name="date"]').val();
    let slot = $(this).find('select[name="slot"]').val();
    setCookie('report-date', date, 7);
    setCookie('chain-slot', slot, 7);

    let formData = $(this).serialize();

 $('#reportArea').html('<div class="alert alert-primary text-center py-3">Loading, Please wait...</div>');
    $.post('reports/load_report.php', formData, function (res) {
      $('#reportArea').html(res);
    });
  });


  // Print
  $('#printBtn').click(function () {
    let content = document.getElementById('reportArea').innerHTML;
    let w = window.open();
    w.document.write(content);
    w.print();
    w.close();
  });

  // PDF (placeholder)
  $('#pdfBtn').click(function () {
    alert('PDF coming soon...');
  });

  // Email (placeholder)
  $('#mailBtn').click(function () {
    alert('Email system coming soon...');
  });

  $('#reportForm').trigger('submit'); 
</script>