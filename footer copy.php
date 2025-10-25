<?php include_once('dev-log/feedback.php'); ?>
<?php include_once('logbook.php'); ?>


<!-- Footer -->
<footer class="content-footer footer bg-footer-theme">
    <div class="container-xxl">
        <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
            <div class="mb-2 mb-md-0">


                <button onclick="requestPermission(101)">Enable Notifications</button>

                <input id="max-limit" type="text" value="<?php echo $_SESSION['max_limit']; ?>" />


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

<!-- <div class="buy-now">
    <a href="https://themeselection.com/item/materio-dashboard-pro-bootstrap/" target="_blank"
        class="btn btn-danger btn-buy-now">Buy Now</a>
</div> -->

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
<!-- Vendors JS -->
<script src="assets/vendor/libs/notiflix/notiflix.js"></script>
<script src="assets/vendor/libs/sortablejs/sortable.js"></script>


<!-- Main JS -->
<script src="assets/js/main.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<!-- Page JS -->
<script src="assets/js/app-logistics-dashboard.js"></script>
<script src="assets/js/extended-ui-tour.js"></script>
<script src="assets/js/pages-profile-user.js"></script>
<script src="dev-log/dev-timeline.js"></script> <!-- আমাদের কাস্টম JS -->
<script src="dev-log/dev-loader.js"></script>
<script src="assets/js/app-chat.js"></script>
<script src="assets/js/cards-actions.js"></script>




<!-- <script type="module" src="js/firebase.js"></script>
<script type="module" src="js/app.js"></script>
<script type="module" src="./js/app.js"></script> -->

<script type="module" src="./js/app.js"></script>


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
            let target = e.target.closest("button, a, input, [data-action]");
            if (!target) return; // অন্য কিছু হলে বেরিয়ে যান
            if (target.dataset.notrack) return;

            let action = target.dataset.feature || target.dataset.action || target.innerText.trim() || target.value;
            let point = target.dataset.point || 0;
            let page = window.location.pathname;

            fetch("core/track_action.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    email: "<?php echo $_SESSION['user_email']; ?>",
                    page: page,
                    action: action,
                    point: point,
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
        const featureElements = document.querySelectorAll('[data-feature]');
        const features = Array.from(featureElements).map(el => el.dataset.feature);
        // document.getElementById("page_features_list").innerHTML = features;
        console.log("Features on page:", features);
        // $("#sidebar_admin").load("dev-log/timeline.php?fl=" + features);
        $("#sidebar_admin").load(
            "dev-log/timeline.php?fl=" + encodeURIComponent(features.join(",")) +
            "&pm=<?= urlencode($_SESSION['permission_message']) ?>"
        );
    });



</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        let maxTime = document.getElementById("max-limit")?.value; // মিনিটে
        // maxTime /= 10;
        if (maxTime > 0) {
            let timeLeft = maxTime * 60; // সেকেন্ডে
            console.log("Timer started for", maxTime, "minutes");

            // 🟢 গোলাকার টাইমার div তৈরি
            const timerDiv = document.createElement("div");
            timerDiv.id = "circularTimer";
            timerDiv.innerHTML = `
            <svg viewBox="0 0 100 100" width="90" height="90">
                <circle cx="50" cy="50" r="45" stroke="#333" stroke-width="8" fill="none"></circle>
                <circle id="progressCircle" cx="50" cy="50" r="45"
                        stroke="#28a745" stroke-width="8" fill="none"
                        stroke-linecap="round"
                        transform="rotate(-90 50 50)"
                        stroke-dasharray="283" stroke-dashoffset="0"></circle>
                <text id="timerText" x="50" y="55" text-anchor="middle" fill="#fff"
                      font-size="16" font-family="monospace">--:--</text>
            </svg>
        `;

            // 🧭 স্টাইলিং
            Object.assign(timerDiv.style, {
                position: "fixed",
                bottom: "20px",
                right: "20px",
                background: "rgba(0,0,0,0.3)",
                borderRadius: "50%",
                padding: "10px",
                display: "flex",
                justifyContent: "center",
                alignItems: "center",
                zIndex: "9999999",
                boxShadow: "0 0 10px rgba(0,0,0,0.3)"
            });
            document.body.appendChild(timerDiv);

            const circle = document.getElementById("progressCircle");
            const text = document.getElementById("timerText");
            const radius = 45;
            const circumference = 2 * Math.PI * radius;
            circle.style.strokeDasharray = circumference;

            updateTimer();

            const interval = setInterval(() => {
                timeLeft--;
                if (timeLeft <= 0) {
                    clearInterval(interval);
                    updateTimer();
                    showPopup();
                } else {
                    updateTimer();
                }
            }, 1000);

            // 🕐 সময় আপডেট
            function updateTimer() {
                const percent = timeLeft / (maxTime * 60);
                const offset = circumference * (1 - percent);
                circle.style.strokeDashoffset = offset;

                const m = Math.floor(timeLeft / 60);
                const s = timeLeft % 60;
                text.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;

                // ⏳ রঙ পরিবর্তন সময় অনুযায়ী
                if (percent < 0.25) circle.style.stroke = "#dc3545"; // লাল
                else if (percent < 0.5) circle.style.stroke = "#ffc107"; // হলুদ
                else circle.style.stroke = "#28a745"; // সবুজ
            }

            // ⏰ সময় শেষ হলে popup
            function showPopup() {
                const popup = document.createElement("div");
                popup.style.position = "fixed";
                popup.style.top = "0";
                popup.style.left = "0";
                popup.style.width = "100%";
                popup.style.height = "100%";
                popup.style.background = "rgba(0,0,0, 0.7)";
                popup.style.display = "flex";
                popup.style.justifyContent = "center";
                popup.style.alignItems = "center";
                popup.style.zIndex = "1000000";
                popup.innerHTML = `
                <div style="padding:20px 30px; border-radius:10px; text-align:center;  width:500px; height:300px;
                            box-shadow:0 0 10px rgba(0,0,0,0.1);"  class="text-danger border border-primary bg-opacity-10 d-flex  align-items-center justify-content-center bg-secondary" >
                    <div><h4>⏰ সময় শেষ!</h4>
                    <p>আপনার সর্বোচ্চ অবস্থান সময় শেষ হয়েছে।</p>
                    <button id="reloadBtn"
                        style="background:#007bff;color:#fff; padding:8px 14px;
                               border-radius:6px;cursor:pointer;">রিলোড করুন</button>
                </div></div>`;
                document.body.appendChild(popup);
                document.getElementById("reloadBtn").addEventListener("click", () => location.reload());
            }
        }
    });
</script>


<script>
    document.getElementById("myform").addEventListener("submit", function (e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const table = form.getAttribute("data-table");

        // 🧹 data-ignore="1" থাকা ইনপুট বাদ দেওয়া
        form.querySelectorAll("[data-ignore]").forEach(el => {
            formData.delete(el.name);
        });

        formData.append("table", table); // টেবিল নাম পাঠানো

        fetch("backend/submit-my-form.php", {
            method: "POST",
            body: formData
        })
            .then(res => res.text())
            .then(data => {
                document.getElementById("response").innerHTML = data;
                form.reset();
                form.querySelector('[name="id"]').value = "0"; // id reset
            })
            .catch(err => {
                document.getElementById("response").innerHTML =
                    "<div class='alert alert-danger'>❌ ত্রুটি ঘটেছে!</div>";
            });
    });
</script>





<script>
    function initPopovers() {
    const popoverList = document.querySelectorAll('[data-bs-toggle="popover"]');
    popoverList.forEach(el => {
        if (!el._popoverInitialized) {
            new bootstrap.Popover(el, {
                container: 'body',
                trigger: 'hover focus',
                html: true
            });
            el._popoverInitialized = true;
        }
    });
}

// DOM Ready
document.addEventListener("DOMContentLoaded", initPopovers);

// Dropdown show এর সময় re-init
document.addEventListener('shown.bs.dropdown', initPopovers);

</script>