<?php
include 'core/config.php';
include 'core/db.php';

// Variables
$sccode = '103187';    // উদাহরণ
$userlevel = 'Administrator';  // উদাহরণ
$usr = 'engrreaz@gmail.com';
$email_null = '';

// SQL query
$sql = "SELECT mm.id AS module_id,
       mm.module_name,
       mm.nav_title,
       mm.related_pages,
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
                       WHEN userlevel = ? THEN 3
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
           OR (userlevel = ? AND 3 = p2.min_priority)
       )
) pm
ON pm.page_name = mm.related_pages
WHERE mm.module_name NOT IN ('Core','Backend')
ORDER BY mm.module_name, mm.nav_title;";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssss",
    $usr,       // 1 - subquery CASE email
    $sccode,    // 2 - subquery CASE sccode
    $userlevel, // 3 - subquery CASE userlevel
    $userlevel, // 4 - subquery CASE userlevel
    $usr,       // 5 - outer CASE email
    $sccode,    // 6 - outer CASE sccode
    $userlevel, // 7 - outer CASE userlevel
    $userlevel  // 8 - outer CASE userlevel
);
$stmt->execute();
$result = $stmt->get_result();

// Build menu array
$menu = [];
while ($row = $result->fetch_assoc()) {
    $module = $row['module_name'];
    $submenu = $row['nav_title'];
    $page = $row['page_name'];

    if (!isset($menu[$module])) {
        $menu[$module] = [];
    }

    if ($page) { // যদি permission পাওয়া যায়
        $menu[$module][] = [
            'submenu' => $submenu,
            'link' => $page,
            'permission' => $row['permission']
        ];
    }
}

// Example output
echo "<pre>";
print_r($menu);
echo "</pre>";






// Generate HTML
echo '<ul class="main-menu">';
foreach ($menu as $moduleName => $submenus) {
    echo '<li class="menu-item">';
    echo '<span class="module-title">' . htmlspecialchars($moduleName) . '</span>';

    if (!empty($submenus)) {
        echo '<ul class="submenu">';
        foreach ($submenus as $sub) {
            // Only show items with permission > 0
            if ($sub['permission'] > 0) {
                echo '<li><a href="' . htmlspecialchars($sub['link']) . '">' . htmlspecialchars($sub['submenu']) . '</a></li>';
            }
        }
        echo '</ul>';
    }

    echo '</li>';
}
echo '</ul>';

$stmt->close();
$conn->close();
?>