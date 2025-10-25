<?php
include_once('core/config.php');
include_once('core/db.php');
include_once('header-plain.php');
?>

<!--
<!DOCTYPE html>

<html
  lang="en"
  class="layout-wide customizer-hide"
  dir="ltr"
  data-skin="default"
  data-bs-theme="light"
  data-assets-path="../../assets/"
  data-template="vertical-menu-template"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <meta name="robots" content="noindex, nofollow" />

    <title>
      Demo: Multi Steps Sign-up - Pages | Materio - Bootstrap Dashboard PRO
    </title>



    <meta
      name="description"
      content="Materio Pro is the best bootstrap 5 dashboard for responsive web apps. Streamline your app development process with ease."
    />
    <meta
      name="keywords"
      content="Materio bootstrap dashboard, Materio bootstrap 5 dashboard, themeselection, html dashboard, web dashboard, frontend dashboard, responsive bootstrap theme"
    />
    <meta
      property="og:title"
      content="Materio Bootstrap 5 Dashboard PRO by ThemeSelection"
    />
    <meta property="og:type" content="product" />
    <meta
      property="og:url"
      content="https://themeselection.com/item/materio-dashboard-pro-bootstrap/"
    />
    <meta
      property="og:image"
      content="https://ts-assets.b-cdn.net/ts-assets/materio/materio-bootstrap-html-admin-template/marketing/materio-bootstrap-html-admin-template-smm.png"
    />
    <meta
      property="og:description"
      content="Materio Pro is the best bootstrap 5 dashboard for responsive web apps. Streamline your app development process with ease."
    />
    <meta property="og:site_name" content="ThemeSelection" />
    <link
      rel="canonical"
      href="https://themeselection.com/item/materio-dashboard-pro-bootstrap/"
    />

    <script>
      (function (w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({ "gtm.start": new Date().getTime(), event: "gtm.js" });
        var f = d.getElementsByTagName(s)[0],
          j = d.createElement(s),
          dl = l != "dataLayer" ? "&l=" + l : "";
        j.async = true;
        j.src = "https://www.googletagmanager.com/gtm.js?id=" + i + dl;
        f.parentNode.insertBefore(j, f);
      })(window, document, "script", "dataLayer", "GTM-5DDHKGP");
    </script>

  
    <link
      rel="icon"
      type="image/x-icon"
      href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/favicon/favicon.ico"
    />

   
    <link rel="preconnect" href="https://fonts.googleapis.com/" />
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;ampdisplay=swap"
      rel="stylesheet"
    />

    <link
      rel="stylesheet"
      href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/fonts/iconify-icons.css"
    />

  


    <link
      rel="stylesheet"
      href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/node-waves/node-waves.css"
    />

    <script src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/@algolia/autocomplete-js.js"></script>

    <link
      rel="stylesheet"
      href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/pickr/pickr-themes.css"
    />

    <link
      rel="stylesheet"
      href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/css/core.css"
    />
    <link
      rel="stylesheet"
      href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/css/demo.css"
    />

 

    <link
      rel="stylesheet"
      href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css"
    />

    <link
      rel="stylesheet"
      href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/bs-stepper/bs-stepper.css"
    />
    <link
      rel="stylesheet"
      href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/bootstrap-select/bootstrap-select.css"
    />
    <link
      rel="stylesheet"
      href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/select2/select2.css"
    />
    <link
      rel="stylesheet"
      href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/@form-validation/form-validation.css"
    />

 
    <link
      rel="stylesheet"
      href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/css/pages/page-auth.css"
    />


    <script src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/js/helpers.js"></script>

    <script src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/js/template-customizer.js"></script>


    <script src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/js/config.js"></script>
  </head>

  <body>
    <noscript
      ><iframe
        src="https://www.googletagmanager.com/ns.html?id=GTM-5DDHKGP"
        height="0"
        width="0"
        style="display: none; visibility: hidden"
      ></iframe
    ></noscript>
-->
<!-- Content -->

<div class="authentication-wrapper authentication-cover">
  <!-- Logo -->
  <a href="index.php"
    class="auth-cover-brand d-flex align-items-center gap-3">
    <span class="app-brand-logo demo">
      <img src="assets/images/logo.png" alt="Logo" width="40">
    </span>
    <span class="app-brand-text demo text-heading fw-semibold">EIMBox</span>
  </a>
  <!-- /Logo -->
  <div class="authentication-inner row m-0">
    <!-- Left Text -->
    <div class="d-none d-lg-flex col-lg-3 align-items-center justify-content-end p-5 pe-0">
      <img alt="register-multi-steps-illustration" src="assets/images/core/regd-form.jpg"
        class="scaleX-n1-rtl w-px-400 h-auto mh-100 img-fluid" />
    </div>
    <!-- /Left Text -->

    <!--  Multi Steps Registration -->
    <div class="d-flex col-lg-9 align-items-center justify-content-center authentication-bg p-5">
      <div class="w-px-700 mt-12 mt-lg-0">

        <h4 class="mb-2 text-center">Registration Form for New Institute</h4>
        <hr class="line">

        <div id="multiStepsValidation" class="bs-stepper wizard-numbered shadow-none">

          <div class="col-md-12" id="message"></div>


          <div class="bs-stepper-header border-bottom-0 mb-2">

            <div class="step" data-target="#accountDetailsValidation" data-step="1">
              <button type="button" class="step-trigger">
                <span class="bs-stepper-circle"><i class="icon-base ri ri-check-line"></i></span>
                <span class="bs-stepper-label">
                  <span class="bs-stepper-number">01</span>
                  <span class="d-flex flex-column ms-1">
                    <span class="bs-stepper-title">EIIN</span>
                    <span class="bs-stepper-subtitle">Institute ID</span>
                  </span>
                </span>
              </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#personalInfoValidation" data-step="2">
              <button type="button" class="step-trigger">
                <span class="bs-stepper-circle"><i class="icon-base ri ri-check-line"></i></span>
                <span class="bs-stepper-label">
                  <span class="bs-stepper-number">02</span>
                  <span class="d-flex flex-column ms-1">
                    <span class="bs-stepper-title">Institute</span>
                    <span class="bs-stepper-subtitle">Details Information</span>
                  </span>
                </span>
              </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#billingLinksValidation" data-step="3">
              <button type="button" class="step-trigger">
                <span class="bs-stepper-circle"><i class="icon-base ri ri-check-line"></i></span>
                <span class="bs-stepper-label">
                  <span class="bs-stepper-number">03</span>
                  <span class="d-flex flex-column ms-1">
                    <span class="bs-stepper-title">Administrator</span>
                    <span class="bs-stepper-subtitle">Admin Account</span>
                  </span>
                </span>
              </button>
            </div>

          </div>




          <div class="bs-stepper-content">
            <form id="multiStepsForm" onSubmit="return false">
              <!-- Account Details -->
              <div id="accountDetailsValidation" class="content">
                <div class="content-header mb-5">
                  <h4 class="mb-0">EIIN Information</h4>
                  <span>Enter Your Institution Identity</span>
                </div>


                <div class="row gx-5">
                  <div class="col-sm-6 mb-5 form-control-validation">
                    <div class="form-floating form-floating-outline">
                      <input type="number" name="multiStepsSccode" id="multiStepsSccode" class="form-control"
                        placeholder="6-digit Number" value="" />
                      <label for="multiStepsSccode">EIIN</label>
                    </div>
                  </div>
                  <div class="col-sm-6 mb-5 form-control-validation">
                    <div class="form-floating form-floating-outline">

                      <!-- placeholder="School/College/Madrasha" aria-label="john.doe" value="School" -->
                      <select name="multiStepsSctype" id="multiStepsSctype" class="select form-select">
                        <option value=""><b>Select Institute Type</b></option>
                        <option value="School">School</option>
                        <option value="College">College</option>
                        <option value="School-College">School-College</option>
                        <option value="Madrasha">Madrasha</option>
                        <option value="Technical">Technical</option>
                        <option value="EIIN LESS">EIIN LESS</option>
                      </select>


                      <label for="multiStepsSctype">Instution Type</label>
                    </div>
                  </div>



                  <div class="col-md-12 mb-5" hidden>
                    <div class="form-floating form-floating-outline">
                      <input type="text" name="multiStepsURL" id="multiStepsURL" class="form-control"
                        placeholder="johndoe/profile" aria-label="johndoe" value="-" />
                      <label for="multiStepsURL">Profile Link</label>
                    </div>
                  </div>



                  <div class="col-12 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-prev" disabled>
                      <i class="icon-base ri ri-arrow-left-line icon-16px me-sm-1_5 me-0"></i>
                      <span class="align-middle d-sm-inline-block d-none">Previous</span>
                    </button>
                    <button type="button" class="btn btn-primary btn-next" id="nextBtn1">
                      <span class="align-middle d-sm-inline-block d-none me-sm-1_5 me-0"> Next </span>
                      <i class="icon-base ri ri-arrow-right-line icon-16px"></i>
                    </button>
                  </div>
                </div>

              </div>
              <!-- Personal Info -->
              <div id="personalInfoValidation" class="content">
                <div class="content-header mb-5">
                  <h4 class="mb-0">Institution Information</h4>
                  <span>Enter Your Institution Details</span>
                </div>
                <div class="row gx-5">
                  <div class="col-md-12 mb-5">
                    <div class="form-floating form-floating-outline">
                      <input type="text" id="multiStepsArea" name="multiStepsArea" class="form-control"
                        placeholder="Name of Institution" value="" />
                      <label for="multiStepsArea">Name of Institution</label>
                    </div>
                  </div>



                  <div class="col-sm-6 mb-5 form-control-validation">
                    <div class="form-floating form-floating-outline">
                      <input type="text" id="multiStepsFirstName" name="multiStepsFirstName" class="form-control"
                        placeholder="e.g. 42/B Mirpur Road" value="" />
                      <label for="multiStepsFirstName">Address Line # 1</label>
                    </div>
                  </div>
                  <div class="col-sm-6 mb-5">
                    <div class="form-floating form-floating-outline">
                      <input type="text" id="multiStepsLastName" name="multiStepsLastName" class="form-control"
                        placeholder="e.g. Opposite of NAEM" value="" />
                      <label for="multiStepsLastName">Address Line # 2</label>
                    </div>
                  </div>

                  <div class="col-md-12 mb-5 form-control-validation" hidden>
                    <div class="form-floating form-floating-outline">
                      <input type="text" id="multiStepsAddress" name="multiStepsAddress" class="form-control"
                        placeholder="Address" value="Address" />
                      <label for="multiStepsAddress">Address</label>
                    </div>
                  </div>


                  <div class="col-sm-6 mb-5 form-control-validation">
                    <div class="form-floating form-floating-outline">
                      <select id="districtSelect" class="select form-select" data-allow-clear="true">
                        <option value="">Select District</option>
                        <!-- JS দিয়ে district option গুলো populate হবে -->
                      </select>
                      <label for="districtSelect">District</label>
                    </div>
                  </div>

                  <div class="col-sm-6 mb-5 form-control-validation">
                    <div class="form-floating form-floating-outline">
                      <select id="upazilaSelect" class="select form-select" data-allow-clear="true">
                        <option value="">Select Upazila/PS</option>
                        <!-- JS দিয়ে upazila option গুলো populate হবে -->
                      </select>
                      <label for="upazilaSelect">Upazila/PS</label>
                    </div>
                  </div>





                  <div class="col-sm-6 mb-5">
                    <div class="input-group input-group-merge">
                      <span class="input-group-text">BD (+88)</span>
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="multiStepsMobile" name="multiStepsMobile"
                          class="form-control multi-steps-mobile" placeholder="0966******" maxlenght="11" value="" />
                        <label for="multiStepsMobile">Mobile</label>
                      </div>
                    </div>
                  </div>


                  <div class="col-sm-6 mb-5">
                    <div class="form-floating form-floating-outline">
                      <input type="email" id="multiStepsPincode" name="multiStepsPincode"
                        class="form-control multi-steps-pincode-2" placeholder="insmail@server.com" value="" />
                      <label for="multiStepsPincode">Institute Email</label>
                    </div>
                  </div>


                  <div class="col-12 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-prev">
                      <i class="icon-base ri ri-arrow-left-line icon-16px me-sm-1_5 me-0"></i>
                      <span class="align-middle d-sm-inline-block d-none">Previous</span>
                    </button>
                    <button type="button" class="btn btn-primary btn-next">
                      <span class="align-middle d-sm-inline-block d-none me-sm-1_5 me-0">Next</span>
                      <i class="icon-base ri ri-arrow-right-line icon-16px"></i>
                    </button>
                  </div>
                </div>
              </div>



              <!-- Billing Links -->
              <div id="billingLinksValidation" class="content">
                <div class="content-header mb-5">
                  <h4 class="mb-0">Administrator</h4>
                  <span>Enter an administrator information</span>
                </div>

                <div class="row gx-5">
                  <div class="col-sm-6 mb-5 form-control-validation">
                    <div class="form-floating form-floating-outline">
                      <input type="text" name="multiStepsUsername" id="multiStepsUsername" class="form-control"
                        placeholder="johndoe" value="engr_reaz_hoque" />
                      <label for="multiStepsUsername">Username</label>
                    </div>
                  </div>
                  <div class="col-sm-6 mb-5 form-control-validation">
                    <div class="form-floating form-floating-outline">
                      <input type="email" name="multiStepsEmail" id="multiStepsEmail" class="form-control"
                        placeholder="john.doe@email.com" aria-label="john.doe" value="" />
                      <label for="multiStepsEmail">Email</label>
                    </div>
                  </div>
                  <div class="col-sm-6 form-password-toggle mb-5 form-control-validation">
                    <div class="input-group input-group-merge">
                      <div class="form-floating form-floating-outline">
                        <input type="password" id="multiStepsPass" name="multiStepsPass" class="form-control"
                          placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                          aria-describedby="multiStepsPass2" value="@@Rdno2025@@" />
                        <label for="multiStepsPass">Password</label>
                      </div>
                      <span class="input-group-text cursor-pointer" id="multiStepsPass2"><i
                          class="icon-base ri ri-eye-off-line ri-20px"></i></span>
                    </div>
                  </div>
                  <div class="col-sm-6 form-password-toggle mb-5 form-control-validation">
                    <div class="input-group input-group-merge">
                      <div class="form-floating form-floating-outline">
                        <input type="password" id="multiStepsConfirmPass" name="multiStepsConfirmPass"
                          class="form-control"
                          placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                          aria-describedby="multiStepsConfirmPass2" value="@@Rdno2025@@" />
                        <label for="multiStepsConfirmPass">Confirm Password</label>
                      </div>
                      <span class="input-group-text cursor-pointer" id="multiStepsConfirmPass2"><i
                          class="icon-base ri ri-eye-off-line ri-20px"></i></span>
                    </div>
                  </div>
                  <div class="col-md-12 mb-5">
                    <div class="form-floating form-floating-outline">
                      <input type="text" name="multiStepsURL2" id="multiStepsURL2" class="form-control"
                        placeholder="johndoe/profile" aria-label="johndoe" />
                      <label for="multiStepsURL2">Profile Link</label>
                    </div>
                  </div>

                </div>
                <!-- Custom plan options -->
                <div class="row gap-md-0 gap-4 mb-12 gx-5" hidden>
                  <div class="col-md">
                    <div class="form-check custom-option custom-option-icon">
                      <label class="form-check-label custom-option-content" for="basicOption">
                        <span class="custom-option-body">
                          <span class="fs-6 d-block fw-medium text-heading mb-2">Basic</span>
                          <small>A simple start for start ups & Students</small>
                          <span class="d-flex justify-content-center py-2">
                            <sup class="text-primary fs-6 lh-1 mt-3">$</sup>
                            <span class="h4 mb-0 text-primary">0</span>
                            <sub class="lh-1 fs-sm mt-auto mb-3 text-body-secondary">/month</sub>
                          </span>
                        </span>
                        <input name="customRadioIcon" class="form-check-input" type="radio" value="" id="basicOption" />
                      </label>
                    </div>
                  </div>
                  <div class="col-md">
                    <div class="form-check custom-option custom-option-icon">
                      <label class="form-check-label custom-option-content" for="standardOption">
                        <span class="custom-option-body">
                          <span class="fs-6 d-block fw-medium text-heading mb-2">Standard</span>
                          <small>For small to medium businesses</small>
                          <span class="d-flex justify-content-center py-2">
                            <sup class="text-primary fs-6 lh-1 mt-3">$</sup>
                            <span class="h4 mb-0 text-primary">99</span>
                            <sub class="lh-1 fs-sm mt-auto mb-3 text-body-secondary">/month</sub>
                          </span>
                        </span>
                        <input name="customRadioIcon" class="form-check-input" type="radio" value="" id="standardOption"
                          checked />
                      </label>
                    </div>
                  </div>
                  <div class="col-md">
                    <div class="form-check custom-option custom-option-icon">
                      <label class="form-check-label custom-option-content" for="enterpriseOption">
                        <span class="custom-option-body">
                          <span class="fs-6 d-block fw-medium text-heading mb-2">Enterprise</span>
                          <small>Solution for enterprise & organizations</small>
                          <span class="d-flex justify-content-center py-2">
                            <sup class="text-primary fs-6 lh-1 mt-3">$</sup>
                            <span class="h4 mb-0 text-primary">499</span>
                            <sub class="lh-1 fs-sm mt-auto mb-3 text-body-secondary">/month</sub>
                          </span>
                        </span>
                        <input name="customRadioIcon" class="form-check-input" type="radio" value=""
                          id="enterpriseOption" />
                      </label>
                    </div>
                  </div>
                </div>
                <!--/ Custom plan options -->
                <div class="content-header mb-5 mt-5" hidden>
                  <h4 class="mb-0">Payment Information</h4>
                  <span>Enter your card information</span>
                </div>
                <!-- Credit Card Details -->
                <div class="row gx-5">


                  <div class="col-12 d-flex justify-content-between form-control-validation">
                    <button type="button" class="btn btn-outline-secondary btn-prev">
                      <i class="icon-base ri ri-arrow-left-line icon-16px me-sm-1_5 me-0"></i>
                      <span class="align-middle d-sm-inline-block d-none">Previous</span>
                    </button>
                    <button type="submit" class="btn btn-success btn-next btn-submit">
                      Submit
                      <i class="icon-base ri ri-check-line icon-16px ms-1_5"></i>
                    </button>
                  </div>
                </div>
                <!--/ Credit Card Details -->
              </div>


            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- / Multi Steps Registration -->
  </div>
</div>



<?php include_once('footer-plain.php'); ?>

<script>
  // Check selected custom option
  window.Helpers.initCustomOptionCheck();
</script>

<!-- / Content -->



<!-- Core JS -->

<!-- build:js assets/vendor/js/theme.js  -->

<script
  src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/jquery/jquery.js"></script>

<script
  src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/popper/popper.js"></script>
<script
  src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/js/bootstrap.js"></script>
<script
  src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/node-waves/node-waves.js"></script>

<script
  src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/pickr/pickr.js"></script>

<script
  src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

<script
  src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/hammer/hammer.js"></script>

<script
  src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/i18n/i18n.js"></script>

<script src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/js/menu.js"></script>

<!-- endbuild -->

<!-- Vendors JS -->
<script
  src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/cleave-zen/cleave-zen.js"></script>
<script
  src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/bs-stepper/bs-stepper.js"></script>
<script
  src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/select2/select2.js"></script>
<script
  src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/@form-validation/popular.js"></script>
<script
  src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script
  src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/vendor/libs/@form-validation/auto-focus.js"></script>

<!-- Main JS -->

<script src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/js/main.js"></script>

<!-- Page JS -->
<script src="assets/js/pages-auth-multisteps.js"></script>



<script>
  $(document).ready(function () {

    $('#multiStepsSccode').focus();

    $('#nextBtn1').prop('disabled', true);

    $('#multiStepsSccode').on('input change blur', function () {
      var eiin = $(this).val().trim();

      if (eiin === '') {
        $('#message').html('<div class="alert alert-danger">⚠️ Please enter EIIN</div>');
        $('#nextBtn1').prop('disabled', true);
        return;
      }

      // EIIN 6 সংখ্যার হলে চেক করো
      if (eiin.length === 6) {
        $.ajax({
          url: 'core/check-sccode.php',
          type: 'POST',
          data: { eiin: eiin },
          dataType: 'json',
          success: function (response) {
            if (response.exists) {
              $('#message').html('<div class="alert alert-warning">⚠️ EIIN already registered with EIMBox</div>');
              $('#multiStepsSccode').val('').focus();
              $('#nextBtn1').prop('disabled', true);
            } else {
              $('#message').html('<div class="alert alert-success">✅ EIIN Accepted!</div>');
              checkFormReady(); // EIIN ঠিক থাকলে ফর্ম চেক
            }
          },
          error: function () {
            $('#message').html('<div class="alert alert-danger">❌ Something has been wrong.</div>');
            $('#nextBtn1').prop('disabled', true);
          }
        });
      } else {
        $('#message').html('<div class="text-muted">EIIN must be a 6 digits number</div>');
        $('#nextBtn1').prop('disabled', true);
      }
    });

    // Sctype এবং URL ইনপুটে পরিবর্তন হলে চেক
    $('#multiStepsSccode, #multiStepsSctype, #multiStepsURL').on('input change blur', function () {
      checkFormReady();
    });

    // ✅ ফাংশন: তিনটি ইনপুট ফাঁকা না থাকলে বাটন enable
    function checkFormReady() {
      var eiin = $('#multiStepsSccode').val().trim();
      var sctype = $('#multiStepsSctype').val().trim();
      var url = $('#multiStepsURL').val().trim();

      if (eiin !== '' && sctype !== '' && url !== '') {
        $('#nextBtn1').prop('disabled', false);
        // $('#message').html('<div class="text-success">✅ সব ফিল্ড পূর্ণ হয়েছে, আপনি পরবর্তী ধাপে যেতে পারেন।</div>');
      } else {
        $('#nextBtn1').prop('disabled', true);
      }
    }
  });
</script>



<script>
  $(document).ready(function () {
    let currentStep = 1;
    const totalSteps = $('.step').length;

    function showStep(step) {
      // $('.step').hide();
      $('.step[data-step="' + step + '"]').show();
    }

    $('.btn-next').on('click', function () {
      if (currentStep < totalSteps) {
        currentStep++;
        showStep(currentStep);
      }
    });

    $('.btn-prev').on('click', function () {
      if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
      }
    });

    // Submit form via AJAX
    $('#multiStepsForm').on('submit', function (e) {
      e.preventDefault();

      let formData = {
        eiin: $('#multiStepsSccode').val(),
        sctype: $('#multiStepsSctype').val(),
        area: $('#multiStepsArea').val(),
        address1: $('#multiStepsFirstName').val(),
        address2: $('#multiStepsLastName').val(),

        dist: $('#districtSelect option:selected').text(),
        ps: $('#upazilaSelect option:selected').text(),

        mobile: $('#multiStepsMobile').val(),
        pincode: $('#multiStepsPincode').val(),
        username: $('#multiStepsUsername').val(),
        email: $('#multiStepsEmail').val(),
        password: $('#multiStepsPass').val()
      };

      $.ajax({
        url: 'core/insert-school.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function (res) {
          if (res.success) {
            alert('✅ Institution registered successfully!');
            $('#multiStepsForm')[0].reset();
            currentStep = 1;
            showStep(currentStep);
          } else {
            alert('❌ ' + res.message);
          }
        },
        error: function () {
          alert('⚠️ Server error occurred!');
        }
      });
    });

    showStep(currentStep); // শুরুতে প্রথম স্টেপ দেখাও
  });
</script>




<script>
  document.addEventListener('DOMContentLoaded', () => {
    const districtSelect = document.getElementById('districtSelect');
    const upazilaSelect = document.getElementById('upazilaSelect');

    // প্রথমে districts.json থেকে ডেটা লোড করো
    fetch('assets/json/districts.json')
      .then(res => res.json())
      .then(districtData => {
        // তোমার JSON ফরম্যাট অনুযায়ী data বের করতে হবে
        const districts = districtData.find(item => item.type === 'table' && item.name === 'districts').data;

        districts.forEach(district => {
          const option = document.createElement('option');
          option.value = district.id;
          option.textContent = decodeURIComponent(district.name);
          districtSelect.appendChild(option);
        });
      });

    // জেলা পরিবর্তন হলে উপযুক্ত উপজেলা লোড করো
    districtSelect.addEventListener('change', () => {
      const districtId = districtSelect.value;

      upazilaSelect.innerHTML = '<option value="">Select Upazila/PS</option>'; // আগেরগুলো ক্লিয়ার করো

      if (!districtId) return;

      fetch('assets/json/upazilas.json')
        .then(res => res.json())
        .then(upazilaData => {
          const upazilas = upazilaData.find(item => item.type === 'table' && item.name === 'upazilas').data;

          // নির্দিষ্ট district_id অনুযায়ী ফিল্টার
          const filteredUpazilas = upazilas.filter(u => u.district_id === districtId);

          filteredUpazilas.forEach(upazila => {
            const option = document.createElement('option');
            option.value = upazila.id;
            option.textContent = decodeURIComponent(upazila.name);
            upazilaSelect.appendChild(option);
          });
        });
    });
  });
</script>




</body>

</html>