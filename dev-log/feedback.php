<?php
require_once '../core/config.php';
require_once '../core/db.php';

$currentFile = basename($_SERVER['PHP_SELF']);

// Fetch latest status per feature
$timeline = [];
$res = $conn->prepare("
    SELECT t1.*
    FROM dev_timeline t1
    INNER JOIN (
        SELECT feature_name, MAX(created_at) AS max_time
        FROM dev_timeline
        WHERE page_name=?
        GROUP BY feature_name
    ) t2 ON t1.feature_name=t2.feature_name AND t1.created_at=t2.max_time
    ORDER BY t1.feature_name ASC
");
$res->bind_param("s", $currentFile);
$res->execute();
$result = $res->get_result();
while ($row = $result->fetch_assoc()) $timeline[] = $row;
$res->close();

// Pre-defined feedback options
$feedbackOptions = ['Error found', 'Not functional', 'Incomplete', 'Need updating', 'Suggestion', 'Other'];

// Fetch logbook summary for this page
$log_res = $conn->prepare("SELECT COUNT(DISTINCT email) AS user_count, SUM(duration) AS total_duration FROM logbook WHERE pagename=?");
$log_res->bind_param("s",$currentFile);
$log_res->execute();
$log_stats = $log_res->get_result()->fetch_assoc();
$log_res->close();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Feature Feedback & Usage</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<style>
.starRating { direction: rtl; font-size: 1.5rem; }
.starRating input { display: none; }
.starRating label { color: #ddd; cursor: pointer; }
.starRating input:checked~label,
.starRating label:hover,
.starRating label:hover~label { color: #f5b301; }
</style>
</head>
<body class="container py-4">

<h3><?= htmlspecialchars($currentFile) ?> - Feature Feedback & Usage</h3>

<div class="mb-3">
    <strong>Page Usage:</strong> <?= $log_stats['user_count'] ?> users, Total Duration: <?= $log_stats['total_duration'] ?> sec
</div>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#featuresModal">View Features & Feedback</button>

<!-- Features & Feedback Modal -->
<div class="modal fade" id="featuresModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Features & Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="accordion" id="featuresAccordion">
                    <?php foreach($timeline as $i => $f): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading_<?= $i ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_<?= $i ?>">
                                <?= htmlspecialchars($f['feature_name']) ?> - Status: <?= htmlspecialchars($f['status']) ?>
                            </button>
                        </h2>
                        <div id="collapse_<?= $i ?>" class="accordion-collapse collapse" data-bs-parent="#featuresAccordion">
                            <div class="accordion-body">
                                <!-- Feedback summary -->
                                <div class="feedbackSummary" id="feature_<?= $i ?>">
                                    <?php
                                    // Fetch feedback stats for this feature
                                    $fb_res = $conn->prepare("
                                        SELECT COUNT(*) AS cnt, IFNULL(ROUND(AVG(rating),1),0) AS avg_rating
                                        FROM page_feedback
                                        WHERE page_name=? AND feature_name=?
                                    ");
                                    $fb_res->bind_param("ss", $currentFile, $f['feature_name']);
                                    $fb_res->execute();
                                    $fb_stats = $fb_res->get_result()->fetch_assoc();
                                    $fb_res->close();

                                    // Latest 5 comments
                                    $latest_comments = [];
                                    $fb_res2 = $conn->prepare("
                                        SELECT feedback_type, notes, rating, logged_by, created_at
                                        FROM page_feedback
                                        WHERE page_name=? AND feature_name=?
                                        ORDER BY created_at DESC LIMIT 5
                                    ");
                                    $fb_res2->bind_param("ss", $currentFile, $f['feature_name']);
                                    $fb_res2->execute();
                                    $result2 = $fb_res2->get_result();
                                    while ($c = $result2->fetch_assoc()) $latest_comments[] = $c;
                                    $fb_res2->close();
                                    ?>

                                    <p><strong>Average Rating:</strong> <?= $fb_stats['avg_rating'] ?> ★ (<?= $fb_stats['cnt'] ?> feedbacks)</p>
                                    <ul class="list-group list-group-flush mb-2">
                                        <?php foreach($latest_comments as $c): ?>
                                            <li class="list-group-item py-1">
                                                [<?= $c['rating'] ?>★] <?= htmlspecialchars($c['feedback_type']) ?> -
                                                <?= htmlspecialchars($c['notes']) ?>
                                                <small>(<?= htmlspecialchars($c['logged_by']) ?>, <?= date('Y-m-d H:i', strtotime($c['created_at'])) ?>)</small>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>

                                    <button class="btn btn-sm btn-primary mb-2" onclick="openFeedbackModal('<?= htmlspecialchars($f['feature_name']) ?>','<?= htmlspecialchars($f['status']) ?>')">Give Feedback / Suggest Feature</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="feedbackForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Feedback / Feature Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="page_name" value="<?= htmlspecialchars($currentFile) ?>">
                <input type="hidden" name="feature_name" id="fb_feature_name">
                <input type="hidden" name="status_name" id="fb_status_name">

                <div class="mb-2">
                    <label>Feedback Type / Request</label>
                    <select name="feedback_type" id="feedback_type" class="form-select" onchange="toggleOther(this)">
                        <option value="">-- Select --</option>
                        <?php foreach ($feedbackOptions as $opt): ?>
                            <option value="<?= $opt ?>"><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="feedback_other" id="feedback_other" class="form-control mt-1 d-none" placeholder="Enter your feedback or new feature request">
                </div>

                <div class="mb-2">
                    <label>Additional Notes</label>
                    <textarea name="feedback_notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="mb-2">
                    <label>Rating</label>
                    <div class="starRating">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>">
                            <label for="star<?= $i ?>">★</label>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Submit</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openFeedbackModal(feature,status){
    $('#fb_feature_name').val(feature);
    $('#fb_status_name').val(status);
    $('#feedbackForm')[0].reset();
    $('#feedback_other').addClass('d-none');
    var modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
    modal.show();
}

function toggleOther(sel){
    $('#feedback_other').toggleClass('d-none', sel.value !== 'Other');
}

// AJAX submit feedback
$('#feedbackForm').submit(function(e){
    e.preventDefault();
    var feature = $('#fb_feature_name').val();
    $.post('feedback_ajax.php', $(this).serialize(), function(resp){
        if(resp.success){
            alert('Feedback submitted!');
            bootstrap.Modal.getInstance(document.getElementById('feedbackModal')).hide();

            // Refresh feedback summary via AJAX
            $.get('feedback_summary.php', { page_name: '<?= $currentFile ?>', feature_name: feature }, function(html){
                $('button[onclick*="'+feature+'"]').closest('.accordion-body').find('.feedbackSummary').html(html);
            });

        } else alert('Error: '+resp.message);
    }, 'json');
});
</script>
</body>
</html>
