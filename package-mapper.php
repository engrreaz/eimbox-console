<?php require_once 'header.php'; ?>
<?php

$files = array_filter(scandir(__DIR__), function ($f) {
    return is_file($f) && substr($f, -4) === '.php';
});
?>

<div class="container-fluid py-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-diagram-3"></i> Page Access Map (Page VS Package)</h4>

    <div class="card shadow-sm">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-light ">
                    <tr>
                        <th class="p-0 py-2 text-center" colspan="3">#</th>
                        <th class="p-0 py-2">Module</th>
                        <th class="p-0 py-2">Page</th>
                        <th class="p-0 py-2">Nav Title</th>
                        <?php

                        $pkgQ = $conn->query("SELECT id, package_name FROM packages ORDER BY serial ASC");
                        $packages = [];
                        while ($p = $pkgQ->fetch_assoc()) {
                            $packages[] = $p;
                            echo "<th class='p-0 py-2 text-center'>{$p['package_name']}</th>";
                        }
                        ?>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $not_assign = 1;
                    foreach ($files as $page) {

                        $modStmt = $conn->prepare("SELECT module_name, nav_icon, nav_title FROM modulemanager WHERE related_pages=?");
                        $modStmt->bind_param("s", $page);
                        $modStmt->execute();
                        $modRes = $modStmt->get_result();
                        $mod = $modRes->fetch_assoc();

                        $moduleName = $mod ? $mod['module_name'] : '—';
                        $navTitle = $mod ? $mod['nav_title'] : '—';

                        $navIcon = $mod ? $mod['nav_icon'] : 'three-dots-vertical';

                        echo "<tr>
                        <td class='ps-5'></td>
                        <td class='p-0'>{$i}</td>
                        <td class='ps-5'><i class='bi bi-{$navIcon}'></i></td>
                        <td class='ps-5'>{$moduleName}</td>
                        <td class='ps-5'>{$page}</td>
                        <td class='ps-5'>{$navTitle}</td>";


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


                            $btnClass = 'btn-outline-dark';
                            $btnLabel = '—';
                            $disabled = '';
                            $disoff = '';


                            if ($access === "Yes") {
                                $btnClass = 'btn-success';
                                $btnLabel = '<i class="bi bi-check2"></i>';
                            } elseif ($access === "No") {
                                $btnClass = 'btn-danger';
                                $btnLabel = '<i class="bi bi-x-lg"></i>';
                            } else {
                                $not_assign++;
                            }



                            if (
                                !is_array($mod) ||
                                !isset($mod['module_name']) ||
                                $mod['module_name'] == '' ||
                                in_array($moduleName, ['Core', 'Backend', 'Orion', 'Seed', 'Authority', 'Developer', ''])
                            ) {
                                $disoff = 'disabled';
                                $disabled = 'disabled';
                                $not_assign--;
                            }
                            ?>
                            <td class='text-center p-0'>
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

                        echo '<td class="pe-5"></td>';
                        echo "</tr>";
                        $i++;
                    }

                    ?>
                </tbody>
            </table>

            <?php echo 'Not Assigned : ' . $not_assign; ?>
        </div>
    </div>
</div>

<!-- SETTINGS MODAL -->
<div class="modal fade" id="mapSettingsModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <form id="mapSettingsForm">
                <div class="modal-header border-bottom pb-3 fw-bold text-danger">
                    <h5 class="modal-title"><i class="bi bi-marker-tip"></i> Permission Settings</h5>
                    <button type="button" class="btn-close text-danger" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <input type="hidden" name="page_name" id="page_name">
                    <input type="hidden" name="package_id" id="package_id">

                    <div class="col-md-4">
                        <label class="form-label">Accessible</label>
                        <select name="access" class="form-control form-control-sm">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Entry Limit (Times)</label>
                        <input type="number" name="entry_limit" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Access Count Limit</label>
                        <input type="number" name="access_count_limit" class="form-control form-control-sm">
                    </div>


                    <div class="col-md-3" >
                        <label class="form-label">Display Limit (Times)</label>
                        <input type="number" name="view_limit" class="form-control form-control-sm">
                    </div>


                    <div class="col-md-4">
                        <label class="form-label">Max Stay Limit (Min)</label>
                        <input type="number" name="max_stay_limit" class="form-control form-control-sm">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Total Time Limit </label>
                        <input type="number" name="total_time_limit" class="form-control form-control-sm">
                    </div>

                    

                    

                    <div class="col-md-4">
                        <label class="form-label">Print</label>
                        <select name="print" class="form-control form-control-sm">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer pt-3 border-top mb-3 pb-0">
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save"></i> &nbsp;&nbsp; Update
                        Setting</button>
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
        // alert(page + "/" + pkgName + "/" + pkgId);

        $('#page_name').val(page);
        $('#package_id').val(pkgId);

        $.post('package-manager/package_map_action.php', {
            action: 'get_page_settings',
            page_name: page,
            package_id: pkgId
        }, function (res) {
            if (res) {
                let data = JSON.parse(res);
                // $('[name=page_name]').val(data.page);
                // $('[name=package_id]').val(data.pkgId);
                $('[name=access]').val(data.access);
                $('[name=entry_limit]').val(data.entry_limit);
                $('[name=view_limit]').val(data.view_limit);
                $('[name=total_time_limit]').val(data.total_time_limit);
                $('[name=access_count_limit]').val(data.access_count_limit);
                $('[name=max_stay_limit]').val(data.max_stay_limit);
                $('[name=print]').val(data.print);
            } else {
                $('#mapSettingsForm')[0].reset();
                $('#page_name').val(page);
                $('#package_id').val(pkgId);
            }

            const modalEl = document.getElementById('mapSettingsModal');
            const mapModal = bootstrap.Modal.getOrCreateInstance(modalEl, {
                backdrop: 'static', // বাইরে ক্লিক করলে বন্ধ হবে না
                keyboard: false     // ESC চাপলে বন্ধ হবে না
            });
            mapModal.show();
        });
    });

    $('#mapSettingsForm').submit(function (e) {
        e.preventDefault();
        $.post('package-manager/package_map_action.php',
            $(this).serialize() + '&action=save_page_settings',
            function (msg) {
                alert(msg);
                const modalEl = document.getElementById('mapSettingsModal');
                const mapModal = bootstrap.Modal.getInstance(modalEl);
                mapModal?.hide();
                // location.reload();
            }
        );
    });
</script>



<!-- ----------------------------------- -->
</body>

</html>