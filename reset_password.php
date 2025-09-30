<?php
require_once 'core/init.php';
$csrf = csrf_token();
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';


include_once('header-plain.php');
?>




<div class="position-relative">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-6 mx-4">
            <div class="card p-sm-7 p-2">
                <!-- Logo -->
                <div class="app-brand justify-content-center mt-5">
                    <a href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/html/vertical-menu-template/index.html"
                        class="app-brand-link gap-3">
                        <span class="app-brand-logo demo">
                            <span class="text-primary"> LOGO </span>
                        </span>
                        <span class="app-brand-text demo text-heading fw-semibold">Materio</span>
                    </a>
                </div>
                <!-- /Logo -->
                <!-- Reset Password -->
                <div class="card-body mt-1">
                    <h4 class="mb-1">Reset Password 🔒</h4>
                    <p class="mb-5">
                        Your new password must be different from previously used passwords
                    </p>



                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="error bg-danger text-white rounded p-3 mb-3"><?php echo h($_SESSION['error']);
                        unset($_SESSION['error']); ?></div>
                    <?php endif; ?>


                    <form id="formAuthentication" class="mb-5" method="POST" action="reset_password_process.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>" />
                        <input type="hidden" name="email" value="<?php echo h($email); ?>" />
                        <input type="hidden" name="token" value="<?php echo h($token); ?>" />

                        <div class="mb-5 form-password-toggle form-control-validation">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        aria-describedby="password" />
                                    <label for="password">New Password</label>
                                </div>
                                <span class="input-group-text cursor-pointer"><i
                                        class="icon-base ri ri-eye-off-line icon-20px"></i></span>
                            </div>
                        </div>
                        <div class="mb-5 form-password-toggle form-control-validation">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input type="password" id="password2" class="form-control" name="password2"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        aria-describedby="password" />
                                    <label for="password2">Confirm Password</label>
                                </div>
                                <span class="input-group-text cursor-pointer"><i
                                        class="icon-base ri ri-eye-off-line icon-20px"></i></span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary d-grid w-100 mb-5">
                            Set new password
                        </button>
                        <div class="text-center">
                            <a href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/html/vertical-menu-template/auth-login-cover.html"
                                class="d-flex align-items-center justify-content-center">
                                <i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-20px me-1_5"></i>
                                Back to login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            <!-- /Reset Password -->
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