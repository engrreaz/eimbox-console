<?php
require_once 'header-plain.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y text-danger text-center" style="text-align:center;">

    <img src="assets/images/logo.png" style="width:80px;" />
    <h2 class="fw-bold m-0 p-0 text-danger">EIMBox</h2>
    <div class="fs-6 m-0 p-0">An Institute Management System to Make Your Institution Paperless</div>


    <div class="error-box text-danger">
        <h2 class="text-danger">Oops! Something went wrong.</h2>
        <p class="text-danger">We're working to fix this. Please try again later.</p>
    </div>

    <i class="bi bi-bug-fill  " style="font-size:100px; font-weight:bold;"></i>
    <br>



    <a class="btn btn-danger mt-5" href="index.php">Back to Home</a>

    <?php
    // echo '<pre>';
    // print_r($_SESSION);
    // echo '</pre>';
    ?>

</div>

<?php require_once 'footer-plain.php'; ?>

<!-- ----------------------------------- -->
<script></script>
<!-- ----------------------------------- -->
</body>

</html>