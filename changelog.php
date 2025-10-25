<?php require_once 'header.php'; ?>

<div class="row">
    <div class="col-xl-12">
        <div class="card h-100">
            <h5 class="card-header">
                <i class="bx bx-history me-2"></i> Change Log Timeline
            </h5>
            <div class="card-body">
                <ul class="timeline mb-0" id="timeline-container">
                    <!-- Timeline items will be loaded here via AJAX -->
                </ul>
                <div class="text-center mt-3">
                    <button class="btn btn-primary" id="loadMoreBtn" data-offset="0">
                        Load More
                    </button>

                </div>
                <div class="p-3"></div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const loadBtn = document.getElementById('loadMoreBtn');
        const container = document.getElementById('timeline-container');

        function loadTimeline(offset = 0) {
            fetch('core/changelog-ajax.php?offset=' + offset)
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === '') {
                        loadBtn.style.display = 'none'; // Hide button if no more records
                    } else {
                        container.insertAdjacentHTML('beforeend', data);
                        loadBtn.dataset.offset = parseInt(offset) + 20;
                    }
                })
                .catch(err => console.error(err));
        }

        // Initial load
        loadTimeline(0);

        // Load more click
        loadBtn.addEventListener('click', function () {
            const offset = this.dataset.offset;
            loadTimeline(offset);
        });
    });
</script>