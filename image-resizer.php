<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-3">Students Images Manager</h4>

    <!-- Search -->
    <form class="d-flex gap-2 mb-3" onsubmit="return false;">
        <input type="text" id="searchName" class="form-control" placeholder="Search filename">
        <input type="number" id="searchSize" class="form-control" placeholder="Min Size KB">
        <button type="button" id="searchBtn" class="btn btn-primary">Search</button>
    </form>

    <!-- Table -->
    <div class="table-responsive" style="max-height:600px; overflow:auto;">
        <table class="table table-bordered" id="imagesTable">
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>Name</th>
                    <th>Size (KB)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <button class="btn btn-secondary mt-3" id="loadMoreBtn">Load More</button>
</div>

<!-- CROP MODAL -->
<div class="modal fade" id="cropModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Image</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="cropImage" style="max-width:100%; border:1px solid #666;">
            </div>
            <div class="modal-footer">
                <button id="saveCropBtn" class="btn btn-success">Save</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>


<link href="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.js"></script>


<script>
    let page = 0;
    let cropper = null;
    let currentFile = "";
    let currentSearchName = "";
    let currentSearchSize = 0;

    // Load images from server
    function loadImages(reset = false) {
        fetch(`core/load-images.php?page=${page}&name=${encodeURIComponent(currentSearchName)}&size=${currentSearchSize}`)
            .then(res => res.text())
            .then(html => {
                const tbody = document.querySelector("#imagesTable tbody");
                if (reset) {
                    tbody.innerHTML = html;
                } else {
                    tbody.insertAdjacentHTML("beforeend", html);
                }
                lazyLoadInit();
                bindEditButtons();
            })
            .catch(err => console.error(err));
    }

    // Lazy load images
    function lazyLoadInit() {
        const imgs = document.querySelectorAll("img.lazy");
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    let img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove("lazy");
                    observer.unobserve(img);
                }
            });
        });
        imgs.forEach(img => observer.observe(img));
    }


    // Bind edit buttons for crop
    function bindEditButtons() {
        document.querySelectorAll(".editBtn").forEach(btn => {
            btn.onclick = () => {
                let tr = btn.closest("tr");
                currentFile = tr.dataset.filename;
                let imgSrc = tr.querySelector("img").dataset.src || tr.querySelector("img").src;

                const cropImageEl = document.getElementById("cropImage");
                cropImageEl.src = imgSrc;

                let modal = new bootstrap.Modal(document.getElementById("cropModal"));
                modal.show();

                // wait until image is loaded
                cropImageEl.onload = function () {
                    if (cropper) cropper.destroy();

                    cropper = new Cropper(cropImageEl, {
                        aspectRatio: 150 / 190,
                        viewMode: 1,
                        autoCropArea: 1,
                        responsive: true,
                        background: true
                    });
                };
            };
        });
    }

    // Save cropped image
    document.getElementById("saveCropBtn").addEventListener("click", function () {
        if (!cropper) return;

        // get cropped canvas with proper size
        cropper.getCroppedCanvas({ width: 150, height: 190 }).toBlob(function (blob) {
            let fd = new FormData();
            fd.append("file", blob, currentFile);

            fetch("core/save-image.php", { method: "POST", body: fd })
                .then(res => res.text())
                .then(resp => {
                    showToast('success', resp, 'Success');
                    location.reload();
                })
                .catch(err => console.error(err));
        }, "image/jpeg", 1.0);
    });




    // Load more button
    document.getElementById("loadMoreBtn").addEventListener("click", () => {
        page++;
        loadImages();
    });

    // Search button
    document.getElementById("searchBtn").addEventListener("click", () => {
        page = 0;
        currentSearchName = document.getElementById("searchName").value.trim();
        currentSearchSize = document.getElementById("searchSize").value || 0;
        loadImages(true);
    });

    // Initial load
    loadImages();
</script>


</body>

</html>