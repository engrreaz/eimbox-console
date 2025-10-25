<?php
require_once '../core/init.php';
require_once  '../vendor/autoload.php'; // HTMLPurifier autoload
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




    <script>
        (function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-5DDHKGP');
    </script>


    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/" />
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;ampdisplay=swap"
        rel="stylesheet" />

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;700&display=swap"
        rel="stylesheet" />


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="../assets/vendor/fonts/iconify-icons.css" />
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
    <link rel="stylesheet" href="../assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />

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
    <!-- <link rel="stylesheet" href="../assets/vendor/libs/plyr/plyr.css" /> -->


    <link rel="stylesheet" href="../assets/css/eimbox.css" />

    <!-- Helpers -->
    <script src="../assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js. -->
    <script src="../assets/vendor/js/template-customizer.js"></script>

    <!--? Config: Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file. -->
    <script src="../assets/js/config.js"></script>

    <script>

    </script>

</head>

<body>
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5DDHKGP" height="0" width="0"
            style="display: none; visibility: hidden"></iframe>
    </noscript>

    <div class="layout-wrapper layout-content-navbar  ">
        <div class="layout-container">
            <?php require_once '../nav/nav.php'; ?>

            <div class="layout-page">

                <?php require_once '../topbar.php'; ?>

                <div class="content-wrapper"></div>



                <div class=" container pt-0 pb-0">
                    <div class="divider divider-primary m-0 p-0"
                        style="--bs-divider-color:<?php echo $release_colors[$page_status]; ?>;">
                        <div class="divider-text fs-5 fw-bold " id="page_link_title"
                            style="color: <?php echo $release_colors[$page_status]; ?>"></div>
                        <nav aria-label="breadcrumb  align-items-center">
                            <ol
                                class="breadcrumb breadcrumb-custom-icon d-flex justify-content-center align-items-center">
                                <li class="breadcrumb-item status-color">
                                    <a href="javascript:void(0);">Home</a>
                                    <i class="breadcrumb-icon icon-base ri ri-arrow-right-s-line align-middle"></i>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="javascript:void(0);" class="status-color" id="parent_item"></a>
                                    <i class="breadcrumb-icon icon-base ri ri-arrow-right-s-line align-middle"></i>
                                </li>
                                <li class="breadcrumb-item active" id="page_link_sub_title"></li>
                            </ol>
                        </nav>
                    </div>
                </div>