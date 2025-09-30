<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Feedback Modal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .star-rating i {
            font-size: 1.5rem;
            color: #ccc;
            cursor: pointer;
        }

        .star-rating i.active {
            color: gold;
        }
    </style>
</head>

<body>
    <div class="container text-center mt-5">
        <!-- Trigger button -->
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#feedbackModal">
            ফিডব্যাক দিন
        </button>
    </div>




    <div class="row">
        <div class="col">Complain

        </div>
        <div class="col">Opinion</div>
        <div class="col">Comments</div>
        <div class="col">Feedback
            <div class="ul">
                <div class="li">UI/UX</div>
                <div class="li">Features</div>
                <div class="li">Functionality</div>
                <div class="li">Ratings</div>
                <div class="li">Bug Free</div>
            </div>



        </div>
        <div class="col">Request</div>
        <div class="col">Bug Hunter</div>

    </div>


    <!-- Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="feedbackModalLabel">ফিডব্যাক / অভিযোগ / রেটিং</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>










                <form id="feedbackForm">
                    <div class="modal-body">

                        <!-- Rating -->
                        <label class="form-label">আপনার রেটিং দিন:</label>
                        <div class="star-rating mb-3">
                            <i class="bi bi-star" data-value="1"></i>
                            <i class="bi bi-star" data-value="2"></i>
                            <i class="bi bi-star" data-value="3"></i>
                            <i class="bi bi-star" data-value="4"></i>
                            <i class="bi bi-star" data-value="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="rating" value="0">

                        <!-- Feedback -->
                        <div class="mb-3">
                            <label for="feedback" class="form-label">আপনার মতামত লিখুন</label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="3" required></textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
                        <button type="submit" class="btn btn-primary">সাবমিট</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Bootstrap & Icons -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <script>
        // ⭐ Rating select
        document.querySelectorAll(".star-rating i").forEach(star => {
            star.addEventListener("click", function () {
                let value = this.getAttribute("data-value");
                document.getElementById("rating").value = value;
                document.querySelectorAll(".star-rating i").forEach(s => s.classList.remove("active", "bi-star-fill"));
                for (let i = 0; i < value; i++) {
                    document.querySelectorAll(".star-rating i")[i].classList.add("active", "bi-star-fill");
                }
            });
        });

        // ফর্ম সাবমিশন
        document.getElementById("feedbackForm").addEventListener("submit", function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            fetch("core/save_feedback.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.text())
                .then(data => {
                    if (data === "success") {
                        alert("✅ ধন্যবাদ! আপনার ফিডব্যাক সংরক্ষণ করা হয়েছে।");
                        document.getElementById("feedbackForm").reset();
                        document.getElementById("rating").value = 0;
                        document.querySelectorAll(".star-rating i").forEach(s => s.classList.remove("active", "bi-star-fill"));
                    } else {
                        alert("❌ সমস্যা হয়েছে: " + data);
                    }
                })
                .catch(err => console.error(err));
        });
    </script>
</body>

</html>