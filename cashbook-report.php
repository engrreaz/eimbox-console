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
        
        <!-- Filter Basis Selector -->
        <div class="col-6 col-md-2">
          <label class="form-label small fw-bold mb-1">Filter Basis (ভিত্তি)</label>
          <select id="filter-basis-main" name="filter_basis" class="form-select form-select-sm fw-bold text-primary">
            <option value="month_year" selected>Month & Year (বিল মাস)</option>
            <option value="date_range">Date Range (তারিখের ভিত্তি)</option>
          </select>
        </div>

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
          <label class="form-label small fw-bold mb-1">Billing Month</label>
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

<!-- Modal: Bind & Final Bill Pass -->
<div class="modal fade" id="bindBillModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
      <div class="modal-header border-bottom py-3">
        <h5 class="modal-title fw-bold text-success" id="bindBillModalTitle">
          <i class="bi bi-file-earmark-check me-2"></i>Final Bill Pass & Bind Vouchers
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="bindBillForm">
        <div class="modal-body p-4">
          <div class="alert alert-info py-2 small shadow-sm mb-3">
            <i class="bi bi-info-circle-fill me-1"></i>
            Selected <strong id="bindSelectedCount">0</strong> voucher(s) will be fixed for the target Month/Year and permanently locked against any future modification, deletion, or reversion.
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label small fw-bold">TARGET MONTH</label>
              <select name="target_month" id="bind_target_month" class="form-select form-select-sm" required>
                <option value="1">01 - January (জানুয়ারি)</option>
                <option value="2">02 - February (ফেব্রুয়ারি)</option>
                <option value="3">03 - March (মার্চ)</option>
                <option value="4">04 - April (এপ্রিল)</option>
                <option value="5">05 - May (মে)</option>
                <option value="6">06 - June (জুন)</option>
                <option value="7">07 - July (জুলাই)</option>
                <option value="8">08 - August (আগস্ট)</option>
                <option value="9">09 - September (সেপ্টেম্বর)</option>
                <option value="10">10 - October (অক্টোবর)</option>
                <option value="11">11 - November (নভেম্বর)</option>
                <option value="12">12 - December (ডিসেম্বর)</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label small fw-bold">TARGET YEAR</label>
              <input type="number" name="target_year" id="bind_target_year" class="form-control form-control-sm" value="<?= date('Y') ?>" min="2000" max="2100" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">REFERENCE NO / FINAL BILL MEMO</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="bi bi-bookmark"></i></span>
              <input type="text" name="refno" id="bind_refno" class="form-control" placeholder="e.g. REF-2026-SEP-01">
            </div>
            <small class="text-muted">Enter a reference or sanction memo number for this batch</small>
          </div>
        </div>

        <div class="modal-footer border-top py-3">
          <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success btn-sm px-4 shadow-sm fw-bold">
            <i class="bi bi-lock-fill me-1"></i> Confirm & Lock Vouchers
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
  let recalculation = <?= ($recalc === '1') ? '1' : '0' ?>;
  let selectedVoucherIds = [];
  let currentReportType = 1;

  function toggleRecalculation(el) {
    recalculation = recalculation ? 0 : 1;
    document.cookie = "cashbook_report_recalculation=" + recalculation + "; path=/; max-age=" + (30*86400);
    const checkEl = document.getElementById("recalc-check");
    if (checkEl) {
      checkEl.innerText = recalculation ? "Enabled" : "Disabled";
      checkEl.className = recalculation ? "badge bg-primary" : "badge bg-secondary";
    }
  }

  // Select All Report Vouchers
  function toggleSelectAllReportVouchers(masterCb) {
    const checkboxes = document.querySelectorAll('.report-voucher-cb:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = masterCb.checked);
  }

  // Open Bind Modal
  function openBindModal() {
    const checkedCbs = document.querySelectorAll('.report-voucher-cb:checked:not(:disabled)');
    if (checkedCbs.length === 0) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'warning',
          title: 'No Vouchers Selected',
          text: 'Please select at least one sanctioned voucher to bind and pass final bill.',
          confirmButtonColor: '#0d6efd'
        });
      } else {
        alert('Please select at least one sanctioned voucher to bind.');
      }
      return;
    }

    selectedVoucherIds = Array.from(checkedCbs).map(cb => cb.value);
    document.getElementById('bindSelectedCount').innerText = selectedVoucherIds.length;

    // Set default month & year from month filter or current date
    const monthInput = document.getElementById('month').value; // YYYY-MM
    if (monthInput) {
      const parts = monthInput.split('-');
      if (parts.length === 2) {
        document.getElementById('bind_target_year').value = parts[0];
        document.getElementById('bind_target_month').value = parseInt(parts[1], 10);
      }
    } else {
      const now = new Date();
      document.getElementById('bind_target_month').value = now.getMonth() + 1;
      document.getElementById('bind_target_year').value = now.getFullYear();
    }

    const bindModal = new bootstrap.Modal(document.getElementById('bindBillModal'));
    bindModal.show();
  }

  // Handle Bind Form Submission
  document.getElementById('bindBillForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    if (selectedVoucherIds.length === 0) return;

    const month = document.getElementById('bind_target_month').value;
    const year = document.getElementById('bind_target_year').value;
    const refno = document.getElementById('bind_refno').value;

    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

    fetch('finance/bind-cashbook-vouchers.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: 'voucher_ids=' + encodeURIComponent(selectedVoucherIds.join(',')) +
            '&target_month=' + encodeURIComponent(month) +
            '&target_year=' + encodeURIComponent(year) +
            '&refno=' + encodeURIComponent(refno)
    })
    .then(res => res.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Confirm & Lock Vouchers';

      const modalEl = document.getElementById('bindBillModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl);
      if (modalInstance) modalInstance.hide();

      if (data.status === 'success') {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'success',
            title: 'Final Bill Passed!',
            text: data.message,
            timer: 3000,
            showConfirmButton: false
          });
        } else {
          alert(data.message);
        }
        get_report(currentReportType);
      } else {
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        } else {
          alert(data.message);
        }
      }
    })
    .catch(err => {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Confirm & Lock Vouchers';
      console.error('Binding Error:', err);
      alert('Failed to bind vouchers: ' + err.message);
    });
  });

  // Dynamic input enable/disable based on Filter Basis
  function updateFilterBasisUI() {
    const basis = document.getElementById('filter-basis-main').value;
    const monthEl = document.getElementById('month');
    const dateFromEl = document.getElementById('date-from-main');
    const dateToEl = document.getElementById('date-to-main');

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

  document.getElementById('filter-basis-main')?.addEventListener('change', updateFilterBasisUI);

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
    currentReportType = type;
    let basis = document.getElementById('filter-basis-main').value;
    let slot = document.getElementById('slot-main').value;
    let session = document.getElementById('session-main').value;
    let monthParam = document.getElementById('month').value;
    let from = document.getElementById('date-from-main').value;
    let to = document.getElementById('date-to-main').value;

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
        "filter_basis=" + encodeURIComponent(basis) +
        "&month_param=" + encodeURIComponent(monthParam) +
        "&date_from=" + encodeURIComponent(from) +
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
    updateFilterBasisUI();
    get_report(1);
  });
</script>
</body>
</html>