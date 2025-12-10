<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

// restore checkbox list from cookie
$cookieList = [];
if (!empty($_COOKIE['examitems'])) {
    $cookieList = explode(",", $_COOKIE['examitems']);
}

$slot = $_POST['slot'];
$session = $_POST['session'];
$exam = $_POST['exam'];
$class = $_POST['class'];
$section = $_POST['section'];
$subject = $_POST['subject'];

$sql = "SELECT examtitle FROM examlist 
        WHERE sccode='$sccode' 
        AND sessionyear='$session' 
        AND (slot='$slot' OR slot='')";

$qq = mysqli_query($conn, $sql);

if (mysqli_num_rows($qq) > 0) {
?>
<div class="card-body">
    <div class="row p-3">
        <?php
        while ($rec = mysqli_fetch_assoc($qq)) {

            $title = $rec['examtitle'];   // real title
            $title_clean = str_replace(" ", "_", $title); // safe key

            // checkbox restore
            $checked = in_array($title, $cookieList) ? "checked" : "";

            // rate cookie
            $rateKey = "rate_" . $title_clean;
            $rateValue = isset($_COOKIE[$rateKey]) ? $_COOKIE[$rateKey] : "";
        ?>
        <div class="form-check mb-1 col-md-4 col-sm-6">
            <div class="row g-1">
                <div class="col-auto">
                    <input class="form-check-input examItem"
                           type="checkbox"
                           name="examitems[]"
                           value="<?php echo htmlspecialchars($title); ?>"
                           id="ex_<?php echo md5($title); ?>"
                           <?php echo $checked; ?>>
                </div>

                <div class="col-auto">
                    <label class="form-check-label" for="ex_<?php echo md5($title); ?>">
                        <?php echo htmlspecialchars($title); ?>
                    </label>
                </div>

                <div class="col-4">
                    <input id="rate_<?php echo md5($title); ?>"
                           name="rate[<?php echo htmlspecialchars($title); ?>]"
                           type="text"
                           value="<?php echo $rateValue; ?>"
                           class="form-control form-control-sm text-center" />
                </div>
                <div class="col-auto pt-1">%</div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
<?php
}
?>