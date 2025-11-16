<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <form method="post">

        <?php
        dropdown('session');
        dropdown('class');
        dropdown('section', ['depends_on' => 'class']);
        dropdown('subject', ['depends_on' => ['class', 'section']]);
        dropdown('exam');
        dropdown('slot');
        ?>

        <button type="submit" class="btn btn-primary">Submit</button>

    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        echo "<hr>";
        echo "Session: " . $_POST['dd_session'] . "<br>";
        echo "Class: " . $_POST['dd_class'] . "<br>";
        echo "Section: " . $_POST['dd_section'] . "<br>";
        echo "Subject: " . $_POST['dd_subject'] . "<br>";
        echo "Exam: " . $_POST['dd_exam'] . "<br>";
        echo "Slot: " . $_POST['dd_slot'] . "<br>";
    }
    ?>

</div>

</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script></script>
<!-- ----------------------------------- -->
</body>

</html>