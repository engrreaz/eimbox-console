<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$currentFile = basename($_SERVER['PHP_SELF']);

// Action Types
$actionTypes = [
    'implement',
    'update',
    'bug_fix',
    'remove',
    'change',
    'refactor',
    'optimize',
    'security_patch',
    'deprecate',
    'migrate',
    'test_case',
    'rollback',
    'hotfix'
];

// Status Types
$statusTypes = [
    'draft',
    'planning',
    'in_progress',
    'testing',
    'alpha',
    'beta',
    'rc',
    'staging',
    'stable',
    'lts',
    'deprecated',
    'archived'
];

// Timeline
$timeline = [];
$res = $conn->prepare("SELECT * FROM dev_timeline WHERE page_name=? ORDER BY created_at DESC");
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
    $colors = [
        'draft' => 'secondary',
        'planning' => 'warning',
        'in_progress' => 'info',
        'testing' => 'info',
        'alpha' => 'primary',
        'beta' => 'primary',
        'rc' => 'primary',
        'staging' => 'info',
        'stable' => 'success',
        'lts' => 'success',
        'deprecated' => 'dark',
        'archived' => 'secondary'
    ];
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

    $units = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second'
    ];
    $values = [
        'y' => $diff->y,
        'm' => $diff->m,
        'w' => $weeks,
        'd' => $days,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s
    ];

    $strings = [];
    foreach ($units as $k => $label) {
        if ($values[$k] > 0) {
            $strings[$k] = $values[$k] . ' ' . $label . ($values[$k] > 1 ? 's' : '');
        }
    }

    if (!$full)
        $strings = array_slice($strings, 0, 1);
    return $strings ? implode(', ', $strings) . ' ago' : 'just now';
}
?>
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script> -->

<h3>Development Timeline: <?= htmlspecialchars($currentFile) ?></h3>
<button class="btn btn-primary mb-3" onclick="openModal()">+ Add Entry</button>

<div id="timelineTable">
    <table class="table table-striped table-sm">
        <thead>
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
        <form id="devForm" class="modal-content"  method="post">
            <div class="modal-header">
                <h5 class="modal-title">Dev Timeline Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="entry_id">
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
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
<script>
    var availableFeatures = <?php echo json_encode($features); ?>;
    $("#feature_new").autocomplete({ source: availableFeatures });

    function toggleFeatureInput(sel) {
        $('#feature_new').toggleClass('d-none', sel.value !== 'new');
    }

    function openModal(id = 0) {
        $('#devForm')[0].reset();
        $('#entry_id').val(id);
        $('#feature_new').addClass('d-none');

        if (id) {
            $.post('dev-log/ajax.php', { id: id }, function (data) {
                $('#feature_select').val(data.feature_in_list ? data.feature_name : 'new');
                if (!data.feature_in_list) $('#feature_new').removeClass('d-none').val(data.feature_name);
                $('#action_type').val(data.action_type);
                $('#status').val(data.status);
                $('#notes').val(data.description);
                new bootstrap.Modal(document.getElementById('devModal')).show();
            }, 'json');
        } else {
            new bootstrap.Modal(document.getElementById('devModal')).show();
        }
    }

    $('#devForm').submit(function (e) {
        e.preventDefault();
        $.post('dev-log/ajax.php', $(this).serialize(), function (resp) {
            if (resp.success) {
                let html = `
                <tr id="row_${resp.id}">
                    <td>${resp.created_at}</td>
                    <td>${resp.feature_name}</td>
                    <td>${resp.action_type.replace(/_/g, ' ')}</td>
                    <td><span class="badge bg-${resp.status_color}">${resp.status}</span></td>
                    <td>${resp.description}</td>
                    <td>${resp.logged_by}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="openModal(${resp.id})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteEntry(${resp.id})">Delete</button>
                    </td>
                </tr>`;
                if ($('#row_' + resp.id).length) {
                    $('#row_' + resp.id).replaceWith(html);
                } else {
                    $('#timelineTable tbody').prepend(html);
                }
                bootstrap.Modal.getInstance(document.getElementById('devModal')).hide();
            } else {
                alert(resp.message);
            }
        }, 'json');
    });

    function deleteEntry(id) {
        if (confirm('Are you sure to delete?')) {
            $.post('dev-log/ajax.php', { delete: id }, function (resp) {
                if (resp.success) $('#row_' + id).remove();
            }, 'json');
        }
    }
</script>