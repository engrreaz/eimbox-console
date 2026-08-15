<?php 
require_once 'header.php'; 

$slot = $_COOKIE['chain-slot'] ?? '';
$sessionyear = $_COOKIE['chain-session'] ?? '';
$class = $_COOKIE['chain-class'] ?? '';
$section = $_COOKIE['chain-section'] ?? '';

?>

<div class="container-xxl flex-grow-1 container-p-y">
    <?php
    $chain_param = '-c 12 -t "Select Voter List Criteria" -b "View Voter List"';
    include 'components/slot-tree-ui.php';
    ?>
</div>

<?php require_once 'footer.php'; ?>

<script>
    function chainBtnFunc() {
        window.location.href = 'voter-info.php';
    }
</script>
</body>

</html>