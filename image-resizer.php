<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-3">Students Images Manager</h4>

    <!-- Search -->
    <form class="d-flex gap-2 mb-3" onsubmit="return false;">
        <input type="text" id="searchName" class="form-control" placeholder="Search filename">

        <input type="number" id="searchMinWidth" class="form-control" placeholder="Min Width px">
        <input type="number" id="searchMinHeight" class="form-control" placeholder="Min Height px">

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
                    <th>Pixels</th>
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


<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Student Details</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="viewBody">
                Loading...
            </div>

            <div class="modal-footer">
                <button class="btn btn-danger" id="deleteImageBtn">Delete</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<?php require_once 'footer.php'; ?>



<script>
    let page = 0;
    let cropper = null;
    let currentFile = "";
    let currentSearchName = "";
    let currentSearchSize = 0;
    let currentMinWidth = 0;
    let currentMinHeight = 0;

    // Load images from server
    function loadImages(reset = false) {
        fetch(`core/load-images.php?page=${page}&name=${encodeURIComponent(currentSearchName)}&size=${currentSearchSize}&w=${currentMinWidth}&h=${currentMinHeight}`)
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
                bindViewButtons();
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


    function bindEditButtons() {
        document.querySelectorAll(".editBtn").forEach(btn => {
            btn.onclick = () => {

                // reset previous cropper
                if (cropper) {
                    try { cropper.destroy(); } catch (e) { }
                    cropper = null;
                }

                let tr = btn.closest("tr");
                currentFile = tr.dataset.filename || "";

                // prefer data-fullsrc, fallback to img dataset.src or src
                let imgEl = tr.querySelector("img");
                let fullSrc = tr.dataset.fullsrc || (imgEl && (imgEl.dataset.src || imgEl.src)) || "";

                if (!fullSrc) {
                    console.error("No image source found for cropping.");
                    return;
                }

                // add cache-bust to force fresh load if needed
                fullSrc = fullSrc + (fullSrc.indexOf('?') === -1 ? '?_=' + Date.now() : '&_=' + Date.now());

                const cropImageEl = document.getElementById("cropImage");

                // set crossOrigin before setting src (CORS header must be present on server)
                cropImageEl.crossOrigin = "anonymous";

                // remove previous onload to avoid multiple binds
                cropImageEl.onload = null;

                // show modal first
                let modalEl = document.getElementById("cropModal");
                let modal = new bootstrap.Modal(modalEl);
                modal.show();

                // when modal fully shown, set src (so animation doesn't mess layout) and init cropper after load
                modalEl.addEventListener('shown.bs.modal', function onShown() {

                    // set image src AFTER modal shown
                    cropImageEl.src = fullSrc;

                    // init when image loaded
                    cropImageEl.onload = function () {
                        // small delay to ensure layout settled (helps with some browsers)
                        setTimeout(() => {
                            if (cropper) {
                                try { cropper.destroy(); } catch (e) { }
                                cropper = null;
                            }

                            // init cropper
                            cropper = new Cropper(cropImageEl, {
                                aspectRatio: 300 / 380,
                                viewMode: 1,
                                autoCropArea: 0.8,
                                responsive: true,
                                background: true
                            });

                        }, 50);
                    };

                    // clean this listener so it runs once
                    modalEl.removeEventListener('shown.bs.modal', onShown);
                });
            };
        });
    }


    document.getElementById("saveCropBtn").addEventListener("click", function () {
        if (!cropper) {
            alert("Cropper not initialized.");
            return;
        }

        // size according to desired output
        cropper.getCroppedCanvas({ width: 300, height: 380 }).toBlob(function (blob) {
            let fd = new FormData();
            fd.append("file", blob, currentFile);

            fetch("core/save-image.php", { method: "POST", body: fd })
                .then(r => r.text())
                .then(t => {
                    showToast && showToast('success', t, 'Success');
                    // close modal first then refresh
                    let modalEl = document.getElementById("cropModal");
                    let bs = bootstrap.Modal.getInstance(modalEl);
                    if (bs) bs.hide();
                    setTimeout(() => location.reload(), 300);
                })
                .catch(err => {
                    console.error(err);
                    alert("Save failed. Check console and server logs.");
                });

        }, "image/jpeg", 0.9);
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

        currentMinWidth = document.getElementById("searchMinWidth").value || 0;
        currentMinHeight = document.getElementById("searchMinHeight").value || 0;

        loadImages(true);
    });

    function bindViewButtons() {
        document.querySelectorAll(".viewBtn").forEach(btn => {
            btn.onclick = () => {

                let tr = btn.closest("tr");
                let filename = tr.dataset.filename;

                // remove extension
                let baseID = filename.replace(/\.[^/.]+$/, "");
      
                const viewBody = document.getElementById("viewBody");
                viewBody.innerHTML = "Loading...";

                // Load student data from server
                fetch("core/view-image-student.php?id=" + baseID)
                    .then(r => r.text())
                    .then(html => viewBody.innerHTML = html)
                    .catch(err => viewBody.innerHTML = "Error loading data");

                // set delete button filename
                document.getElementById("deleteImageBtn").dataset.file = filename;

                let modalEl = document.getElementById("viewModal");
                let modal = new bootstrap.Modal(modalEl);
                modal.show();
            };
        });
    }

    document.getElementById("deleteImageBtn").addEventListener("click", function () {

        let filename = this.dataset.file;
        if (!confirm("Are you sure you want to delete " + filename + "?")) return;

        fetch("core/delete-student-image.php?file=" + filename)
            .then(r => r.text())
            .then(msg => {
                showToast('success', msg, "Deleted");
                location.reload();
            })
            .catch(err => alert("Delete failed."));
    });


    // Initial load
    loadImages();
    bindViewButtons();
</script>


</body>

</html>