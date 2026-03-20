<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$today = date("Y-m-d");

// ১. ডাটা ফেচিং লজিক (আজকের + পূর্বের অসম্পূর্ণ কাজ)
// user ফাঁকা থাকতে পারে এমন ডাটাও আসতে পারে (নমুনা ডাটা অনুযায়ী)
$stmt = $conn->prepare("SELECT * FROM todolist WHERE sccode = ? AND (user = ? OR user = '') AND status = 0 ORDER BY date DESC, id DESC");
$stmt->bind_param("is", $sccode, $usr);
$stmt->execute();
$todo_res = $stmt->get_result();
$todo_count = $todo_res->num_rows;
?>

<style>
    .todo-item {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f1f1f1;
    }

    .todo-item:last-child {
        border-bottom: none;
    }

    .todo-item:hover {
        background-color: #f8f9fa;
    }

    .form-check-input {
        cursor: pointer;
        width: 1.2em;
        height: 1.2em;
    }

    .todo-type {
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .todo-date {
        font-size: 11px;
    }
</style>

<div class="card card-border-shadow-primary h-100 shadow-none border">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <div class="avatar me-2">
                    <span class="avatar-initial rounded bg-label-primary">
                        <i class="bi bi-list-check fs-4"></i>
                    </span>
                </div>
                <h6 class="mb-0 fw-bold">Pending Tasks</h6>
            </div>
            <span class="badge rounded-pill bg-primary" id="todo-badge"><?= $todo_count ?></span>
        </div>

        <div class="todo-list-container" style="max-height: 250px; overflow-y: auto;">
            <?php if ($todo_count > 0): ?>
                <?php while ($row = $todo_res->fetch_assoc()): ?>
                    <div class="todo-item p-2 d-flex align-items-start" id="todo-row-<?= $row['id'] ?>">
                        <div class="form-check me-2 mt-1">
                            <input class="form-check-input border-primary" type="checkbox"
                                onclick="markAsDone(<?= $row['id'] ?>)" id="check-<?= $row['id'] ?>">
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="todo-type text-primary"><?= $row['todotype'] ?></span>
                                <span class="todo-date text-muted">
                                    <i class="bi bi-calendar-event me-1"></i><?= date('d M', strtotime($row['date'])) ?>
                                </span>
                            </div>
                            <div class="fw-semibold text-dark small" style="line-height: 1.2;">
                                <?= $row['descrip1'] ?: 'No description' ?>
                            </div>
                            <?php if ($row['descrip2']): ?>
                                <div class="text-muted" style="font-size: 11px;"><?= $row['descrip2'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-emoji-smile fs-1 d-block mb-2"></i>
                    <p class="small">All caught up! No pending tasks.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

