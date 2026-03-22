<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$today = date("Y-m-d");

/* ===== Fetch Notice Authors ===== */
$notice_authors = [];

if (!empty($notices)) {

    $author_emails = array_unique(array_filter(array_column($notices, 'entryby')));

    if ($author_emails) {
        $placeholders = implode(',', array_fill(0, count($author_emails), '?'));
        $types = str_repeat('s', count($author_emails));

        $stmt = $conn->prepare("
            SELECT email, profilename
            FROM usersapp
            WHERE email IN ($placeholders)
        ");

        $stmt->bind_param($types, ...$author_emails);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $notice_authors[$row['email']] = $row['profilename'];
        }

        $stmt->close();
    }
}
?>

<div class="card card-border-shadow-primary  shadow-none border">
    <div class="card-body">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <div class="avatar me-2">
                    <span class="avatar-initial rounded bg-label-primary">
                        <i class="bi bi-list-check fs-4"></i>
                    </span>
                </div>
                <h6 class="mb-0 fw-bold">Notice Board</h6>
            </div>
            <span class="badge rounded-pill bg-primary" id="notice-badge"></span>
        </div>

        <div style="max-height:200px;overflow-y:auto">

            <div class="m3-notice-card shadow-sm">

                <!-- Card Title -->
                <div class="d-flex justify-content-between align-items-center mb-3">
            
                    <?php if (!empty($notices)): ?>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2"
                            style="font-size:.6rem;">Latest</span>
                    <?php endif; ?>
                </div>

                <?php if (empty($notices)): ?>

                    <!-- Empty State -->
                    <div class="text-center py-4 opacity-25">
                        <i class="bi bi-chat-left-dots display-6 fs-1"></i>
                        <p class="small fw-bold mt-2 mb-0">No active notices.</p>
                    </div>

                <?php else: ?>

                    <!-- Notice List -->
                    <div class="notice-list">

                        <?php foreach (array_slice($notices, 0, 3) as $i => $n):

                            $author = $notice_authors[$n['entryby']] ?? 'System';
                            $nid = 'n_collapse_' . $i;
                            $icon = htmlspecialchars($n['icon'] ?? 'bell-fill');
                            $color = htmlspecialchars($n['color'] ?? '#6750A4');
                            ?>

                            <div class="notice-item-m3 shadow-sm">

                                <div class="d-flex align-items-center" data-bs-toggle="collapse" href="#<?= $nid ?>"
                                    role="button">

                                    <div class="notice-icon-box" style="background:<?= $color ?>15;color:<?= $color ?>;">
                                        <i class="bi bi-<?= $icon ?>"></i>
                                    </div>

                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="n-title text-truncate">
                                            <?= htmlspecialchars($n['title']) ?>
                                        </div>
                                        <div class="n-meta">
                                            <?= date('d M, Y', strtotime($n['entrytime'])) ?>
                                            <i class="bi bi-dot"></i>
                                            By <?= htmlspecialchars($author) ?>
                                        </div>
                                    </div>

                                    <i class="bi bi-chevron-down text-muted small ms-2" hidden></i>
                                </div>

                                <div class="collapse" id="<?= $nid ?>" hidden>
                                    <div class="notice-desc-box">
                                        <?= nl2br(htmlspecialchars($n['descrip'])) ?>
                                    </div>
                                </div>

                            </div>

                        <?php endforeach; ?>
                    </div>

                    <!-- View All -->
                    <?php if (count($notices) > 3): ?>
                        <a href="notices.php?year=<?= $current_session ?>" class="btn-all-notices shadow-sm">
                            VIEW ALL NOTICES (<?= count($notices) ?>)
                        </a>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </div>

    </div>
</div>