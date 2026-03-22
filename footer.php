<?php
include_once('dev-log/feedback.php');
include_once('core/page_status_access.php');


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



    #idleProgressContainer {
        /* position: fixed; */
        /* bottom: 10px; */
        /* right: 10px; */
        /* width: 100%; */
        height: 3px;
        background: #ddd;
        border-radius: 5px;
        overflow: hidden;
        z-index: 99999;
    }

    #idleProgressBar {
        width: 100%;
        height: 100%;
        background: linear-gradient(to right, #28a745, #ffc107, #dc3545);
        transition: width 1s linear;
    }

    #idleTimeText {
        position: absolute;
        top: -20px;
        right: 0;
        font-size: 12px;
        color: #333;
        background: #fff;
        padding: 2px 4px;
        border-radius: 3px;
    }

    .tree-node {
        cursor: pointer;
        padding: 4px 6px;
    }

    .tree-node.selected {
        font-weight: bold;
        background: #e9f2ff;
        border-left: 3px solid #0d6efd;
    }

    .tree-node.disabled {
        pointer-events: none;
        opacity: 0.6;
    }
</style>


<input type="hidden" id="selectedTree">

<!-- ------------------ LIMIT MONITOR PANEL ------------------------------ -->
<style>
    .limit-monitor {
        position: fixed;
        bottom: 15px;
        left: 15px;
        width: 260px;
        background: #fff;
        border-radius: 10px;
        padding: 12px;
        box-shadow: 0 0 15px rgba(0, 0, 0, .15);
        font-size: 12px;
        z-index: 99999;
    }

    .lm-title {
        font-weight: 600;
        margin-bottom: 6px;
        color: #0d6efd;
    }

    .lm-item {
        margin-bottom: 8px;
    }

    .lm-label {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        margin-bottom: 3px;
    }

    .progress {
        height: 6px;
        border-radius: 5px;
    }

    .lm-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }

    .lm-close {
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        color: #999;
    }

    .lm-close:hover {
        color: #dc3545;
    }
</style>

<?php
$monitorPanel = false;
if ($monitorPanel === true) { ?>

    <div class="limit-monitor" id="limitMonitor">

        <div class="lm-header">
            <span class="lm-title">Usage Monitor</span>
            <span class="lm-close" id="closeLimit">×</span>
        </div>

        <!-- Page Stay -->
        <div class="lm-item">
            <div class="lm-label">
                Page Time
                <span><?= gmdate("H:i:s", $totalDurationPage) ?> / <?= gmdate("H:i:s", $total_time_limit * 60) ?></span>
            </div>
            <div class="progress">
                <div class="progress-bar <?= $pagePercent >= 100 ? 'bg-danger' : 'bg-warning' ?>"
                    style="width:<?= $pagePercent ?>%"></div>
            </div>
        </div>

        <!-- Total Time -->
        <div class="lm-item">
            <div class="lm-label">
                Total Time
                <span><?= gmdate("i:s", $totalDurationAll) ?> / <?= gmdate("i:s", $total_time_limit_all * 60) ?></span>
            </div>
            <div class="progress">
                <div class="progress-bar <?= $totalPercent >= 100 ? 'bg-danger' : 'bg-success' ?>"
                    style="width:<?= $totalPercent ?>%"></div>
            </div>
        </div>




        <!-- Access Count -->
        <div class="lm-item">
            <div class="lm-label">
                Access Count
                <span><?= $totalRecordsPage ?> / <?= $access_count_limit ?></span>
            </div>
            <div class="progress">
                <div class="progress-bar <?= $accessPercent >= 100 ? 'bg-danger' : 'bg-info' ?>"
                    style="width:<?= $accessPercent ?>%"></div>
            </div>
        </div>


    </div>
<?php } ?>

<!-- ------------------ LIMIT MONITOR PANEL ------------------------------ -->

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notice-title">Static Backdrop Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="notice-body">
                Modal body content goes here.
            </div>
            <div class="modal-footer" id="notice-footer">
                <button type="button" class="btn btn-dark" onclick="goback();" data-bs-dismiss="modal">Go Back
                    Please</button>
                <button type="button" id="proceed-button" class="btn btn-danger" onclick="proceed();"
                    data-bs-dismiss="modal">Yes,
                    Proceed</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>








<footer>
    <div class="footer  bg-light" id="extend-footer">

        <div id="idleProgressContainer">
            <div id="idleProgressBar"></div>
            <span id="idleTimeText">ffffff</span>
        </div>

        <div class="container-fluid container-p-x pt-3 pb-3">

            <div class="row">
                <div class="col-12">

                    <?php

                    echo '/' . $package_check . '/';
                    if ($package_check === true) {
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
                            <a href="changelog.php" class="footer-link d-block pb-2">Changelog</a>
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
                            <a href="package-pricing.php" class="footer-link d-block pb-2">Package & Pricing</a>
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
                    <label class="form-check-label" for="customCheck2"> Always Show </label>
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

                <button id="manualLock" class="btn btn-sm btn-outline-warning"> <i class="bi bi-lock-fill"></i>
                </button>

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

                <input id="max-limit" type="text" style="width:50px;"
                    value="<?php echo $_SESSION['max_limit'] ?? 0; ?>" />
                <input id="total-time-limit" type="text" style="width:50px;" value="<?php echo $total_time_limit; ?>" />
                <input id="entry-limit" type="text" style="width:50px;" value="<?php echo $entry_limit; ?>" />
                <input id="access-count-limit" type="text" style="width:50px;"
                    value="<?php echo $access_count_limit; ?>" />


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

<?php
$conn->close(); 
?>


<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000"></div>


<!-- Core JS -->

<script src="assets/vendor/libs/popper/popper.js"></script>

<script src="assets/vendor/js/menu.js"></script>
<script src="assets/vendor/libs/notiflix/notiflix.js"></script>
<script src="assets/js/forms-editors.js"></script>


<script src="assets/js/app-logistics-dashboard.js"></script>
<script src="assets/js/extended-ui-tour.js"></script>
<script src="assets/js/pages-profile-user.js"></script>
<script src="dev-log/dev-timeline.js"></script> <!-- আমাদের কাস্টম JS -->
<script src="dev-log/dev-loader.js"></script>
<script src="assets/js/app-chat.js"></script>
<script src="assets/js/cards-action.js"></script>
<script src="assets/js/notifications.js"></script>
<!-- <script src="assets/js/extended-ui-media-player.js"></script> -->
<script src="assets/js/pages-auth-multisteps.js"></script>
<script src="assets/js/extended-ui-treeview.js"></script>


<!-- <script src="assets/js/ui-toasts.js"></script> -->

<script src="ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
<script src="assets/js/app-academy-dashboard.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script src="https://cdn.jsdelivr.net/npm/@algolia/autocomplete-js"></script>
<script src="https://cdn.jsdelivr.net/npm/perfect-scrollbar"></script>



<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css">

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/locales-all.min.js"></script>


<script src="assets/js/eimbox.js"></script>

<!-- Main JS -->
<script src="assets/js/main.js"></script>



<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<!-- Custom JS -->

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">



<script>
    $(document).ready(function () {

        // page load এ একবার call
        $.post('ajax/page-access.php', {
            action: 'access', page: '<?= $currentFile ?>'
        });

        // প্রতি 15 সেকেন্ড পর stay update
        setInterval(function () {
            $.post('ajax/page-access.php', {
                action: 'stay', page: '<?= $currentFile ?>'
            });
        }, 15000);

    });

</script>



<script>
    function removeBackdrop() {
        let bd = document.getElementById('pageBackdrop');
        if (bd) bd.remove();
    }
    // Page load হলে
    window.addEventListener('load', function () {
        removeBackdrop();
    });

    // JS error হলে
    window.addEventListener('error', function () {
        removeBackdrop();
    });

    // Promise / async error হলে
    window.addEventListener('unhandledrejection', function () {
        removeBackdrop();
    });
</script>



<script>
    const monitor = document.getElementById('limitMonitor');
    const closeBtn = document.getElementById('closeLimit');

    if (monitor && closeBtn) {
        closeBtn.addEventListener('click', () => {
            monitor.style.display = 'none';
        });
    }

</script>

<script>$(document).ready(function () {
        let table = document.querySelector("table.data-table");

        if (table) {
            $('.data-table').DataTable({
                pageLength: 25,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        }
    });
</script>

<script>

    function setCookie(name, value, days = 30) {
        let d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = name + "=" + value + "; expires=" + d.toUTCString() + "; path=/";
    }

    setInterval(function () {

        // যেসব ক্লাসকে hide করতে চান
        const hideClasses = ['elmoro', 'Campaign'];

        hideClasses.forEach(cls => {
            document.querySelectorAll('.' + cls).forEach(el => {
                el.style.display = "none";
                el.style.visibility = "hidden";
                el.style.opacity = "0";
                el.remove();
            });
        });

    }, 300);
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>


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
        if (<?= $package_check ?> === false) {
            maxInput = 0;
        }
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
            const sccode = '<?php echo $sccode; ?>';
            if (sccode == '') sccode = 0;
            const page = '<?php echo $currentFile; ?>';
            fetch("core/track_action.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    email: "<?php echo $_SESSION['user_email'] ?? ''; ?>",
                    page: page, url: url, sccode: sccode, action: action, point: point, timestamp: new Date().toISOString()
                })
            })
                .then(res => res.text())
                .then(data => console.log("Track Response:", data))
                .catch(err => console.error(err));
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.backend-login').forEach(function (button) {
            button.addEventListener('click', function () {
                const email = this.dataset.mail; // data-mail থেকে email নাও

                alert(email);

                fetch('backend/backend-login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'email=' + encodeURIComponent(email)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            window.location.href = 'index.php';
                        } else {
                            alert(data.message || 'Login failed');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('AJAX request failed');
                    });
            });
        });
    });

</script>




<script>
    function loginuser(email) {
        alert(email);

        fetch('backend/backend-login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'email=' + encodeURIComponent(email)
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = 'index.php';
                } else {
                    alert(data.message || 'Login failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('AJAX request failed');
            });


    }
</script>




<script>

    let idleTime = 0;
    let idleLimit = 0;
    if (<?php echo $_SESSION['locktime']; ?> == 0) {
        idleLimit = 10;
    } else {
        idleLimit = <?php echo $_SESSION['locktime']; ?>;
    }
    const progressBar = document.getElementById('idleProgressBar');
    const timeText = document.getElementById('idleTimeText');

    // প্রতি সেকেন্ডে টাইম কাউন্ট হবে
    setInterval(function () {
        idleTime++;
        const remain = idleLimit - idleTime;
        const percent = (remain / idleLimit) * 100;

        // প্রগ্রেসবার আপডেট
        progressBar.style.width = percent + '%';
        timeText.textContent = remain + 's বাকি';

        // লক ট্রিগার
        if (idleTime >= idleLimit) {
            localStorage.setItem('screenLocked', '1');
            location.href = "lock.php";
        }
    }, 1000);

    // ইউজার নড়াচড়া করলে idle reset
    function resetIdle() {
        idleTime = 0;
        progressBar.style.width = '100%';
        timeText.textContent = idleLimit + 's বাকি';
    }

    ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(evt => {
        document.addEventListener(evt, resetIdle);
    });

</script>





<script>
    // Idle lock
    document.onmousemove = document.onkeypress = function () { idleTime = 0; }

    // Manual Lock Button
    document.getElementById('manualLock').addEventListener('click', function () {
        // বর্তমান পেজের নাম বের করা
        const currentPage = window.location.pathname.split('/').pop();

        // পেজ নাম POST মেথডে পাঠানো
        fetch('core/lock_manual.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'page=' + encodeURIComponent(currentPage)
        })
            .then(() => {
                localStorage.setItem('screenLocked', '1');
                location.href = "lock.php";
            })
            .catch(err => console.error('Error:', err));
    });


    // Check lock status on load
    if (localStorage.getItem('screenLocked') === '1') {
        location.href = 'lock.php';
    }
</script>



<script>
    let access_grant = <?php echo $access_grant ? 'true' : 'false'; ?>;

    if (access_grant === false) {

        let proceed_grant = <?php echo $proceed_grant ? 'true' : 'false'; ?>;

        let page_status = '<?php echo $page_status; ?>';
        let page_status_name = '<?php echo $page_status_names[$page_status]; ?>';
        let page_status_color = '<?php echo $page_status_colors[$page_status]; ?>';
        let en_text = '<?php echo $status_desc_en[$page_status]; ?>';
        let bn_text = '<?php echo $status_desc_bn[$page_status]; ?>';

        $('#notice-title').text('<?= $page_title; ?>');

        $('#notice-body').html(<?php
        $body = 'Dear ' . ($fullname ?? $usr) . ', ';
        $body .= '<br> You\'ve accessed this page <b>"' . $page_title . '"</b>, currently in <b>' . $page_status_names[$page_status] . '</b> development status.<br>';
        $body .= $status_desc_en[$page_status] . '<hr class="m-2 p-2 bd-danger">' . $status_desc_bn[$page_status];
        $body .= '<br>Please note that using this page may involve risks. Proceed at your own risk.<br>';
        $body .= 'Alternatively, close this modal and return to the previous page.<br>Thank You.';
        echo json_encode($body);
        ?>);

        $('#notice-body').css('color', page_status_color);

        if (proceed_grant === false) {
            document.getElementById('proceed-button').disabled = true;
        }

        var modalEl = document.getElementById('myModal');
        var myModal = new bootstrap.Modal(modalEl, {
            backdrop: 'static',
            keyboard: false
        });
        myModal.show();
    }

    function goback() {
        window.history.back();
    }
</script>


<script>
    $(document).ready(function () {

        let nodeTreeBlock = document.getElementById('nodeTreeModal');

        if (nodeTreeBlock) {
            let modal = new bootstrap.Modal(nodeTreeBlock);

            $('#openTree').on('click', function () {
                $('#treeRoot').html('');
                modal.show();
                loadNodes('slot', {}, $('#treeRoot'));
            });

        } else {
            console.log('nodeTreeModal এখনো DOM এ নাই');
        }

    });

</script>

<script>





    function loadNodes(type, context, container) {

        let chainInput = $('#chainInput').val();

        $.post('components/node-tree.php', {
            type: type,
            context: context
        }, function (res) {

            let data = JSON.parse(res);

            data.forEach(item => {

                let li = $('<li>');
                let node = $('<div class="tree-node">');
                let toggle = $('<span class="toggle">+</span>');
                let text = $('<span>').text(item.text);

                node.append(toggle).append(text);
                li.append(node);

                let children = $('<ul>').hide();
                li.append(children);

                node.on('click', function (e) {
                    e.stopPropagation();

                    if (node.hasClass('disabled')) return;

                    // remove previous selection (same level)
                    node.closest('ul').find('.tree-node').removeClass('selected');

                    // mark selected
                    node.addClass('selected');

                    let ctx = Object.assign({}, context);

                    /* ---- context mapping ---- */
                    if (type === 'slot') ctx.slot = item.text;
                    if (type === 'session') ctx.sessionyear = item.text;
                    if (type === 'exam') ctx.exam = item.text;
                    if (type === 'class') ctx.areaname = item.text;
                    if (type === 'section') ctx.subarea = item.text;
                    // if (type === 'subject') ctx.subject = item.text;


                    if (type === 'section' && chainInput.includes('subject')) {

                        // show right panel
                        $('#subjectColumn').removeClass('d-none');

                        // modal auto expand
                        $('#nodeTreeModal')
                            .removeClass('modal-lg')
                            .addClass('modal-xl');

                        // clear old subject
                        $('#subjectList').html('');

                        // load subject into right panel
                        loadSubjectList(ctx);

                        return; // stop tree expansion here
                    }



                    /* ---- nextType resolver ---- */
                    let nextType = null;

                    // helper (exact match এড়াতে চাইলে)
                    const hasExam = /\bexam\b/.test(chainInput);
                    const hasClass = /\bclass\b/.test(chainInput);
                    const hasSubject = /\bsubject\b/.test(chainInput);
                    const hasReload = /\breload\b/.test(chainInput);

                    if (type === 'slot') {

                        nextType = 'session';

                    } else if (type === 'session') {

                        if (hasClass) {
                            nextType = null;

                        } else if (hasExam) {
                            nextType = 'exam';

                        } else {
                            nextType = 'class';
                        }

                    } else if (type === 'exam') {

                        nextType = 'class';

                    } else if (type === 'class') {

                        nextType = 'section';

                    } else if (type === 'section') {

                        nextType = hasSubject ? 'subject' : null;
                    }


                    // disable this node after selection
                    node.addClass('disabled');

                    if (nextType) {
                        // load only once
                        if (children.children().length === 0) {
                            loadNodes(nextType, ctx, children);
                        }
                        node.closest('ul').find('ul').not(children).slideUp();
                        node.closest('ul').find('.toggle').text('+');
                        children.slideDown();
                        toggle.text('-');
                    } else {
                        finalizeSelection(ctx, item);
                        if (hasReload) {
                            window.location.reload();
                        }
                    }
                });


                container.append(li);


            });
        });

    }


    function finalizeSelection(ctx, item) {

        let selected = {
            slot: ctx.slot ?? null,
            session: ctx.sessionyear ?? null,
            exam: ctx.exam ?? null,
            class: ctx.areaname ?? null,
            section: ctx.subarea ?? null,
            subject: ctx.subject ?? null,
            final_text: item.text,
            final_id: item.id
        };

        if ($('#slot-main').length) {
            $('#slot-main').val(selected.slot);
            setCookie('chain-slot', selected.slot);
        }

        if ($('#session-main').length) {
            $('#session-main').val(selected.session);
            setCookie('chain-session', selected.session);
        }

        if ($('#exam-main').length) {
            $('#exam-main').val(selected.exam);
            setCookie('chain-exam', selected.exam);
        }

        if ($('#class-main').length) {
            $('#class-main').val(selected.class);
            setCookie('chain-class', selected.class);
        }

        if ($('#section-main').length) {
            $('#section-main').val(selected.section);
            setCookie('chain-section', selected.section);
        }

        if ($('#subject-main').length) {
            $('#subject-main').val(selected.subject);
            setCookie('chain-subject', selected.subject);
        }

        $('#selectedTree').val(JSON.stringify(selected));
        console.log(selected);
        // let nodeTreeBlock = document.getElementById('nodeTreeModal');
        // let modal = bootstrap.Modal(nodeTreeBlock);
        // modal.hide();
    }



    function loadSubjectList(ctx) {
        // alert(JSON.stringify(ctx));
        $.post('components/node-tree.php', {
            type: 'subject',
            context: ctx
        }, function (res) {

            let data = JSON.parse(res);
            let ul = $('#subjectList');

            ul.html('');

            data.forEach(item => {

                let li = $('<li class="list-group-item">')
                    .text(item.text)
                    .on('click', function () {

                        ul.find('.list-group-item').removeClass('active');
                        $(this).addClass('active');

                        ctx.subject = item.text;

                        finalizeSelection(ctx, item);
                    });

                ul.append(li);
            });
        });
    }

    $('#nodeTreeModal').on('hidden.bs.modal', function () {

        $('#subjectColumn').addClass('d-none');
        $('#subjectList').html('');

        $('#treeModalDialog')
            .removeClass('modal-xl')
            .addClass('modal-lg');
    });

</script>

<script>
    function addParams(params) {
        const url = new URL(window.location);
        Object.keys(params).forEach(key => {
            url.searchParams.set(key, params[key]);
        });
        history.pushState({}, '', url);
    }

    // usage
    // addParams({
    //     id: 10,
    //     class: 'Eight',
    //     session: 2026
    // });

    function removeParams(params) {
        const url = new URL(window.location.href);
        params.forEach(p => url.searchParams.delete(p));
        history.pushState({}, '', url);
    }

    // usage
    // removeParams(['id', 'session']);
</script>



<script>
    $(function () {
        let isAutoLoad = true; // 🔐 only for first load
        /* ===============================
           1️⃣ Cookie → Select value set
           =============================== */
        const cookieMap = {
            '#slot-main': <?= json_encode($_COOKIE['chain-slot'] ?? '') ?>,
            '#session-main': <?= json_encode($_COOKIE['chain-session'] ?? '') ?>,
            '#exam-main': <?= json_encode($_COOKIE['chain-exam'] ?? '') ?>,
            '#class-main': <?= json_encode($_COOKIE['chain-class'] ?? '') ?>,
            '#section-main': <?= json_encode($_COOKIE['chain-section'] ?? '') ?>,
            '#subject-main': <?= json_encode($_COOKIE['chain-subject'] ?? '') ?>,
            '#date-from-main': <?= json_encode($_COOKIE['chain-date-from'] ?? '') ?>,
            '#date-to-main': <?= json_encode($_COOKIE['chain-date-to'] ?? '') ?>
        };

        $.each(cookieMap, function (selector, value) {
            if ($(selector).length && value) {
                $(selector).val(value);
            }
        });

        /* ===============================
           2️⃣ Session → Class
           =============================== */


        function get_exam_list(slot, session) {
            // কুয়েরি প্যারামিটার তৈরি
            const params = new URLSearchParams({
                slot: slot,
                session: session
            });

            fetch(`payments/get-exam-list.php?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Exam List Received:", data);

                    // উদাহরণ: আইডি 'exam_id' নামের একটি ড্রপডাউন আপডেট করা
                    const examSelect = document.getElementById('exam-main');
                    if (examSelect) {
                        examSelect.innerHTML = '<option value="">Select Exam</option>';
                        data.forEach(exam => {
                            examSelect.innerHTML += `<option value="${exam}">${exam}</option>`;
                        });
                    }
                    $('#exam-main').val(cookieMap['#exam-main']);


                })
                .catch(error => console.error('Error fetching exam list:', error));
        }


        $('#session-main').on('change', function () {

            let session = $(this).val();
            let slot = $('#slot-main').val();
            if (!slot) return;

            if (!session) return;


            if ($('#exam-main').length) {
                console.log('Exam Trigger');
                get_exam_list(slot, session);


            }

            $('#class-main').html('<option value="">Loading...</option>');
            $('#section-main').html('<option value="">Select class first</option>');

            $.post('payments/get-class.php', { session }, function (res) {

                $('#class-main').html(res);

                // ✅ cookie only on first load
                if (isAutoLoad && cookieMap['#class-main']) {
                    $('#class-main').val(cookieMap['#class-main']).trigger('change');
                }
            });
        });

        /* ===============================
           3️⃣ Class → Section
           =============================== */
        $('#class-main').on('change', function () {

            let cls = $(this).val();
            if (!cls) return;

            let session = $('#session-main').val();
            if (!session) return;

            $('#section-main').html('<option value="">Loading...</option>');

            $.post('payments/get-sections.php', { cls, session }, function (res) {

                $('#section-main').html(res);

                // ✅ cookie only on first load
                if (isAutoLoad && cookieMap['#section-main']) {
                    $('#section-main').val(cookieMap['#section-main']);
                }

                isAutoLoad = false; // 🔓 auto-load finished
            });

            // 🔄 update cookie on manual change
            document.cookie = "chain-class=" + cls + "; path=/";
        });

        /* ===============================
           4️⃣ Section change → cookie save
           =============================== */
        $('#section-main').on('change', function () {
            document.cookie = "chain-section=" + $(this).val() + "; path=/";
        });

        $('#session-main').on('change', function () {
            document.cookie = "chain-session=" + $(this).val() + "; path=/";
        });

        $('#slot-main').on('change', function () {
            document.cookie = "chain-slot=" + $(this).val() + "; path=/";
        });

        $('#exam-main').on('change', function () {
            document.cookie = "chain-exam=" + $(this).val() + "; path=/";
        });

        $('#subject-main').on('change', function () {
            document.cookie = "chain-subject=" + $(this).val() + "; path=/";
        });

        $('#date-from-main').on('change', function () {
            document.cookie = "chain-date-from=" + $(this).val() + "; path=/";
        });

        $('#date-to-main').on('change', function () {
            document.cookie = "chain-date-to=" + $(this).val() + "; path=/";
        });

        /* ===============================
           5️⃣ Auto trigger only once
           =============================== */
        if ($('#session-main').val()) {
            $('#session-main').trigger('change');
        }

    });
</script>


<!-- ------------------------- last Function ------------------------------ -->

<script>
    function triggerAltCtrlV() {
        console.log('Trigger Ready');
        const e = new KeyboardEvent('keydown', {
            key: 'v',
            code: 'KeyV',
            ctrlKey: true,
            altKey: true,
            bubbles: true
        });
        document.dispatchEvent(e);

    }
</script>