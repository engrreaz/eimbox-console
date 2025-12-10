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

</body>

</html>