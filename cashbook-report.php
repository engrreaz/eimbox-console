<?php require_once 'header.php'; ?>

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
          <button class="btn btn-primary" onclick="get_report();">View Report</button>
        </div>
      </div>
    </div>


    <div id="report-block mt-3"></div>

  </div>
</div>



</div>



<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->

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

  function get_report() {
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
      body: "date_from=" + encodeURIComponent(from) + "&date_to=" + encodeURIComponent(to) + "&slot=" + encodeURIComponent(slot) + "&session=" + encodeURIComponent(session)
    })
      .then(res => res.text())
      .then(html => {
        document.getElementById("report-block").innerHTML = html;
      })
      .catch(err => {
        console.error(err);
        alert("Report load failed");
      });
  }
</script>

<script>

</script>
<!-- ----------------------------------- -->
</body>

</html>