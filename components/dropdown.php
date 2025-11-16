<?php
function dropdown($name, $options = []) {
    $label = ucfirst($name);
    $id = "dd_" . $name;

    // depends_on = string | array | null
    $depends = $options['depends_on'] ?? null;

    // force array
    if ($depends && !is_array($depends)) {
        $depends = [$depends];
    }

    $depends_ids = [];
    if ($depends) {
        foreach ($depends as $d) $depends_ids[] = "dd_" . $d;
    }
?>
    <div class="mb-3">
        <label class="form-label"><?= $label ?></label>
        <select id="<?= $id ?>" name="<?= $id ?>" class="form-control"
                data-name="<?= $name ?>"
                data-depends="<?= implode(',', $depends_ids) ?>">
            <option value="">-- Select <?= $label ?> --</option>
        </select>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        let target = document.getElementById("<?= $id ?>");
        let depends = target.dataset.depends;

        // no dependency → auto load
        if (!depends) {
            loadOptions("<?= $name ?>", {}, "<?= $id ?>");
            return;
        }

        let parents = depends.split(",");

        parents.forEach(parentID => {
            document.getElementById(parentID).addEventListener("change", function () {

                let parentValues = {};
                parents.forEach(p => {
                    parentValues[p.replace("dd_", "")] = document.getElementById(p).value;
                });

                loadOptions("<?= $name ?>", parentValues, "<?= $id ?>");
            });
        });
    });
    </script>
<?php
}
?>
