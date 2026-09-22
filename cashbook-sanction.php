<?php
require_once 'header.php';

// Action Handler for Sanctioning / Rejecting Vouchers
$alert_msg = '';
$alert_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type'], $_POST['voucher_id'])) {
    $voucher_id = intval($_POST['voucher_id']);
    $action_type = trim($_POST['action_type']);
    $new_status = ($action_type === 'sanction') ? 1 : (($action_type === 'reject') ? 2 : 0);

    if ($voucher_id > 0) {
        $stmt = $conn->prepare("UPDATE cashbook SET status = ?, modifieddate = NOW() WHERE id = ? AND sccode = ? AND (is_locked IS NULL OR is_locked = 0)");
        $stmt->bind_param("iii", $new_status, $voucher_id, $sccode);
        if ($stmt->execute()) {
            $alert_msg = "Voucher #{$voucher_id} successfully " . ($new_status === 1 ? 'Sanctioned & Approved' : 'Rejected') . ".";
            $alert_type = ($new_status === 1) ? 'success' : 'warning';
        } else {
            $alert_msg = "Failed to update voucher status.";
            $alert_type = 'danger';
        }
    }
}

// Bulk Sanction Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_sanction'], $_POST['selected_vouchers']) && is_array($_POST['selected_vouchers'])) {
    $ids = array_map('intval', $_POST['selected_vouchers']);
    if (!empty($ids)) {
        $id_list = implode(',', $ids);
        $sql = "UPDATE cashbook SET status = 1, modifieddate = NOW() WHERE id IN ($id_list) AND sccode = '$sccode' AND (is_locked IS NULL OR is_locked = 0)";
        if ($conn->query($sql)) {
            $count = count($ids);
            $alert_msg = "Successfully Sanctioned {$count} vouchers.";
            $alert_type = 'success';
        } else {
            $alert_msg = "Failed to perform bulk sanction.";
            $alert_type = 'danger';
        }
    }
}

// Query Pending Vouchers
$sql_pending = "SELECT c.*, 
                       COALESCE(h.account_head, s.account_head, 'General') AS main_head_name,
                       COALESCE(s.sub_head, 'General') AS sub_head_name
                FROM cashbook c
                LEFT JOIN account_sub_head s ON (c.account_sub_head = s.id OR c.partid = s.id)
                LEFT JOIN account_head h ON (c.account_head = h.id OR s.account_head_id = h.id)
                WHERE c.sccode = '$sccode' 
                AND c.status = 0
                AND (c.is_locked IS NULL OR c.is_locked = 0)
                ORDER BY c.date DESC, c.id DESC";
$res_pending = $conn->query($sql_pending);
$pending_vouchers = [];
if ($res_pending) {
    while ($row = $res_pending->fetch_assoc()) {
        $pending_vouchers[] = $row;
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">

  <!-- SweetAlert Notification Integration -->
  <?php if ($alert_msg): ?>
      <script>
          document.addEventListener('DOMContentLoaded', function() {
              if (typeof Swal !== 'undefined') {
                  Swal.fire({
                      icon: '<?= ($alert_type === 'danger') ? 'error' : htmlspecialchars($alert_type) ?>',
                      title: '<?= addslashes(htmlspecialchars($alert_msg)) ?>',
                      toast: true,
                      position: 'top-end',
                      showConfirmButton: false,
                      timer: 3500,
                      timerProgressBar: true
                  });
              }
          });
      </script>
      <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show shadow-sm" role="alert">
          <i class="bi bi-info-circle me-2"></i><?= htmlspecialchars($alert_msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
  <?php endif; ?>

  <div class="card shadow-sm border-0">
    <div class="card-header border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h5 class="mb-0 fw-bold text-primary">
          <i class="bi bi-shield-check me-2"></i>Cashbook Voucher Sanction & Approval
        </h5>
        <small class="text-muted">Review, Approve, or Reject pending cashbook vouchers for financial period closing</small>
      </div>
      <div>
        <span class="badge bg-warning text-dark fs-6 px-3 py-2 rounded-pill">
          <i class="bi bi-clock-history me-1"></i> <?= count($pending_vouchers) ?> Pending Vouchers
        </span>
      </div>
    </div>

    <div class="card-body p-0">
      <form method="POST" id="bulkSanctionForm">
        <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between gap-2 d-print-none">
          <div class="d-flex align-items-center gap-2">
            <input type="checkbox" id="selectAllVouchers" class="form-check-input mt-0" style="cursor: pointer;">
            <label for="selectAllVouchers" class="form-check-label small fw-bold text-secondary" style="cursor: pointer;">Select All Pending</label>
          </div>
          <button type="button" class="btn btn-success btn-sm px-3 shadow-sm fw-bold" onclick="confirmBulkSanction()">
            <i class="bi bi-check-all me-1"></i> Bulk Sanction Selected
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-light">
              <tr class="small text-uppercase">
                <th style="width: 40px;" class="text-center">#</th>
                <th style="width: 40px;" class="text-center">Select</th>
                <th>Voucher Date</th>
                <th>Type</th>
                <th>Account Head</th>
                <th>Sub-Sector</th>
                <th>Particulars</th>
                <th class="text-end">Amount (৳)</th>
                <th>Entered By</th>
                <th class="text-center" style="width: 140px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($pending_vouchers)): ?>
                <tr>
                  <td colspan="10" class="text-center py-5 text-muted">
                    <i class="bi bi-check2-circle text-success fs-1 d-block mb-2"></i>
                    All cashbook vouchers have been sanctioned! No pending vouchers found.
                  </td>
                </tr>
              <?php else: ?>
                <?php 
                $sl = 1;
                foreach ($pending_vouchers as $v): 
                  $is_inc = ($v['type'] === 'Income');
                ?>
                  <tr>
                    <td class="text-center text-muted small fw-bold"><?= $sl++ ?></td>
                    <td class="text-center">
                      <input type="checkbox" name="selected_vouchers[]" value="<?= $v['id'] ?>" class="form-check-input voucher-checkbox" style="cursor: pointer;">
                    </td>
                    <td class="fw-semibold text-nowrap"><?= date('d M, Y', strtotime($v['date'])) ?></td>
                    <td>
                      <span class="badge <?= $is_inc ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> border">
                        <?= htmlspecialchars($v['type'] ?: 'Expense') ?>
                      </span>
                    </td>
                    <td class="fw-bold text-primary"><?= htmlspecialchars($v['main_head_name']) ?></td>
                    <td class="fw-semibold text-secondary"><?= htmlspecialchars($v['sub_head_name']) ?></td>
                    <td><?= htmlspecialchars($v['particulars'] ?: $v['sub_head_name']) ?></td>
                    <td class="text-end fw-bold <?= $is_inc ? 'text-success' : 'text-danger' ?>">
                      ৳<?= number_format($v['amount'], 2) ?>
                    </td>
                    <td class="small text-muted"><?= htmlspecialchars($v['entryby']) ?></td>
                    <td class="text-center">
                      <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-success" title="Sanction & Approve" onclick="confirmSingleSanction(<?= $v['id'] ?>)">
                          <i class="bi bi-check-lg"></i> Approve
                        </button>
                        <button type="button" class="btn btn-outline-danger" title="Reject" onclick="confirmSingleReject(<?= $v['id'] ?>)">
                          <i class="bi bi-x-lg"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </form>

      <!-- Separate Single Action Forms -->
      <?php foreach ($pending_vouchers as $v): ?>
        <form id="singleForm_<?= $v['id'] ?>" method="POST" style="display: none;">
          <input type="hidden" name="voucher_id" value="<?= $v['id'] ?>">
          <input type="hidden" name="action_type" value="sanction">
        </form>
        <form id="singleRejectForm_<?= $v['id'] ?>" method="POST" style="display: none;">
          <input type="hidden" name="voucher_id" value="<?= $v['id'] ?>">
          <input type="hidden" name="action_type" value="reject">
        </form>
      <?php endforeach; ?>

    </div>
  </div>

</div>

<?php require_once 'footer.php'; ?>

<script>
  // Select All Checkbox Script
  document.getElementById('selectAllVouchers')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.voucher-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
  });

  function confirmBulkSanction() {
      const selected = document.querySelectorAll('.voucher-checkbox:checked');
      if (selected.length === 0) {
          if (typeof Swal !== 'undefined') {
              Swal.fire({
                  icon: 'warning',
                  title: 'No Vouchers Selected',
                  text: 'Please select at least one voucher to sanction.',
                  confirmButtonColor: '#0d6efd'
              });
          } else {
              alert('Please select at least one voucher to sanction.');
          }
          return;
      }
      if (typeof Swal !== 'undefined') {
          Swal.fire({
              title: 'Bulk Sanction Vouchers?',
              text: "Approve and sanction " + selected.length + " selected voucher(s)?",
              icon: 'question',
              showCancelButton: true,
              confirmButtonColor: '#198754',
              cancelButtonColor: '#6c757d',
              confirmButtonText: 'Yes, Sanction All'
          }).then((res) => {
              if (res.isConfirmed) {
                  const form = document.getElementById('bulkSanctionForm');
                  const hiddenInput = document.createElement('input');
                  hiddenInput.type = 'hidden';
                  hiddenInput.name = 'bulk_sanction';
                  hiddenInput.value = '1';
                  form.appendChild(hiddenInput);
                  form.submit();
              }
          });
      } else {
          if (confirm('Sanction all selected vouchers?')) {
              const form = document.getElementById('bulkSanctionForm');
              const hiddenInput = document.createElement('input');
              hiddenInput.type = 'hidden';
              hiddenInput.name = 'bulk_sanction';
              hiddenInput.value = '1';
              form.appendChild(hiddenInput);
              form.submit();
          }
      }
  }

  function confirmSingleSanction(id) {
      if (typeof Swal !== 'undefined') {
          Swal.fire({
              title: 'Sanction & Approve Voucher?',
              text: "Approve voucher #" + id + " into the official ledger?",
              icon: 'question',
              showCancelButton: true,
              confirmButtonColor: '#198754',
              cancelButtonColor: '#6c757d',
              confirmButtonText: 'Yes, Approve'
          }).then((res) => {
              if (res.isConfirmed) {
                  document.getElementById('singleForm_' + id).submit();
              }
          });
      } else {
          document.getElementById('singleForm_' + id).submit();
      }
  }

  function confirmSingleReject(id) {
      if (typeof Swal !== 'undefined') {
          Swal.fire({
              title: 'Reject Voucher?',
              text: "Are you sure you want to reject voucher #" + id + "?",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#dc3545',
              cancelButtonColor: '#6c757d',
              confirmButtonText: 'Yes, Reject'
          }).then((res) => {
              if (res.isConfirmed) {
                  document.getElementById('singleRejectForm_' + id).submit();
              }
          });
      } else {
          if (confirm('Reject this voucher?')) {
              document.getElementById('singleRejectForm_' + id).submit();
          }
      }
  }
</script>
</body>
</html>
