<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3">Students Images</h4>

    <!-- Search form -->
    <form id="searchForm" class="mb-3 d-flex gap-2">
        <input type="text" id="searchName" class="form-control" placeholder="Search by filename">
        <input type="number" id="searchSize" class="form-control" placeholder="Min size in KB">
        <button type="button" id="searchBtn" class="btn btn-primary">Search</button>
    </form>

    <!-- Table -->
    <div class="table-responsive" style="max-height: 600px; overflow-y:auto;">
        <table class="table table-bordered table-hover" id="imagesTable">
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>Filename</th>
                    <th>Size (KB)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                $host = $_SERVER['HTTP_HOST'];
                $projectFolder = basename(dirname(__DIR__));

                $baseURL = $protocol . $host . "/" . $projectFolder . "/students/";
                $baseURL = BASE_PATH . "students/";

                echo $baseURL;



                echo "__DIR__ = " . __DIR__ . "<br>";
                echo "DOCUMENT_ROOT = " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
                echo "students full path = " . dirname(__DIR__) . "/students<br>";

                $test = dirname(__DIR__) . "/students";
                echo "is_dir(students)? ";
                echo is_dir($test) ? "YES" : "NO";


                $folder = dirname(__DIR__) . '/students';
                $images = glob($folder . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

                foreach ($images as $img) {

                    $sizeKB = round(filesize($img) / 1024, 2);
                    $filename = basename($img);

                    // Auto generated full URL for local + live server
                    $url = $baseURL . $filename;

                    echo "<tr data-filename='$filename' data-size='$sizeKB'>
                        <td><img src='$url'  loading='lazy' style='height:50px; border:1px solid #ccc;'></td>
                        <td>$filename</td>
                        <td>$sizeKB</td>
                        <td><button class='btn btn-sm btn-success editBtn'>Edit</button></td>
                    </tr>";
                }

                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Cropper Modal -->
<div class="modal fade" id="cropModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Crop & Resize Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="cropImage" src="" style="max-width:100%; display:block; margin:auto;">
            </div>
            <div class="modal-footer">
                <button type="button" id="cropSaveBtn" class="btn btn-success">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- Cropper.js -->
<link href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>

<script>
    let cropper;
    let currentFile;

    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            const tr = this.closest('tr');
            currentFile = tr.getAttribute('data-filename');
            const img = tr.querySelector('img').src;
            const cropImage = document.getElementById('cropImage');
            cropImage.src = img;

            const modal = new bootstrap.Modal(document.getElementById('cropModal'));
            modal.show();

            if (cropper) cropper.destroy();
            cropper = new Cropper(cropImage, {
                aspectRatio: 150 / 190,
                viewMode: 1
            });
        });
    });

    document.getElementById('cropSaveBtn').addEventListener('click', function () {
        const canvas = cropper.getCroppedCanvas({ width: 150, height: 190 });
        canvas.toBlob(function (blob) {
            const formData = new FormData();
            formData.append('file', blob, currentFile);

            fetch('core/save-image.php', { method: 'POST', body: formData })
                .then(res => res.text())
                .then(res => {
                    alert(res);
                    location.reload();
                });
        });
    });

    // Search functionality
    document.getElementById('searchBtn').addEventListener('click', function () {
        const name = document.getElementById('searchName').value.toLowerCase();
        const size = parseFloat(document.getElementById('searchSize').value) || 0;

        document.querySelectorAll('#imagesTable tbody tr').forEach(tr => {
            const fname = tr.getAttribute('data-filename').toLowerCase();
            const fsize = parseFloat(tr.getAttribute('data-size'));
            if (fname.includes(name) && fsize >= size) {
                tr.style.display = '';
            } else {
                tr.style.display = 'none';
            }
        });
    });
</script>