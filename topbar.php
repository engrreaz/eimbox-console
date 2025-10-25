<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
    id="layout-navbar">

    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0   d-xl-none ">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="icon-base ri ri-menu-line icon-md"></i>
        </a>
    </div>


    <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
        <div class="me-3">
            <img src="<?php echo BASE_PATH . '/logo/' . $sccode; ?>.png"
                style="height:30px; width:30px; border-radius:50%;" />
        </div>
        <div class="me-8">
            <div class="m-0 p-0  fw-bold btn"><a href="index.php"><?php echo $_SESSION['scname']; ?></a></div>
            <div class="p-0 m-0 pt-1 text-muted fs-tiny "><?php echo $_SESSION['scaddress_top']; ?></div>

        </div>

        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item navbar-search-wrapper mb-0">
                <a class="nav-item nav-link search-toggler px-0" href="javascript:void(0);">
                    <span class="d-inline-block text-body-secondary fw-normal" id="autocomplete"></span>
                </a>
            </div>
        </div>

        <!-- /Search -->





        <ul class="navbar-nav flex-row align-items-center ms-md-auto">




            <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <i class="icon-base bi bi-translate icon-22px"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-language="en" data-text-direction="ltr"
                            data-notrack="true">
                            <span>English</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-language="fr"
                            data-text-direction="ltr">
                            <span>French</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-language="ar"
                            data-text-direction="rtl">
                            <span>Arabic</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-language="de"
                            data-text-direction="ltr">
                            <span>German</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ Language -->

            <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <i class="icon-base bi bi-file-earmark icon-22px"
                        style="color: <?php echo $release_colors[$page_status]; ?>"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item text-primary" href="#" data-bs-toggle="modal"
                            data-bs-target="#documentation">
                            <i class="icon-base bi bi-file-text icon-22px me-2"></i>
                            <span class="align-middle"> Documentation </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="dropdown-item " data-bs-toggle="modal" data-bs-target="#youtubeModal"
                            style="color: crimson;">
                            <i class="icon-base bi bi-youtube icon-22px me-2"></i>
                            <span class="align-middle">Watch Video</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#featuresModal"
                            style="color: <?php echo $release_colors[$page_status]; ?>">
                            <i class="icon-base bi bi-file-earmark icon-22px me-2"></i>
                            <span class="align-middle">
                                About This Page </span>
                        </a>



                    </li>



                </ul>
            </li>

            <!-- Style Switcher -->
            <li class="nav-item dropdown me-sm-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                    id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <i class="icon-base ri ri-sun-line icon-22px theme-icon-active"></i>
                    <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
                    <li>
                        <button type="button" class="dropdown-item align-items-center active"
                            data-bs-theme-value="light" aria-pressed="false">
                            <span> <i class="icon-base bi bi-brightness-high icon-md me-3"
                                    data-icon="sun-line"></i>Light</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark"
                            aria-pressed="true">
                            <span> <i class="icon-base bi bi-moon-stars icon-md me-3"
                                    data-icon="moon-clear-line"></i>Dark</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system"
                            aria-pressed="false">
                            <span> <i class="icon-base ri ri-computer-line icon-md me-3"
                                    data-icon="computer-line"></i>System</span>
                        </button>
                    </li>
                </ul>
            </li>
            <!-- / Style Switcher-->



            <?php include_once('core/shortcut-icon.php'); ?>
            <!-- Quick links -->

            <!-- Quick links -->

            <!-- Notification -->
            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
                <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                    href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                    aria-expanded="false">
                    <span class="position-relative">
                        <i class="icon-base bi bi-bell icon-22px"></i>
                        <span id="dotBadge"
                            class="badge rounded-pill bg-gray bg-danger badge-dot badge-notifications border"></span>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-0">
                    <li class="dropdown-menu-header border-bottom">
                        <div class="dropdown-header d-flex align-items-center py-3">
                            <h6 class="mb-0 me-auto">Notification</h6>
                            <div class="d-flex align-items-center h6 mb-0">
                                <span class="badge bg-label-primary rounded-pill me-2" id="notifCountBadge">
                                    0</span>
                                <a href="javascript:void(0)" class="dropdown-notifications-all p-2"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read">
                                    <i class="icon-base ri ri-mail-open-line text-heading"></i>
                                </a>
                            </div>
                        </div>
                    </li>
                    <li class="dropdown-notifications-list scrollable-container">
                        <ul id="notifList" aria-atomic="" class="list-group list-group-flush">
                        </ul>
                    </li>
                    <li class="border-top">
                        <div class="d-grid p-4">
                            <a class="btn btn-primary btn-sm d-flex h-px-34" href="notifications.php">
                                <small class="align-middle">View all notifications</small>
                            </a>
                        </div>
                    </li>
                </ul>
            </li>
            <!--/ Notification -->






            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="<?php echo $pth; ?>" alt="alt" class="rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end mt-3 py-2">
                    <li>
                        <a class="dropdown-item" style="min-block-size:auto;" href="#">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-2">
                                    <div class="avatar avatar-online">
                                        <img src="    <?php echo $pth; ?>" alt="alt"
                                            class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 small"><?php echo $fullname; ?></h6>
                                    <small class="text-body-secondary"><?php echo trim($usr, '%'); ?></small><br>
                                    <small class="text-body-info"><?php echo trim($userlevel, '%'); ?></small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" style="min-block-size:auto;" href="user-profile.php">
                            <i class="icon-base ri ri-user-3-line icon-22px me-2"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li>
                    <li hidden>
                        <a class="dropdown-item" style="min-block-size:auto;"
                            href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/html/vertical-menu-template/pages-account-settings-account.html">
                            <i class="icon-base ri ri-settings-4-line icon-22px me-2"></i>
                            <span class="align-middle">Settings</span>
                        </a>
                    </li>
                    <li hidden>
                        <a class="dropdown-item" style="min-block-size:auto;"
                            href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/html/vertical-menu-template/pages-account-settings-billing.html">
                            <span class="d-flex align-items-center align-middle">
                                <i class="flex-shrink-0 icon-base ri ri-file-text-line icon-22px me-2"></i>
                                <span class="flex-grow-1 align-middle">Billing</span>
                                <span
                                    class="flex-shrink-0 badge badge-center rounded-pill bg-danger h-px-20 d-flex align-items-center justify-content-center">4</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li hidden>
                        <a class="dropdown-item" style="min-block-size:auto;"
                            href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/html/vertical-menu-template/pages-pricing.html">
                            <i class="icon-base ri ri-money-dollar-circle-line icon-22px me-2"></i>
                            <span class="align-middle">Pricing</span>
                        </a>
                    </li>

                    <?php if (isset($_SESSION['_backup'])) { ?>
                        <li>
                            <a class="dropdown-item" style="min-block-size:auto;" href="core/back-log.php">
                                <i class="icon-base bi bi-box-arrow-right icon-22px me-2"></i>
                                <span class="align-middle text-danger fw-bolder"> Back Login </span>
                            </a>
                        </li>
                    <?php } ?>


                    <?php if ($is_admin > 4) { ?>
                        <li>
                            <a class="dropdown-item text-danger" style="min-block-size:auto;" href="admin-home.php">
                                <i class="icon-base bi bi-anthropic icon-22px me-2"></i>
                                <span class="align-middle  fw-bolder"> Admin Panel </span>
                            </a>
                        </li>
                    <?php } ?>

                    <li>
                        <div class="d-grid px-4 pt-2 pb-1">
                            <a class="btn btn-danger d-flex" href="logout.php" target="_blank">
                                <small class="align-middle">Logout</small>
                                <i class="icon-base ri ri-logout-box-r-line ms-2 icon-16px"></i>
                            </a>
                        </div>
                    </li>
                </ul>
            </li>
            <!--/ User -->

        </ul>
    </div>

</nav>



<div class="modal fade" id="youtubeModal" tabindex="-1" aria-labelledby="youtubeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="youtubeModalLabel">Video Tutorial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="ratio ratio-16x9">
                    <iframe id="youtubeIframe" src="https://www.youtube.com/embed/Cn4G2lZ_g2I"
                        title="YouTube video player" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade responsive" id="documentation" tabindex="-1" aria-labelledby="documentationLabel"
    aria-hidden="true" style="max-height:95vh;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title" id="documentationLabel">Documentation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body " style="overflow-y: auto;" ;>

                <!-- ducumentation page wrapper..... -->
                <?php


                if (!$documentation_data) {
                    echo "<div class='alert alert-warning'>Documentation not found.</div>";
                } else {
                    echo "<div class='text-center'>";
                    echo "<h5 class='text-primary p-0 m-0'> $page_title </h5>";
                    echo "<h6 class='text-secondary text-small p-0 m-0'> $currentFile </h6>";
                    echo "</div>";
                    foreach ($documentation_data as $doc) {



                        // 🧹 HTML Purifier config (safe but allows rich HTML)
                        $config = HTMLPurifier_Config::createDefault();

                        // iframe whitelist
                        $config->set('HTML.SafeIframe', true);
                        $config->set('URI.SafeIframeRegexp', '#^(https?:)?//(www\.youtube\.com/embed/|player\.vimeo\.com/video/)#');

                        // ✅ data:image/* allow করা
                        $config->set('URI.AllowedSchemes', array(
                            'http' => true,
                            'https' => true,
                            'data' => true
                        ));

                        // ✅ HTML allow list
                        $config->set('HTML.Allowed', 'p,br,b,strong,i,em,u,a[href|target],ul,ol,li,img[src|alt|width|height|style],h1,h2,h3,h4,h5,h6,table,tr,td,th,thead,tbody,pre,code,blockquote,iframe');
                        $config->set('CSS.AllowedProperties', ['width', 'height', 'float', 'margin', 'border', 'background', 'color']);

                        $purifier = new HTMLPurifier($config);

                        // 🔒 Safe render
                        $safe_html = $purifier->purify($doc['full_documentation']);

                        ?>

                        <style>
                            .documentation-content {
                                font-size: 16px;
                                line-height: 1.6;
                            }

                            .documentation-content img {
                                max-width: 100%;
                                border-radius: 6px;
                                margin: 10px 0;
                                display: block;
                            }

                            .documentation-content iframe {
                                max-width: 100%;
                                min-height: 300px;
                                border: none;
                            }

                            .documentation-content pre {
                                background: #f8f9fa;
                                padding: 10px;
                                border-radius: 4px;
                                overflow-x: auto;
                            }

                            .documentation-content a {
                                color: #0d6efd;
                                text-decoration: underline;
                            }
                        </style>

                        <div class="container mt-4 responsive">
                            <h6 class="mb-0"><?= htmlspecialchars($doc['feature_title']) ?></h6>
                            <div class="mb-2 text-secondary small">
                                <strong>Feature:</strong> <?= htmlspecialchars($doc['feature_title']) ?>
                                <span class="ps-4"><i class="bi bi-file-diff-fill"></i></span>
                                <strong>Version:</strong> <?= htmlspecialchars($doc['version']) ?>
                            </div>



                            <div class="documentation-content overflow-auto" style="max-height:40vh;">
                                <?= $safe_html ?>
                            </div>
                        </div>
                        <hr>
                    <?php }
                } ?>

            </div>
        </div>
    </div>
</div>