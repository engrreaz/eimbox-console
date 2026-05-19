<?php require_once 'header.php'; 
$recalc = $_COOKIE['cashbook_report_recalculation'] ?? '1';
?>

<div class="container-xxl flex-grow-1 container-p-y">

  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-2">
          <label class="form-label">Slot</label>
          <select id="slot-main" name="slot-main" class="form-select form-select-sm">
            <option value="">Select Slot</option>
            <?php
            $q = $conn->query("SELECT slotname FROM slots WHERE sccode='$sccode' ORDER BY slotname");
            while ($r = $q->fetch_assoc()) {
              echo "<option value='{$r['slotname']}'>{$r['slotname']}</option>";
            }
            ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Session</label>
          <select id="session-main" name="session-main" class="form-select form-select-sm">
            <option value="">Select Session</option>
            <?php
            $q = $conn->query("SELECT syear FROM sessionyear 
                                       WHERE sccode='$sccode' AND active=1 
                                       ORDER BY syear DESC");
            while ($r = $q->fetch_assoc()) {
              echo "<option value='{$r['syear']}'>{$r['syear']}</option>";
            }
            ?>
          </select>
        </div>


        <div class="col-md-2">
          <label class="form-label">Date From</label>
          <input type="date" class="form-control form-control-sm" name="date-from-main" id="date-from-main">
        </div>
        <div class="col-md-2">
          <label class="form-label">Date To</label>
          <input type="date" class="form-control form-control-sm" name="date-to-main" id="date-to-main">
        </div>
        <div class="col-md-2">
          <label class="form-label">Month</label>
          <input type="month" class="form-control form-control-sm" name="month" id="month">
        </div>

        <div class="col-md-2 ">
          <div class="btn-group">

            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown"
              aria-expanded="false">
              Report
            </button>

            <ul class="dropdown-menu">

              <li>
                <a class="dropdown-item" href="javascript:void(0)" onclick="get_report(1)">
                  View Report
                </a>
              </li>

              <li>
                <a class="dropdown-item" href="javascript:void(0)" onclick="get_report(0)">
                  Minified Report
                </a>
              </li>

              <li>
                <hr class="dropdown-divider">
              </li>

              <li>
                <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)"
                  onclick="toggleRecalculation(this)">

                  <span>Re-calculation</span>

                  <span id="recalc-check"><?php echo $recalc === '1' ? '✔' : ''; ?></span>
                </a>
              </li>

            </ul>
          </div>
        </div>
      </div>
    </div>




  </div>

  <div id="report-block" class="mt-3"></div>
</div>



</div>



<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script>
  let recalculation = 1;

  function toggleRecalculation(el) {

    recalculation = recalculation ? 0 : 1;
    setCookie("cashbook_report_recalculation", recalculation, 30);

    document.getElementById("recalc-check").innerHTML =
      recalculation ? "✔" : "";
  }
</script>

<script>
  document.getElementById('month').addEventListener('change', function () {
    let monthValue = this.value; // format: YYYY-MM

    if (!monthValue) return;

    let fromDate = monthValue + "-01";

    // last day calculate
    let tempDate = new Date(monthValue + "-01");
    let lastDay = new Date(tempDate.getFullYear(), tempDate.getMonth() + 1, 0);

    // local format safe
    let year = lastDay.getFullYear();
    let month = String(lastDay.getMonth() + 1).padStart(2, '0');
    let day = String(lastDay.getDate()).padStart(2, '0');

    let toDate = `${year}-${month}-${day}`;

    document.getElementById('date-from-main').value = fromDate;
    document.getElementById('date-to-main').value = toDate;
  });


  function get_report(type = 1) {

    let slot = document.getElementById('slot-main').value;
    let session = document.getElementById('session-main').value;
    let from = document.getElementById('date-from-main').value;
    let to = document.getElementById('date-to-main').value;

    if (!from || !to) {
      alert("Please select date range");
      return;
    }

    fetch("finance/get-report-by-date.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body:
        "date_from=" + encodeURIComponent(from) +
        "&date_to=" + encodeURIComponent(to) +
        "&slot=" + encodeURIComponent(slot) +
        "&session=" + encodeURIComponent(session) +
        "&type=" + encodeURIComponent(type) +
        "&recalculation=" + encodeURIComponent(recalculation)
    })
      .then(response => {

        console.log("HTTP Status:", response.status);

        if (!response.ok) {
          throw new Error("HTTP error " + response.status);
        }

        return response.text();
      })
      .then(html => {

        console.log(html);

        let reportBlock = document.getElementById("report-block");

        if (!reportBlock) {
          throw new Error("#report-block not found");
        }

        reportBlock.innerHTML = html;
      })
      .catch(err => {
        console.error("FULL ERROR:", err);
        alert(err);
      });
  }


</script>

<script>

</script>
<!-- ----------------------------------- -->
</body>

</html>