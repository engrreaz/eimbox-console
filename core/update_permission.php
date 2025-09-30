<?php
require_once 'config.php';
require_once 'db.php';

$page_name  = $_POST['page_name'] ?? '';
$userlevel  = $_POST['userlevel'] ?? '';
$permission = $_POST['permission'] ?? '';

if ($page_name && $userlevel) {
    // যদি Not Assigned (permission = '') হয় → DELETE
    if ($permission === '') {
        $stmt = $conn->prepare("DELETE FROM permission_map 
                                WHERE page_name=? 
                                  AND userlevel=? 
                                  AND (sccode IS NULL OR sccode='' OR sccode='0')");
        $stmt->bind_param("ss", $page_name, $userlevel);
        if ($stmt->execute()) {
            echo "Permission removed";
        } else {
            echo "Failed to remove";
        }

    } else {
        // আগে রেকর্ড আছে কিনা চেক
        $stmt = $conn->prepare("SELECT id FROM permission_map 
                                WHERE page_name=? 
                                  AND userlevel=? 
                                  AND (sccode IS NULL OR sccode='' OR sccode='0')
                                LIMIT 1");
        $stmt->bind_param("ss", $page_name, $userlevel);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            // UPDATE
            $row = $res->fetch_assoc();
            $id = $row['id'];

            $stmt = $conn->prepare("UPDATE permission_map 
                                    SET permission=?, modifiedtime=NOW() 
                                    WHERE id=?");
            $stmt->bind_param("ii", $permission, $id);
            if ($stmt->execute()) {
                echo "Updated successfully";
            } else {
                echo "Update failed";
            }

        } else {
            // INSERT
            $stmt = $conn->prepare("INSERT INTO permission_map 
                (page_name, userlevel, sccode, email, permission, entryby) 
                VALUES (?, ?, '0', '', ?, 'system')");
            $stmt->bind_param("ssi", $page_name, $userlevel, $permission);
            if ($stmt->execute()) {
                echo "Inserted successfully";
            } else {
                echo "Insert failed";
            }
        }
    }
} else {
    echo "Invalid data";
}
?>
