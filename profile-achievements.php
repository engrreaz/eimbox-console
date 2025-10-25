<?php
$page_title = "User Profile";
include 'header.php';

// ধরে নিচ্ছি এগুলো সেশনে বা অন্য কোথাও ডিফাইন করা আছে
// $usr = $_SESSION['email'] ?? '';
// $sccode = $_SESSION['sccode'] ?? '';

$sql_cat = "SELECT * FROM achievements_category ORDER BY sl ASC";
$res_cat = $conn->query($sql_cat);

if ($res_cat && $res_cat->num_rows > 0) {
    echo "<div class='container mt-4'>";
    echo "<h3 class='mb-4 text-center'>Achievements by Category</h3>";

    while ($cat = $res_cat->fetch_assoc()) {
        $category_name = htmlspecialchars($cat['category']);

        echo "<div class='card mb-4 shadow-sm'>";
        echo "<div class='card-header bg-primary text-white'>";
        echo "<h5 class='mb-0'>$category_name</h5>";
        echo "</div>";

        // প্রতিটি ক্যাটাগরির অধীনে অ্যাচিভমেন্ট লিস্ট
        $sql_ach = "SELECT * FROM achievements_list WHERE category='$category_name' ORDER BY id ASC";
        $res_ach = $conn->query($sql_ach);

        echo "<div class='card-body'>";
        if ($res_ach && $res_ach->num_rows > 0) {
            echo "<div class='list-group'>";
            while ($ach = $res_ach->fetch_assoc()) {
                $ach_id = $ach['id'];
                $ach_name = htmlspecialchars($ach['name']);
                $ach_desc = htmlspecialchars($ach['description']);
                $points = intval($ach['points']);
                $level_req = htmlspecialchars($ach['level_requirment'] ?? 0);
                $tier = htmlspecialchars($ach['tier']);
                $type = htmlspecialchars($ach['type']);

                // ইউজার অ্যাচিভমেন্ট পেয়েছে কিনা চেক করা
                $sql_user = "SELECT * FROM user_achievements 
                             WHERE email='$usr' AND sccode='$sccode' AND achievement_id='$ach_id' LIMIT 1";
                $res_user = $conn->query($sql_user);

                $achieved = false;
                $progress = 0;

                if ($res_user && $res_user->num_rows > 0) {
                    $achieved = true;
                    $progress = 100;
                } else {
                    // এখনো অর্জিত হয়নি, পরে এখানে লজিক বসানো হবে
                    $progress = 0;

                    // ===========================================================
                    // CHECK / READ Progress
                    // ===========================================================
                    $username_field = [
                        'feedbacks' => 'email',
                        'page_feedback' => 'logged_by'
                    ];

                    $tbl_name = htmlspecialchars($ach['tbl_name'] ?? '');
                    $aggregate = htmlspecialchars($ach['aggregate'] ?? '');
                    $field = htmlspecialchars($ach['field'] ?? '');
                    $params = ($ach['params'] ?? '');
                    $requirement = htmlspecialchars($ach['requirement'] ?? 0);
                    if ($params != '' || $params != NULL) {
                        $where = ' AND ' . $params;
                    } else {
                        $where = '';
                    }
                    if ($tbl_name !== '') {
                        $eskuiel = "SELECT $aggregate($field) as ach FROM $tbl_name WHERE sccode='$sccode' and $username_field[$tbl_name] ='$usr' $where";
                        // echo $eskuiel;
                        $res_catx = $conn->query($eskuiel);

                        if ($res_catx && $res_catx->num_rows > 0) {

                            while ($qry = $res_catx->fetch_assoc()) {
                                $ach = htmlspecialchars($qry['ach']);
                            }
                        }

                        $progress = ceil((int) $ach * 100 / $requirement);
                    }

                }

          
                    // $progress = rand(1, 100);

                    if ($progress >= 80) {
                        $progress_color = 'limegreen';
                    } else if ($progress >= 60) {
                        $progress_color = 'teal';
                    } else if ($progress >= 40) {
                        $progress_color = 'darkorange';
                    } else if ($progress >= 20) {
                        $progress_color = 'chocolate';
                    } else if ($progress >= 1) {
                        $progress_color = 'slategray';
                    } else {
                        $progress_color = 'gray';
                    }
                

                // প্রগ্রেস বার রঙ নির্ধারণ
                // $progress_color = $achieved ? '#4caf50' : $progress_color;
                $text_color = $achieved ? 'text-success' : 'text-secondary';


                echo "<div class='list-group-item'>";
                echo "<div class='d-flex justify-content-between align-items-center'>";
                echo "<div>";
                echo "<h6 class='mb-1'><strong>$ach_name</strong> 
                      <small class='text-muted'>(Tier: $tier | Type: $type)</small></h6>";
                echo "<p class='mb-1 text-secondary'>$ach_desc</p>";
                echo "<small>Level: $level_req | Points: $points</small>";
                echo "</div>";

                // গোলাকার প্রগ্রেস বার
                echo "<div class='text-center' style='min-width:70px;'>";
                echo "<div class='progress-circle text-center m-auto' data-progress='$progress' style='--progress:$progress; --color:$progress_color;'></div>";
                echo "<small class='$text_color'>" . ($achieved ? 'Achieved' : 'In progress') . "</small>";
                echo "</div>";

                echo "</div>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<p class='text-muted mb-0'>No achievements found under this category.</p>";
        }

        echo "</div>"; // card-body
        echo "</div>"; // card
    }

    echo "</div>"; // container
} else {
    echo "<p class='text-center mt-5 text-danger'>No categories found.</p>";
}
?>

<style>
    /* গোলাকার প্রগ্রেস বার ডিজাইন */
    .progress-circle {
        --size: 50px;
        --thickness: 6px;
        --progress: 0;
        --color: #4caf50;
        width: var(--size);
        height: var(--size);
        border-radius: 50%;
        background: conic-gradient(var(--color) calc(var(--progress) * 1%), #eee 0);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        color: #333;
        font-weight: bold;
        position: relative;
    }

    .progress-circle::after {
        content: attr(data-progress) '%';
        position: absolute;
        font-size: 0.7rem;
    }
</style>

<?php include 'footer.php'; ?>
</body>

</html>