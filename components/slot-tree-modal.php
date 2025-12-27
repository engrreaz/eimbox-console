<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/global_values.php';

// $chain = $_GET['chain'] ?? '';  
// $nodes = explode(' ', $chain);

if (strpos($chain, 'subject') !== false) {
    $col = 5;
    $size = 'xl';
} else {
    $col = 12;
    $size = 'lg';
}

$Title = 'Slot → Session';
if (strpos($chain, 'exam') !== false) {
    $Title .= ' → Exam';
}

$Title .= ' → Class → Section';

if (strpos($chain, 'subject') !== false) {
    $Title .= ' →  Subject';
}

if (strpos($chain, 'class') !== false) {
    $Title = str_replace(' → Class → Section', '', $Title);
}

?>

<div class="modal fade" id="nodeTreeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-<?= $size ?> modal-dialog-scrollable">
        <div class="modal-content" style="width:80%; max-width: 500px; margin:auto;">
            <input type="text" id="chainInput" value="<?php echo h($chain); ?>" hidden>
            <div class="modal-header">
                <h5 class="modal-title">Select <span class="text-primary fw-bold"> &nbsp; <?= $Title ?></span></h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row">

                    <!-- LEFT : TREE -->
                    <div class="col-<?= $col ?>" id="treeColumn">
                        <ul id="treeRoot" class="tree-root"></ul>
                    </div>

                    <!-- RIGHT : SUBJECT -->
                    <div class="col-7 d-none" id="subjectColumn">
                        <h6 class="mb-2">Subjects</h6>
                        <ul id="subjectList" class="list-group"></ul>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>






<style>
    .tree-root,
    .tree-root ul {
        list-style: none;
        padding-left: 18px;
    }

    .tree-node {
        cursor: pointer;
        padding: 4px 0;
    }

    .tree-node .toggle {
        font-weight: bold;
        margin-right: 6px;
    }

    .tree-node.section {
        color: #0d6efd;
    }

    .tree-node.section:hover {
        text-decoration: underline;
    }


    .tree-root,
    #subjectList {
        max-height: 60vh;
        overflow-y: auto;
    }

    #subjectList .list-group-item {
        cursor: pointer;
    }

    #subjectList .list-group-item.active {
        font-weight: bold;
        background: #0d6efd;
        color: #fff;
    }
</style>