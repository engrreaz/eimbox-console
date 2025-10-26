<?php
include_once('dev-log/feedback.php');



// Sample icons (DB থেকে আসবে)
$icons = [
    ['related_pages' => 'page1.php', 'nav_icon' => 'book', 'nav_title' => 'Books', 'status_name' => 'active'],
    ['related_pages' => 'page2.php', 'nav_icon' => 'calendar', 'nav_title' => 'Calendar', 'status_name' => 'pending'],
    ['related_pages' => 'page3.php', 'nav_icon' => 'gear', 'nav_title' => 'Settings', 'status_name' => 'active'],
];

$release_colors = [
    'active' => '#28a745',
    'pending' => '#ffc107',
    'inactive' => '#dc3545'
];
?>



<style>
    #extend-footer {
        max-height: 2px;
        overflow: hidden;
        transition: max-height 0.3s ease-in-out, padding 0.3s ease-in-out;
        padding: 0 1rem;
        /* start padding 0 */
    }

    #extend-footer.active {
        max-height: 300px;
        /* content height adjust করতে পারো */
        padding: 1rem;
    }

    .footer {
        transition: all 0.3s ease;
    }

    #mainFooter.fixed {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 10300;
    }
</style>


<footer>
    <div class="footer  bg-light" id="extend-footer">
        <div class="container-fluid container-p-x pt-3 pb-3">
            <div class="row">
                <div class="col-12">
                    <?php
                    if ($is_admin >= 4) {
                        include_once('logbook.php');
                    }
                    ?>
                </div>
            </div>



            <div class="row">
                <div class="col-12 col-sm-6 col-md-3 mb-6 mb-sm-0">
                    <h4 class="fw-bold mb-1">
                        <a href="index.php" target="_blank" class="footer-brand">EIMBox </a>
                    </h4>
                    <span class="mb-2">for a paperless institute</span>

                    <div class="social-icon d-flex flex-wrap gap-2 my-4">
                        <a href="javascript:void(0)" class="btn btn-icon btn-sm btn-facebook">
                            <i class="icon-base ri ri-facebook-circle-fill icon-20px"></i>
                        </a>
                        <a href="javascript:void(0)" class="btn btn-icon btn-sm btn-twitter">
                            <i class="icon-base ri ri-twitter-fill icon-20px"></i>
                        </a>
                        <a href="javascript:void(0)" class="btn btn-icon btn-sm btn-linkedin">
                            <i class="icon-base ri ri-linkedin-box-fill icon-20px"></i>
                        </a>
                        <a href="javascript:void(0)" class="btn btn-icon btn-sm btn-github">
                            <i class="icon-base ri ri-github-fill icon-20px"></i>
                        </a>
                    </div>
                    <p class="pt-6">
                        <script>
                            document.write(new Date().getFullYear());
                        </script>
                        ©
                        <a href="#" target="_blank" class="text-primary">EIMBox </a>
                    </p>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-6 mb-md-0">
                    <h5>Company</h5>
                    <ul class="list-unstyled">
                        <li>
                            <a href="javascript:void(0)" class="footer-link d-block pb-2">About</a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="footer-link d-block pb-2">Career <span
                                    class="badge bg-label-primary text-capitalize rounded-pill">We're hiring</span></a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="footer-link d-block pb-2">Blog</a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="footer-link d-block pb-2">Happy Institutes</a>
                        </li>

                    </ul>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-6 mb-sm-0">
                    <h5>Products</h5>
                    <ul class="list-unstyled">

                        <li>
                            <a href="javascript:void(0)" class="footer-link d-block pb-2">Dev Guide</a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="footer-link d-block pb-2">What's New</a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="footer-link d-block pb-2">API</a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="footer-link d-block pb-2">Documentation</a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="footer-link d-block pb-2">Changelog</a>
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <h5>Features</h5>
                    <ul class="list-unstyled">
                        <li>
                            <a href="javascript:void(0)" class="footer-link d-block pb-2">Submit a ticket</a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="footer-link d-block pb-2">Customization</a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="footer-link d-block pb-2">Support</a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="footer-link d-block pb-2">Current Offers</a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="footer-link d-block pb-2">Documetation Writer</a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="footer-link d-block pb-2">Package & Pricing</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row" style="height:50px;">

            </div>
        </div>
    </div>

    <div class="footer " id="mainFooter">
        <div
            class="container-fluid d-flex flex-md-row flex-column justify-content-between align-items-md-center gap-1 container-p-x py-1">
            <div>
                <a href="index.php" target="_blank" class="footer-brand fw-bold">EIMBox</a> ©
            </div>
            <div class="d-flex flex-column flex-sm-row gap-4">
                <div class="form-check footer-link mb-0 mt-2">
                    <input class="form-check-input" type="checkbox" value="" id="customCheck2" checked="checked" />
                    <label class="form-check-label" for="customCheck2"> Show Always </label>
                </div>
                <div class="dropdown dropup footer-link" hidden>
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Currency</button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="javascript:void(0);"> <i
                                class="icon-base ri ri-money-dollar-circle-line me-2"></i>USD</a>
                        <a class="dropdown-item" href="javascript:void(0);"> <i
                                class="icon-base ri ri-money-euro-circle-line me-2"></i>Euro</a>
                        <a class="dropdown-item" href="javascript:void(0);"> <i
                                class="icon-base ri ri-money-pound-circle-line me-2"></i>Pound</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="javascript:void(0);"> <i
                                class="icon-base ri ri-btc-line me-2"></i>Bitcoin</a>
                    </div>
                </div>
                <a href="logout.php" class="btn btn-sm btn-outline-danger">
                    <i class="icon-base ri ri-logout-box-r-line icon-xs me-1"></i>Logout</a>

                <a href="javascript:void(0)" class="btn btn-sm px-1" id="footer-button">
                    <i class="icon-base bi bi-chevron-double-up icon-xs me-1"></i></a>
            </div>
        </div>
    </div>
</footer>















<footer class="content-footer footer bg-footer-theme" hidden>
    <div class="container-xxl">
        <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
            <div class="mb-2 mb-md-0">
                <button onclick="requestPermission(101)">Enable Notifications</button>
                <input id="max-limit" type="text" value="<?php echo $_SESSION['max_limit'] ?? 0; ?>" />
                &#169; <span id="footer-year"></span>
                <script>document.querySelector("#footer-year").textContent = new Date().getFullYear();</script>
                , made with ❤️ by
                <a href="https://themeselection.com/" target="_blank" class="footer-link fw-medium">ThemeSelection</a>
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

<!-- Core JS -->
<script src="assets/vendor/libs/jquery/jquery.js"></script>
<script src="assets/vendor/libs/popper/popper.js"></script>
<script src="assets/vendor/js/bootstrap.js"></script>
<script src="assets/vendor/js/menu.js"></script>
<script src="assets/vendor/libs/notiflix/notiflix.js"></script>
<script src="assets/js/forms-editors.js"></script>
<script src="assets/js/cards-action.js"></script>

<script src="assets/js/app-logistics-dashboard.js"></script>
<script src="assets/js/extended-ui-tour.js"></script>
<script src="assets/js/pages-profile-user.js"></script>
<script src="dev-log/dev-timeline.js"></script> <!-- আমাদের কাস্টম JS -->
<script src="dev-log/dev-loader.js"></script>
<script src="assets/js/app-chat.js"></script>
<script src="assets/js/cards-actions.js"></script>
<script src="assets/js/notifications.js"></script>
<!-- <script src="assets/js/extended-ui-media-player.js"></script> -->
<script src="assets/js/pages-auth-multisteps.js"></script>



<!-- Main JS -->
<script src="assets/js/main.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<!-- Custom JS -->

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // ===========================
        // Helpers for Color
        // ===========================
        const Helpers = {
            setColor: function (color, save = false) {
                if (!color) return;
                document.documentElement.style.setProperty('--bs-primary', color);
                if (save) localStorage.setItem('templateCustomizer-vertical-menu-template--Color', color);
            }
        };

        // Load saved color
        const savedColor = localStorage.getItem('templateCustomizer-vertical-menu-template--Color');
        if (savedColor) Helpers.setColor(savedColor);

        // Radio / Pickr changes
        document.querySelectorAll(".custom-option input[type='radio']").forEach(el => {
            el.addEventListener("change", function () {
                const selectedColor = this.dataset.color;
                if (selectedColor) Helpers.setColor(selectedColor, true);
            });
        });

        document.querySelectorAll(".pickr").forEach(pickrDiv => {
            const button = pickrDiv.querySelector(".pcr-button");
            if (button) {
                button.addEventListener("change", function () {
                    const selectedColor = this.dataset.color;
                    if (selectedColor) Helpers.setColor(selectedColor, true);
                });
            }
        });

    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        // ===========================
        // Popover Initialization (Stable for dynamic content)
        // ===========================
        function initPopovers(parent = document) {
            const popoverList = parent.querySelectorAll('[data-bs-toggle="popover"]');
            popoverList.forEach(el => {
                if (!el._popoverInitialized) {
                    new bootstrap.Popover(el, { container: 'body', trigger: 'hover focus', html: true });
                    el._popoverInitialized = true;
                }
            });
        }
        initPopovers();
        document.querySelectorAll('.dropdown-shortcuts').forEach(dropdown => {
            dropdown.addEventListener('shown.bs.dropdown', function () { initPopovers(dropdown); });
        });

        // MutationObserver for dynamic elements
        const observer = new MutationObserver(mutations => {
            mutations.forEach(m => {
                m.addedNodes.forEach(node => {
                    if (node.nodeType === 1) initPopovers(node);
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // ===========================
        // Circular Timer
        // ===========================
        const maxInput = document.getElementById("max-limit");
        let maxTime = parseInt(maxInput?.value) || 0;
        if (maxTime > 0) {
            let timeLeft = maxTime * 60;
            const timerDiv = document.createElement("div");
            timerDiv.id = "circularTimer";
            timerDiv.innerHTML = `
            <svg viewBox="0 0 100 100" width="90" height="90">
                <circle cx="50" cy="50" r="45" stroke="#333" stroke-width="8" fill="none"></circle>
                <circle id="progressCircle" cx="50" cy="50" r="45"
                        stroke="#28a745" stroke-width="8" fill="none"
                        stroke-linecap="round" transform="rotate(-90 50 50)"
                        stroke-dasharray="283" stroke-dashoffset="0"></circle>
                <text id="timerText" x="50" y="55" text-anchor="middle" fill="#fff"
                      font-size="16" font-family="monospace">--:--</text>
            </svg>
        `;
            Object.assign(timerDiv.style, {
                position: "fixed", bottom: "20px", right: "20px", background: "rgba(0,0,0,0.3)",
                borderRadius: "50%", padding: "10px", display: "flex", justifyContent: "center",
                alignItems: "center", zIndex: "9999999", boxShadow: "0 0 10px rgba(0,0,0,0.3)"
            });
            document.body.appendChild(timerDiv);

            const circle = document.getElementById("progressCircle");
            const text = document.getElementById("timerText");
            const radius = 45, circumference = 2 * Math.PI * radius;
            circle.style.strokeDasharray = circumference;

            const interval = setInterval(() => {
                timeLeft--;
                if (timeLeft <= 0) { clearInterval(interval); updateTimer(); showPopup(); }
                else updateTimer();
            }, 1000);

            function updateTimer() {
                const percent = timeLeft / (maxTime * 60);
                circle.style.strokeDashoffset = circumference * (1 - percent);
                const m = Math.floor(timeLeft / 60), s = timeLeft % 60;
                text.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                circle.style.stroke = percent < 0.25 ? "#dc3545" : percent < 0.5 ? "#ffc107" : "#28a745";
            }

            function showPopup() {
                const popup = document.createElement("div");
                popup.style.position = "fixed"; popup.style.top = 0; popup.style.left = 0;
                popup.style.width = "100%"; popup.style.height = "100%"; popup.style.background = "rgba(0,0,0,0.7)";
                popup.style.display = "flex"; popup.style.justifyContent = "center"; popup.style.alignItems = "center";
                popup.style.zIndex = "1000000";
                popup.innerHTML = `<div style="padding:20px 30px; border-radius:10px; text-align:center; width:500px; height:300px;
                                    box-shadow:0 0 10px rgba(0,0,0,0.1);" class="text-danger border border-primary bg-opacity-10 d-flex align-items-center justify-content-center bg-secondary">
                                    <div>
                                        <h4>⏰ সময় শেষ!</h4>
                                        <p>আপনার সর্বোচ্চ অবস্থান সময় শেষ হয়েছে।</p>
                                        <button id="reloadBtn" style="background:#007bff;color:#fff; padding:8px 14px; border-radius:6px;cursor:pointer;">রিলোড করুন</button>
                                    </div>
                                 </div>`;
                document.body.appendChild(popup);
                document.getElementById("reloadBtn").addEventListener("click", () => location.reload());
            }
        }
    });
</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        // ===========================
        // Form Submit (data-ignore support)
        // ===========================
        const myForm = document.getElementById("myform");
        if (myForm) {
            myForm.addEventListener("submit", e => {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                form.querySelectorAll("[data-ignore]").forEach(el => formData.delete(el.name));
                formData.append("table", form.getAttribute("data-table"));
                fetch("backend/submit-my-form.php", { method: "POST", body: formData })
                    .then(res => res.text())
                    .then(data => {
                        document.getElementById("response").innerHTML = data;
                        form.reset();
                        form.querySelector('[name="id"]')?.setAttribute("value", "0");
                    })
                    .catch(err => {
                        document.getElementById("response").innerHTML = "<div class='alert alert-danger'>❌ ত্রুটি ঘটেছে!</div>";
                    });
            });
        }

    });
</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        // ===========================
        // Track User Interaction
        // ===========================
        document.addEventListener("click", e => {
            const target = e.target.closest("button, a, input, [data-action]");
            if (!target || target.dataset.notrack) return;
            const action = target.dataset.feature || target.dataset.action || target.innerText.trim() || target.value;
            const point = target.dataset.point || 0;
            const url = window.location.pathname;
            const sccode = <?php echo $sccode; ?>;
            const page = '<?php echo $currentFile; ?>';
            fetch("core/track_action.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    email: "<?php echo $_SESSION['user_email'] ?? ''; ?>",
                    page: page, url: url, sccode: sccode, action: action, point: point, timestamp: new Date().toISOString()
                })
            }).then(res => res.text()).then(data => console.log("Track Response:", data)).catch(err => console.error(err));
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // ===========================
        // Sidebar Features Load
        // ===========================
        $(function () {
            const features = Array.from(document.querySelectorAll('[data-feature]')).map(el => el.dataset.feature);
            $("#sidebar_admin").load("dev-log/timeline.php?fl=" + encodeURIComponent(features.join(",")) + "&pm=<?= urlencode($_SESSION['permission_message'] ?? '') ?>");
        });

    });
</script>

<!-- <script>
    document.addEventListener("DOMContentLoaded", function () {
        const popover_docker = document.getElementById('data-bs-featurs').innerHTML;
    });
</script> -->


<script>
    document.addEventListener("DOMContentLoaded", function () {
        // সব button এর popover initialize
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.forEach(function (el) {
            // check: prevent double init
            if (!el._popoverInitialized) {
                new bootstrap.Popover(el, {
                    trigger: 'hover',    // hover বা focus
                    placement: 'top',
                    html: true
                });
                el._popoverInitialized = true;
            }
        });
    });
</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        // ===========================
        // Debug Popovers
        // ===========================
        function debugPopovers() {
            const popoverList = document.querySelectorAll('[data-bs-toggle="popover"]');
            popoverList.forEach(el => {
                if (el._popoverInitialized) {
                    console.log("✅ Popover initialized for:", el, "content:", el.getAttribute('data-bs-content'));
                    el.style.outline = '';
                    el.style.backgroundColor = '';
                } else {
                    console.warn("⚠️ Popover NOT initialized for:", el);
                    el.style.outline = '2px solid red';
                    el.style.backgroundColor = 'rgba(255,0,0,0.1)';
                }
            });
        }
        debugPopovers();
        document.addEventListener('shown.bs.dropdown', debugPopovers);

    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        // // সবগুলো add বাটন ধরছি
        document.querySelectorAll('.add-remove').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation(); // parent link কাজ করবে না
                e.preventDefault();  // default behavior বন্ধ

                // alert('Ready for ID: ' + this.getAttribute('data-bs-crud'));

                let formData = new FormData();
                formData.append('id', this.getAttribute('data-bs-crud'));
                formData.append('action', 'add_shortcut');

                if (this.getAttribute('data-bs-crud') == 0) {
                    formData.append('user_email', '<?php echo $usr; ?>');
                    formData.append('sccode', '<?php echo $sccode; ?>');
                    formData.append('page_name', '<?php echo $currentFile; ?>');
                    formData.append('page_title', '<?php echo $page_title; ?>');
                    formData.append('page_icon', '<?php echo $page_icon; ?>');
                    formData.append('module', '<?php echo $cur_page_module; ?>');
                    formData.append('status', 'active');
                }

                fetch('core/save_shortcut.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(err => console.error('Fetch Error:', err));
            });
        });
    });
</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        const footerBtn = document.getElementById("footer-button");
        const extendFooter = document.getElementById("extend-footer");
        const icon = footerBtn.querySelector("i");

        footerBtn.addEventListener("click", function () {
            extendFooter.classList.toggle("active");

            // আইকন পরিবর্তন
            if (extendFooter.classList.contains("active")) {
                icon.classList.replace("bi-chevron-double-up", "bi-chevron-double-down");
            } else {
                icon.classList.replace("bi-chevron-double-down", "bi-chevron-double-up");
            }
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const footer = document.getElementById('mainFooter');
        const checkbox = document.getElementById('customCheck2');

        // লোকাল স্টোরেজ থেকে আগের মান পড়া
        let showFooter = localStorage.getItem('showFooter');

        // ডিফল্ট false
        if (showFooter === null) {
            showFooter = 'true';
            localStorage.setItem('showFooter', showFooter);
        }

        // প্রাথমিক অবস্থা সেট করা
        if (showFooter === 'true') {
            footer.classList.add('fixed');
            checkbox.checked = true;
        } else {
            footer.classList.remove('fixed');
            checkbox.checked = false;
        }

        // চেকবক্স পরিবর্তনে আচরণ
        checkbox.addEventListener('change', function () {
            if (checkbox.checked) {
                footer.classList.add('fixed');
                localStorage.setItem('showFooter', 'true');
            } else {
                footer.classList.remove('fixed');
                localStorage.setItem('showFooter', 'false');
            }
        });
    });
</script>

<script>
    (function () {
        let theme = localStorage.getItem("templateCustomizer-vertical-menu-template--Theme");
        if (theme && document.cookie.indexOf("site_theme=" + theme) === -1) {
            document.cookie = "site_theme=" + theme + "; path=/";
            // location.reload(); // একবার reload হবে, তারপর PHP থিম পাবে
        }
    })();
</script>