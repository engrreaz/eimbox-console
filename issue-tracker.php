<?php require_once 'header.php'; ?>

<style>
    .text-secondary {
        color: beige !important;
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">

    <?php

    $col_icon = [
        'crud' => 'bi bi-code-slash',
        'ui' => 'bi bi-braces-asterisk',
        'image' => 'bi bi-image',
        'perm' => 'bi bi-shield-check',
        'error' => 'bi bi-bug-fill',
        'feature' => 'bi bi-feather',
        'doc' => 'bi bi-file-earmark-text',
        'youtube' => 'bi bi-youtube'
    ];


    $menu_text = [
        null => 'Not Define',
        '0' => 'Error',
        '1' => 'Partial Error',
        '2' => 'Major Error',
        '3' => 'Testing',
        '4' => 'Stable',
    ];

    // modulemanager থেকে ডেটা ফেচ করা
    $sql = "SELECT id, slno, module_name, module_topic, status_name, nav_icon, crud, ui, image, perm, error, feature, doc, youtube, ytlink
            FROM modulemanager
            WHERE module_topic IS NOT NULL AND module_topic != ''
            ORDER BY module_name, slno";
    $result = $conn->query($sql);

    $modules = [];
    $stats = ['red' => 0, 'orange' => 0, 'violet' => 0, 'blue' => 0, 'green' => 0];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $modules[$row['module_name']][] = $row;

            // পরিসংখ্যান গণনা
            foreach (['crud', 'ui', 'image', 'perm', 'error', 'feature', 'doc', 'youtube'] as $col) {
                switch ($row[$col]) {
                    case 0:
                        $stats['red']++;
                        break;
                    case 1:
                        $stats['orange']++;
                        break;
                    case 2:
                        $stats['violet']++;
                        break;
                    case 3:
                        $stats['blue']++;
                        break;
                    case 4:
                        $stats['green']++;
                        break;
                }
            }
        }
    }
    ?>

    <div class="mb-3">
        <strong>Statistics:</strong>
        <span class="text-danger">Red: <?php echo $stats['red']; ?></span> |
        <span class="text-warning">Orange: <?php echo $stats['orange']; ?></span> |
        <span class="text-purple">Violet: <?php echo $stats['violet']; ?></span> |
        <span class="text-primary">Blue: <?php echo $stats['blue']; ?></span> |
        <span class="text-success">Green: <?php echo $stats['green']; ?></span>
    </div>

    <div class="accordion" id="moduleAccordion">
        <?php foreach ($modules as $module_name => $items): ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-<?php echo md5($module_name); ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse-<?php echo md5($module_name); ?>" aria-expanded="false">
                        <?php echo htmlspecialchars($module_name); ?>
                    </button>
                </h2>
                <div id="collapse-<?php echo md5($module_name); ?>" class="accordion-collapse collapse"
                    data-bs-parent="#moduleAccordion">
                    <div class="accordion-body p-0">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Topic</th>
                                    <th>Status</th>
                                    <th>CRUD</th>
                                    <th>UI</th>
                                    <th>Image</th>
                                    <th>Perm</th>
                                    <th>Error</th>
                                    <th>Feature</th>
                                    <th>Doc</th>
                                    <th>YouTube</th>
                                    <th>Link</th>
                                </tr>
                            </thead>
                           <tbody class="sortable-body">
                                <?php foreach ($items as $item): ?>
                                    <tr data-id="<?= $item['id'] ?>">
                                        <td class="text-center">
    <i class="bi bi-grip-vertical drag-handle" style="cursor:grab"></i>
</td>
                                        <td><?php echo htmlspecialchars($item['module_topic']); ?></td>
                                        
                                        <td style="color:<?= $page_status_colors[$item['status_name']]; ?>;">
                                            <?php echo htmlspecialchars($page_status_names[$item['status_name']]); ?>
                                        </td>
                                        <?php
                                        $cols = ['crud', 'ui', 'image', 'perm', 'error', 'feature', 'doc', 'youtube'];
                                        $colors = [0 => 'danger', 1 => 'warning', 2 => 'info', 3 => 'primary', 4 => 'success', null => 'secondary'];
                                        foreach ($cols as $col):
                                            $val = $item[$col];
                                            $color = isset($colors[$val]) ? $colors[$val] : 'secondary';
                                            $icon = $col_icon[$col];
                                            ?>
                                            <td class="text-center p-1">
                                                <div class="dropdown">
                                                    <i class="<?php echo $icon; ?> text-<?php echo $color; ?>"
                                                        data-bs-toggle="dropdown" style="cursor:pointer; font-size:18px;"></i>
                                                    <ul class="dropdown-menu">
                                                        <?php for ($i = 0; $i <= 4; $i++): ?>
                                                            <li>
                                                                <a class="dropdown-item update-status" href="#"
                                                                    data-idno="<?php echo $item['id']; ?>"
                                                                    data-id="<?php echo $item['module_name']; ?>"
                                                                    data-col="<?php echo $col; ?>" data-val="<?php echo $i; ?>">
                                                                    <?php echo ucfirst($menu_text[$i] ?? $col) ?>
                                                                    <i class="bi bi-arrow-right"></i>
                                                                    <?php
                                                                    // echo $i; 
                                                                    ?>
                                                                </a>
                                                            </li>
                                                        <?php endfor; ?>
                                                    </ul>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                        <td>
                                            <div class=" ytlink" data-id="<?= $item['id'] ?>" style="width:100%; min-height: 20px;;"
                                                data-val="<?= htmlspecialchars($item['ytlink']) ?>">
                                                <?= htmlspecialchars($item['ytlink'] ?? '-') ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>


<!-- YT Link Edit Modal -->
<div class="modal fade" id="ytModal">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit YouTube Link</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="yt_id">
                <label>Link</label>
                <input type="text" id="yt_val" class="form-control">
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="saveYT()">Save</button>
            </div>
        </div>
    </div>
</div>



<?php require_once 'footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.update-status').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                let module = this.dataset.id;
                let id = this.dataset.idno;
                let col = this.dataset.col;
                let val = this.dataset.val;

                fetch('core/update_module_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'module_name=' + encodeURIComponent(module) + '&col=' + encodeURIComponent(col) + '&val=' + encodeURIComponent(val) + '&id=' + encodeURIComponent(id)
                }).then(res => res.text()).then(data => {
                    alert('Status updated successfully!');
                    location.reload();
                });
            });
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        document.querySelectorAll('.ytlink').forEach(el => {
            el.addEventListener('click', function () {
                yt_id.value = this.dataset.id;
                yt_val.value = this.dataset.val;
                new bootstrap.Modal(ytModal).show();
            });
        });

    });

    function saveYT() {
        const fd = new URLSearchParams({
            id: yt_id.value,
            ytlink: yt_val.value
        });

        fetch('core/update-ytlink.php', {
            method: 'POST',
            body: fd
        })
            .then(r => r.text())
            .then(msg => {
                if (msg === 'OK') location.reload();
                else alert(msg);
            });
    }
</script>

<script>
document.querySelectorAll('.sortable-body').forEach(function(tbody){

    new Sortable(tbody, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'table-warning',

        onEnd: function () {
            let order = [];
            tbody.querySelectorAll('tr').forEach((tr, index) => {
                order.push({
                    id: tr.dataset.id,
                    slno: index + 1
                });
            });

            fetch('core/update_slno.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify(order)
            })
            .then(r => r.text())
            .then(msg => {
                console.log(msg);
            });
        }
    });

});
</script>