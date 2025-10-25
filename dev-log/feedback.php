<?php
// require_once '../core/config.php';
// require_once '../core/db.php';

// $currentFile = basename($_SERVER['PHP_SELF']);

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
while ($row = $result->fetch_assoc())
    $timeline[] = $row;
$res->close();

// Pre-defined feedback options
$feedbackOptions = ['Error found', 'Not functional', 'Incomplete', 'Need updating', 'Suggestion', 'Request', 'Unnecessary', 'Request for Trashy', 'Other'];

// Fetch logbook summary for this page
$log_res = $conn->prepare("SELECT COUNT(DISTINCT email) AS user_count, SUM(duration) AS total_duration FROM logbook WHERE pagename=?");
$log_res->bind_param("s", $currentFile);
$log_res->execute();
$log_stats = $log_res->get_result()->fetch_assoc();
$log_res->close();
?>

<style>
    .starRating {
        direction: rtl;
        font-size: 1.5rem;
    }

    .starRating input {
        display: none;
    }

    .starRating label {
        color: #ddd;
        cursor: pointer;
    }

    .starRating input:checked~label,
    .starRating label:hover,
    .starRating label:hover~label {
        color: #f5b301;
    }
</style>








<!-- Features & Feedback Modal -->
<div class="modal fade" id="featuresModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Features & Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3" style="color: <?php echo $page_status_colors[$page_status]; ?>">
                    <strong>Page Usage:</strong> <?= $log_stats['user_count'] ?> users, Total Duration:
                    <?= $log_stats['total_duration'] ?> sec | Page Status :
                    <?php echo $page_status_names[$page_status]; ?>

                    <span class="btn  btn-muted rounded-pill btn-icon" id="statusHelpBtn">
                        <i class="bi bi-question "></i>
                    </span>


                </div>
                <div class="accordion" id="featuresAccordion">
                    <?php foreach ($timeline as $i => $f): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading_<?= $i ?>">
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
                                ?>
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse_<?= $i ?>">
                                    <div class="row col-11 m-0 p-0">
                                        <div class="col p-0 m-0">
                                            <?= htmlspecialchars($f['feature_name']) ?> - Status:
                                            <?= htmlspecialchars($f['status']) ?>
                                        </div>
                                        <div class="col p-0 m-0 text-end">
                                            <span class="float-end"><strong>Average Rating:</strong>
                                                <?= $fb_stats['avg_rating'] ?> ★
                                                (<?= $fb_stats['cnt'] ?> feedbacks)</span>
                                        </div>
                                    </div>



                                </button>
                            </h2>
                            <div id="collapse_<?= $i ?>" class="accordion-collapse collapse"
                                data-bs-parent="#featuresAccordion">
                                <div class="accordion-body">
                                    <!-- Feedback summary -->
                                    <div class="feedbackSummary" id="feature_<?= $i ?>">
                                        <?php


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
                                        while ($c = $result2->fetch_assoc())
                                            $latest_comments[] = $c;
                                        $fb_res2->close();
                                        ?>




                                        <ul class="list-group list-group-flush mb-2" id="feedbackList_<?= $i ?>">
                                            <?php foreach ($latest_comments as $c): ?>
                                                <li class="list-group-item py-1">
                                                    [<?= $c['rating'] ?>★] <?= htmlspecialchars($c['feedback_type']) ?> -
                                                    <?= htmlspecialchars($c['notes']) ?>
                                                    <small>(<?= htmlspecialchars($c['logged_by']) ?>,
                                                        <?= date('Y-m-d H:i', strtotime($c['created_at'])) ?>)</small>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>

                                        <!-- Load More বাটন -->
                                        <button id="loadMoreBtn_<?= $i ?>" class="btn btn-sm btn-outline-primary mb-2"
                                            onclick="loadFeedback('<?= $currentFile ?>','<?= $f['feature_name'] ?>',<?= $i ?>,5)">
                                            Load More
                                        </button>

                                        <button class="btn btn-sm btn-primary mb-2"
                                            onclick="openFeedbackModal('<?= htmlspecialchars($f['feature_name']) ?>','<?= htmlspecialchars($f['status']) ?>')">Give
                                            Feedback / Suggest Feature</button>
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


<!-- Modal পেইজ স্ট্যাটাস-->
<div class="modal fade modal-lg" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center" id="statusModalLabel">Page Status Descriptions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul>
                    <?php
                    for ($i = 0; $i <= 8; $i++) {
                        ?>
                        <li style="color:<?php echo $page_status_colors[$i]; ?>">
                            <strong><?php echo $page_status_names[$i]; ?></strong> <br>
                            <small><?php echo $status_desc_en[$i]; ?></small> <br>
                            <small><?php echo $status_desc_bn[$i]; ?></small>

                            <hr class="m-0 mt-1 mb-1">


                        </li>
                        <?php
                    }

                    ?>


                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="feedbackForm" class="modal-content" method="post">
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
                    <input type="text" name="feedback_other" id="feedback_other" class="form-control mt-1 d-none"
                        placeholder="Enter your feedback or new feature request">
                </div>

                <div class="mb-2">
                    <label>Additional Notes</label>
                    <textarea name="feedback_notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="mb-2">
                    <label>Rating</label>
                    <div class="starRating">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?= $i ?>" name="rating" data-point="<?= $i; ?>" value="<?= $i ?>">
                            <label for="star<?= $i ?>">★</label>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="submit_user_feedback_form();" class="btn btn-primary">Submit</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openFeedbackModal(feature, status) {
        // alert(feature + '///' + status);
        document.getElementById('fb_feature_name').value = feature;
        document.getElementById('fb_status_name').value = status;
        // $('#').val(feature);
        // $('#').val(status);
        $('#feedbackForm')[0].reset();
        $('#feedback_other').addClass('d-none');
        var modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
        modal.show();
    }

    function toggleOther(sel) {
        $('#feedback_other').toggleClass('d-none', sel.value !== 'Other');
    }
</script>

<script>
    // AJAX submit feedback
    function submit_user_feedback_form() {
        var $form = $('#feedbackForm');
        var feature = $('#fb_feature_name').val();
        // alert(feature);

        var formData = $form.serialize();
        // alert("Form data being sent:\n" + formData); // কী-ভ্যালুগুলো দেখাবে
        console.log("Form data:", formData);




        $.post('dev-log/feedback_ajax.php', $form.serialize(), function (resp) {
            if (resp.success) {
                alert('Feedback submitted!');
                bootstrap.Modal.getInstance(document.getElementById('feedbackModal')).hide();

                // Refresh feedback summary via AJAX
                $.get('dev-log/feedback_summary.php', { page_name: '<?= $currentFile ?> ', feature: feature }, function (html) {
                    $('button[onclick*="' + feature + '"]').closest('.accordion-body').find('.feedbackSummary').html(html);
                });

            } else alert('Error: ' + resp.message);
        }, 'json');

    }



    var feedbackOffsets = {};

    function loadFeedback(pageName, featureName, idx, limit) {
        if (!feedbackOffsets[idx]) feedbackOffsets[idx] = 5; // প্রথমে ৫টা দেখানো হয়েছে

        var offset = feedbackOffsets[idx];

        $.get("dev-log/feedback_summary.php",
            { page_name: pageName, feature: featureName, offset: offset },
            function (data) {
                if ($.trim(data) !== "") {
                    $("#feedbackList_" + idx).append(data);
                    feedbackOffsets[idx] += limit;
                } else {
                    // আর কোন ফিডব্যাক না থাকলে বাটন হাইড
                    $("#loadMoreBtn_" + idx).hide();
                }
            }
        );
    }


</script>

<script>
    document.getElementById('statusHelpBtn').addEventListener('click', function () {
        var statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
        statusModal.show();
    });
</script>