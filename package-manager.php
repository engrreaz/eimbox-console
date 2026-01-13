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
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form id="settingForm">
        <div class="modal-header bg-light pb-3">
          <h5 class="modal-title">Add / Edit Package Setting</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <input type="hidden" name="action" value="save_settings">
          <input type="hidden" name="id" id="id">
          <input type="hidden" name="package_id" id="package_id">

          <!-- BASIC CONFIG -->
          <div class="row g-2">
            <div class="col-md-3">
              <label>Institution Tier</label>
              <select name="ins_tier" class="form-control form-control-sm" required>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
                <option value="E">E</option>
              </select>
            </div>

            <div class="col-md-3">
              <label>Billing Cycle</label>
              <select name="billing_cycle" class="form-control form-control-sm">
                <option>Monthly</option>
                <option>Quarterly</option>
                <option>Half Yearly</option>
                <option>Yearly</option>
              </select>
            </div>

            <div class="col-md-3">
              <label>Payment Model</label>
              <select name="payment_model" class="form-control form-control-sm">
                <option>Pre-paid</option>
                <option>Post-paid</option>
              </select>
            </div>

            <div class="col-md-3">
              <label>Status</label>
              <select name="status" class="form-control form-control-sm">
                <option>active</option>
                <option>inactive</option>
              </select>
            </div>

            <div class="col-md-3">
              <label>Price</label>
              <input type="number" step="0.01" name="price" class="form-control form-control-sm" required>
            </div>




            <!-- LIMITS -->

            <div class="col-md-3">
              <label>Total Uses Limit</label>
              <input type="number" name="total_uses_limit" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
              <label>Photo Upload Limit</label>
              <input type="number" name="photo_upload" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
              <label>Print Limit</label>
              <input type="number" name="print" class="form-control form-control-sm">
            </div>
          </div>



          <!-- MODULES -->
          <div>
            <label class="fw-bold mb-2">Enabled Modules</label>
            <div class="row g-1" id="moduleListContainer">

              <?php
              // load public modules
              $m = $conn->query("SELECT id,module_name,core FROM modulelist WHERE is_public=1 ORDER BY module_name");
              while ($mod = $m->fetch_assoc()) {
                $disabled = $mod['core'] == 1 ? 'disabled checked' : '';
                echo "
                <div class='col-md-3'>
                  <div class='form-check'>
                    <input class='form-check-input' type='checkbox'
                      name='module[]'
                      value='{$mod['module_name']}'
                      id='mod{$mod['id']}'
                      {$disabled}>
                    <label class='form-check-label' for='mod{$mod['id']}'>
                      {$mod['module_name']}
                      " . ($mod['core'] == 1 ? "<span class='badge bg-secondary ms-1'>Core</span>" : "") . "
                    </label>
                  </div>
                </div>";
              }
              ?>
            </div>
          </div>


          <?php
  
          // যদি edit-mode থেকে আগের selection থাকে
          $selected_panels = []; // DB থেকে এসেছে: "Admin,Student"
          
          echo '<div class="mb-3">';
          echo '<label class="fw-bold mb-2">Panels Access</label>';
          echo '<div class="row g-1"  id="panelListContainer" >';




          foreach ($eimbox_panels as $i => $panel) {
            $isChecked = in_array($panel, $selected_panels) ? 'checked' : '';
            echo '<div class="col-md-3">';
            echo '  <div class="form-check">';
            echo '    <input class="form-check-input" type="checkbox" 
                name="panel[]" 
                value="' . $panel . '" 
                id="panel' . $i . '" ' . $isChecked . '>';
            echo '    <label class="form-check-label" for="panel' . $i . '">' . $panel . '</label>';
            echo '  </div>';
            echo '</div>';
          }

          echo '</div>';
          echo '</div>';
          ?>

          <div class="mt-3">
            <button type="submit" class="btn btn-success w-100 btn-sm">
              Save Setting
            </button>
          </div>

        </div>
      </form>
    </div>
  </div>
</div>



<div class="modal fade" id="editPackageModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editPackageForm">
        <div class="modal-header bg-light">
          <h5>Edit Package</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="action" value="update_package">
          <input type="hidden" name="id" id="ep_id">

          <div class="row">
            <div class="mb-2 col-md-2">
              <label>Serial</label>
              <input type="number" name="serial" id="ep_serial" class="form-control form-control-sm " disabled>
            </div>

            <div class="mb-2 col-md-4">
              <label>Package Name</label>
              <input type="text" name="package_name" id="ep_name" class="form-control form-control-sm">
            </div>

            <div class="mb-2 col-md-3">
              <label>Package Code</label>
              <input type="text" name="package_code" id="ep_code" class="form-control form-control-sm">
            </div>



            <div class="mb-2 col-md-3">
              <label>Status</label>
              <select name="status" id="ep_status" class="form-control form-control-sm">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="mb-2 col-md-12">
              <label>Description</label>
              <textarea name="description" id="ep_desc" class="form-control form-control-sm"></textarea>
            </div>
          </div>



          <button class="btn btn-success w-100 btn-sm">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- <button onclick="$('#settingsModal').modal('show')" class="btn btn-success">Test Settings Modal</button> -->


<?php require_once 'footer.php'; ?>



<script>
  $(document).ready(function () {
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
        // alert(res);
        const addModal = bootstrap.Modal.getInstance(document.getElementById('addPackageModal'));
        addModal?.hide();
        $('#addPackageForm')[0].reset();
        loadPackages();
      });
    });

    // Open Settings Modal
    $(document).on('click', '.btn-settings', function () {
      let pkgId = $(this).data('id');
      const modalEl = document.getElementById('settingsModal');
      let myModal = bootstrap.Modal.getInstance(modalEl);
      if (!myModal) myModal = new bootstrap.Modal(modalEl);
      myModal.show();

      $.post('package-manager/package_settings_view.php', { package_id: pkgId }, function (html) {
        $('#settingsTableContainer').html(html);
      });
    });

    // Open Add/Edit Setting Modal
    $(document).on('click', '.btn-edit-setting', function () {
      let id = $(this).data('data-id');
      let pkg = $(this).data('package-id');
      let tier = $(this).data('ins-tier');
      let bill = $(this).data('billing');
      // alert(id);
      $.post('package-manager/package_get_setting.php',
        { data_id: id, package_id: pkg, ins_tier: tier, billing_cycle: bill },
        function (res) {
          // alert(JSON.stringify(res));
          Object.entries(res).forEach(([k, v]) => {
            $('[name="' + k + '"]').val(v);
          });

          $('#moduleListContainer input[type=checkbox]').each(function () {
            if (!this.disabled) this.checked = false;
          });
          $('#panelListContainer input[type=checkbox]').each(function () {
            if (!this.disabled) this.checked = false;
          });

          if (res.module && res.module.length > 0) {
            let modulesArr = res.module.split(',').map(m => m.trim());

            // তারপর loop করে check
            modulesArr.forEach(function (m) {
              $('#moduleListContainer input[value="' + m + '"]').prop('checked', true);
            });
          }

          if (res.panel && res.panel.length > 0) {
            let selectedPanels = res.panel;
            let panelsArr = selectedPanels.split(',').map(p => p.trim());
            panelsArr.forEach(p => {
              $('#panelListContainer input[value="' + p + '"]').prop('checked', true);
            });
          }
          new bootstrap.Modal('#editSettingModal').show();
        }, 'json');
    });


    // Save Setting
    $('#settingForm').submit(function (e) {
      e.preventDefault();
      $.post('package-manager/package_actions.php', $(this).serialize(), function (res) {
        alert(res);

        const editModal = bootstrap.Modal.getInstance(document.getElementById('editSettingModal'));
        const settingsModal = bootstrap.Modal.getInstance(document.getElementById('settingsModal'));
        editModal?.hide();
        settingsModal?.hide();

        loadPackages();
      });
    });



    $(document).on('click', '.btn-edit-package', function () {
      let id = $(this).data('id');

      $.post('package-manager/package_actions.php',
        { action: 'get_package', id: id },
        function (res) {
          $('#ep_id').val(res.id);
          $('#ep_serial').val(res.serial);
          $('#ep_name').val(res.package_name);
          $('#ep_code').val(res.package_code);
          $('#ep_desc').val(res.description);
          $('#ep_status').val(res.status);

          new bootstrap.Modal('#editPackageModal').show();
        }, 'json');
    });



    $('#editPackageForm').submit(function (e) {
      e.preventDefault();

      $.post('package-manager/package_actions.php',
        $(this).serialize(),
        function (res) {
          // alert(res);

          bootstrap.Modal.getInstance(
            document.getElementById('editPackageModal')
          ).hide();

          // settings modal reopen + refresh
          let pkgId = $('#ep_id').val();
          $.post('package-manager/package_settings_view.php',
            { package_id: pkgId },
            function (html) {
              $('#settingsTableContainer').html(html);
            }
          );

          // main package list refresh
          loadPackages();
        }
      );
    });


  });
</script>



</body>

</html>