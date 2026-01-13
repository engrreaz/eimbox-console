<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$page = $_POST['page'] ?? '';
$action = $_POST['action'] ?? '';

if ($sccode == '' || $page == '' || $is_admin > 0) {
    exit;
}

/* =========================
   ACCESS COUNT (page load)
========================= */
if ($action === 'access') {

    $sql = "SELECT id FROM package_limit_data 
            WHERE sccode=? AND page_name=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $sccode, $page);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        // UPDATE
        $conn->query("
            UPDATE package_limit_data 
            SET access_count = access_count + 1
            WHERE sccode='$sccode' AND page_name='$page'
        ");
    } else {
        // INSERT
        $conn->query("
            INSERT INTO package_limit_data 
            (sccode, page_name, access_count, total_stay)
            VALUES ('$sccode', '$page', 1, 0)
        ");
    }
}

/* =========================
   TOTAL STAY (every 15 sec)
========================= */
if ($action === 'stay') {

    // 1️⃣ check record exists
    $chk = $conn->prepare("
        SELECT id 
        FROM package_limit_data
        WHERE sccode=? AND page_name=?
        LIMIT 1
    ");
    $chk->bind_param("ss", $sccode, $page);
    $chk->execute();
    $res = $chk->get_result();

    if ($res->num_rows > 0) {

        // 2️⃣ UPDATE if found
        $upd = $conn->prepare("
            UPDATE package_limit_data
            SET total_stay = total_stay + 0.25
            WHERE sccode=? AND page_name=?
        ");
        $upd->bind_param("ss", $sccode, $page);
        $upd->execute();

    } else {

        // 3️⃣ INSERT if not found
        $ins = $conn->prepare("
            INSERT INTO package_limit_data
            (sccode, page_name, access_count, total_stay)
            VALUES (?, ?, 0, 0.25)
        ");
        $ins->bind_param("ss", $sccode, $page);
        $ins->execute();
    }
}
