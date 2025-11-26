<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-3">Students Images Manager</h4>

    <!-- Search -->
    <form class="d-flex gap-2 mb-3">
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

<!-- Cropper.js -->
<link href="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.js"></script>

<script>
let page = 0;
let cropper;
let currentFile = "";
let currentSearchName = "";
let currentSearchSize = 0;

function loadImages(reset = false) {

    fetch("core/load-images.php?page=" + page +
          "&name=" + encodeURIComponent(currentSearchName) +
          "&size=" + currentSearchSize)
    .then(res => res.text())
    .then(html => {

        if(reset) {
            document.querySelector("#imagesTable tbody").innerHTML = html;
        } else {
            document.querySelector("#imagesTable tbody").insertAdjacentHTML("beforeend", html);
        }

        lazyLoadInit();
        bindEditButtons();
    });
}

document.getElementById("loadMoreBtn").addEventListener("click", function(){
    page++;
    loadImages();
});

document.getElementById("searchBtn").addEventListener("click", function(){
    page = 0;
    currentSearchName = document.getElementById("searchName").value;
    currentSearchSize = document.getElementById("searchSize").value;

    loadImages(true);
});

// Lazy Load
function lazyLoadInit() {
    const imgs = document.querySelectorAll("img.lazy");
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if(e.isIntersecting){
                let img = e.target;
                img.src = img.dataset.src;
                img.classList.remove("lazy");
                obs.unobserve(img);
            }
        });
    });
    imgs.forEach(i => obs.observe(i));
}

// Bind edit buttons
function bindEditButtons() {
    document.querySelectorAll(".editBtn").forEach(btn => {
        btn.onclick = () => {
            let tr = btn.closest("tr");
            currentFile = tr.dataset.filename;
            let img = tr.querySelector("img").dataset.src || tr.querySelector("img").src;

            document.getElementById("cropImage").src = img;

            let modal = new bootstrap.Modal(document.getElementById("cropModal"));
            modal.show();

            if(cropper) cropper.destroy();
            cropper = new Cropper(document.getElementById("cropImage"), {
                aspectRatio: 190/150,
                viewMode: 1
            });
        };
    });
}

document.getElementById("saveCropBtn").addEventListener("click", function(){
    let canvas = cropper.getCroppedCanvas({ width:190, height:150 });

    canvas.toBlob(function(blob){
        let fd = new FormData();
        fd.append("file", blob, currentFile);

        fetch("core/save-image.php", { method:"POST", body:fd })
        .then(r => r.text())
        .then(t => {
            alert(t);
            location.reload();
        });
    });
});

// Load first batch
loadImages();
</script>
