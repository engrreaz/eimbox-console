<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/functions.php';

require_once '../core/global_values.php';
require_once '../core/page_load_query.php';


$fl = $_GET['fl'] ?? '';
$fla = [];
$fla = explode(',', $fl);

$pmsg = $_GET['pm'] ?? '';

// Action Types
$actionTypes = ['implement', 'update', 'bug_fix', 'remove', 'change', 'refactor', 'optimize', 'security_patch', 'deprecate', 'migrate', 'test_case', 'rollback', 'hotfix'];

// Status Types
$statusTypes = ['draft', 'planning', 'in_progress', 'testing', 'alpha', 'beta', 'rc', 'staging', 'stable', 'lts', 'deprecated', 'archived'];

// Timeline
$timeline = [];
$res = $conn->prepare("SELECT * FROM dev_timeline WHERE page_name=? ORDER BY created_at DESC");
$res->bind_param("s", $currentFile);
$res->execute();
$result = $res->get_result();
while ($row = $result->fetch_assoc())
    $timeline[] = $row;
$res->close();

$timeline = [];
$res = $conn->prepare("
    SELECT t1.*
    FROM dev_timeline t1
    INNER JOIN (
        SELECT feature_name, MAX(created_at) AS max_time
        FROM dev_timeline
        WHERE page_name=?
        GROUP BY feature_name
    ) t2 ON t1.feature_name=t2.feature_name AND t1.created_at=t2.max_time
    ORDER BY t1.created_at DESC
");
$res->bind_param("s", $currentFile);
$res->execute();
$result = $res->get_result();
while ($row = $result->fetch_assoc())
    $timeline[] = $row;
$res->close();


// Features
$features = [];
$res = $conn->prepare("SELECT DISTINCT feature_name FROM dev_timeline WHERE page_name=? AND feature_name IS NOT NULL");
$res->bind_param("s", $currentFile);
$res->execute();
$result = $res->get_result();
while ($row = $result->fetch_assoc())
    $features[] = $row['feature_name'];
$res->close();

// Badge function
function statusBadge($status)
{
    $colors = ['draft' => 'secondary', 'planning' => 'warning', 'in_progress' => 'info', 'testing' => 'info', 'alpha' => 'primary', 'beta' => 'primary', 'rc' => 'primary', 'staging' => 'info', 'stable' => 'success', 'lts' => 'success', 'deprecated' => 'dark', 'archived' => 'secondary'];
    $color = $colors[$status] ?? 'secondary';
    return "<span class='badge bg-$color'>" . ucfirst(str_replace('_', ' ', $status)) . "</span>";
}


?>

<style>
    .fs-username {
        font-size: 12px;
        font-weight: bold;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    // Real all Features Element
    // const featureElements = document.querySelectorAll('[data-feature]');
    // const features = Array.from(featureElements).map(el => el.dataset.feature);
    // document.getElementById("page_features_list").innerHTML = features;
    // console.log("Features on page:", features);


</script>

<div class="nav-align-top nav-tabs-shadow">
    <ul class="nav nav-tabs nav-fill" role="tablist">


        <?php if ($is_admin >= 4) { ?>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link waves-effect active" role="tab" data-bs-toggle="tab"
                    data-bs-target="#navs-justified-dev-note" aria-controls="navs-justified-home" aria-selected="true">
                    <span class="d-none d-sm-inline-flex align-items-center">
                        <i class="icon-base bi bi-braces icon-sm me-1_5"></i>
                        <!-- <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-danger ms-1_5">3</span> -->
                    </span>
                    <i class="icon-base bi bi-check icon-sm d-sm-none"></i>
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link waves-effect " role="tab" data-bs-toggle="tab"
                    data-bs-target="#navs-justified-home-pre" aria-controls="navs-justified-home" aria-selected="true">
                    <span class="d-none d-sm-inline-flex align-items-center">
                        <i class="icon-base bi bi-person-workspace icon-sm me-1_5"></i>
                        <!-- <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-danger ms-1_5">3</span> -->
                    </span>
                    <i class="icon-base bi bi-check icon-sm d-sm-none"></i>
                </button>
            </li>


            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link waves-effect " role="tab" data-bs-toggle="tab"
                    data-bs-target="#navs-justified-home" aria-controls="navs-justified-home" aria-selected="true">
                    <span class="d-none d-sm-inline-flex align-items-center">
                        <i class="icon-base bi bi-code-square icon-sm me-1_5"></i>
                        <!-- <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-danger ms-1_5">3</span> -->
                    </span>
                    <i class="icon-base bi bi-house icon-sm d-sm-none"></i>
                </button>
            </li>
        <?php } ?>


        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link waves-effect" role="tab" data-bs-toggle="tab"
                data-bs-target="#navs-justified-profile" aria-controls="navs-justified-profile" aria-selected="false"
                tabindex="-1">
                <span class="d-none d-sm-inline-flex align-items-center"><i
                        class="icon-base bi bi-chat-square icon-sm me-1_5"></i></span>
                <i class="icon-base bi bi-setting icon-sm d-sm-none"></i>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link waves-effect" role="tab" data-bs-toggle="tab"
                data-bs-target="#navs-justified-messages" aria-controls="navs-justified-messages" aria-selected="false"
                tabindex="-1">
                <span class="d-none d-sm-inline-flex align-items-center"><i
                        class="icon-base bi bi-question-square icon-sm me-1_5"></i></span>
                <i class="icon-base bi bi-question icon-sm d-sm-none"></i>
            </button>
        </li>
    </ul>


    <div class="tab-content p-0">

        <?php if ($is_admin >= 4) { ?>

            <div class="tab-pane fade active show" id="navs-justified-dev-note" role="tabpanel">

                <div class="col-lg-12 p-0">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0">Developer Notes</h6>
                        <button class="btn btn-sm btn-primary" onclick="openModal(0);" data-notrack="true">
                            <i class="bi bi-plus-circle me-1"></i> Add Note
                        </button>
                    </div>

                    <div id="devNotesContainer" class="demo-inline-spacing mt-2" style="max-height:60vh; overflow-y:auto;">
                        <?php
                        $q = $conn->prepare("
                            SELECT dn1.*, u.profilename AS admin_name
                            FROM dev_notes dn1
                            INNER JOIN (
                                SELECT COALESCE(ref_id, id) AS group_id, MAX(id) AS last_id
                                FROM dev_notes
                                WHERE sccode = ?
                                GROUP BY group_id
                            ) dn2 ON dn1.id = dn2.last_id
                            LEFT JOIN usersapp u ON dn1.admin_id = u.id
                            ORDER BY dn1.created_at DESC
                        ");
                        $q->bind_param("s", $sccode);
                        $q->execute();
                        $res = $q->get_result();


                        if ($res->num_rows > 0) {
                            echo '<ul class="list-group">';
                            while ($row = $res->fetch_assoc()) {
                                $statusColors = [
                                    'New' => 'secondary',
                                    'Open' => 'primary',
                                    'Waiting' => 'warning',
                                    'Replied' => 'info',
                                    'Progress' => 'success',
                                    'Hold' => 'dark',
                                    'Resolved' => 'success',
                                    'Closed' => 'danger'
                                ];
                                $color = $statusColors[$row['status']] ?? 'secondary';
                                $ref_id = $row['ref_id'] ?: $row['id'];
                                ?>

                                <li class="list-group-item mb-2 border rounded shadow-sm small">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-<?= $color ?> me-1">
                                                <?= htmlspecialchars($row['status']) ?></span>
                                            <small
                                                class=" fs-tiny text-muted ms-2"><?= htmlspecialchars($row['created_at']) ?></small>
                                        </div>
                                        <div class="d-flex">
                                            <button class="btn btn-sm btn-outline-secondary p-1 ps-2 pe-2 me-1"
                                                onclick="openModal(<?= $row['id'] ?>);">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info  p-1 ps-2 pe-2"
                                                onclick="toggleHistory(<?= $ref_id ?>, this);">
                                                <i class="bi bi-clock-history"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-dark"><?= nl2br(htmlspecialchars($row['note_line'])) ?></div>
                                    <div class="text-end mt-1 text-muted fst-italic small">
                                        — <?= htmlspecialchars($row['admin_name'] ?? 'Unknown') ?>
                                    </div>
                                    <div id="history-<?= $ref_id ?>" class="mt-2 ps-3 border-start d-none"></div>
                                </li>

                                <?php
                            }
                            echo '</ul>';
                        } else {
                            echo '<div class="alert alert-warning small">No developer notes found.</div>';
                        }
                        $q->close();
                        ?>
                    </div>
                </div>


            </div>


            <div class="tab-pane fade " id="navs-justified-home-pre" role="tabpanel">
                <div class="col-lg-12 p-0">
                    <div class="row mt-3">


                        <div class="col pt-3">
                            <small class="fw-medium">User List</small>
                        </div>

                        <div class="col">
                            <button class="float-end btn btn-primary " id="openInsList">Ins List</button>
                        </div>



                    </div>


                    <div class="demo-inline-spacing mt-2" style="max-height:70vh; overflow-y:auto;">
                        <?php
                        // usersapp টেবিল থেকে নির্দিষ্ট প্রতিষ্ঠানের (sccode) ইউজার লিস্ট
                        $query = $conn->prepare("SELECT id, email, profilename, userlevel FROM usersapp WHERE sccode = ? and hiddenuser = 0 ORDER BY profilename ASC");
                        $query->bind_param("s", $sccode);
                        $query->execute();
                        $res = $query->get_result();

                        echo '<ul class="list-group">';
                        if ($res->num_rows > 0) {

                            $i = 1;
                            while ($row = $res->fetch_assoc()) {
                                ?>

                                <li class="list-item mt-2 mb-2">
                                    <div class="row">
                                        <div class="col-1"> <i class="bi bi-person-fill me-2 text-primary"></i></div>
                                        <div class="col-8">
                                            <div class="fs-username  text-success">
                                                <?php echo $row['profilename'] ?? '<span class="text-danger">No Name Define.</span>'; ?>
                                                <span class="text-secondary fw-normal "> |
                                                    <?php echo htmlspecialchars($row['userlevel']); ?> </span>
                                            </div>
                                            <div class="fs-tiny fw-normal text-small text-dark">
                                                <?php echo htmlspecialchars($row['email']); ?>
                                            </div>
                                        </div>

                                        <div class="col">
                                            <button class=" btn btn-sm text-primary "
                                                onclick="loginuser('<?php echo addslashes($row['email']); ?>');">
                                                <i class="bi bi-box-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </li>

                                <?php

                            }

                            echo '</ul>';
                        } else {
                            echo '<div class="alert alert-warning small">No users found for this institution.</div>';
                        }

                        $query->close();
                        ?>
                    </div>

                </div>
            </div>




            <div class="tab-pane fade  " id="navs-justified-home" role="tabpanel">
                <div class="col-lg-12 p-0">
                    <div class="row mt-3">
                        <div class="col pt-3">
                            <small class="fw-medium">Developement Timeline</small>
                        </div>

                        <div class="col">
                            <button class="btn btn-primary btn-sm mb-3  float-end" onclick="openModal()">+ Add
                                Feature</button>
                        </div>

                    </div>


                    <div class="demo-inline-spacing mt-2 " style="max-height:70vh; overflow-y:auto;">
                        <ul class="list-group list-group-timeline">
                            <?php foreach ($timeline as $row): ?>
                                <li id="row_<?= $row['id'] ?>" class="list-group-item list-group-timeline-danger">
                                    <div class="row">
                                        <div class="col-9">
                                            <small class="text-body-secondary"><?= timeAgo($row['created_at']) ?></small>
                                            <br>
                                            <span><?= statusBadge($row['status']) ?></span>
                                            <br>
                                            <span><?= htmlspecialchars($row['feature_name']) ?></span>
                                        </div>
                                        <div class="col-3 pt-6">

                                            <span class="cursor-pointer" onclick="openModal(<?= $row['id'] ?>)"><i
                                                    class="bi bi-pencil-square text-warning fs-5"></i></span>

                                            <span class="ms-2 cursor-pointer" onclick="deleteEntry(<?= $row['id'] ?>)"><i
                                                    class="bi bi-trash text-danger fs-5"></i></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <span class="small text-info"><?= htmlspecialchars($row['description']);
                                            if ($row['description'] != '')
                                                echo '<br>'; ?></span>

                                            <span
                                                class="small text-dark "><?= ucfirst(str_replace('_', ' ', $row['action_type'])) ?></span>

                                            &mdash; <span
                                                class="small text-dark "><?= htmlspecialchars($row['logged_by']) ?></span>

                                        </div>
                                    </div>


                                </li>
                                <?php

                                $remove = htmlspecialchars($row['feature_name']);
                                foreach ($fla as $key => $value) {
                                    if ($value === $remove) {
                                        unset($fla[$key]);
                                    }
                                }

                            endforeach;

                            array_values($fla);
                            // var_dump($fla);
                        
                            foreach ($fla as $fff) {
                                $k = "INSERT INTO dev_timeline (page_name, feature_name, action_type, status, description, logged_by, created_at) VALUES ('$currentFile', '$fff', 'Implement', 'Draft', '', '$usr', '$cur');";
                                $conn->query($k);
                            }

                            ?>
                            <li class="list-group-item list-group-timeline-danger" hidden>Bonbon toffee muffin</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php } ?>



        <div class="tab-pane fade" id="navs-justified-profile" role="tabpanel">
            <?php echo $pmsg; ?>



        </div>





        <div class="tab-pane fade" id="navs-justified-messages" role="tabpanel">
            <?php

            // FAQ লোড
            $sql = "SELECT * FROM faqs WHERE page_name=? ORDER BY id DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $currentFile);
            $stmt->execute();
            $result = $stmt->get_result();
            ?>

            <div class="container mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6><?= ucfirst($page_title) ?> FAQs</h6>
                    <?php if ($is_admin >= 4) { ?>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#faqModal"
                            onclick="openAddModal()"><i class="bi bi-plus"></i> Add FAQ</button>
                    <?php } ?>
                </div>

                <div class="accordion" id="faqAccordion">
                    <?php
                    $i = 1;
                    while ($faq = $result->fetch_assoc()) { ?>
                        <div class="accordion-item mb-2" id="faq-<?= $faq['id'] ?>">
                            <h2 class="accordion-header" id="heading<?= $i ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse<?= $i ?>" aria-expanded="false">
                                    <?= htmlspecialchars($faq['question']) ?>
                                </button>
                            </h2>
                            <div id="collapse<?= $i ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= nl2br(htmlspecialchars($faq['answer'])) ?>

                                    <?php if ($is_admin >= 4) { ?>
                                        <div class="mt-2 text-end">
                                            <button class="btn btn-sm btn-warning" onclick="openEditModal(<?= $faq['id'] ?>)">✏️
                                                Edit</button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteFAQ(<?= $faq['id'] ?>)">🗑
                                                Delete</button>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <?php $i++;
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>




<!-- Modal -->
<div class="modal fade" id="insListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title">Institution List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body m-5 fs-6" id="ins-list" style="max-height:70vh; overflow-y:auto;">

            </div>

        </div>
    </div>
</div>


<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Institution Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="ins-details" class="p-2 text-center text-secondary">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="faqModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="faqForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="faqModalLabel">Add FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="faq_id">
                    <input type="hidden" name="page_name" value="<?= $currentFile ?>">

                    <div class="mb-3">
                        <label class="form-label">Question</label>
                        <textarea name="question" id="question" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Answer</label>
                        <textarea name="answer" id="answer" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById("faqForm").reset();
        document.getElementById("faq_id").value = "";
        document.getElementById("faqModalLabel").textContent = "Add FAQ";
    }

    function openEditModal(id) {
        fetch('core/faq_action.php?action=get&id=' + id)
            .then(res => res.json())
            .then(data => {
                document.getElementById("faq_id").value = data.id;
                document.getElementById("question").value = data.question;
                document.getElementById("answer").value = data.answer;
                document.getElementById("faqModalLabel").textContent = "Edit FAQ";
                new bootstrap.Modal(document.getElementById("faqModal")).show();
            });
    }

    document.getElementById("faqForm").addEventListener("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('core/faq_action.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                alert(res.message);
                if (res.status === "success") location.reload();
            });
    });

    function deleteFAQ(id) {
        if (!confirm("Are you sure to delete this FAQ?")) return;
        fetch('core/faq_action.php?action=delete&id=' + id)
            .then(res => res.json())
            .then(res => {
                alert(res.message);
                if (res.status === "success") location.reload();
            });
    }
</script>





<div class="modal fade" id="devNoteModal" tabindex="-1" aria-labelledby="devNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="devNoteModalLabel">Add Developer Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="devNoteForm">
                    <input type="hidden" id="dev_ticket_id" name="ticket_id" value="">
                    <input type="hidden" id="dev_note_id" name="dev_note_id" value="">
                    <textarea class="form-control mb-3" id="dev_note_text" name="note" rows="5"
                        placeholder="Write your note (each line will be a separate record)..."></textarea>

                    <select id="dev_note_status" name="status" class="form-select">
                        <option value="New">New</option>
                        <option value="Open">Open</option>
                        <option value="Waiting">Waiting</option>
                        <option value="Replied">Replied</option>
                        <option value="Progress">Progress</option>
                        <option value="Hold">Hold</option>
                        <option value="Resolved">Resolved</option>
                        <option value="Closed">Closed</option>
                    </select>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="save_dev_note();"
                    id="saveDevNoteBtn">Save</button>
            </div>
        </div>
    </div>
</div>






<!-- Modal -->
<div class="modal fade" id="devModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="devForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Dev Timeline Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="entry_id" id="entry_id" value="0">
                <input type="hidden" name="cat" id="cat" value="entry">
                <input type="hidden" name="page_name" value="<?= htmlspecialchars($currentFile) ?>">

                <div class="mb-3">
                    <label class="form-label">Feature</label>
                    <select name="feature_select" id="feature_select" class="form-select"
                        onchange="toggleFeatureInput(this)">
                        <option value="">-- Select Feature --</option>
                        <?php foreach ($features as $f): ?>
                            <option value="<?= htmlspecialchars($f) ?>"><?= htmlspecialchars($f) ?></option>
                        <?php endforeach; ?>
                        <option value="new">+ New Feature</option>
                    </select>
                    <input type="text" name="feature_new" id="feature_new" class="form-control mt-2 d-none"
                        placeholder="Enter new feature">
                </div>

                <div class="mb-3">
                    <label class="form-label">Action Type</label>
                    <select name="action_type" id="action_type" class="form-select" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($actionTypes as $a): ?>
                            <option value="<?= $a ?>"><?= ucfirst(str_replace('_', ' ', $a)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($statusTypes as $s): ?>
                            <option value="<?= $s ?>"><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="submitModalForm();" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    var availableFeatures = <?= json_encode($features) ?>;
    $("#feature_new").autocomplete({ source: availableFeatures });

    function toggleFeatureInput(sel) {
        $('#feature_new').toggleClass('d-none', sel.value !== 'new');
    }

    // Open modal with data (edit) or empty (new)
    function openModal(id = 0) {
        $('#devForm')[0].reset();
        $('#entry_id').val(id); // important
        $('#feature_new').addClass('d-none');

        if (id) {
            $.post('dev-log/ajax.php', { id: id }, function (data) {
                if (data) {
                    $('#feature_select').val(data.feature_name);
                    $('#action_type').val(data.action_type);
                    $('#status').val(data.status);
                    $('#notes').val(data.description);
                    new bootstrap.Modal(document.getElementById('devModal')).show();
                }
            }, 'json');
        } else {
            new bootstrap.Modal(document.getElementById('devModal')).show();
        }
    }



    function submitModalForm() {
        // form reference
        var $form = $('#devForm');

        // serialize করা ডেটা দেখানোর জন্য
        var formData = $form.serialize();
        console.log("Form data being sent:", formData);

        $.post('dev-log/ajax.php', formData, function (resp) {
            console.log("AJAX response:", resp);

            if (resp.success) {
                // নতুন HTML লিস্ট আইটেম তৈরি
                let html = `<li id="row_${resp.id}" class="list-group-item list-group-timeline-danger">
                <div class="row">
                    <div class="col-9">
                        <span class="small text-secondary">${resp.created_at}</span><br>
                        <span>${resp.status_badge}</span><br>
                        <span>${resp.feature_name}</span>
                    </div>
                    <div class="col-3">
                        <span onclick="openModal(${resp.id})">
                            <i class="bi bi-pencil-square text-warning fs-5"></i>
                        </span>
                        <span class="ms-2" onclick="deleteEntry(${resp.id})">
                            <i class="bi bi-trash text-danger fs-5"></i>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <span class="small text-info">${resp.description ? resp.description + '<br>' : ''}</span>
                        <span class="small text-dark">${resp.action_type.replace(/_/g, ' ')}</span>
                        &mdash; <span class="small text-dark">${resp.logged_by}</span>
                    </div>
                </div>
            </li>`;

                // যদি একই আইডি আগে থেকে থাকে, replace; নাহলে prepend
                if ($('#row_' + resp.id).length) {
                    $('#row_' + resp.id).replaceWith(html);
                } else {
                    $('.list-group-timeline').prepend(html);
                }

                // modal hide
                bootstrap.Modal.getInstance(document.getElementById('devModal')).hide();
            } else {
                alert(resp.message);
            }
        }, 'json');
    }



    // Delete
    function deleteEntry(id) {
        if (confirm('Are you sure?')) {
            $.post('dev-log/ajax.php', { delete: id }, function (resp) {
                if (resp.success) $('#row_' + id).remove();
            }, 'json');
        }
    }




</script>


<script>

    document.getElementById('openInsList').addEventListener('click', function () {
        const modalEl = document.getElementById('insListModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        fetch('core/get-ins-list.php')
            .then(res => res.text()) // HTML রেসপন্স পড়া
            .then(html => {
                document.getElementById('ins-list').innerHTML = html;
            })
            .catch(err => console.error('❌ Fetch error:', err));
    });

</script>


<script>
    function viewDetails(sccode) {
        // মডাল ওপেন
        var detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));
        detailsModal.show();

        // লোডিং স্পিনার দেখাও
        const insList = document.getElementById('ins-details');
        insList.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    `;

        // AJAX request
        fetch('core/get-ins-details.php?sccode=' + encodeURIComponent(sccode))
            .then(res => res.text())
            .then(html => {
                insList.innerHTML = html; // রেসপন্স HTML মডালে দেখাও
            })
            .catch(err => {
                console.error(err);
                insList.innerHTML = '<div class="text-danger">Failed to load data</div>';
            });
    }
</script>

<script>
    function loginSccode(sccode) {
        if (!confirm("Do you want to login as " + sccode + "?")) return;

        fetch('core/admin-login-sccode.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'sccode=' + encodeURIComponent(sccode)
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('✅ Logged in as ' + sccode);
                    location.reload();
                } else {
                    alert('❌ ' + (data.message || 'Failed to update sccode.'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('⚠️ Request failed!');
            });
    }
</script>


<script>
    function openModal(id) {
        const modalEl = document.getElementById("devNoteModal");
        const modal = new bootstrap.Modal(modalEl);

        document.getElementById("devNoteModalLabel").innerText = "Add Developer Note";
        document.getElementById("dev_note_text").value = "";
        document.getElementById("dev_note_status").value = "New";
        document.getElementById("dev_ticket_id").value = "";
        document.getElementById("dev_note_id").value = id;
        delete modalEl.dataset.mode; // respond মোড রিসেট

        modal.show();
    }

    function save_dev_note() {
        const noteText = $("#dev_note_text").val().trim();
        const status = $("#dev_note_status").val();
        if (!noteText) return alert("Please write something first.");

        const mode = $("#devNoteModal").data("mode") || "add";
        const url = "tickets/respond_devnote.php";

        $.post(url, {
            sccode: <?= json_encode($sccode); ?>,
            ref_id: $("#dev_note_id").val(),
            ticket_id: $("#dev_ticket_id").val() || 0,
            status: status,
            note: noteText,
            notes: noteText
        }, function (res) {
            if (res === "success") {
                alert("Note saved successfully!");
                $("#devNoteModal").modal("hide"); // ✅ এটিই যথেষ্ট
                $("#devNotesContainer").load(location.href + " #devNotesContainer>*", "");
            } else {
                alert("Error: " + res);
                console.log("Error: " + res);
            }
        });

        $("#devNoteModal").modal("hide");
    }

</script>

<script>
    function toggleHistory(ref_id, btn) {
        const container = $("#history-" + ref_id);
        if (container.hasClass("d-none")) {
            $(btn).html('<i class="bi bi-dash-circle p-1 ps-2 pe-2"></i>');
            // Load all history for this ref_id
            $.get("tickets/load_devnote_history.php", { ref_id }, function (data) {
                container.html(data).removeClass("d-none");
            });
        } else {
            $(btn).html('<i class="bi bi-clock-history p-1 ps-2 pe-2"></i>');
            container.addClass("d-none");
        }
    }
</script>