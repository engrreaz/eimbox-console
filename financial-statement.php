<?php 
require_once 'header.php'; 
?>

<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Financial Statement Header Card -->
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-header border-bottom py-3">
      <h5 class="mb-0 fw-bold text-primary">
        <i class="bi bi-bank me-2"></i>Comprehensive Financial Statement (Chart of Accounts)
      </h5>
      <small class="text-muted">Head-wise Income & Expenditure Statements, Bank Accounts Liquidity, and Sanctioned Vouchers</small>
    </div>
    <div class="card-body p-3">
      <div class="row g-2 align-items-end">

        <!-- Filter Basis Selector -->
        <div class="col-6 col-md-2">
          <label class="form-label small fw-bold mb-1">Filter Basis (ভিত্তি)</label>
          <select id="filter-basis-statement" name="filter_basis" class="form-select form-select-sm fw-bold text-primary">
            <option value="month_year" selected>Month & Year (বিল মাস)</option>
            <option value="date_range">Date Range (তারিখের ভিত্তি)</option>
          </select>
        </div>
        
        <!-- Slot Selector -->
        <div class="col-6 col-md-2">
          <label class="form-label small fw-bold mb-1">Slot / Unit</label>
          <select id="slot-statement" name="slot-statement" class="form-select form-select-sm">
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
          <select id="session-statement" name="session-statement" class="form-select form-select-sm">
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
          <label class="form-label small fw-bold mb-1">Billing Month</label>
          <input type="month" class="form-control form-control-sm" name="month" id="month-statement" value="<?= date('Y-m') ?>">
        </div>

        <!-- Date From -->
        <div class="col-6 col-md-2">
          <label class="form-label small fw-bold mb-1">Date From</label>
          <input type="date" class="form-control form-control-sm" name="date-from-statement" id="date-from-statement" value="<?= date('Y-m-01') ?>">
        </div>

        <!-- Date To -->
        <div class="col-6 col-md-2">
          <label class="form-label small fw-bold mb-1">Date To</label>
          <input type="date" class="form-control form-control-sm" name="date-to-statement" id="date-to-statement" value="<?= date('Y-m-t') ?>">
        </div>

        <!-- Status Filter -->
        <div class="col-6 col-md-2">
          <label class="form-label small fw-bold mb-1">Voucher Status</label>
          <select id="status-filter" name="status-filter" class="form-select form-select-sm">
            <option value="1" selected>Sanctioned Only</option>
            <option value="0">Pending Only</option>
            <option value="-1">All Vouchers</option>
          </select>
        </div>

        <!-- Action Buttons -->
        <div class="col-10 text-end mt-3 "> 
          <button type="button" class="btn btn-primary btn-sm px-4 shadow-sm fw-bold" onclick="generate_statement()">
            <i class="bi bi-play-circle me-1"></i> Generate Statement
          </button>
        </div>

      </div>
    </div>
  </div>

  <!-- Statement Output Container -->
  <div id="financial-statement-block"></div>

</div>

<?php require_once 'footer.php'; ?>

<script>
  // Dynamic input enable/disable based on Filter Basis
  function updateFilterBasisUI() {
    const basis = document.getElementById('filter-basis-statement').value;
    const monthEl = document.getElementById('month-statement');
    const dateFromEl = document.getElementById('date-from-statement');
    const dateToEl = document.getElementById('date-to-statement');

    if (!monthEl || !dateFromEl || !dateToEl) return;

    if (basis === 'month_year') {
      monthEl.disabled = false;
      dateFromEl.disabled = true;
      dateToEl.disabled = true;
      monthEl.classList.remove('bg-light');
      dateFromEl.classList.add('bg-light');
      dateToEl.classList.add('bg-light');
    } else {
      monthEl.disabled = true;
      dateFromEl.disabled = false;
      dateToEl.disabled = false;
      monthEl.classList.add('bg-light');
      dateFromEl.classList.remove('bg-light');
      dateToEl.classList.remove('bg-light');
    }
  }

  document.getElementById('filter-basis-statement')?.addEventListener('change', updateFilterBasisUI);

  // Quick Month Selector
  document.getElementById('month-statement')?.addEventListener('change', function () {
    let monthValue = this.value;
    if (!monthValue) return;

    let fromDate = monthValue + "-01";
    let tempDate = new Date(monthValue + "-01");
    let lastDay = new Date(tempDate.getFullYear(), tempDate.getMonth() + 1, 0);

    let year = lastDay.getFullYear();
    let month = String(lastDay.getMonth() + 1).padStart(2, '0');
    let day = String(lastDay.getDate()).padStart(2, '0');
    let toDate = `${year}-${month}-${day}`;

    document.getElementById('date-from-statement').value = fromDate;
    document.getElementById('date-to-statement').value = toDate;
  });

  function generate_statement() {
    let basis = document.getElementById('filter-basis-statement').value;
    let slot = document.getElementById('slot-statement').value;
    let session = document.getElementById('session-statement').value;
    let monthParam = document.getElementById('month-statement').value;
    let from = document.getElementById('date-from-statement').value;
    let to = document.getElementById('date-to-statement').value;
    let status = document.getElementById('status-filter').value;

    const statementBlock = document.getElementById("financial-statement-block");
    statementBlock.innerHTML = `
      <div class="card shadow-sm border-0 text-center py-5">
        <div class="spinner-border text-primary mx-auto mb-2" role="status"></div>
        <p class="text-muted mb-0">Generating Financial Statement & COA Breakdown...</p>
      </div>`;

    fetch("finance/get-financial-statement.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body:
        "filter_basis=" + encodeURIComponent(basis) +
        "&month_param=" + encodeURIComponent(monthParam) +
        "&date_from=" + encodeURIComponent(from) +
        "&date_to=" + encodeURIComponent(to) +
        "&slot=" + encodeURIComponent(slot) +
        "&session=" + encodeURIComponent(session) +
        "&status_filter=" + encodeURIComponent(status)
    })
      .then(response => {
        if (!response.ok) {
          throw new Error("Server returned HTTP error " + response.status);
        }
        return response.text();
      })
      .then(html => {
        statementBlock.innerHTML = html;
      })
      .catch(err => {
        console.error("Financial Statement Error:", err);
        statementBlock.innerHTML = `
          <div class="alert alert-danger shadow-sm">
            <i class="bi bi-x-circle me-2"></i>Failed to generate financial statement. ${err.message}
          </div>`;
      });
  }

  // Load statement automatically on DOM ready
  document.addEventListener('DOMContentLoaded', function() {
    updateFilterBasisUI();
    generate_statement();
  });
</script>
</body>
</html>
