<?php require_once 'header.php'; ?>
<?php

$files = array_filter(scandir(__DIR__), function ($f) {
    return is_file($f) && substr($f, -4) === '.php'; // শুধু php ফাইল ধরলাম
});
?>


<div class="container-fluid py-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-diagram-3"></i> Page Access Map (Per Package)</h4>



    <div class="card shadow-sm">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-light ">
                    <tr>
                        <th>#</th>
                        <th>Module</th>
                        <th>Page</th>
                        <th>Nav Title</th>
                        <?php
                        // সব প্যাকেজ লোড করো
                        $pkgQ = $conn->query("SELECT id, package_name FROM packages ORDER BY serial ASC");
                        $packages = [];
                        while ($p = $pkgQ->fetch_assoc()) {
                            $packages[] = $p;
                            echo "<th class='text-center'>{$p['package_name']}</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    foreach ($files as $page) {
                        // modulemanager থেকে মিলে যাওয়ার চেষ্টা
                        $modStmt = $conn->prepare("SELECT module_name, nav_title FROM modulemanager WHERE related_pages=?");
                        $modStmt->bind_param("s", $page);
                        $modStmt->execute();
                        $modRes = $modStmt->get_result();
                        $mod = $modRes->fetch_assoc();

                        $moduleName = $mod ? $mod['module_name'] : '—';
                        $navTitle = $mod ? $mod['nav_title'] : '—';

                        echo "<tr>
                <td>{$i}</td>
                <td>{$moduleName}</td>
                <td>{$page}</td>
                <td>{$navTitle}</td>";

                        foreach ($packages as $pkg) {
                            $mapQ = $conn->prepare("SELECT * FROM package_map WHERE page_name=? AND package_id=?");
                            $mapQ->bind_param("si", $page, $pkg['id']);
                            $mapQ->execute();
                            $mapR = $mapQ->get_result();
                            $map = $mapR->fetch_assoc();

                            $access = $map['access'] ?? '&mdash;';
                            $entry_limit = $map['entry_limit'] ?? null;
                            $view_limit = $map['view_limit'] ?? null;
                            $total_time_limit = $map['total_time_limit'] ?? null;
                            $access_count_limit = $map['access_count_limit'] ?? null;
                            $max_stay_limit = $map['max_stay_limit'] ?? null;
                            $print = $map['print'] ?? null;
                            $modified_time = $map['modified_time'] ?? null;

                            // বাটন ক্লাস + লেবেল
                            if ($access === "Yes") {
                                $btnClass = 'btn-outline-success';
                                $btnLabel = '✅';
                                $disabled = '';
                            } elseif ($access === "No") {
                                $btnClass = 'btn-outline-danger';
                                $btnLabel = '❌';
                                $disabled = '';
                            } else {
                                $btnClass = 'btn-outline-secondary';
                                $btnLabel = '—';
                                $disabled = '';
                            }

                            
                            if($moduleName == 'Core' || $moduleName == 'Backend' || $moduleName == 'Orion') {
                                $disoff = ' disabled';
                            }
                            ?>
                            <td class='text-center' <?php echo $disoff;?>>
                                <button type="button" class="btn btn-sm <?= $btnClass ?> editMap"
                                    data-page="<?= htmlspecialchars($page) ?>" data-pkg="<?= $pkg['id'] ?>"
                                    data-name="<?= htmlspecialchars($pkg['package_name']) ?>" data-bs-toggle="popover"
                                    data-bs-html="true" title="Settings for <?= htmlspecialchars($pkg['package_name']) ?>"
                                    data-bs-content="<?=
                                        '<b>Access:</b> ' . ($access ?: "N/A") . '<br>' .
                                        '<b>Entry Limit:</b> ' . ($entry_limit ?: "N/A") . '<br>' .
                                        '<b>View Limit:</b> ' . ($view_limit ?: "N/A") . '<br>' .
                                        '<b>Total Time Limit:</b> ' . ($total_time_limit ?: "N/A") . '<br>' .
                                        '<b>Access Count:</b> ' . ($access_count_limit ?: "N/A") . '<br>' .
                                        '<b>Max Stay:</b> ' . ($max_stay_limit ?: "N/A") . '<br>' .
                                        '<b>Print:</b> ' . ($print ?: "N/A") . '<br>' .
                                        '<b>Modified:</b> ' . ($modified_time ?: "—")
                                        ?>" <?= $disabled ?>>
                                    <?= $btnLabel ?>
                                </button>
                            </td>
                            <?php
                        }

                        echo "</tr>";
                        $i++;
                    }
                    ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

<!-- SETTINGS MODAL -->
<div class="modal fade" id="mapSettingsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="mapSettingsForm">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-gear"></i> Page Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <input type="hidden" name="page_name" id="page_name">
                    <input type="hidden" name="package_id" id="package_id">

                    <div class="col-md-3">
                        <label class="form-label">Access</label>
                        <select name="access" class="form-select">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Entry Limit</label>
                        <input type="number" name="entry_limit" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Display Limit</label>
                        <input type="number" name="view_limit" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Total Time Limit</label>
                        <input type="number" name="total_time_limit" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Access Count Limit</label>
                        <input type="number" name="access_count_limit" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Max Stay Limit</label>
                        <input type="number" name="max_stay_limit" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Print</label>
                        <select name="print" class="form-select">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->

<script>
    $(document).on('click', '.editMap', function () {
        let page = $(this).data('page');
        let pkgId = $(this).data('pkg');
        let pkgName = $(this).data('name');

        $('#page_name').val(page);
        $('#package_id').val(pkgId);

        $.post('package-manager/package_map_action.php', { action: 'get_page_settings', page_name: page, package_id: pkgId }, function (res) {
            if (res) {
                let data = JSON.parse(res);
                // alert(res);
                $('[name=access]').val(data.access);
                $('[name=entry_limit]').val(data.entry_limit);
                $('[name=view_limit]').val(data.view_limit);
                $('[name=total_time_limit]').val(data.total_time_limit);
                $('[name=access_count_limit]').val(data.access_count_limit);
                $('[name=max_stay_limit]').val(data.max_stay_limit);
                $('[name=print]').val(data.print);
            } else {
                $('#mapSettingsForm')[0].reset();
            }
            $('#mapSettingsModal').modal('show');
        });
    });

    $('#mapSettingsForm').submit(function (e) {
        e.preventDefault();
        $.post('package-manager/package_map_action.php', $(this).serialize() + '&action=save_page_settings', function (msg) {
            alert(msg);
            $('#mapSettingsModal').modal('hide');
            location.reload();
        });
    });
</script>


<!-- ----------------------------------- -->
</body>

</html>