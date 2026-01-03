<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/global_values.php';

$chain = '';
// $nodes = explode(' ', $chain);


function parseChainParam(string $param): array
{
    $out = [
        'title' => '',
        'url' => false,
        'hidden' => [],
        'reload' => false
    ];

    if (!$param)
        return $out;

    // -u flag
    if (strpos($param, '-u') !== false) {
        $out['url'] = true;
    }
    if (strpos($param, '-r') !== false) {
        $out['reload'] = true;
    }

    // -t Title
    if (preg_match('/-t\s+([^-\n]+)/', $param, $m)) {
        $out['title'] = trim($m[1]);
    }
    if (preg_match('/-b\s+([^-\n]+)/', $param, $m)) {
        $out['button'] = trim($m[1]);
    }

    // -h hidden list
    if (preg_match('/-h\s+([a-zA-Z0-9_,]+)/', $param, $m)) {
        $out['hidden'] = array_filter(
            array_map('trim', explode(',', $m[1]))
        );
    }

    return $out;
}


$my_chain_params = parseChainParam($chain_param);



$box_title = $my_chain_params['title'] ?? 'Choose Values';
$chain_button_text = $my_chain_params['button'] ?? 'View';

if ($my_chain_params['url'] == true) {
    $chain .= ' url ';
}
if ($my_chain_params['reload'] == true) {
    $chain .= ' reload ';
}

foreach ($my_chain_params['hidden'] as $ygl) {
    $chain .= ' ' . $ygl . ' ';
}


// --------------------------------------------------------------

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



<div class="card mb-3 card-border-shadow-primary ">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="text-muted mb-0"><?= $box_title ?></h6>
        <button type="button" class="btn btn-icon rounded-pill btn-label-github waves-effect bg-info text-white "
            id="openTree">
            <i class="bi bi-stack"></i>
        </button>
    </div>

    <div class="card-body">
        <div class="row g-2 align-items-end">

            <!-- SLOT -->
            <div class="col-md-2">
                <label class="form-label fs-small">Slot / Unit</label>
                <select id="slot-main" class="form-select form-select-sm">
                    <option value="">Select Slot</option>
                    <?php
                    $q = $conn->query("SELECT slotname FROM slots WHERE sccode='$sccode' ORDER BY slotname");
                    while ($r = $q->fetch_assoc()) {
                        echo "<option value='{$r['slotname']}'>{$r['slotname']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- SESSION -->
            <div class="col-md-2">
                <label class="form-label fs-small">Session</label>
                <select id="session-main" class="form-select form-select-sm">
                    <option value="">Select Session</option>
                    <?php
                    $q = $conn->query("SELECT syear FROM sessionyear 
                                       WHERE sccode='$sccode' AND active=1 
                                       ORDER BY syear DESC");
                    while ($r = $q->fetch_assoc()) {
                        echo "<option value='{$r['syear']}'>{$r['syear']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- CLASS -->
            <div class="col-md-2">
                <label class="form-label fs-small">Class</label>
                <select id="class-main" class="form-select form-select-sm">
                    <option value="">Select Class</option>
                </select>
            </div>

            <!-- SECTION -->
            <div class="col-md-2">
                <label class="form-label fs-small">Section</label>
                <select id="section-main" class="form-select form-select-sm">
                    <option value="">Select Section</option>
                </select>
            </div>

            <!-- ACTION -->
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-primary w-100 py-2 pt-3" id="btn-chain">
                    <div class="row">
                        <div class="col text-start">
                            <?= $chain_button_text ?>
                        </div>
                        <div class="col-auto text-end">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>

                </button>
            </div>

        </div>
    </div>
</div>