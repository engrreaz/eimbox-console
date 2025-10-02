<?php
// Variables


$email_null = '';


// Fetch modules with highest priority permission
$sql = "SELECT mm.id AS module_id,
               mm.module_name,
               mm.nav_title,
               mm.related_pages,
               mm.nav_icon,
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
    $submenu = $row['nav_title'];
    $page = $row['page_name'] ?: $row['related_pages'];
    $permission = $row['permission'] ?? 0;
    $icon = $row['nav_icon'] ?? 'three-dots-vertical';

    if ($permission <= 0)
        continue; // skip no permission

    if (!isset($menu[$module]))
        $menu[$module] = [];

    // Prevent duplicate links
    $exists = false;
    foreach ($menu[$module] as $item) {
        if ($item['link'] === $page) {
            $exists = true;
            break;
        }
    }
    if (!$exists) {
        $menu[$module][] = [
            'submenu' => $submenu,
            'link' => $page,
            'nav_icon' => $icon,
            'permission' => $permission
        ];
    }
}

$stmt->close();




?>


<aside id="layout-menu" class="layout-menu menu-vertical menu">

    <div class="app-brand demo ">
        <a href="index.php" class="app-brand-link gap-xl-0 gap-2">
            <span class="app-brand-logo demo me-1">
                <span class="text-primary">
                    <img src="assets/images/logo.png" style="width:30px;" />
                </span>
            </span>
            <span class="app-brand-text demo menu-text fw-semibold ms-2">EIMBox</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="menu-toggle-icon d-xl-inline-block align-middle"></i>
        </a>
    </div>



    <div class="menu-inner-shadow"></div>


    <ul class="menu-inner py-1">
        <li class="menu-item">
            <a href="index.php" class="menu-link">
                <i class="menu-icon icon-base bi bi-house"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>




        <?php foreach ($menu as $moduleName => $submenus): ?>
            <li class="menu-item parent">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon icon-base ri ri-folder-line"></i>
                    <div data-i18n="<?= htmlspecialchars($moduleName) ?>"><?= htmlspecialchars($moduleName) ?></div>
                </a>
                <?php if (!empty($submenus)): ?>
                    <ul class="menu-sub">
                        <?php foreach ($submenus as $sub): ?>
                            <li class="menu-item">
                                <a href="<?= htmlspecialchars($sub['link']) ?>" class="menu-link">
                                    <i class="menu-icon icon-base bi bi-<?php echo $sub['nav_icon']; ?>"></i>

                                    <div data-i18n="<?= htmlspecialchars($sub['submenu'] ?? '') ?>">
                                        <?= htmlspecialchars($sub['submenu'] ?? '') ?>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</aside>



<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Current page filename
        let currentFile = window.location.pathname.split("/").pop();

        // সব menu link
        let links = document.querySelectorAll("#layout-menu a");

        let matchedLink = null;

        // ১. Find the matching link first
        links.forEach(link => {
            let hrefFile = link.getAttribute("href");
            if (hrefFile && hrefFile.split("/").pop() === currentFile) {
                matchedLink = link;
            }
        });

        if (!matchedLink) {
            // No menu item corresponds to current page
            return; // Exit, no active/open class will be set
        }

        // ২. Current link active
        let li = matchedLink.closest(".menu-item");
        if (li) li.classList.add("active");

        // ৩. Parent chain open only for this link
        let parent = li.parentElement;
        while (parent && parent.id !== "layout-menu") {
            let parentLi = parent.closest(".menu-item.parent");
            if (parentLi) {
                parentLi.classList.add("active", "open");
                // Move up to the next parent
                parent = parentLi.parentElement;
            } else {
                break;
            }
        }
    });
</script>





<style>
    /* Sidebar */
    #sidebar {
        position: fixed;
        top: 0;
        right: -300px;
        /* hidden */
        width: 300px;
        height: 100%;
        /* background: #2c3e50; */
        /* color: #fff; */
        padding: 20px;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.5);
        transition: right 0.4s ease;
        /* smooth transition */
        z-index: 10000;
    }

    #sidebar.open {
        right: 0;
    }

    /* Lock button */
    #lockBtn {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #e67e22;
        color: #fff;
        border: none;
        padding: 6px 12px;
        cursor: pointer;
        border-radius: 4px;
    }

    #lockBtn.locked {
        background: #27ae60;
    }
</style>





<div id="sidebar" class="card card-border-shadow-primary">
    <div class="card-body">
        <button id="lockBtn">🔓 Lock</button>
        <h3>Sidebar Menu</h3>
        <p>Some content inside sidebar...</p>

        <div id="sidebar_admin">
            load content for admin user
        </div>
    </div>
</div>


<script>
    let sidebar = document.getElementById("sidebar");
    let lockBtn = document.getElementById("lockBtn");
    let timer = null;
    let delay = 500; // 0.5 sec
    let locked = false;

    // Mouse edge detect → open sidebar
    document.addEventListener("mousemove", function (e) {
        let viewportWidth = document.documentElement.clientWidth;
        let mouseX = e.clientX;

        if (!locked && mouseX >= viewportWidth - 1) {
            if (!timer && !sidebar.classList.contains("open")) {
                timer = setTimeout(() => {
                    sidebar.classList.add("open");
                    timer = null;
                }, delay);
            }
        } else {
            clearTimeout(timer);
            timer = null;
        }
    });

    // Auto close when mouse leaves sidebar
    sidebar.addEventListener("mouseleave", function () {
        if (!locked) {
            sidebar.classList.remove("open");
        }
    });

    // 🔥 Scroll detect → auto close
    window.addEventListener("scroll", function () {
        if (!locked && sidebar.classList.contains("open")) {
            sidebar.classList.remove("open");
        }
    });

    // Lock button toggle
    lockBtn.addEventListener("click", function () {
        locked = !locked;
        if (locked) {
            sidebar.classList.add("open"); // ensure open
            lockBtn.textContent = "🔒 Locked";
            lockBtn.classList.add("locked");
        } else {
            lockBtn.textContent = "🔓 Lock";
            lockBtn.classList.remove("locked");
        }
    });
</script>


