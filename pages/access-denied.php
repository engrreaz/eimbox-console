<?php require_once 'header-plain.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y  text-center">

    <img src="assets/images/logo.png" style="width:80px;" />
    <h2 class="fw-bold m-0 p-0">EIMBox</h2>
    <div class="fs-6 m-0 p-0">An Institute Management System to Make Your Institution Paperless</div>

    <div class="alert alert-danger text-center m-5 fw-bold">
        <?php echo $error_message; ?>
    </div>

    <i class="bi bi-ban " style="font-size:100px; font-weight:bold;"></i>
    <br>

    <a class="btn btn-dark mt-5" href="index.php">Back to Home</a>

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