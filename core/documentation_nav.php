<?php

$userlevel = '%' . $userlevel . '%';
// $like_userlevel = %administrator%'; // LIKE এর জন্য wildcard সহ

// Fetch modules with highest priority permission
$sql = "SELECT mm.id AS module_id,
               mm.module_name,
               mm.nav_title,
               mm.related_pages,
               mm.nav_icon AS mm_nav_icon,
               ml.module_icon AS ml_module_icon,
               pm.page_name,
               pm.permission,
               pm.email,
               pm.userlevel,
               pm.sccode
        FROM modulemanager mm
        LEFT JOIN (
            SELECT p1.*
            FROM permission_map p1
            INNER JOIN (
                SELECT page_name,
                       MIN(
                           CASE
                               WHEN email = ? THEN 1
                               WHEN sccode = ? AND userlevel = ? THEN 2
                               WHEN userlevel LIKE ? THEN 3
                               ELSE 4
                           END
                       ) AS min_priority
                FROM permission_map
                WHERE permission > 0
                GROUP BY page_name
            ) p2
            ON p1.page_name = p2.page_name
               AND (
                   (email = ? AND 1 = p2.min_priority)
                   OR (sccode = ? AND userlevel = ? AND 2 = p2.min_priority)
                   OR (userlevel LIKE ? AND 3 = p2.min_priority)
               )
        ) pm
        ON pm.page_name = mm.related_pages
        LEFT JOIN modulelist ml ON ml.module_name = mm.module_name
        WHERE mm.module_name NOT IN ('Core')
        ORDER BY ml.slno ASC, ml.module_name ASC, mm.nav_title ASC;";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssss",
    $usr,
    $sccode,
    $userlevel,
    $userlevel,
    $usr,
    $sccode,
    $userlevel,
    $userlevel
);
$stmt->execute();
$result = $stmt->get_result();

// Build menu array (duplicate-free)
$menu = [];
while ($row = $result->fetch_assoc()) {
    $module = $row['module_name'];
    $module_icon = $row['ml_module_icon'] ?? 'three-dots-vertical'; // মূল মেনুর আইকন
    $submenu = $row['nav_title'];
    $page = $row['page_name'] ?: $row['related_pages'];
    $permission = $row['permission'] ?? 0;
    $nav_icon = $row['mm_nav_icon'] ?? 'three-dots-vertical'; // সাবমেনুর আইকন

    if ($is_admin < 4 && $permission == 0) {
        continue; // skip no permission
    }

    if (!isset($menu[$module])) {
        // মূল মেনুর আইকন যোগ করা
        $menu[$module] = [
            'module_icon' => $module_icon,
            'submenus' => []
        ];
    }

    // Prevent duplicate links in submenus
    $exists = false;
    foreach ($menu[$module]['submenus'] as $item) {
        if ($item['link'] === $page) {
            $exists = true;
            break;
        }
    }
    if (!$exists) {
        $menu[$module]['submenus'][] = [
            'submenu' => $submenu,
            'link' => $page,
            'nav_icon' => $nav_icon,
            'permission' => $permission
        ];
    }
}

$stmt->close();

// echo '<pre>';
// print_r($menu);
// echo '</pre>';

// var_dump($menu);
// var_dump($submenus);
// exit;
?>

<aside id="layout-menu-2" class="layout-menu0 menu-vertical menu  border-end p-2 rounded">

    <div class="app-brand demo mb-3 text-center">
        <a href="index.php" class="d-flex align-items-center justify-content-center text-decoration-none">
            <img src="assets/images/logo.png" style="width:30px;" class="me-2" />
            <span class="fw-bold fs-5 text-primary">EIMBox</span>
        </a>
    </div>

    <ul class="list-unstyled">

        <!-- Dashboard -->
        <li class="mb-2">
            <a href="documentation.php"
                class="d-flex align-items-center text-dark text-decoration-none py-2 px-3 rounded hover-bg">
                <i class="bi bi-house me-2"></i> Dashboard
            </a>
        </li>

        <!-- Dynamic Modules -->
        <?php foreach ($menu as $moduleName => $moduleData): ?>
            <?php
            // Filter only submenus with permission > 0
            $allowed_submenus = array_filter($moduleData['submenus'], fn($s) => $s['permission'] > 0);
            if (empty($allowed_submenus))
                continue; // skip module if no permitted submenu
            $collapseId = 'collapse_' . preg_replace('/\s+/', '_', strtolower($moduleName));
            ?>

            <li class="mb-2">
                <!-- Module (Parent) -->
                <a class="d-flex justify-content-between align-items-center text-dark text-decoration-none py-2 px-3 rounded hover-bg"
                    data-bs-toggle="collapse" href="#<?= $collapseId ?>" role="button" aria-expanded="false"
                    aria-controls="<?= $collapseId ?>">
                    <div>
                        <i class="bi bi-<?= htmlspecialchars($moduleData['module_icon']); ?> me-2"></i>
                        <?= htmlspecialchars($moduleName) ?>
                    </div>
                    <i class="bi bi-caret-down-fill small"></i>
                </a>

                <!-- Submenu (Children) -->
                <ul class="collapse ps-3 mt-1" id="<?= $collapseId ?>">
                    <?php foreach ($allowed_submenus as $sub): ?>
                        <?php
                        $isActive = basename($_SERVER['PHP_SELF']) == $sub['link'] ? 'active' : '';
                        ?>
                        <li>
                            <a href="documentation.php?doc=<?= htmlspecialchars($sub['link']) ?>"
                                class="d-flex align-items-center text-secondary text-decoration-none py-1 px-2 rounded hover-bg-sub <?= $isActive ?>">
                                <i class="bi bi-<?= htmlspecialchars($sub['nav_icon']); ?> me-2"></i>
                                <?= htmlspecialchars($sub['submenu'] ?: 'Untitled') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    </ul>
</aside>