<?php
require_once 'core/init.php';
$csrf = csrf_token();
include_once('header-plain.php');
?>




<div class="position-relative">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-6 mx-4">
            <!-- Logo -->
            <div class="card p-sm-7 p-2">
                <!-- Forgot Password -->
                <div class="app-brand justify-content-center mt-5">
                    <?php include 'core/top-part.php'; ?>
                </div>
                <!-- /Logo -->
                <div class="card-body mt-1">
                    <h4 class="mb-1">Forgot Password? 🔒</h4>
                    <p class="mb-5">Enter your email and we'll send you instructions to reset your password</p>

                    <?php if (!empty($_SESSION['msg'])): ?>
                        <div class="msg bg-primary text-white rounded p-3 mb-3"><?php echo h($_SESSION['msg']);
                        unset($_SESSION['msg']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="error bg-danger text-white rounded p-3 mb-3"><?php echo h($_SESSION['error']);
                        unset($_SESSION['error']); ?></div>
                    <?php endif; ?>

                    <form id="formAuthentication" class="mb-5" method="POST" action="forgot_password_process.php">

                        <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">


                        <div class="form-floating form-floating-outline mb-5 form-control-validation">
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Enter your email" autofocus required />
                            <label>Email</label>
                        </div>
                        <button type="submit" class="btn btn-primary d-grid w-100 mb-5">Send Reset Link</button>
                    </form>
                    <div class="text-center">
                        <a href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/html/vertical-menu-template/auth-login-basic.html"
                            class="d-flex align-items-center justify-content-center">
                            <i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-20px me-1_5"></i>
                            Back to login
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Forgot Password -->
            <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/illustrations/tree-3.png"
                alt="auth-tree" class="authentication-image-object-left d-none d-lg-block" />
            <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/illustrations/auth-basic-mask-light.png"
                class="authentication-image d-none d-lg-block scaleX-n1-rtl" height="172" alt="triangle-bg"
                data-app-light-img="illustrations/auth-basic-mask-light.png"
                data-app-dark-img="https://demos.themeselection.com/materio-bootstrap-html-admin-template/html/vertical-menu-template/illustrations/auth-basic-mask-dark.png" />
            <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/illustrations/tree.png"
                alt="auth-tree" class="authentication-image-object-right d-none d-lg-block" />
        </div>
    </div>
</div>


<?php include_once('footer-plain.php'); ?>