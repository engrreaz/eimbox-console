<?php 
require_once('../core/core-val.php');
require_once('../core/global_values.php');
require_once('../core/functions.php');
?>


<form id="settingsForm" class="row g-3" enctype="multipart/form-data">
    <input type="hidden" name="sccode" value="<?= htmlspecialchars($new_sccode) ?>">

    <!-- Logo -->
    <div class="col-md-6">
        <label class="form-label">Institution Logo</label>
        <div class="d-flex align-items-center mb-2">

                <img id="currentLogo" src="<?= institute_logo($new_sccode) ?>"
                    style="max-width:100px; max-height:100px; display:block; margin-right:10px;">
          

            <input type="file" name="logo" id="logoInput" class="form-control" style="flex:1; margin-right:5px;">
            <button type="button" id="uploadLogoBtn" class="btn btn-success">Upload</button>
        </div>
        <div id="logoUploadMsg" class="text-sm text-muted"></div>
    </div>

    <!-- Administrators -->
    <div class="col-md-6">
        <label class="form-label ms-8 fw-bold text-warning">Administrators</label>
        <?php if (!empty($admins)): ?>
            <ul>
                <hr class="mt-1 mb-1 p-0">
                <?php foreach ($admins as $adm): ?>
                    <li><?= htmlspecialchars($adm['profilename']) ?> (<?= htmlspecialchars($adm['email']) ?>)
                        <hr class="mt-1 mb-1 p-0">
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="text-warning">No administrator found</div>
        <?php endif; ?>
    </div>

    <!-- Active Session Year -->
    <div class="col-md-6">
        <label class="form-label">Active Session Year</label>
        <input type="text" class="form-control" name="active_syear" value="<?= $activeSession['syear'] ?? date('Y') ?>"
            placeholder="e.g. 25">
    </div>


    <?php
    if ($globalSettings === NULL) {
        $glset = '';
    } else {
        $glset = 'Setting Found';
    }
    ?>

    <!-- Global Settings -->
    <div class="col-md-6">
        <label class="form-label">Global Settings</label>
        <input type="text" class="form-control" name="globalsetting1" value="<?= $glset ?? '' ?>" disabled>
    </div>

    <!-- Other Settings -->
    <div class="col-12">
        <label class="form-label">Settings</label>
        <div class="row g-2">
            <?php
            // $setting_titles = ['Medium', 'Version', 'Module', 'Weekends', 'Collection', 'Profile Entry', 'Panel'];
            // $setting_def = ['Bengali', 'Bengali', 'Result.Attendance.Payment', 'Friday.Saturday', 'Administrator.Class Teacher', 'Administrator.Class Teacher', 'Admin.Teacher.Student'];
            
            $sql = "SELECT setting_title, max(settings_value) as settings_value  FROM settings  GROUP BY setting_title  ORDER BY setting_title ASC";
            $result = $conn->query($sql);
            $setting_titles = [];
            $setting_def = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $setting_titles[] = $row['setting_title'];
                    $setting_def[] = $row['settings_value'];
                }
            }

            foreach ($setting_titles as $i => $title):
                $value = !empty($settingsArr[$title]) ? $settingsArr[$title] : ($setting_def[$i] ?? '');
                $disabled = empty($settingsArr[$title]) ? 'readonlyx' : '';
                ?>
                <div class="col-md-3 mb-2">
                    <label class="form-level"><?= htmlspecialchars($title) ?></label>
                    <input type="text" class="form-control" name="settings[<?= htmlspecialchars($title) ?>]"
                        value="<?= htmlspecialchars($value) ?>" <?= $disabled ?>>
                </div>
            <?php endforeach; ?>


        </div>
    </div>

    <!-- Buttons -->
    <div class="col-12 text-end mt-3">
        <button type="submit" class="btn btn-primary me-2">Update Settings</button>
        <button type="button" class="btn btn-success" id="toStep3" onclick="thirdSetp();">Next</button>
    </div>
</form>


<script>
    document.getElementById('uploadLogoBtn').addEventListener('click', function () {



        const fileInput = document.getElementById('logoInput');
        const msgDiv = document.getElementById('logoUploadMsg');

        if (fileInput.files.length === 0) {
            msgDiv.textContent = "Please select a file to upload.";
            msgDiv.className = "text-danger";
            return;
        }

        const formData = new FormData();
        formData.append('logo', fileInput.files[0]);
        formData.append('sccode', '<?= $new_sccode ?>'); // PHP থেকে পাস করা

        msgDiv.textContent = "Uploading...";
        msgDiv.className = "text-muted";

        fetch('ajax/upload_logo.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    msgDiv.textContent = "Logo uploaded successfully!";
                    msgDiv.className = "text-success";
                    // নতুন লোগো দেখানো
                    const logoImg = document.getElementById('currentLogo');
                    if (logoImg.tagName === "IMG") {
                        logoImg.src = "<?= institute_logo($new_sccode) ?>?v=" + new Date().getTime();
                    } else {
                        logoImg.innerHTML = '';
                        logoImg.style.background = 'none';
                        logoImg.textContent = '';
                        const newImg = document.createElement('img');
                        newImg.src = "<?= institute_logo($new_sccode) ?>?v=" + new Date().getTime();
                        newImg.style.maxWidth = "100px";
                        newImg.style.maxHeight = "100px";
                        logoImg.replaceWith(newImg);
                        newImg.id = "currentLogo";
                    }
                } else {
                    msgDiv.textContent = "Upload failed: " + data.msg;
                    msgDiv.className = "text-danger";
                }
            })
            .catch(err => {
                msgDiv.textContent = "Error: " + err.message;
                msgDiv.className = "text-danger";
            });
    });

</script>