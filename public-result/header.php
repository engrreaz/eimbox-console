<?php

require_once '../core/config.php';
require_once '../core/db.php';

require_once '../core/core-val.php';
require_once '../core/global_values.php';

$logo_path = BASE_PATH . 'logo/' . $sccode . '.png';
?>

<!doctype html>
<html lang="en" class=" layout-navbar-fixed layout-menu-fixed layout-compact " dir="ltr" data-skin="default"
    data-bs-theme="light" data-assets-path="assets/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />

    <title>EIMBox | An Educational Institute Management System</title>



    <!-- Canonical SEO -->

    <meta name="description"
        content="Materio Pro is the best bootstrap 5 dashboard for responsive web apps. Streamline your app development process with ease." />
    <meta name="keywords"
        content="Materio bootstrap dashboard, Materio bootstrap 5 dashboard, themeselection, html dashboard, web dashboard, frontend dashboard, responsive bootstrap theme" />
    <meta property="og:title" content="Materio Bootstrap 5 Dashboard PRO by ThemeSelection" />
    <meta property="og:type" content="product" />
    <meta property="og:url" content="https://themeselection.com/item/materio-dashboard-pro-bootstrap/" />
    <meta property="og:image"
        content="https://ts-assets.b-cdn.net/ts-assets/materio/materio-bootstrap-html-admin-template/marketing/materio-bootstrap-html-admin-template-smm.png" />
    <meta property="og:description"
        content="Materio Pro is the best bootstrap 5 dashboard for responsive web apps. Streamline your app development process with ease." />
    <meta property="og:site_name" content="ThemeSelection" />
    <link rel="canonical" href="https://themeselection.com/item/materio-dashboard-pro-bootstrap/" />


    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/" />
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;ampdisplay=swap"
        rel="stylesheet" />

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;700&display=swap"
        rel="stylesheet" />


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="assets/vendor/fonts/iconify-icons.css" />
    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->

    <link rel="stylesheet" href="../assets/vendor/libs/node-waves/node-waves.css" />
    <script src="../assets/vendor/libs/@algolia/autocomplete-js.js"></script>
    <link rel="stylesheet" href="../assets/vendor/libs/pickr/pickr-themes.css" />
    <link rel="stylesheet" href="../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- endbuild -->

    <link rel="stylesheet" href="../assets/vendor/libs/apex-charts/apex-charts.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet" href="../assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.dataTables.min.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="../assets/vendor/css/pages/app-logistics-dashboard.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/shepherd/shepherd.css" />
    <link rel="stylesheet" href="../assets/vendor/css/pages/app-chat.css" />

    <link rel="stylesheet" href="../assets/vendor/libs/spinkit/spinkit.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/notiflix/notiflix.css" />

    <link rel="stylesheet" href="../assets/vendor/libs/quill/typography.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/highlight/highlight.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/quill/katex.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/quill/editor.css" />
    <!-- <link rel="stylesheet" href="assets/vendor/libs/plyr/plyr.css" /> -->
    <link rel="stylesheet" href="../assets/vendor/css/pages/page-auth.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/jstree/jstree.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/animate-css/animate.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/notyf/notyf.css" />


    <link rel="stylesheet" href="../assets/css/eimbox.css" />

    <!-- Helpers -->
    <script src="../assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js. -->
    <script src="../assets/vendor/js/template-customizer.js"></script>

    <!--? Config: Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file. -->
    <script src="../assets/js/config.js"></script>

    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">
    <link rel="stylesheet" href="https://unpkg.com/intro.js/minified/introjs.min.css">




    <style>
        #om-rtggssyej7eonp0vagzg,
        .elmoro-campaign {
            display: none !important;
        }

        .swal-on-top {
            z-index: 5000 !important;
        }

        .swal2-container {
            z-index: 3000 !important;
        }
    </style>

    <script>

    </script>

</head>

<body>

    <div class="layout-wrapper layout-content-navbar  ">
        <div class="layout-container">
            <div class="layout-page">
                <div class="content-wrapper">
                    <div class=" container pt-0 pb-0 no-print">
                        <div class="divider divider-primary m-0 p-0"
                            style="--bs-divider-color: teal;">
                            <div class="divider-text fs-5 fw-bold " id="page_link_title"
                                style="color: teal">Result Viewer
                            </div>
                            <nav aria-label="breadcrumb  align-items-center">
                                <ol
                                    class="breadcrumb breadcrumb-custom-icon d-flex justify-content-center align-items-center">
                                    <li class="breadcrumb-item status-color">
                                        <a href="javascript:void(0);" class="page-title">Home</a>
                                        <i class="breadcrumb-icon icon-base ri ri-arrow-right-s-line align-middle"></i>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="javascript:void(0);" class="status-color"
                                            id="parent_item"><?php echo 'Public Results'; ?></a>
                                        <i class="breadcrumb-icon icon-base ri ri-arrow-right-s-line align-middle"></i>
                                    </li>
                                    <li class="breadcrumb-item active" id="page_link_sub_title">
                                        <?php echo 'Result Viewer'; ?>
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>