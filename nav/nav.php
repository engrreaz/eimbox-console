<?php
// Variables


$email_null = '';
$userlevel = '%' . $userlevel . '%';
// $like_userlevel = %administrator%'; // LIKE এর জন্য wildcard সহ

// Fetch modules with highest priority permission
$sql = "SELECT mm.id AS module_id,
               mm.module_name,
               mm.nav_title,
               mm.related_pages,
               mm.nav_icon AS mm_nav_icon,
               mm.root_page,
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
    $root_page = $row['root_page'] ?? '';


    if (!empty($root_page)) {
        continue; // root_page সেট থাকলে সাবপেজ মেনুতে যোগ হবে না
    }

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

    <div class="cards">
        <div class="" style="height:80px;">

        </div>
    </div>

    <ul class="menu-inner py-1  " style="height:90vh; overflow-y: auto;">
        <li class="menu-item">
            <a href="index.php" class="menu-link">
                <i class="menu-icon icon-base bi bi-house"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>




        <?php foreach ($menu as $moduleName => $moduleData):
            if ($moduleName == 'Backend') {
                $mname_color = '#769603ff';
            } else if ($moduleName == 'Orion') {
                $mname_color = '#960b83ff';
            } else if ($moduleName == 'Seed') {
                $mname_color = '#940808ff';
            } else if ($moduleName == 'Authority') {
                $mname_color = '#058d70ff';
            } else {
                $mname_color = 'gray';
            }

            ?>
            <li class="menu-item parent">
                <a href="javascript:void(0);" class="menu-link menu-toggle" style="color:<?php echo $mname_color; ?>">
                    <i class="menu-icon icon-base bi bi-<?php echo $moduleData['module_icon']; ?>"></i>
                    <div data-i18n="<?= htmlspecialchars($moduleName) ?>"><?= htmlspecialchars($moduleName) ?></div>
                </a>
                <?php if (!empty($moduleData['submenus'])): ?>
                    <ul class="menu-sub">
                        <?php foreach ($moduleData['submenus'] as $sub): ?>
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
    const pageRoots = <?php
    $rootMap = [];
    $res = $conn->query("SELECT related_pages AS page_name, root_page FROM modulemanager WHERE root_page != ''");
    if ($res) {
        while ($rp = $res->fetch_assoc()) {
            $rootMap[$rp['page_name']] = $rp['root_page'];
        }
    }
    echo json_encode($rootMap, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    ?>;
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let currentFile = window.location.pathname.split("/").pop();
        let links = document.querySelectorAll("#layout-menu a");
        let matchedLink = null;

        links.forEach(link => {
            let hrefFile = link.getAttribute("href");
            if (hrefFile && hrefFile.split("/").pop() === currentFile) {
                matchedLink = link;
                let linkText = matchedLink.innerText;
                document.getElementById("page_link_sub_title").innerHTML = linkText;
            }
        });

        if (!matchedLink && pageRoots[currentFile]) {
            const root = pageRoots[currentFile];
            console.log(`(EIMBox) Root-page fallback: ${currentFile} → ${root}`);
            links.forEach(link => {
                let hrefFile = link.getAttribute("href");
                if (hrefFile && hrefFile.split("/").pop() === root) {
                    matchedLink = link;
                }
            });
        }

        let li = matchedLink ? matchedLink.closest(".menu-item") : null;
        if (!li) return;

        li.classList.add("active");

        let parent = li.parentElement;
        while (parent && parent.id !== "layout-menu") {
            let parentLi = parent.closest(".menu-item.parent");
            if (parentLi) {
                parentLi.classList.add("active", "open");
                parent = parentLi.parentElement;
                let menuLink = parentLi.querySelector("a.menu-link");
                if (menuLink) {
                    let linkText = menuLink.innerText;
                    document.getElementById("parent_item").innerHTML = linkText;
                }
            } else {
                break;
            }
        }
    });
</script>


<!-- --------------------------------------------------------------------------------------------------------------------------------------------------- -->
<!-- --------------------------------------------------------------------------------------------------------------------------------------------------- -->
<!-- --------------------------------------------------------------------------------------------------------------------------------------------------- -->
<!-- SIDE BAR -->
<!-- --------------------------------------------------------------------------------------------------------------------------------------------------- -->
<!-- --------------------------------------------------------------------------------------------------------------------------------------------------- -->
<!-- --------------------------------------------------------------------------------------------------------------------------------------------------- -->


<style>
    /* Sidebar */
    #sidebar {
        position: fixed;
        top: 0;
        left: -400px;
        /* hidden */
        width: 400px;
        max-width:90%;
        height: 100%;
        max-width: 90%;
        /* background: #2c3e50; */
        /* color: #fff; */
        padding: 20px;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.5);
        transition: left 0.4s ease;
        /* smooth transition */
        z-index: 10000;
    }

    #sidebar.open {
        left: 0;
    }

    /* Lock button */
    #lockBtn {
        color: #dff13cff;
        border: 1px solid var(--card-border-shadow-primary);
        padding: 8px;
        cursor: pointer;
        border-radius: 4px 8px;
        font-size: 20px;
    }

    #lockBtn.locked {
        background: #27ae60;
    }
</style>





<div id="sidebar" class="card card-border-shadow-primary p-0">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col fs-6 fw-bold">Imperious Objects</div>
            <div class="col text-right">
                <button class="btn float-end" id="lockBtn"><i class="bi bi-lock"></i></button>
            </div>
        </div>

        <div class="fs-8" id="page_features_list"></div>

        <div id="sidebar_admin">
            load content for admin user
        </div>
    </div>
</div>


<script>
    let sidebar = document.getElementById("sidebar");
    let lockBtn = document.getElementById("lockBtn");
    let timer = null;
    let delay = 500;
    let locked = false;

    // ----- Mouse edge detect → open sidebar (LEFT SIDE) -----
    document.addEventListener("mousemove", function (e) {
        let mouseX = e.clientX;

        // LEFT EDGE → 0px–1px
        if (!locked && mouseX <= 1) {
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

    // Auto close when mouse leaves
    sidebar.addEventListener("mouseleave", function () {
        if (!locked) sidebar.classList.remove("open");
    });

    // Scroll detect → close
    window.addEventListener("scroll", function () {
        if (!locked && sidebar.classList.contains("open"))
            sidebar.classList.remove("open");
    });

    // Lock toggle
    lockBtn.addEventListener("click", function () {
        locked = !locked;
        if (locked) {
            sidebar.classList.add("open");
            lockBtn.classList.add("locked");
        } else {
            lockBtn.classList.remove("locked");
        }
    });

    // Escape → close
    document.addEventListener("keydown", function (e) {
        if (!locked && e.key === "Escape" && sidebar.classList.contains("open")) {
            sidebar.classList.remove("open");
        }
    });

    // ----- Swipe detect (same logic works for left sidebar) -----
    let touchStartX = 0;
    let touchEndX = 0;

    document.addEventListener("touchstart", e => {
        touchStartX = e.changedTouches[0].screenX;
    });

    document.addEventListener("touchend", e => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    function handleSwipe() {
        const swipeDistance = touchEndX - touchStartX;
        const threshold = 50;

        if (!locked) {
            if (swipeDistance > threshold) {
                // Right swipe → open sidebar (LEFT SIDEBAR)
                sidebar.classList.add("open");
            } else if (swipeDistance < -threshold) {
                // Left swipe → close
                sidebar.classList.remove("open");
            }
        }
    }
</script>