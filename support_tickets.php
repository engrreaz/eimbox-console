<?php
// ---------------------------------------------------------
// AJAX / API REQUEST HANDLERS (No HTML Header)
// ---------------------------------------------------------
if (isset($_GET['ajax']) || isset($_POST['ajax'])) {
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
    require_once __DIR__ . '/core/config.php';
    require_once __DIR__ . '/core/db.php';
    $conn = db_connect();
    header('Content-Type: application/json; charset=utf-8');

    $curSccode = (int)($_SESSION['sccode'] ?? 0);
    $curUserId = (int)($_SESSION['user_id'] ?? $_SESSION['userid'] ?? 0);
    $userEmail = trim($_SESSION['user_email'] ?? $_SESSION['email'] ?? $_SESSION['user'] ?? '');
    $userName  = trim($_SESSION['fullname'] ?? $_SESSION['profilename'] ?? 'User');
    $userRole  = trim($_SESSION['userlevel'] ?? $_SESSION['user_role'] ?? 'User');

    if (!empty($userEmail)) {
        $uStmt = $conn->prepare("SELECT id, sccode, userid, profilename, userlevel FROM usersapp WHERE email = ? LIMIT 1");
        if ($uStmt) {
            $uStmt->bind_param("s", $userEmail);
            $uStmt->execute();
            $uRes = $uStmt->get_result();
            if ($uRes && $uRow = $uRes->fetch_assoc()) {
                if ($curUserId <= 0) {
                    $curUserId = (int)$uRow['id'];
                }
                if ($curSccode <= 0) {
                    $curSccode = (int)$uRow['sccode'];
                }
                if (empty($userName) || $userName === 'User') {
                    $userName = $uRow['profilename'] ?: $userEmail;
                }
                $userRole = $uRow['userlevel'] ?: $userRole;
            }
            $uStmt->close();
        }
    }

    $sccode = $curSccode;
    $userId = $curUserId;

    if ($sccode <= 0 && $userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized session. Please login.']);
        exit;
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    // ---------------------------------------------------------
    // 1. List Tickets for Current School / User
    // ---------------------------------------------------------
    if ($action === 'list') {
        $stmt = $conn->prepare("SELECT t.*, 
                                       (SELECT COUNT(*) FROM ticket_messages tm WHERE tm.ticket_id = t.id AND tm.notes = 0) as message_count,
                                       (SELECT MAX(sent_at) FROM ticket_messages tm WHERE tm.ticket_id = t.id AND tm.notes = 0) as last_reply_at
                                FROM tickets t 
                                WHERE (t.sccode = ? OR t.user_id = ?) 
                                ORDER BY t.id DESC LIMIT 100");
        $stmt->bind_param("ii", $sccode, $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $tickets = [];
        $counts = [
            'total'       => 0,
            'open'        => 0,
            'in_progress' => 0,
            'resolved'    => 0,
            'closed'      => 0
        ];

        while ($row = $res->fetch_assoc()) {
            $tickets[] = $row;
            $counts['total']++;
            $st = strtolower(trim((string)$row['status']));
            if ($st === 'open') {
                $counts['open']++;
            } elseif ($st === 'in_progress' || $st === 'waiting' || $st === 'replied') {
                $counts['in_progress']++;
            } elseif ($st === 'resolved') {
                $counts['resolved']++;
            } elseif ($st === 'closed') {
                $counts['closed']++;
            }
        }
        $stmt->close();

        echo json_encode([
            'success' => true, 
            'tickets' => $tickets,
            'counts'  => $counts
        ]);
        exit;
    }

    // ---------------------------------------------------------
    // 2. Fetch Conversation Messages
    // ---------------------------------------------------------
    if ($action === 'messages') {
        $ticketId = (int)($_GET['ticket_id'] ?? 0);
        if ($ticketId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ticket ID.']);
            exit;
        }

        try {
            $stmt = $conn->prepare("SELECT tm.*, 
                                           CASE 
                                               WHEN tm.admin_response = 1 THEN 'Support Agent'
                                               WHEN tm.sender_id = ? THEN 'You'
                                               ELSE COALESCE((SELECT profilename FROM usersapp WHERE id = tm.sender_id LIMIT 1), 'User')
                                           END as sender_name 
                                    FROM ticket_messages tm
                                    WHERE tm.ticket_id = ? AND tm.notes = 0 
                                    ORDER BY tm.id ASC");
            if ($stmt) {
                $stmt->bind_param("ii", $userId, $ticketId);
                $stmt->execute();
                $res = $stmt->get_result();
                $messages = [];
                while ($row = $res->fetch_assoc()) {
                    $messages[] = $row;
                }
                $stmt->close();
                echo json_encode(['success' => true, 'messages' => $messages]);
                exit;
            }

            // Fallback query
            $stmt = $conn->prepare("SELECT tm.*, 
                                           IF(tm.admin_response = 1, 'Support Agent', 'You') as sender_name 
                                    FROM ticket_messages tm 
                                    WHERE tm.ticket_id = ? AND tm.notes = 0 
                                    ORDER BY tm.id ASC");
            $stmt->bind_param("i", $ticketId);
            $stmt->execute();
            $res = $stmt->get_result();
            $messages = [];
            while ($row = $res->fetch_assoc()) {
                $messages[] = $row;
            }
            $stmt->close();
            echo json_encode(['success' => true, 'messages' => $messages]);
            exit;
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to load conversation: ' . $e->getMessage()]);
            exit;
        }
    }

    // ---------------------------------------------------------
    // 3. Create New Ticket
    // ---------------------------------------------------------
    if ($action === 'create') {
        $subject   = trim($_POST['subject'] ?? '');
        $message   = trim($_POST['message'] ?? '');
        $category  = trim($_POST['category'] ?? 'General');
        $priority  = trim($_POST['priority'] ?? 'Medium');
        $ticketFor = trim($_POST['ticket_for'] ?? 'Institute');

        if (empty($subject) || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Subject and message are required.']);
            exit;
        }

        $now = date('Y-m-d H:i:s');
        $status = 'open';
        $platform = 'Modern';

        $stmt = $conn->prepare("INSERT INTO tickets (sccode, user_id, subject, status, ticket_for, category, priority, platform, created_at, modifieddate) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssssss", $sccode, $userId, $subject, $status, $ticketFor, $category, $priority, $platform, $now, $now);
        $stmt->execute();
        $ticketId = $stmt->insert_id;
        $stmt->close();

        if ($ticketId) {
            $stmtMsg = $conn->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, message, admin_response, dev_sub, notes, sent_at, modifieddate) 
                                       VALUES (?, ?, ?, 0, 0, 0, ?, ?)");
            $stmtMsg->bind_param("iisss", $ticketId, $userId, $message, $now, $now);
            $stmtMsg->execute();
            $stmtMsg->close();

            // Trigger Push Notification to Core Helpdesk / Admins
            try {
                $pushEnginePaths = [
                    __DIR__ . '/../eimbox-core/core/PushEngine.php',
                    __DIR__ . '/core/PushEngine.php',
                    dirname(__DIR__, 2) . '/eimbox-core/core/PushEngine.php'
                ];
                foreach ($pushEnginePaths as $p) {
                    if (file_exists($p)) {
                        require_once $p;
                        break;
                    }
                }
                if (class_exists('PushEngine')) {
                    PushEngine::notifyTicketEvent($conn, [
                        'ticket_id'   => $ticketId,
                        'sccode'      => $sccode,
                        'user_id'     => $userId,
                        'subject'     => $subject,
                        'message'     => $message,
                        'sender_name' => $userName
                    ], 'new_ticket');
                }
            } catch (Throwable $e) {
                error_log('[Support Ticket Push Error] ' . $e->getMessage());
            }

            echo json_encode(['success' => true, 'message' => 'Ticket submitted successfully!', 'ticket_id' => $ticketId]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Failed to create ticket.']);
        exit;
    }

    // ---------------------------------------------------------
    // 4. Reply to Ticket
    // ---------------------------------------------------------
    if ($action === 'reply') {
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $message  = trim($_POST['message'] ?? '');

        if ($ticketId <= 0 || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
            exit;
        }

        // Guard: Prevent replying to closed tickets
        $statusStmt = $conn->prepare("SELECT status FROM tickets WHERE id = ? LIMIT 1");
        $statusStmt->bind_param("i", $ticketId);
        $statusStmt->execute();
        $statusRes = $statusStmt->get_result();
        $ticketRow = $statusRes ? $statusRes->fetch_assoc() : null;
        $statusStmt->close();

        if ($ticketRow && strtolower(trim((string)$ticketRow['status'])) === 'closed') {
            echo json_encode(['success' => false, 'message' => 'This ticket is closed. Replies are disabled.']);
            exit;
        }

        // Prevent duplicate submissions within the last 3 seconds
        $chk = $conn->prepare("SELECT id FROM ticket_messages 
                               WHERE ticket_id = ? AND sender_id = ? AND message = ? 
                               AND sent_at >= DATE_SUB(NOW(), INTERVAL 3 SECOND) LIMIT 1");
        if ($chk) {
            $chk->bind_param("iis", $ticketId, $userId, $message);
            $chk->execute();
            $chkRes = $chk->get_result();
            if ($chkRes && $chkRes->num_rows > 0) {
                $chk->close();
                echo json_encode(['success' => true, 'message' => 'Reply sent successfully.']);
                exit;
            }
            $chk->close();
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, message, admin_response, dev_sub, notes, sent_at, modifieddate) 
                                VALUES (?, ?, ?, 0, 0, 0, ?, ?)");
        $stmt->bind_param("iisss", $ticketId, $userId, $message, $now, $now);
        $stmt->execute();
        $stmt->close();

        $update = $conn->prepare("UPDATE tickets SET status = 'in_progress', modifieddate = ? WHERE id = ?");
        $update->bind_param("si", $now, $ticketId);
        $update->execute();
        $update->close();

        // Fetch ticket details for notification
        $tktSubject = 'Support Ticket #' . $ticketId;
        $tktSccode = $sccode;
        try {
            $tStmt = $conn->prepare("SELECT subject, sccode FROM tickets WHERE id = ? LIMIT 1");
            if ($tStmt) {
                $tStmt->bind_param("i", $ticketId);
                $tStmt->execute();
                $tRes = $tStmt->get_result();
                if ($tRow = $tRes->fetch_assoc()) {
                    $tktSubject = $tRow['subject'];
                    $tktSccode = (int)$tRow['sccode'];
                }
                $tStmt->close();
            }
        } catch (Throwable $e) {}

        // Trigger Push Notification to Core Helpdesk / Admins
        try {
            $pushEnginePaths = [
                __DIR__ . '/../eimbox-core/core/PushEngine.php',
                __DIR__ . '/core/PushEngine.php',
                dirname(__DIR__, 2) . '/eimbox-core/core/PushEngine.php'
            ];
            foreach ($pushEnginePaths as $p) {
                if (file_exists($p)) {
                    require_once $p;
                    break;
                }
            }
            if (class_exists('PushEngine')) {
                PushEngine::notifyTicketEvent($conn, [
                    'ticket_id'   => $ticketId,
                    'sccode'      => $tktSccode,
                    'user_id'     => $userId,
                    'subject'     => $tktSubject,
                    'message'     => $message,
                    'sender_name' => $userName
                ], 'user_reply');
            }
        } catch (Throwable $e) {
            error_log('[Support Ticket Push Error] ' . $e->getMessage());
        }

        echo json_encode(['success' => true, 'message' => 'Reply sent successfully.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;
}

// ---------------------------------------------------------
// FULL PAGE RENDER (Includes Materio Header & Footer)
// ---------------------------------------------------------
require_once 'header.php';
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Page Header & Action -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1 text-primary fw-normal">
                <i class="bi bi-headset me-2"></i>Help &amp; Support Desk
            </h4>
            <p class="text-muted small mb-0 fw-normal">
                Submit software issues, account queries, or request help directly to the EIMBox Support Team.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm fw-normal" onclick="loadTickets()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </button>
            <button type="button" class="btn btn-primary btn-sm fw-normal" onclick="openCreateTicketModal()">
                <i class="bi bi-plus-circle me-1"></i> New Ticket
            </button>
        </div>
    </div>

    <!-- Quick KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Tickets -->
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-label-primary" style="width: 44px; height: 44px; min-width: 44px;">
                        <i class="bi bi-ticket-perforated fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-normal">Total Tickets</div>
                        <div class="fs-5 fw-normal text-dark" id="kpiTotal">0</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Open Tickets -->
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-label-warning" style="width: 44px; height: 44px; min-width: 44px;">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-normal">Open</div>
                        <div class="fs-5 fw-normal text-warning" id="kpiOpen">0</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- In Progress Tickets -->
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-label-info" style="width: 44px; height: 44px; min-width: 44px;">
                        <i class="bi bi-arrow-repeat fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-normal">In Progress</div>
                        <div class="fs-5 fw-normal text-info" id="kpiInProgress">0</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resolved Tickets -->
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-label-success" style="width: 44px; height: 44px; min-width: 44px;">
                        <i class="bi bi-check2-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-normal">Resolved</div>
                        <div class="fs-5 fw-normal text-success" id="kpiResolved">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Tickets Card -->
    <div class="card shadow-sm border">
        <!-- Card Header with Filters & Search -->
        <div class="card-header py-3 border-bottom bg-light">
            <div class="row g-2 align-items-center justify-content-between">
                <!-- Status Filter Pills -->
                <div class="col-12 col-md-auto">
                    <div class="btn-group btn-group-sm" role="group" id="statusFilterGroup">
                        <button type="button" class="btn btn-primary fw-normal active" onclick="filterByStatus('all', this)">All</button>
                        <button type="button" class="btn btn-outline-warning fw-normal" onclick="filterByStatus('open', this)">Open</button>
                        <button type="button" class="btn btn-outline-info fw-normal" onclick="filterByStatus('in_progress', this)">In Progress</button>
                        <button type="button" class="btn btn-outline-success fw-normal" onclick="filterByStatus('resolved', this)">Resolved</button>
                        <button type="button" class="btn btn-outline-secondary fw-normal" onclick="filterByStatus('closed', this)">Closed</button>
                    </div>
                </div>

                <!-- Live Search Box -->
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" id="ticketSearchInput" class="form-control fw-normal" placeholder="Filter tickets by ID, subject, category..." onkeyup="handleFilterSearch()">
                        <button class="btn btn-outline-secondary fw-normal" type="button" onclick="clearSearch()"><i class="bi bi-x"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tickets Table Body -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0" id="ticketsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3 fw-normal" style="width: 90px;">Ticket ID</th>
                            <th class="py-3 fw-normal" style="max-width:120px;" >Subject</th>
                            <th class="py-3 fw-normal text-center" >Platform</th>
                            <th class="py-3 fw-normal text-center" >Scope</th>
                            <th class="py-3 fw-normal text-center" >Category</th>
                            <th class="py-3 fw-normal text-center" >Priority</th>
                            <th class="py-3 fw-normal text-center" >Status</th>
                            <th class="py-3 fw-normal text-center" >Submitted</th>
                            <th class="text-end pe-3 py-3 fw-normal" >Action</th>
                        </tr>
                    </thead>
                    <tbody id="ticketsTableBody">
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted fw-normal">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Loading tickets...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Card Footer -->
        <div class="card-footer py-2 border-top bg-light d-flex justify-content-between align-items-center">
            <span class="text-muted small fw-normal" id="ticketTableSummary">Showing tickets...</span>
            <span class="badge bg-label-secondary fw-normal" id="ticketCountBadge">Total: 0</span>
        </div>
    </div>

</div>

<!-- ============================================================== -->
<!-- Modal: Create New Support Ticket -->
<!-- ============================================================== -->
<div class="modal fade" id="createTicketModal" tabindex="-1" aria-hidden="true" style="z-index: 3060;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 540px;">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-primary text-white py-3 px-3">
                <h5 class="modal-title text-white mb-0 fs-6 fw-normal d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle"></i> Create Support Ticket
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="hideModal('createTicketModal')"></button>
            </div>
            <form id="createTicketForm" onsubmit="handleCreateTicket(event)">
                <div class="modal-body p-3">
                    <!-- Field 1: Subject -->
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1 fw-normal d-flex align-items-center gap-1">
                            <i class="bi bi-chat-left-text text-primary"></i> Issue Subject <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="subject" class="form-control fw-normal" required placeholder="Brief summary of the issue (e.g. Mark entry field missing)">
                    </div>

                    <!-- Row 2: Scope, Category, Priority -->
                    <div class="row g-2 mb-3">
                        <div class="col-sm-4">
                            <label class="form-label small text-muted mb-1 fw-normal d-flex align-items-center gap-1">
                                <i class="bi bi-building text-info"></i> Scope
                            </label>
                            <select name="ticket_for" class="form-select form-select-sm fw-normal">
                                <option value="Institute" selected>Institute</option>
                                <option value="Application">Software Bug</option>
                                <option value="Personal">Personal</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small text-muted mb-1 fw-normal d-flex align-items-center gap-1">
                                <i class="bi bi-tag text-info"></i> Category
                            </label>
                            <select name="category" class="form-select form-select-sm fw-normal">
                                <option value="General" selected>General</option>
                                <option value="Accounts">Accounts</option>
                                <option value="Attendance">Attendance</option>
                                <option value="Result">Result</option>
                                <option value="Billing">Billing</option>
                                <option value="SMS">SMS Gateway</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small text-muted mb-1 fw-normal d-flex align-items-center gap-1">
                                <i class="bi bi-flag text-warning"></i> Priority
                            </label>
                            <select name="priority" class="form-select form-select-sm fw-normal">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                    </div>

                    <!-- Field 3: Detailed Description -->
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1 fw-normal d-flex align-items-center gap-1">
                            <i class="bi bi-text-paragraph text-primary"></i> Detailed Description <span class="text-danger">*</span>
                        </label>
                        <textarea name="message" class="form-control fw-normal" rows="4" required placeholder="Please describe the problem with details, steps to reproduce, or student/voucher IDs..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 border-top bg-light d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-normal" data-bs-dismiss="modal" onclick="hideModal('createTicketModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-normal" id="btnSubmitTicket">
                        <i class="bi bi-send me-1"></i> Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- Modal: View Conversation & Reply Thread -->
<!-- ============================================================== -->
<div class="modal fade" id="viewTicketModal" tabindex="-1" aria-hidden="true" style="z-index: 3060;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow border-0">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white py-3 px-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2 text-truncate pe-2">
                    <span class="badge bg-warning text-dark fw-normal" id="v_ticketNo">#TKT-0</span>
                    <span class="badge bg-label-info fw-normal text-white border border-light" id="v_platformBadge">
                        <i class="bi bi-display me-1"></i>Materio
                    </span>
                    <h5 class="modal-title text-white mb-0 fs-6 fw-normal text-truncate" id="v_subject">Ticket Subject</h5>
                </div>
                <button type="button" class="btn-close btn-close-white flex-shrink-0" data-bs-dismiss="modal" onclick="hideModal('viewTicketModal')"></button>
            </div>

            <!-- Modal Body: Conversation Feed & Reply -->
            <div class="modal-body p-3 bg-light">
                <!-- Meta strip -->
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <span class="small fw-normal text-muted" id="v_statusBadge">Status: —</span>
                    <span class="small fw-normal text-muted" id="v_categoryBadge">Category: —</span>
                </div>

                <!-- Messages Thread Container -->
                <div id="v_messagesThread" class="border rounded bg-white p-3 mb-3 d-flex flex-column gap-2" style="max-height: 400px; min-height: 180px; overflow-y: auto;">
                    <!-- Dynamic conversation items rendered here -->
                </div>

                <!-- Reply Form -->
                <form id="replyTicketForm" onsubmit="handleSendReply(event)">
                    <input type="hidden" id="v_ticketId" name="ticket_id">
                    <div class="input-group">
                        <input type="text" id="v_replyInput" name="message" class="form-control fw-normal" placeholder="Write a response or follow-up note..." required autocomplete="off">
                        <button class="btn btn-primary fw-normal" type="submit" id="btnSubmitReply">
                            <i class="bi bi-send me-1"></i> Reply
                        </button>
                    </div>
                </form>

                <!-- Closed Ticket Notice -->
                <div id="v_closedNotice" class="alert alert-secondary py-2 px-3 m-0 small fw-normal text-center d-none">
                    <i class="bi bi-lock-fill me-1 text-danger"></i> This ticket is closed. Replies are disabled.
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer py-2 px-3 border-top bg-light d-flex justify-content-between align-items-center">
                <span class="small text-muted fw-normal"><i class="bi bi-shield-check me-1 text-success"></i> EIMBox Support Center</span>
                <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-normal" data-bs-dismiss="modal" onclick="hideModal('viewTicketModal')">
                    <i class="bi bi-x-circle me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- Bootstrap 5 Toast Notification Container -->
<!-- ============================================================== -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="toastNotification" class="toast align-items-center text-white bg-success border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center fw-normal" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- ============================================================== -->
<!-- Client-Side JavaScript Logic -->
<!-- ============================================================== -->
<script>
let allTicketsData = [];
let currentFilterStatus = 'all';
let activeViewingTicketId = 0;
let activeViewingTicketStatus = '';
let isSendingReply = false;

// -------------------------------------------------------------
// Initialize on DOM Ready
// -------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    loadTickets();
});

// -------------------------------------------------------------
// Modal Show / Hide Helpers (Bootstrap 5 Standard)
// -------------------------------------------------------------
function showModal(modalId) {
    const el = document.getElementById(modalId);
    if (!el) return;
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
            return;
        }
    } catch (e) {
        console.warn('Bootstrap modal show warning:', e);
    }
    if (typeof $ !== 'undefined' && typeof $.fn.modal === 'function') {
        $('#' + modalId).modal('show');
    }
}

function hideModal(modalId) {
    const el = document.getElementById(modalId);
    if (!el) return;
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const inst = bootstrap.Modal.getInstance(el);
            if (inst) inst.hide();
            return;
        }
    } catch (e) {
        console.warn('Bootstrap modal hide warning:', e);
    }
    if (typeof $ !== 'undefined' && typeof $.fn.modal === 'function') {
        $('#' + modalId).modal('hide');
    }
}

// -------------------------------------------------------------
// Toast Notification Function (No SweetAlert)
// -------------------------------------------------------------
function showToast(message, type = 'success') {
    const toastEl = document.getElementById('toastNotification');
    const toastMsg = document.getElementById('toastMessage');
    if (!toastEl || !toastMsg) return;

    let bgClass = 'bg-success';
    let iconClass = 'bi-check-circle-fill';
    if (type === 'error' || type === 'danger') {
        bgClass = 'bg-danger';
        iconClass = 'bi-exclamation-triangle-fill';
    } else if (type === 'warning') {
        bgClass = 'bg-warning text-dark';
        iconClass = 'bi-exclamation-circle-fill';
    } else if (type === 'info') {
        bgClass = 'bg-info text-dark';
        iconClass = 'bi-info-circle-fill';
    }

    toastEl.className = `toast align-items-center text-white ${bgClass} border-0 shadow`;
    toastMsg.innerHTML = `<i class="bi ${iconClass} me-2 fs-6"></i> <span>${escapeHtml(message)}</span>`;

    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3500 });
        toast.show();
    }
}

// -------------------------------------------------------------
// Human-readable Relative Time Calculator
// -------------------------------------------------------------
function timeAgo(dateString) {
    if (!dateString) return '—';
    const formattedStr = dateString.includes(' ') && !dateString.includes('T') ? dateString.replace(' ', 'T') : dateString;
    const d = new Date(formattedStr);
    if (isNaN(d.getTime())) return dateString.slice(0, 16);

    const now = new Date();
    const diffSeconds = Math.floor((now - d) / 1000);

    if (diffSeconds < 10) return 'just now';
    if (diffSeconds < 60) return `${diffSeconds}s ago`;

    const diffMinutes = Math.floor(diffSeconds / 60);
    if (diffMinutes < 60) return `${diffMinutes}m ago`;

    const diffHours = Math.floor(diffMinutes / 60);
    if (diffHours < 24) return `${diffHours}h ago`;

    const diffDays = Math.floor(diffHours / 24);
    if (diffDays === 1) return 'yesterday';
    if (diffDays < 30) return `${diffDays}d ago`;

    const diffMonths = Math.floor(diffDays / 30);
    if (diffMonths < 12) return `${diffMonths}mo ago`;

    const diffYears = Math.floor(diffDays / 365);
    return `${diffYears}y ago`;
}

// -------------------------------------------------------------
// Load Tickets via AJAX
// -------------------------------------------------------------
async function loadTickets() {
    const tbody = document.getElementById('ticketsTableBody');
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-5 text-muted fw-normal"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Loading tickets...</td></tr>';

    try {
        const res = await fetch('support_tickets.php?ajax=1&action=list');
        const data = await res.json();

        if (data.success) {
            allTicketsData = data.tickets || [];
            const counts = data.counts || {};

            // Update KPI counters
            document.getElementById('kpiTotal').innerText = counts.total || 0;
            document.getElementById('kpiOpen').innerText = counts.open || 0;
            document.getElementById('kpiInProgress').innerText = counts.in_progress || 0;
            document.getElementById('kpiResolved').innerText = counts.resolved || 0;
            document.getElementById('ticketCountBadge').innerText = `Total: ${allTicketsData.length}`;

            renderTicketsTable();
        } else {
            tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-danger fw-normal"><i class="bi bi-x-circle me-1"></i> ${escapeHtml(data.message || 'Failed to load tickets.')}</td></tr>`;
            showToast(data.message || 'Failed to load tickets list.', 'error');
        }
    } catch (err) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4 fw-normal"><i class="bi bi-wifi-off me-1"></i> Network error loading tickets.</td></tr>';
        showToast('Network error while fetching tickets.', 'error');
    }
}

// -------------------------------------------------------------
// Render Table Based on Filter & Search
// -------------------------------------------------------------
function renderTicketsTable() {
    const tbody = document.getElementById('ticketsTableBody');
    const searchVal = (document.getElementById('ticketSearchInput')?.value || '').toLowerCase().trim();

    const filtered = allTicketsData.filter(t => {
        // Status filter
        const st = (t.status || '').toLowerCase();
        if (currentFilterStatus !== 'all') {
            if (currentFilterStatus === 'open' && st !== 'open') return false;
            if (currentFilterStatus === 'in_progress' && (st !== 'in_progress' && st !== 'waiting' && st !== 'replied')) return false;
            if (currentFilterStatus === 'resolved' && st !== 'resolved') return false;
            if (currentFilterStatus === 'closed' && st !== 'closed') return false;
        }

        // Text search filter
        if (searchVal) {
            const idStr = String(t.id || '');
            const subStr = String(t.subject || '').toLowerCase();
            const catStr = String(t.category || '').toLowerCase();
            const prioStr = String(t.priority || '').toLowerCase();
            const platStr = String(t.platform || '').toLowerCase();
            if (!idStr.includes(searchVal) && !subStr.includes(searchVal) && !catStr.includes(searchVal) && !prioStr.includes(searchVal) && !platStr.includes(searchVal)) {
                return false;
            }
        }

        return true;
    });

    document.getElementById('ticketTableSummary').innerText = `Showing ${filtered.length} of ${allTicketsData.length} tickets`;

    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-5 text-muted fw-normal"><i class="bi bi-inbox fs-4 d-block mb-2 text-secondary"></i> No matching support tickets found.</td></tr>';
        return;
    }

    let html = '';
    filtered.forEach(t => {
        // Status badge styling
        let statusBadge = 'bg-label-secondary';
        const st = (t.status || '').toLowerCase();
        if (st === 'open') statusBadge = 'bg-label-warning';
        else if (st === 'in_progress' || st === 'waiting' || st === 'replied') statusBadge = 'bg-label-info';
        else if (st === 'resolved') statusBadge = 'bg-label-success';
        else if (st === 'closed') statusBadge = 'bg-label-secondary';

        // Priority badge styling
        let prioBadge = 'bg-label-secondary';
        const prio = (t.priority || '').toLowerCase();
        if (prio === 'critical') prioBadge = 'bg-label-danger';
        else if (prio === 'high') prioBadge = 'bg-label-warning';
        else if (prio === 'medium') prioBadge = 'bg-label-info';
        else if (prio === 'low') prioBadge = 'bg-label-secondary';

        html += `
            <tr>
                <td class="ps-3 font-monospace fw-normal text-primary">#TKT-${t.id}</td>
                <td>
                    <div class="fw-normal text-dark text-truncate" style="max-width: 200px;" title="${escapeHtml(t.subject)}">
                        ${escapeHtml(t.subject)}
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge bg-label-secondary fw-normal">
                        <i class="bi bi-display me-1"></i>${escapeHtml(t.platform || 'Materio')}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge bg-label-info fw-normal">${escapeHtml(t.ticket_for || 'Institute')}</span>
                </td>
                <td class="text-center">
                    <span class="badge bg-label-primary fw-normal">${escapeHtml(t.category || 'General')}</span>
                </td>
                <td class="text-center">
                    <span class="badge ${prioBadge} fw-normal">${escapeHtml(t.priority || 'Medium')}</span>
                </td>
                <td class="text-center">
                    <span class="badge ${statusBadge} fw-normal text-uppercase">${escapeHtml(t.status || 'open')}</span>
                </td>
                <td class="text-center text-muted small fw-normal" title="${escapeHtml(t.created_at || '')}">
                    <i class="bi bi-clock me-1"></i>${timeAgo(t.created_at)}
                </td>
                <td class="text-end pe-3">
                    <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2 fw-normal" 
                        onclick="openViewTicket(${t.id}, '${escapeJs(t.subject)}', '${escapeJs(t.status)}', '${escapeJs(t.platform || 'Materio')}', '${escapeJs(t.category || 'General')}')">
                        <i class="bi bi-chat-dots me-1"></i> (${t.message_count || 0})
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

// -------------------------------------------------------------
// Filter & Search Handlers
// -------------------------------------------------------------
function filterByStatus(status, btn) {
    currentFilterStatus = status;
    const group = document.getElementById('statusFilterGroup');
    if (group) {
        group.querySelectorAll('button').forEach(b => {
            b.classList.remove('active', 'btn-primary');
            b.classList.add('btn-outline-primary');
        });
        if (btn) {
            btn.classList.add('active', 'btn-primary');
            btn.classList.remove('btn-outline-primary');
        }
    }
    renderTicketsTable();
}

function handleFilterSearch() {
    renderTicketsTable();
}

function clearSearch() {
    const input = document.getElementById('ticketSearchInput');
    if (input) {
        input.value = '';
        renderTicketsTable();
    }
}

// -------------------------------------------------------------
// Modal: Open Create Ticket
// -------------------------------------------------------------
function openCreateTicketModal() {
    const form = document.getElementById('createTicketForm');
    if (form) form.reset();
    showModal('createTicketModal');
}

// -------------------------------------------------------------
// Create Ticket Form Handler
// -------------------------------------------------------------
async function handleCreateTicket(e) {
    e.preventDefault();
    const form = document.getElementById('createTicketForm');
    const subject = form.querySelector('[name="subject"]').value.trim();
    const message = form.querySelector('[name="message"]').value.trim();

    if (!subject) {
        showToast('Please provide a subject for your ticket.', 'warning');
        return;
    }
    if (!message) {
        showToast('Please provide a detailed description.', 'warning');
        return;
    }

    const formData = new FormData(form);
    formData.append('ajax', '1');
    formData.append('action', 'create');

    const btn = document.getElementById('btnSubmitTicket');
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';

    try {
        const res = await fetch('support_tickets.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast(data.message || 'Your support ticket has been submitted successfully!', 'success');
            hideModal('createTicketModal');
            form.reset();
            loadTickets();
        } else {
            showToast(data.message || 'Failed to create ticket.', 'error');
        }
    } catch (err) {
        showToast('Network error while creating ticket.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// -------------------------------------------------------------
// Modal: Open View Ticket Conversation
// -------------------------------------------------------------
async function openViewTicket(id, subject, status, platform = 'Materio', category = 'General') {
    activeViewingTicketId = id;
    activeViewingTicketStatus = String(status || '').toLowerCase().trim();
    const isClosed = (activeViewingTicketStatus === 'closed');

    document.getElementById('v_ticketId').value = id;
    document.getElementById('v_ticketNo').innerText = `#TKT-${id}`;
    document.getElementById('v_subject').innerText = subject;
    document.getElementById('v_platformBadge').innerHTML = '<i class="bi bi-display me-1"></i>' + escapeHtml(platform || 'Materio');

    let statusBadgeClass = 'bg-label-primary';
    if (isClosed) statusBadgeClass = 'bg-label-secondary';
    else if (activeViewingTicketStatus === 'open') statusBadgeClass = 'bg-label-warning';
    else if (activeViewingTicketStatus === 'resolved') statusBadgeClass = 'bg-label-success';
    else if (activeViewingTicketStatus === 'in_progress' || activeViewingTicketStatus === 'replied') statusBadgeClass = 'bg-label-info';

    document.getElementById('v_statusBadge').innerHTML = 'Status: <span class="badge ' + statusBadgeClass + ' fw-normal text-uppercase">' + escapeHtml((status || 'open')) + '</span>';
    document.getElementById('v_categoryBadge').innerHTML = 'Category: <span class="badge bg-label-secondary fw-normal">' + escapeHtml(category) + '</span>';

    // Disable reply form and show notice for closed tickets
    const replyForm = document.getElementById('replyTicketForm');
    const closedNotice = document.getElementById('v_closedNotice');
    const replyInput = document.getElementById('v_replyInput');
    const submitBtn = document.getElementById('btnSubmitReply');

    if (isClosed) {
        if (replyForm) replyForm.classList.add('d-none');
        if (closedNotice) closedNotice.classList.remove('d-none');
        if (replyInput) replyInput.disabled = true;
        if (submitBtn) submitBtn.disabled = true;
    } else {
        if (replyForm) replyForm.classList.remove('d-none');
        if (closedNotice) closedNotice.classList.add('d-none');
        if (replyInput) {
            replyInput.disabled = false;
            replyInput.value = '';
        }
        if (submitBtn) submitBtn.disabled = false;
    }

    const thread = document.getElementById('v_messagesThread');
    thread.innerHTML = '<div class="text-center text-muted py-4 fw-normal"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Loading conversation messages...</div>';
    showModal('viewTicketModal');

    try {
        const res = await fetch(`support_tickets.php?ajax=1&action=messages&ticket_id=${id}`);
        const data = await res.json();
        if (data.success) {
            const rawMsgs = data.messages || [];
            const seenIds = new Set();
            const msgs = [];
            rawMsgs.forEach(m => {
                if (!seenIds.has(m.id)) {
                    seenIds.add(m.id);
                    msgs.push(m);
                }
            });

            if (msgs.length === 0) {
                thread.innerHTML = '<div class="text-center py-4 text-muted fw-normal">No messages in this conversation yet.</div>';
                return;
            }

            let html = '';
            msgs.forEach((m) => {
                const isAdmin = parseInt(m.admin_response) === 1;
                const bubbleBg = isAdmin ? 'bg-label-info border-start border-4 border-info' : 'bg-light border-start border-4 border-primary';
                const roleBadge = isAdmin 
                    ? '<span class="badge bg-info text-white ms-2 fw-normal" style="font-size: 10px;"><i class="bi bi-headset me-1"></i>Support Agent</span>' 
                    : '<span class="badge bg-primary text-white ms-2 fw-normal" style="font-size: 10px;"><i class="bi bi-person me-1"></i>You</span>';

                html += `
                    <div class="card p-2 border-0 shadow-none mb-1 ${bubbleBg}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <span class="fw-normal text-dark small">${escapeHtml(m.sender_name)}</span>
                                ${roleBadge}
                            </div>
                            <small class="text-muted" style="font-size: 11px;" title="${escapeHtml(m.sent_at || '')}">
                                <i class="bi bi-clock me-1"></i>${timeAgo(m.sent_at)}
                            </small>
                        </div>
                        <div class="text-dark small fw-normal" style="white-space: pre-line;">${escapeHtml(m.message)}</div>
                    </div>
                `;
            });

            thread.innerHTML = html;
            thread.scrollTop = thread.scrollHeight;
        } else {
            thread.innerHTML = `<div class="text-danger text-center py-3 fw-normal">${escapeHtml(data.message || 'No messages found.')}</div>`;
            showToast(data.message || 'Failed to load conversation messages.', 'error');
        }
    } catch (err) {
        thread.innerHTML = '<div class="text-danger text-center py-3 fw-normal"><i class="bi bi-wifi-off me-1"></i> Failed to load conversation.</div>';
        showToast('Network error while loading conversation.', 'error');
    }
}

// -------------------------------------------------------------
// Reply Form Handler
// -------------------------------------------------------------
async function handleSendReply(e) {
    e.preventDefault();
    if (isSendingReply) return;

    if (activeViewingTicketStatus === 'closed') {
        showToast('This ticket is closed. Replies are disabled.', 'warning');
        return;
    }

    const input = document.getElementById('v_replyInput');
    const msg = input.value.trim();
    if (!msg) {
        showToast('Please write a message before sending.', 'warning');
        return;
    }

    const btn = document.getElementById('btnSubmitReply');
    isSendingReply = true;
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Posting...';
    }

    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('action', 'reply');
    formData.append('ticket_id', activeViewingTicketId);
    formData.append('message', msg);

    try {
        const res = await fetch('support_tickets.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            input.value = '';
            showToast('Reply sent successfully!', 'success');
            openViewTicket(activeViewingTicketId, document.getElementById('v_subject').innerText, 'in_progress');
            loadTickets();
        } else {
            showToast(data.message || 'Failed to send reply.', 'error');
        }
    } catch (err) {
        showToast('Network error while sending reply.', 'error');
    } finally {
        isSendingReply = false;
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send me-1"></i> Reply';
        }
    }
}

// -------------------------------------------------------------
// Utility String Escape Helpers
// -------------------------------------------------------------
function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function escapeJs(text) {
    if (!text) return '';
    return String(text).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '');
}
</script>
