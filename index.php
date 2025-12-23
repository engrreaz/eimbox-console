<?php
require_once 'header.php';

if ($_SESSION['user_role'] == 'guest' && $_SESSION['userlevel'] == 'guest') {
    require_once 'index-guest.php';
} else {
    require_once 'index-admin.php';
}

require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script></script>
<!-- ----------------------------------- -->

<?php if ($_SESSION['user_role'] == 'guest' && $_SESSION['userlevel'] == 'guest') { ?>
    <script>
        $('.view-transcript').on('click', function () {
            // alert('Transcript ID: <?php echo $id; ?>\nGPA: <?php echo $gpa; ?>\nGrade: <?php echo $grade; ?>\nTotal Marks: <?php echo $totalMarks; ?>');
            let modal = new bootstrap.Modal(document.getElementById('transcriptModal'));
            modal.show();
        });
    </script>


    <script>
        $('.print-transcript').on('click', function () {
            window.open(
                'panel/guest/print-marksheet.php?stid=<?= $stid ?>&id=<?= urlencode($id) ?>',
                '_blank'
            );
        });
    </script>

    <script>
        $('.download-transcript').on('click', function () {
            window.location.href =
                'panel/guest/download-pdf.php?stid=<?= $stid ?>&id=<?= urlencode($id) ?>';
        });
    </script>



<?php } ?>


</body>

</html>