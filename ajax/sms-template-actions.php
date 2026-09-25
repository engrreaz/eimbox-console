<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';

header('Content-Type: application/json; charset=utf-8');

$sccode = $sccode ?? ($_SESSION['sccode'] ?? '');
if (empty($sccode)) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'fetch';

try {
    // ----------------------------------------------------
    // Action 1: Fetch Templates
    // ----------------------------------------------------
    if ($action === 'fetch') {
        $cat = mysqli_real_escape_string($conn, trim($_POST['category'] ?? ''));
        $search = mysqli_real_escape_string($conn, trim($_POST['search'] ?? ''));
        $scope = $_POST['scope'] ?? 'all'; // all, system, custom

        $where = "((sccode='$sccode') OR (sccode=0)) AND status=1";
        if ($scope === 'system') {
            $where = "sccode=0 AND status=1";
        } elseif ($scope === 'custom') {
            $where = "sccode='$sccode' AND status=1";
        }

        if (!empty($cat) && $cat !== 'all') {
            $where .= " AND temp_type='$cat'";
        }

        if (!empty($search)) {
            $where .= " AND (temp_title LIKE '%$search%' OR temp_text LIKE '%$search%')";
        }

        $sql = "SELECT id, sccode, temp_type, target_audience, temp_title, temp_text, language, is_default, created_time, modifieddate 
                FROM sms_templete 
                WHERE $where 
                ORDER BY (sccode='$sccode') DESC, is_default DESC, id DESC";
        $res = $conn->query($sql);

        $templates = [];
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $is_system = ($r['sccode'] == 0);
                $templates[] = [
                    'id' => intval($r['id']),
                    'sccode' => intval($r['sccode']),
                    'is_system' => $is_system,
                    'type_badge' => $is_system ? 'System Template' : 'Custom Template',
                    'temp_type' => $r['temp_type'],
                    'target_audience' => $r['target_audience'] ?? 'all',
                    'temp_title' => $r['temp_title'],
                    'temp_text' => $r['temp_text'],
                    'language' => $r['language'] ?? 'bn',
                    'is_default' => intval($r['is_default']),
                    'char_count' => mb_strlen($r['temp_text']),
                    'created_time' => $r['created_time'] ?? ''
                ];
            }
        }

        echo json_encode([
            'status' => 'success',
            'total' => count($templates),
            'data' => $templates
        ]);
        exit;
    }

    // ----------------------------------------------------
    // Action 2: Get Single Template
    // ----------------------------------------------------
    elseif ($action === 'get_single') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Template ID.']);
            exit;
        }

        $stmt = $conn->prepare("SELECT id, sccode, temp_type, target_audience, temp_title, temp_text, language, is_default FROM sms_templete WHERE id=? AND (sccode=? OR sccode=0) LIMIT 1");
        $stmt->bind_param("is", $id, $sccode);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $row = $res->fetch_assoc()) {
            $is_system = ($row['sccode'] == 0);
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'id' => intval($row['id']),
                    'sccode' => intval($row['sccode']),
                    'is_system' => $is_system,
                    'temp_type' => $row['temp_type'],
                    'target_audience' => $row['target_audience'],
                    'temp_title' => $row['temp_title'],
                    'temp_text' => $row['temp_text'],
                    'language' => $row['language'] ?? 'bn',
                    'is_default' => intval($row['is_default'])
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Template not found or access denied.']);
        }
        exit;
    }

    // ----------------------------------------------------
    // Action 3: Save Template (Create / Update / Clone System)
    // ----------------------------------------------------
    elseif ($action === 'save') {
        $id = intval($_POST['id'] ?? 0);
        $temp_type = mysqli_real_escape_string($conn, trim($_POST['temp_type'] ?? 'general'));
        $target_audience = mysqli_real_escape_string($conn, trim($_POST['target_audience'] ?? 'all'));
        $temp_title = mysqli_real_escape_string($conn, trim($_POST['temp_title'] ?? ''));
        $temp_text = mysqli_real_escape_string($conn, trim($_POST['temp_text'] ?? ''));
        $language = mysqli_real_escape_string($conn, trim($_POST['language'] ?? 'bn'));
        $is_default = !empty($_POST['is_default']) ? 1 : 0;
        $usr_esc = mysqli_real_escape_string($conn, $usr ?? 'Admin');

        if (empty($temp_title) || empty($temp_text)) {
            echo json_encode(['status' => 'error', 'message' => 'Template title and message text cannot be empty.']);
            exit;
        }

        // Auto detect language if not explicitly specified
        if (empty($language)) {
            $language = preg_match('/\p{Bengali}/u', $temp_text) ? 'bn' : 'en';
        }

        // If setting as default, unset other defaults for this institution & category
        if ($is_default == 1) {
            $conn->query("UPDATE sms_templete SET is_default=0 WHERE sccode='$sccode' AND temp_type='$temp_type'");
        }

        // Check if editing existing template
        if ($id > 0) {
            $check = $conn->query("SELECT id, sccode FROM sms_templete WHERE id=$id LIMIT 1");
            if ($check && $r = $check->fetch_assoc()) {
                // If it is a System template (sccode = 0), CLONE it for this school!
                if ($r['sccode'] == 0) {
                    $insert_sql = "INSERT INTO sms_templete 
                                  (sccode, temp_type, target_audience, temp_title, temp_text, language, is_default, status, created_by, created_time) 
                                  VALUES ('$sccode', '$temp_type', '$target_audience', '$temp_title', '$temp_text', '$language', $is_default, 1, '$usr_esc', NOW())";
                    if ($conn->query($insert_sql)) {
                        $new_id = $conn->insert_id;
                        echo json_encode([
                            'status' => 'success',
                            'is_cloned' => true,
                            'new_id' => $new_id,
                            'message' => 'System template successfully customized and saved for your institution!'
                        ]);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
                    }
                    exit;
                }
                // If it is their own school's template (sccode = $sccode), UPDATE it!
                elseif ($r['sccode'] == $sccode) {
                    $update_sql = "UPDATE sms_templete 
                                   SET temp_type='$temp_type', target_audience='$target_audience', temp_title='$temp_title', 
                                       temp_text='$temp_text', language='$language', is_default=$is_default, modifieddate=NOW() 
                                   WHERE id=$id AND sccode='$sccode'";
                    if ($conn->query($update_sql)) {
                        echo json_encode([
                            'status' => 'success',
                            'is_cloned' => false,
                            'id' => $id,
                            'message' => 'Institution template updated successfully!'
                        ]);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
                    }
                    exit;
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Permission denied. Cannot modify this template.']);
                    exit;
                }
            }
        }

        // New Template Creation
        $insert_sql = "INSERT INTO sms_templete 
                      (sccode, temp_type, target_audience, temp_title, temp_text, language, is_default, status, created_by, created_time) 
                      VALUES ('$sccode', '$temp_type', '$target_audience', '$temp_title', '$temp_text', '$language', $is_default, 1, '$usr_esc', NOW())";
        if ($conn->query($insert_sql)) {
            $new_id = $conn->insert_id;
            echo json_encode([
                'status' => 'success',
                'new_id' => $new_id,
                'message' => 'New template created successfully!'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
        }
        exit;
    }

    // ----------------------------------------------------
    // Action 4: Delete Template
    // ----------------------------------------------------
    elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid template ID.']);
            exit;
        }

        $check = $conn->query("SELECT id, sccode FROM sms_templete WHERE id=$id LIMIT 1");
        if ($check && $r = $check->fetch_assoc()) {
            if ($r['sccode'] == 0) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'System default templates (sccode=0) are global and cannot be deleted.'
                ]);
                exit;
            }

            if ($r['sccode'] == $sccode) {
                $conn->query("UPDATE sms_templete SET status=0 WHERE id=$id AND sccode='$sccode'");
                echo json_encode(['status' => 'success', 'message' => 'Template deleted successfully!']);
                exit;
            }
        }

        echo json_encode(['status' => 'error', 'message' => 'Template not found or unauthorized.']);
        exit;
    }

    // ----------------------------------------------------
    // Action 5: Set Default
    // ----------------------------------------------------
    elseif ($action === 'set_default') {
        $id = intval($_POST['id'] ?? 0);
        $check = $conn->query("SELECT id, sccode, temp_type FROM sms_templete WHERE id=$id AND (sccode='$sccode' OR sccode=0) LIMIT 1");
        if ($check && $r = $check->fetch_assoc()) {
            $temp_type = $r['temp_type'];
            // Unset other defaults for this institution
            $conn->query("UPDATE sms_templete SET is_default=0 WHERE sccode='$sccode' AND temp_type='$temp_type'");
            
            // If it's a system template, clone and set as default for school
            if ($r['sccode'] == 0) {
                $conn->query("INSERT INTO sms_templete (sccode, temp_type, target_audience, temp_title, temp_text, language, is_default, status, created_by, created_time) 
                              SELECT '$sccode', temp_type, target_audience, temp_title, temp_text, language, 1, 1, 'Admin', NOW() 
                              FROM sms_templete WHERE id=$id");
            } else {
                $conn->query("UPDATE sms_templete SET is_default=1 WHERE id=$id AND sccode='$sccode'");
            }

            echo json_encode(['status' => 'success', 'message' => 'Default template updated!']);
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Template not found.']);
        exit;
    }

    else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action requested.']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
    exit;
}
