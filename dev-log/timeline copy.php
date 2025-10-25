<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

// $currentFile = basename($_SERVER['PHP_SELF']);

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
    ORDER BY t1.feature_name ASC
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

function timeAgo($datetime, $full = false)
{
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    $weeks = floor($diff->d / 7);
    $days = $diff->d - ($weeks * 7);
    $units = ['y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second'];
    $values = ['y' => $diff->y, 'm' => $diff->m, 'w' => $weeks, 'd' => $days, 'h' => $diff->h, 'i' => $diff->i, 's' => $diff->s];
    $strings = [];
    foreach ($units as $k => $label) {
        if ($values[$k] > 0)
            $strings[$k] = $values[$k] . ' ' . $label . ($values[$k] > 1 ? 's' : '');
    }
    if (!$full)
        $strings = array_slice($strings, 0, 1);
    return $strings ? implode(', ', $strings) . ' ago' : 'just now';
}
?>





<div class="card-body">
    <div class="row">
        <!-- Timeline Style -->
        <div class="col-lg-12 p-2">
            <div class="row">
                <div class="col">
                    <small class="fw-medium">Developement Timeline</small>
                </div>
                <div class="col">
                    <button class="btn btn-primary btn-sm mb-3 float-end" onclick="openModal()">+ Add Feature</button>
                </div>
            </div>

            <div class="demo-inline-spacing mt-4" >
                <ul class="list-group list-group-timeline">
                    <?php foreach ($timeline as $row): ?>
                        <li id="row_<?= $row['id'] ?>" class="list-group-item list-group-timeline-danger">
                            <div class="row">
                                <div class="col-9">
                                    <span class="small text-secondary"><?= timeAgo($row['created_at']) ?></span>
                                    <br>
                                    <span><?= statusBadge($row['status']) ?></span>
                                    <br>
                                    <span><?= htmlspecialchars($row['feature_name']) ?></span>





                                </div>
                                <div class="col-3">

                                    <span onclick="openModal(<?= $row['id'] ?>)"><i
                                            class="bi bi-pencil-square text-warning fs-5"></i></span>

                                    <span class="ms-2" onclick="deleteEntry(<?= $row['id'] ?>)"><i
                                            class="bi bi-trash text-danger fs-5"></i></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <span
                                        class="small text-info"><?= htmlspecialchars($row['description']);
                                        if ($row['description'] != '')
                                            echo '<br>'; ?></span>

                                    <span
                                        class="small text-dark "><?= ucfirst(str_replace('_', ' ', $row['action_type'])) ?></span>

                                    &mdash; <span class="small text-dark "><?= htmlspecialchars($row['logged_by']) ?></span>

                                </div>
                            </div>


                        </li>
                    <?php endforeach; ?>
                    <li class="list-group-item list-group-timeline-danger">Bonbon toffee muffin</li>
                </ul>
            </div>
        </div>

    </div>
</div>


<div id="timelineTable" id="table-responsive m-0" style="max-height:400px; overflow:auto;" hidden>
    <table class="table table-sm table-condensed small mb-0">
        <thead class="table-light">
            <tr>
                <th>Date</th>
                <th>Feature</th>
                <th>Action</th>
                <th>Status</th>
                <th>Notes</th>
                <th>By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($timeline as $row): ?>
                <tr id="row_<?= $row['id'] ?>">
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="openModal(<?= $row['id'] ?>)">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteEntry(<?= $row['id'] ?>)">Delete</button>
                    </td>
                    <td><?= timeAgo($row['created_at']) ?></td>
                    <td><?= htmlspecialchars($row['feature_name']) ?></td>
                    <td><?= ucfirst(str_replace('_', ' ', $row['action_type'])) ?></td>
                    <td><?= statusBadge($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['logged_by']) ?></td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
        // alert("Form data being sent:\n" + formData); // কী-ভ্যালুগুলো দেখাবে
        console.log("Form data:", formData);

        // serialize and post


        $.post('dev-log/ajax.php', formData, function (resp) {
            console.log("AJAX response:", resp); // সার্ভার রেসপন্স কনসোলে দেখাবে
            if (resp.success) {
                let html = `<tr id="row_${resp.id}">
                <td>
                    <button class="btn btn-sm btn-warning" onclick="openModal(${resp.id})">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteEntry(${resp.id})">Delete</button>
                </td>
                <td>${resp.created_at}</td>
                <td>${resp.feature_name}</td>
                <td>${resp.action_type.replace(/_/g, ' ')}</td>
                <td><span class="badge bg-${resp.status_color}">${resp.status}</span></td>
                <td>${resp.description}</td>
                <td>${resp.logged_by}</td>
            </tr>`;

                if ($('#row_' + resp.id).length) {
                    $('#row_' + resp.id).replaceWith(html);
                } else {
                    $('#timelineTable tbody').prepend(html);
                }

                // hide modal
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