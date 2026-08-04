

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


<style>
    .ai-guide {
        position: fixed;
        bottom: 28px;
        right: 28px;
        display: flex;
        align-items: flex-end;
        gap: 14px;
        z-index: 99999;
        transition: all .4s ease;
    }

    .hidden {
        opacity: 0;
        transform: translateY(40px);
        pointer-events: none;
    }

    /* Avatar */
    .ai-avatar {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        backdrop-filter: blur(10px);
        background: transparent;
        box-shadow:
            0 10px 30px rgba(0, 0, 0, .15),
            inset 0 0 0 1px rgba(255, 255, 255, .2);
        display: flex;
        align-items: center;
        justify-content: center;
        animation: float 4s ease-in-out infinite;
    }


    .ai-avatar {

        overflow: hidden;
        /* 👈 এটা জরুরি */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ai-avatar img,
    .ai-avatar lottie-player {
        width: 100%;
        height: 100%;
    }

    .ai-avatar lottie-player {
        width: 100%;
        height: 100%;
        display: block;
    }

    /* Bubble */
    .ai-bubble {
        max-width: 240px;
        padding: 14px 16px;
        border-radius: 18px;
        font-size: 14px;
        line-height: 1.5;
        backdrop-filter: blur(14px);
        background: rgba(255, 255, 255, .25);
        box-shadow:
            0 10px 30px rgba(0, 0, 0, .12),
            inset 0 0 0 1px rgba(255, 255, 255, .35);
        position: relative;
        animation: bubbleIn .35s ease;
    }

    .ai-bubble:after {
        content: "";
        position: absolute;
        right: -8px;
        bottom: 18px;
        width: 16px;
        height: 16px;
        background: rgba(255, 255, 255, .25);
        transform: rotate(45deg);
        border-radius: 3px;
    }

    .introjs-tooltip {
        z-index: 100000 !important;
    }

    .introjs-overlay {
        z-index: 99998 !important;
    }

    .ai-guide {
        pointer-events: none;
    }




    @keyframes float {
        0% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-12px);
        }

        100% {
            transform: translateY(0);
        }
    }

    @keyframes bubbleIn {
        from {
            transform: translateY(10px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .ai-exit {
        animation: flyOut .9s cubic-bezier(.22, .61, .36, 1) forwards;
    }

    @keyframes flyOut {
        0% {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        100% {
            transform: translateY(-180px) scale(.4);
            opacity: 0;
        }
    }




    .swal2-title {
        line-height: 1.0;
        font-size: 20px;
    }
</style>









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


<!-- https://assets2.lottiefiles.com/packages/lf20_ydo1amjm.json -->
<!-- https://assets4.lottiefiles.com/packages/lf20_3rwasyjy.json -->
<!-- Floating AI Guide -->
<div id="aiGuide" class="ai-guide hidden">
    <div class="ai-bubble" id="aiBubble"></div>

    <div class="ai-avatar">
        <lottie-player src="https://assets4.lottiefiles.com/packages/lf20_1pxqjqps.json" background="transparent"
            speed="1" autoplay loop>
        </lottie-player>
    </div>
</div>

<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>










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








<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000"></div>


<!-- Core JS -->

<script src="../assets/vendor/libs/popper/popper.js"></script>

<script src="../assets/vendor/js/menu.js"></script>
<script src="../assets/vendor/libs/notiflix/notiflix.js"></script>
<script src="../assets/js/forms-editors.js"></script>
   

<script src="../assets/js/app-logistics-dashboard.js"></script>
<script src="../assets/js/extended-ui-tour.js"></script>
<script src="../assets/js/pages-profile-user.js"></script>
<script src="../dev-log/dev-timeline.js"></script> <!-- আমাদের কাস্টম JS -->
<script src="../dev-log/dev-loader.js"></script>
<script src="../assets/js/app-chat.js"></script>
<script src="../assets/js/cards-action.js"></script>
<script src="../assets/js/notifications.js"></script>
<!-- <script src="../assets/js/extended-ui-media-player.js"></script> -->
<script src="../assets/js/pages-auth-multisteps.js"></script>
<script src="../assets/js/extended-ui-treeview.js"></script>


<!-- <script src="../assets/js/ui-toasts.js"></script> -->

<script src="../ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
<script src="../assets/js/app-academy-dashboard.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script src="https://cdn.jsdelivr.net/npm/@algolia/autocomplete-js"></script>
<script src="https://cdn.jsdelivr.net/npm/perfect-scrollbar"></script>



<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css">

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/locales-all.min.js"></script>


<script src="../assets/js/eimbox.js"></script>

<!-- Main JS -->
<script src="../assets/js/main.js"></script>



<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>
<script src="https://unpkg.com/intro.js/minified/intro.min.js"></script>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<!-- Custom JS -->

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">



