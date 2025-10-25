<?php require_once 'header.php';

// ডাটাবেস থেকে সব রেকর্ড লোড
$sql = "SELECT * FROM suspicious_activity_types ORDER BY id DESC";
$result = $conn->query($sql);
if (!$result) {
    die('Query Error: ' . $conn->error);
}

?>
<style>
    .top-actions {
        display: flex;
        gap: 12px;
        margin-bottom: 6px;
    }

    td.description {
        max-width: 420px;
        white-space: pre-wrap;
    }



    /* modal simple styles */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center
    }

    .modal .inner {
        background: #fff;
        padding: 16px;
        border-radius: 8px;
        width: 760px;
        max-width: 95%
    }

    .form-row {
        margin-bottom: 10px
    }

    .form-row label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">


    <div class="card">
        <div class="card-header">
            <h4>🔧 Suspicious Activity Types</h4>
            <div class="top-actions">
                <a href="#" id="btnAdd" class="btn "> Add New </a>
                <a href="#" id="btnRefresh" class="btn"> Refresh </a>
                <div style="margin-left:auto" class="small">Total: <?= $result->num_rows ?> records</div>
            </div>
        </div>


        <div class="table-responsive">
            <table id="typesTable" class="table table-sm display fs-6" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Risk Score</th>
                        <th>Recommended Action</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr data-id="<?= $row['id'] ?>">
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['category']) ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td class="description"><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= intval($row['risk_score']) ?></td>
                            <td><span
                                    class="badge <?= $row['recommended_action'] ?>"><?= strtoupper($row['recommended_action']) ?></span>
                            </td>
                            <td><?php echo date('d-m-Y', strtotime($row['created_at'])) . '<br>' . date('H:i:s', strtotime($row['created_at'])); ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="#" class="btnEdit small btn p-0 px-4 "><i class="bi bi-pencil text-primary"></i></a>
                                    <a href="#" class="btnDelete small btn p-0 "><i class="bi bi-trash text-danger"></i></a>
                                </div>

                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Modal: Add / Edit -->
    <div id="modal" class="modal">
        <div class="inner">
            <h3 id="modalTitle">নতুন suspicious activity যোগ করুন</h3>
            <form id="frmType">
                <input type="hidden" name="id" id="type_id" value="">
                <div class="form-row">
                    <label>Category</label>
                    <input type="text" name="category" id="category" required>
                </div>
                <div class="form-row">
                    <label>Title</label>
                    <input type="text" name="title" id="title" required>
                </div>
                <div class="form-row">
                    <label>Description</label>
                    <textarea name="description" id="description" rows="4"></textarea>
                </div>
                <div class="form-row">
                    <label>Risk Score (0-100)</label>
                    <input type="text" name="risk_score" id="risk_score" value="10">
                </div>
                <div class="form-row">
                    <label>Recommended Action</label>
                    <select name="recommended_action" id="recommended_action">
                        <option value="log_only">log_only</option>
                        <option value="alert">alert</option>
                        <option value="review">review</option>
                        <option value="block">block</option>
                    </select>
                </div>

                <div style="text-align:right;margin-top:10px">
                    <button type="button" id="btnCancel" class="btn secondary">Cancel</button>
                    <button type="submit" id="btnSave" class="btn">Save</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script></script>

<!-- <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script> -->
<script>
    $(document).ready(function () {
        var table = $('#typesTable').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel']
        });

        $('#btnRefresh').on('click', function (e) { e.preventDefault(); location.reload(); });

        // Modal functions
        function openModal(mode, data) {
            $('#modalTitle').text(mode === 'add' ? 'নতুন suspicious activity যোগ করুন' : 'Edit suspicious activity');
            if (mode === 'add') {
                $('#type_id').val('');
                $('#category').val('');
                $('#title').val('');
                $('#description').val('');
                $('#risk_score').val('10');
                $('#recommended_action').val('alert');
            } else {
                $('#type_id').val(data.id);
                $('#category').val(data.category);
                $('#title').val(data.title);
                $('#description').val(data.description);
                $('#risk_score').val(data.risk_score);
                $('#recommended_action').val(data.recommended_action);
            }
            $('#modal').css('display', 'flex');
        }
        function closeModal() { $('#modal').hide(); }

        $('#btnAdd').on('click', function (e) { e.preventDefault(); openModal('add'); });
        $('#btnCancel').on('click', function () { closeModal(); });
        $('#modal').on('click', function (e) { if (e.target === this) closeModal(); });

        // Edit button click
        $(document).on('click', '.btnEdit', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr');
            var id = tr.data('id');
            // fetch row values
            var row = {
                id: id,
                category: tr.find('td').eq(1).text().trim(),
                title: tr.find('td').eq(2).text().trim(),
                description: tr.find('td').eq(3).text().trim(),
                risk_score: tr.find('td').eq(4).text().trim(),
                recommended_action: tr.find('.badge').hasClass('alert') ? 'alert' : (tr.find('.badge').hasClass('block') ? 'block' : (tr.find('.badge').hasClass('review') ? 'review' : 'log_only'))
            };
            openModal('edit', row);
        });

        // Delete
        $(document).on('click', '.btnDelete', function (e) {
            e.preventDefault();
            if (!confirm('Are you sure to delete this type?')) return;
            var tr = $(this).closest('tr');
            var id = tr.data('id');
            $.post('action_types.php', { action: 'delete', id: id }, function (resp) {
                if (resp.success) { location.reload(); } else { alert('Delete failed: ' + (resp.message || 'Unknown')) }
            }, 'json');
        });

        // Save (Add / Edit) via AJAX
        $('#frmType').on('submit', function (e) {
            e.preventDefault();
            var form = $(this).serializeArray();
            $.post('action_types.php', form, function (resp) {
                if (resp.success) { closeModal(); location.reload(); } else { alert('Save failed: ' + (resp.message || 'Unknown')) }
            }, 'json');
        });
    });
</script>

<!-- ----------------------------------- -->
</body>

</html>