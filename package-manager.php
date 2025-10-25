<?php require_once 'header.php'; ?>

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-box-seam"></i> Package Management</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPackageModal">
      <i class="bi bi-plus-lg"></i> Add Package
    </button>
  </div>

  <!-- Package List Table -->
  <table class="table table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Code</th>
        <th>Description</th>
        <th>Status</th>
        <th>Settings</th>
      </tr>
    </thead>
    <tbody id="packageTable"></tbody>
  </table>

</div>

<!-- ================= Add Package Modal ================= -->
<div class="modal fade" id="addPackageModal" tabindex="-1">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <form id="addPackageForm">
        <div class="modal-header bg-light">
          <h5 class="modal-title">Add New Package</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="add_package">
          <div class="mb-2">
            <label>Serial</label>
            <input type="number" name="serial" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Package Name</label>
            <input type="text" name="package_name" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Package Code</label>
            <input type="text" name="package_code" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary w-100">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= Settings Modal ================= -->
<div class="modal fade" id="settingsModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title"><i class="bi bi-gear"></i> Package Settings</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="settingsTableContainer"></div>
    </div>
  </div>
</div>

<!-- ================= Add/Edit Setting Modal ================= -->
<div class="modal fade" id="editSettingModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="settingForm">
        <div class="modal-header bg-light">
          <h5 class="modal-title">Add/Edit Setting</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="text" name="action" value="save_settings">
          <input type="text" name="data_id" id="data_id">
          <input type="text" name="package_id" id="package_id">

          <div class="row g-2">
            <div class="col-md-6">
              <label>Institution Category</label>
              <select name="ins_cat" class="form-select" required>
                <option value="A">Cat A</option>
                <option value="B">Cat B</option>
                <option value="C">Cat C</option>
                <option value="D">Cat D</option>
                <option value="E">Cat E</option>
              </select>
            </div>
            <div class="col-md-6">
              <label>Billing Cycle</label>
              <select name="billing_cycle" class="form-select">
                <option value="Monthly">Monthly</option>
                <option value="Quarterly">Quarterly</option>
                <option value="Half Yearly">Half-Yearly</option>
                <option value="Yearly">Yearly</option>
              </select>
            </div>
            <div class="col-md-6">
              <label>Payment Model</label>
              <select name="payment_model" class="form-select">
                <option value="Pre-paid">Prepaid</option>
                <option value="Post-paid">Postpaid</option>
              </select>
            </div>
            <div class="col-md-6">
              <label>Status</label>
              <select name="status" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>

          <hr>
          <div class="row g-2">
            <div class="col"><label>Cat A</label><input type="number" step="0.01" name="price_cat_a"
                class="form-control"></div>
            <div class="col"><label>Cat B</label><input type="number" step="0.01" name="price_cat_b"
                class="form-control"></div>
            <div class="col"><label>Cat C</label><input type="number" step="0.01" name="price_cat_c"
                class="form-control"></div>
            <div class="col"><label>Cat D</label><input type="number" step="0.01" name="price_cat_d"
                class="form-control"></div>
            <div class="col"><label>Cat E</label><input type="number" step="0.01" name="price_cat_e"
                class="form-control"></div>
          </div>

          <button type="submit" class="btn btn-success w-100 mt-3">Save Setting</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
  $(function () {
    // Load packages
    function loadPackages() {
      $.post('package-manager/package_actions.php', { action: 'load_packages' }, function (data) {
        $('#packageTable').html(data);
      });
    }
    loadPackages();

    // Add new package
    $('#addPackageForm').submit(function (e) {
      e.preventDefault();
      $.post('package-manager/package_actions.php', $(this).serialize(), function (res) {
        alert(res);
        $('#addPackageModal').modal('hide');
        $('#addPackageForm')[0].reset();
        loadPackages();
      });
    });

    // Open Settings Modal
    $(document).on('click', '.btn-settings', function () {
      let pkgId = $(this).data('id');
      $('#settingsModal').modal('show');
      $.post('package-manager/package_settings_view.php', { package_id: pkgId }, function (html) {
        $('#settingsTableContainer').html(html);
      });
    });

    // Open Add/Edit Setting Modal
    $(document).on('click', '.btn-edit-setting', function () {
      let pkgId = $(this).data('package-id');
      let insCat = $(this).data('ins-cat') || '';
      alert(pkgId + '/' + insCat);
      $('#package_id').val(pkgId);
      $('#settingForm')[0].reset();

      if (insCat) {
        $.post('package-manager/package_get_setting.php', { package_id: pkgId, ins_cat: insCat }, function (data) {
          Object.entries(data).forEach(([k, v]) => $('[name="' + k + '"]').val(v));
        }, 'json');
      }
      $('#editSettingModal').modal('show');
    });

    // Save Setting
    $('#settingForm').submit(function (e) {
      e.preventDefault();
      alert($(this).serialize());
      $.post('package-manager/package_actions.php', $(this).serialize(), function (res) {
        alert(res);
        $('#editSettingModal').modal('hide');
        $('#settingsModal').modal('hide');
        loadPackages();
      });
    });
  });



</script>


</body>

</html>