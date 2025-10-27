<style>
    #themeToggleBtn {
        position: fixed;
        bottom: 40px;
        right: 45px;
        z-index: 1000;
    }
</style>


<button id="themeToggleBtn" class="btn btn-outline-primary">
    Toggle Theme
</button>


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

<script src="assets/js/pages-auth.js"></script>
<script src="assets/js/pages-auth-two-steps.js"></script>
<script src="assets/js/app-logistics-dashboard.js"></script>
<script src="assets/js/pages-auth-multisteps.js"></script>



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
                    email: "<?php echo $_SESSION['user_email'] ?? ''; ?>",
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



<script>
    document.addEventListener("DOMContentLoaded", function () {
        const key = "templateCustomizer-vertical-menu-template--Theme";
        const btn = document.getElementById("themeToggleBtn");
        const currentTheme = localStorage.getItem(key) || "light";
        updateButtonText(currentTheme);

        btn.addEventListener("click", function () {
            const current = localStorage.getItem(key) || "light";
            const next = current === "light" ? "dark" : "light";
            localStorage.setItem(key, next);
            updateButtonText(next);
            window.location.reload();
        });

        function updateButtonText(theme) {
            btn.innerHTML = theme === "light"
                ? '<i class="ri-moon-line me-1"></i> Dark Mode'
                : '<i class="ri-sun-line me-1"></i> Light Mode';
        }
    });
</script>

<!-- ----------------------------------- -->
<script></script>
<!-- ----------------------------------- -->

</body>

</html>