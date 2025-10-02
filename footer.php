<?php include_once('logbook.php'); ?>


<!-- Footer -->
<footer class="content-footer footer bg-footer-theme">
    <div class="container-xxl">
        <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
            <div class="mb-2 mb-md-0">
                &#169; <span id="footer-year"></span>
                <script>
                    document.querySelector("#footer-year").textContent = new Date().getFullYear();
                </script>
                , made with ❤️ by <a href="https://themeselection.com/" target="_blank"
                    class="footer-link fw-medium">ThemeSelection</a>
            </div>
            <div class="d-none d-lg-inline-block">
                <a href="https://themeselection.com/item/category/admin-templates/" target="_blank"
                    class="footer-link me-4">Admin Templates</a>

                <a href="https://themeselection.com/license/" class="footer-link me-4" target="_blank">License</a>

                <a href="https://themeselection.com/item/category/bootstrap-templates/" target="_blank"
                    class="footer-link me-4">Bootstrap Dashboard</a>
                <a href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/documentation/"
                    target="_blank" class="footer-link me-4">Documentation</a>


                <a href="https://themeselection.com/support/" target="_blank"
                    class="footer-link d-none d-sm-inline-block">Support</a>

            </div>
        </div>
    </div>
</footer>
<!-- / Footer -->


<div class="content-backdrop fade"></div>
</div>


</div>
</div>

<!-- Overlay -->
<div class="layout-overlay layout-menu-toggle"></div>

<!-- Drag Target Area To SlideIn Menu On Small Screens -->
<div class="drag-target"></div>

</div>
<!-- / Layout wrapper -->

<div class="buy-now">
    <a href="https://themeselection.com/item/materio-dashboard-pro-bootstrap/" target="_blank"
        class="btn btn-danger btn-buy-now">Buy Now</a>
</div>

<!-- Core JS -->
<!-- build:js assets/vendor/js/theme.js  -->
<script src="assets/vendor/libs/jquery/jquery.js"></script>
<script src="assets/vendor/libs/popper/popper.js"></script>
<script src="assets/vendor/js/bootstrap.js"></script>
<script src="assets/vendor/libs/node-waves/node-waves.js"></script>
<script src="assets/vendor/libs/pickr/pickr.js"></script>
<script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="assets/vendor/libs/hammer/hammer.js"></script>
<script src="assets/vendor/libs/i18n/i18n.js"></script>
<script src="assets/vendor/js/menu.js"></script>

<!-- endbuild -->

<!-- Vendors JS -->
<script src="assets/vendor/libs/apex-charts/apexcharts.js"></script>
<script src="assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>

<!-- Main JS -->
<script src="assets/js/main.js"></script>
<!-- Page JS -->
<script src="assets/js/app-logistics-dashboard.js"></script>
<script src="assets/js/pages-profile-user.js"></script>
<script src="dev-log/dev-timeline.js"></script> <!-- আমাদের কাস্টম JS -->
<script src="dev-log/dev-loader.js"></script>


<script>

</script>

<script>
    // Helpers অবজেক্ট ধরে নিচ্ছি আগের মতোই
    let Helpers = {
        setColor: function (color, save = false) {
            if (!color) return;
            document.documentElement.style.setProperty('--bs-primary', color);

            // if (save) {
            //     localStorage.setItem('bs-primary-color', color);
            // }
            // console.log('Primary color set to:', color);
        }
    };

    // পেজ লোড হওয়ার সময় লোকাল স্টোরেজ থেকে কালার বের করে সেট করা
    window.addEventListener('DOMContentLoaded', () => {


        const savedColor = localStorage.getItem('templateCustomizer-vertical-menu-template--Color');
        if (savedColor) {
            Helpers.setColor(savedColor, false); // save=false, কারণ ইতিমধ্যেই লোকাল স্টোরেজে আছে
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".custom-option input[type='radio']").forEach((el) => {
            el.addEventListener("change", function () {
                const selectedColor = this.dataset.color;
                if (selectedColor) {
                    console.log("Color Changed:", selectedColor);
                    Helpers.setColor(selectedColor, true);
                    // চাইলে লোকাল স্টোরেজেও সংরক্ষণ
                    // localStorage.setItem('templateCustomizer-vertical-menu-template--Color', selectedColor);
                }
            });
        });


        // Pickr ইনস্ট্যান্স তৈরি
        document.querySelectorAll(".pickr").forEach(pickrDiv => {
            const button = pickrDiv.querySelector(".pcr-button");
            button.addEventListener("change", function () {
                const selectedColor = this.dataset.color;
                if (selectedColor) {
                    console.log("Color Changed:", selectedColor);
                    Helpers.setColor(selectedColor, true);
                }
            });
        });




    });


</script>


<script>
    // Track Click User Interaction
    document.addEventListener("DOMContentLoaded", () => {
        document.addEventListener("click", e => {
            let target = e.target.closest("button, a, [data-action]");
            if (!target) return; // অন্য কিছু হলে বেরিয়ে যান

            let action = target.dataset.action || target.innerText.trim();
            let page = window.location.pathname;

            fetch("core/track_action.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    email: "<?php echo $_SESSION['user_email']; ?>",
                    page: page,
                    action: action,
                    timestamp: new Date().toISOString()
                })
            })
                .then(res => res.text())
                .then(data => console.log("Track Response:", data))
                .catch(err => console.error("Track Error:", err));
        });
    });
</script>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function () {
        $("#sidebar_admin").load("dev-log/timeline.php");
    });
</script>