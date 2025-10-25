<div class="container text-center mt-3">
    <div id="preview" class="border rounded p-3 mx-auto"
        style="width:<?php echo $wi; ?>px;height:<?php echo $hi; ?>px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
        <span>Select File/Image</span>
    </div>
    <input type="file" id="fileInput" class="d-none" accept="image/*">
</div>

<!-- Bootstrap Modal -->
<div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title ">Crop Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center m-3 " style="border-radius:8px;">
                <img id="cropImage" style="max-width:100%; max-height:100%; height:auto; border-radius:8px;">
            </div>
            <div class="modal-footer flex-column">
                <div class="progress w-100 mb-2" style="display:none;">
                    <div class="progress-bar" role="progressbar" style="width:0%"></div>
                </div>
                <button id="uploadBtn" class="btn btn-primary w-100">আপলোড করুন</button>
            </div>
        </div>
    </div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const preview = document.getElementById('preview');
        const fileInput = document.getElementById('fileInput');
        const cropModalEl = document.getElementById('cropModal');
        const cropImage = document.getElementById('cropImage');
        const uploadBtn = document.getElementById('uploadBtn');
        const progressWrapper = cropModalEl.querySelector('.progress');
        const progressBar = cropModalEl.querySelector('.progress-bar');

        let cropper = null;
        const cropModal = new bootstrap.Modal(cropModalEl);

        // preview click → file select
        preview.addEventListener('click', () => fileInput.click());

        // file selected
        fileInput.addEventListener('change', e => {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = () => {
                cropImage.src = reader.result;
                cropModal.show();

                cropImage.onload = () => {
                    if (cropper) cropper.destroy();
                    cropper = new Cropper(cropImage, {
                        aspectRatio: <?php echo $wi / $hi; ?>,
                        viewMode: 2,
                        autoCropArea: 1,
                        responsive: true,
                    });
                };
            };
            reader.readAsDataURL(file);
        });

        // upload cropped image
        uploadBtn.addEventListener('click', () => {
            if (!cropper) return;
            cropper.getCroppedCanvas({
                maxWidth: 800,
                maxHeight: 800
            }).toBlob(blob => {
                const formData = new FormData();
                formData.append('file', blob, 'photo.jpg');
                formData.append('output_dir', '<?= $output_dir; ?>');
                formData.append('output_file_name', '<?= $output_file_name; ?>');

                const xhr = new XMLHttpRequest();
                progressWrapper.style.display = 'block';
                progressBar.style.width = '0%';

                xhr.upload.onprogress = e => {
                    if (e.lengthComputable) {
                        progressBar.style.width = Math.round((e.loaded / e.total) * 100) + '%';
                    }
                };

                xhr.onload = () => {
                    progressWrapper.style.display = 'none';
                    cropModal.hide();
                    if (cropper) { cropper.destroy(); cropper = null; }

                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.status === 'success') {
                            preview.innerHTML = `<img src="${res.path}?t=${Date.now()}" class="img-fluid rounded" alt="Uploaded">`;
                        } else {
                            alert(res.message || 'Upload failed!');
                        }
                    } catch {
                        alert('Invalid server response');
                    }
                };

                xhr.open('POST', 'core/photo_upload_single.php', true);
                xhr.send(formData);
            }, 'image/jpeg', 0.9);
        });

        // modal hide cleanup
        cropModalEl.addEventListener('hidden.bs.modal', () => {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.paddingRight = '';
        });
    });
</script>