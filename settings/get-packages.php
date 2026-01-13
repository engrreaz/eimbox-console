<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$current = $_POST['current'] ?? '';
$tier    = $_POST['tier'] ?? '';


$pkgQ = mysqli_query($conn, "
    SELECT id, package_name, description 
    FROM packages 
    WHERE status='active' 
    ORDER BY id
");

while ($pkg = mysqli_fetch_assoc($pkgQ)) {

    $pkgId   = $pkg['id'];
    $isOpen  = ($pkgId == $current) ? 'show' : '';
    $checked = ($pkgId == $current) ? 'checked' : '';

    echo '<div class="list-group-item package-card mb-2" data-pkg="'.$pkgId.'">';

    // -------- Package Header ----------
    echo '
    <div class="d-flex align-items-start package-header"
         data-bs-toggle="collapse"
         data-bs-target="#pkg-'.$pkgId.'"
         style="cursor:pointer;">

        <input type="radio" 
               name="package"
               class="form-check-input mt-1 me-2"
               value="'.$pkgId.'"
               data-package="'.$pkgId.'"
               '.$checked.'>

        <div>
            <div class="fw-bold">'.$pkg['package_name'].'</div>
            <div class="fs-tiny text-secondary">'.$pkg['description'].'</div>
        </div>

        <div class="ms-auto text-secondary arrow">
            <i class="bi bi-chevron-down"></i>
        </div>
    </div>';

    // -------- Tier List ----------
    echo '<div class="collapse mt-2 ms-4 '.$isOpen.'" id="pkg-'.$pkgId.'">';

    $tierQ = mysqli_query($conn, "
        SELECT * FROM package_settings 
        WHERE package_id='$pkgId'
        ORDER BY ins_tier
    ");

    while ($row = mysqli_fetch_assoc($tierQ)) {

        $tierId   = $row['id'];
        $tierName = $row['ins_tier'];

        $tierChecked = ($pkgId == $current && $tierName == $tier) ? 'checked' : '';

        echo '
        <label class="d-flex border rounded p-2 mb-2 tier-box align-items-center">
            <input type="radio" 
                   class="form-check-input me-2"
                   name="tier"
                   value="'.$tierId.'"
                   data-package="'.$pkgId.'"
                   data-modules="'.$row['module'].'"
                   data-panels="'.$row['panel'].'"
                   '.$tierChecked.'>

            <div class="flex-grow-1">
                <div class="fw-bold">'.$tierName.' Tier</div>
                <div class="fs-tiny text-secondary">
                    Modules: '.$row['module'].' |
                    Panels: '.$row['panel'].' |
                    Price: '.$row['price'].'
                </div>
            </div>
        </label>';
    }

    echo '</div></div>';
}
