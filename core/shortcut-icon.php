<?php
// Step 1: শেষ ৩ দিনের সর্বাধিক দেখা পেজগুলো বের করা
$query = "
    SELECT pagename, COUNT(*) AS visit_count
    FROM logbook
    WHERE email = ? AND entrytime >= DATE_SUB(NOW(), INTERVAL 3 DAY)
    GROUP BY pagename
    ORDER BY visit_count DESC
    LIMIT 6
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $usr);
$stmt->execute();
$result = $stmt->get_result();

$pages = [];
while ($row = $result->fetch_assoc()) {
    $pages[] = $row['pagename'];
}

// Step 2: modulemanager টেবিল থেকে রিলেটেড ডেটা আনা
$icons = [];
if (!empty($pages)) {
    // related_pages এর সাথে LIKE ম্যাচ করা
    $where = [];
    foreach ($pages as $p) {
        $where[] = "related_pages LIKE '%" . $conn->real_escape_string($p) . "%'";
    }
    $sql = "SELECT related_pages, nav_icon, nav_title, status_name FROM modulemanager WHERE " . implode(" OR ", $where);
    $res = $conn->query($sql);

    while ($r = $res->fetch_assoc()) {
        $icons[] = $r;
    }
}
?>

<!-- Step 3: Frontend (Bootstrap popover সহ) -->






<li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-sm-2 me-xl-0">
    <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
        href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="icon-base bi bi-bookmark-star icon-22px"></i>
    </a>

    <div class="dropdown-menu dropdown-menu-end p-0">
        <!-- Header -->
        <div class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-2 my-50">
                <h6 class="mb-0 me-auto">Shortcuts</h6>
                <a href="javascript:void(0)"
                    class="dropdown-shortcuts-add add-remove btn btn-text-secondary rounded-pill btn-icon"
                    data-bs-toggle="tooltip" data-bs-crud="0" data-bs-placement="top" title="Add shortcuts">
                    <i class="icon-base bi bi-plus icon-20px text-heading"></i>
                </a>
            </div>
        </div>

        <!-- Shortcut Icons -->
        <div class="dropdown-menu-header border-bottom text-center py-2">
            <?php foreach ($icons as $icon): ?>
                <a href="<?= htmlspecialchars($icon['related_pages']) ?>" class="shortcut-link" data-bs-toggle="popover"
                    title="<?= htmlspecialchars($icon['nav_title'] ?? '') ?>"
                    data-bs-content="<?= htmlspecialchars($icon['nav_title'] ?? '') ?>">
                    <i class="bi bi-<?= htmlspecialchars($icon['nav_icon'] ?? '') ?> fs-4 mx-2"
                        style="color: <?= htmlspecialchars($release_colors[$icon['status_name']]) ?>; cursor:pointer;">
                    </i>
                </a>
            <?php endforeach; ?>



        </div>

        <?php
        $usr = mysqli_real_escape_string($conn, $usr);
        $sccode = mysqli_real_escape_string($conn, $sccode);

        // SQL: সর্বশেষ ১২টি active রেকর্ড
        $sql = "SELECT *
        FROM user_shortcuts
        WHERE user_email = '$usr'
          AND sccode = '$sccode'
          AND status = 'active'
        ORDER BY created_at DESC
        LIMIT 12";

        $result = $conn->query($sql);

        $shortcuts = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $shortcuts[] = $row;
            }
        }

        ?>
        <!-- Optional: other static shortcut items -->
        <div class="dropdown-shortcuts-list scrollable-container">
            <div class="row row-bordered g-0">
                <?php foreach ($shortcuts as $scut) { ?>
                    <div class="dropdown-shortcuts-item col-4 position-relative">
                        <!-- Add/Remove বাটন -->
                        <a href="javascript:void(0)"
                            class="add-remove btn btn-text-danger btn-danger rounded-pill btn-icon position-absolute top-0 end-0 m-2 pt-2"
                            data-bs-toggle="tooltip" data-bs-crud="<?php echo $scut['id']; ?>" data-bs-placement="top"
                            title="Add shortcuts" style="z-index: 1050;">
                            <i class="icon-base bi bi-trash icon-16px  text-danger"></i>
                        </a>

                        <!-- Shortcut content -->
                        <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                            <i class="icon-base bi bi-<?php echo $scut['page_icon']; ?> icon-26px text-heading"></i>
                        </span>
                        <a href="<?php echo $scut['page_name']; ?>" class="stretched-link">
                            <?php echo $scut['page_title'] ?? 'EIMBox'; ?>
                        </a>
                        <small><?php echo $scut['module']; ?></small>
                    </div>


                <?php } ?>
            </div>
        </div>
    </div>
</li>