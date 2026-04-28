<?php require_once 'header.php';

$date = $_COOKIE['report-date'] ?? date('Y-m-d');
$slot = $_COOKIE['chain-slot'] ?? 'School';
$sessionyear = $_COOKIE['chain-session'] ?? date('Y');
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
  href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&family=Noto+Sans+Bengali:wght@100..900&family=Noto+Serif+Bengali:wght@100..900&display=swap"
  rel="stylesheet">
<style>
  #reportArea {
    font-family: "IBM Plex Mono", monospace;

  }

  .action-btn {
    cursor: pointer;
    transition: .2s;
  }

  .action-btn:hover {
    transform: scale(1.2);
  }

  @media print {

    table,
    tr,
    td {
      border-collapse: collapse;
      border: 1px solid #000 !important;
      width: 100%;
    }
  }
</style>

<div class="container-xxl flex-grow-1 container-p-y" id="main-page-block">

  <div class="card mb-4">
    <div class="card-body">

      <form id="reportForm" class="row g-3">

        <div class="col-md-2">
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

        <div class="col-md-2">
          <label class="form-label">Session Year</label>
          <select name="sessionyear" class="form-control form-control-sm">
            <?php
            $qr = "SELECT DISTINCT syear FROM sessionyear WHERE sccode='$sccode' AND active=1 order by syear";
            $res = $conn->query($qr);

            while ($row = $res->fetch_assoc()) {
              $syear = htmlspecialchars($row['syear']);
              $selected = ($syear == $sessionyear) ? 'selected' : '';
              echo "<option value='$syear' $selected>$syear</option>";
            }
            ?>
          </select>
        </div>


        <div class="col-md-2">
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

          <i class="bi bi-printer fs-3 text-primary action-btn" id="printBtn" title="Print"></i>

          <i class="bi bi-file-earmark-pdf fs-3 text-danger action-btn" id="pdfBtn" title="Download PDF"></i>

          <i class="bi bi-envelope fs-3 text-success action-btn" id="mailBtn" title="Send Email"></i>

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
    let sessionyear = $(this).find('select[name="sessionyear"]').val();
    setCookie('report-date', date, 7);
    setCookie('chain-slot', slot, 7);
    setCookie('chain-session', sessionyear, 7);

    let formData = $(this).serialize();

    $('#reportArea').html('<div class="alert alert-primary text-center py-3">Loading, Please wait...</div>');
    $.post('reports/load_report.php', formData, function (res) {
      $('#reportArea').html(res);
    });
  });


  // Print
  $('#printBtn').click(function () {

    let style = `
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono&family=Noto+Sans+Bengali&display=swap" rel="stylesheet">

<style>
      @media print {

          @page {
              size: A4;       
              margin: 10mm 10mm 5mm 15mm;  /* bottom margin বাড়ানো */
          }

          body {
              font-family: "IBM Plex Mono", monospace;
              margin-bottom: 50px; /* 🔥 footer এর জন্য জায়গা */
          }

          .print-footer {
              position: fixed;
              bottom: 0;
              left: 0;
              width: 100%;
              height: 30px;
              text-align: center;
              font-size: 12px;
              background: #fff; /* overlap হলে clean দেখাবে */
          }

          table {
              width: 100% !important;
              border-collapse: collapse;
          }

          table, th, td {
              border: 1px solid #000 !important;
          }

          th, td {
              padding: 5px;
              font-size: 12px;
          }
      }
      </style>
      `;

    let content = document.getElementById('reportArea').innerHTML;

    let w = window.open('', '', 'width=900,height=700');

    w.document.write(`
        <html>
            <head>
                <title>Print Report</title>
                ${style}
            </head>
            <body>
                ${content}

                <div class="print-footer">
                    <hr style="margin:5px;"/>
                    Daily Report - Date : ${$('input[name="date"]').val()} | Slot : ${$('select[name="slot"]').val()} | Session Year : ${$('select[name="sessionyear"]').val()}
                </div>
            </body>
        </html>
    `);

    w.document.close();
    w.focus();
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