<?php 
require_once 'header.php'; 
$recalc = $_COOKIE['cashbook_report_recalculation'] ?? '1';
?>

<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Report Filter Header Card -->
  <div class="card shadow-sm border-0 mb-3">
    <div class="card-header border-bottom py-3">
      <h5 class="mb-0 fw-bold text-primary">
        <i class="bi bi-file-earmark-bar-graph me-2"></i>Cash Book Financial Reports
      </h5>
      <small class="text-muted">Generate Detailed Ledgers, Head-wise Breakdown, and Daily Cash Flow Statements</small>
    </div>
    <div class="card-body p-3">
      <div class="row g-2 align-items-end">
        
        <!-- Slot Selector -->
        <div class="col-6 col-md-2">
          <label class="form-label small fw-bold mb-1">Slot / Unit</label>
          <select id="slot-main" name="slot-main" class="form-select form-select-sm">
            <option value="">All Slots</option>
            <?php
            $q = $conn->query("SELECT slotname FROM slots WHERE sccode='$sccode' ORDER BY slotname");
            if ($q) {
              while ($r = $q->fetch_assoc()) {
                echo "<option value='{$r['slotname']}'>{$r['slotname']}</option>";
              }
            }
            ?>
          </select>
        </div>

        <!-- Session Selector -->
        <div class="col-6 col-md-2">
          <label class="form-label small fw-bold mb-1">Session Year</label>
          <select id="session-main" name="session-main" class="form-select form-select-sm">
            <option value="">All Sessions</option>
            <?php
            $q = $conn->query("SELECT syear FROM sessionyear WHERE sccode='$sccode' AND active=1 ORDER BY syear DESC");
            if ($q) {
              while ($r = $q->fetch_assoc()) {
                $sel = ($r['syear'] == ($sessionyear ?? date('Y'))) ? 'selected' : '';
                echo "<option value='{$r['syear']}' $sel>{$r['syear']}</option>";
              }
            }
            ?>
          </select>
        </div>

        <!-- Quick Month Selector -->
        <div class="col-6 col-md-2">
          <label class="form-label small fw-bold mb-1">Quick Month</label>
          <input type="month" class="form-control form-control-sm" name="month" id="month" value="<?= date('Y-m') ?>">
        </div>

        <!-- Date From -->
        <div class="col-6 col-md-2">
          <label class="form-label small fw-bold mb-1">Date From</label>
          <input type="date" class="form-control form-control-sm" name="date-from-main" id="date-from-main" value="<?= date('Y-m-01') ?>">
        </div>

        <!-- Date To -->
        <div class="col-6 col-md-2">
          <label class="form-label small fw-bold mb-1">Date To</label>
          <input type="date" class="form-control form-control-sm" name="date-to-main" id="date-to-main" value="<?= date('Y-m-t') ?>">
        </div>

        <!-- Action Button Dropdown -->
        <div class="col-12 col-md-2">
          <div class="btn-group w-100">
            <button type="button" class="btn btn-primary btn-sm dropdown-toggle w-100 py-2 shadow-sm fw-bold" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-file-earmark-text me-1"></i> Generate Report
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
              <li>
                <a class="dropdown-item py-2" href="javascript:void(0)" onclick="get_report(1)">
                  <i class="bi bi-journal-text text-primary me-2"></i> Detailed Cash Ledger
                </a>
              </li>
              <li>
                <a class="dropdown-item py-2" href="javascript:void(0)" onclick="get_report(0)">
                  <i class="bi bi-pie-chart text-success me-2"></i> Head-wise Summary
                </a>
              </li>
              <li>
                <a class="dropdown-item py-2" href="javascript:void(0)" onclick="get_report(2)">
                  <i class="bi bi-calendar3-range text-info me-2"></i> Daily Cash Flow
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item py-2 d-flex align-items-center justify-content-between" href="javascript:void(0)" onclick="toggleRecalculation(this)">
                  <span><i class="bi bi-arrow-repeat text-warning me-2"></i>Auto Re-calculate</span>
                  <span id="recalc-check" class="badge bg-primary"><?= $recalc === '1' ? 'Enabled' : 'Disabled' ?></span>
                </a>
              </li>
            </ul>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Dynamic Report Output Container -->
  <div id="report-block"></div>

</div>

<?php require_once 'footer.php'; ?>

<script>
  let recalculation = <?= ($recalc === '1') ? '1' : '0' ?>;

  function toggleRecalculation(el) {
    recalculation = recalculation ? 0 : 1;
    document.cookie = "cashbook_report_recalculation=" + recalculation + "; path=/; max-age=" + (30*86400);
    const checkEl = document.getElementById("recalc-check");
    if (checkEl) {
      checkEl.innerText = recalculation ? "Enabled" : "Disabled";
      checkEl.className = recalculation ? "badge bg-primary" : "badge bg-secondary";
    }
  }

  // Quick Month Selector to auto-set Date From & Date To
  document.getElementById('month')?.addEventListener('change', function () {
    let monthValue = this.value; // YYYY-MM
    if (!monthValue) return;

    let fromDate = monthValue + "-01";
    let tempDate = new Date(monthValue + "-01");
    let lastDay = new Date(tempDate.getFullYear(), tempDate.getMonth() + 1, 0);

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
      alert("Please select a valid date range.");
      return;
    }

    const reportBlock = document.getElementById("report-block");
    reportBlock.innerHTML = `
      <div class="card shadow-sm border-0 text-center py-5">
        <div class="spinner-border text-primary mx-auto mb-2" role="status"></div>
        <p class="text-muted mb-0">Generating Financial Report...</p>
      </div>`;

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
        if (!response.ok) {
          throw new Error("Server returned HTTP error " + response.status);
        }
        return response.text();
      })
      .then(html => {
        reportBlock.innerHTML = html;
      })
      .catch(err => {
        console.error("Report Generation Error:", err);
        reportBlock.innerHTML = `
          <div class="alert alert-danger shadow-sm">
            <i class="bi bi-x-circle me-2"></i>Failed to generate report. ${err.message}
          </div>`;
      });
  }

  // Auto-generate report on page load with default dates
  document.addEventListener('DOMContentLoaded', function() {
    get_report(1);
  });
</script>
</body>
</html>