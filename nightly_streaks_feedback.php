<?php
// nightly_streaks_feedback.php
if (php_sapi_name() !== 'cli') exit("Run from CLI only\n");

require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/global_values.php';
require_once 'achievements_engine.php';

$LOG_FILE = __DIR__.'/logs/nightly_streaks_feedback.log';
$BATCH_SIZE = 500;
$SLEEP_USEC = 20000;

function logmsg($msg){
    global $LOG_FILE;
    $line = "[".date('Y-m-d H:i:s')."] ".$msg.PHP_EOL;
    echo $line;
    @file_put_contents($LOG_FILE,$line,FILE_APPEND|LOCK_EX);
}

logmsg("Starting nightly_streaks_feedback.");

// Step 1: iterate all users by email
$offset = 0;
while(true){
    $sql = "SELECT DISTINCT email FROM user_actions WHERE email IS NOT NULL AND email<>'' ORDER BY email LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii',$BATCH_SIZE,$offset);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    if(count($rows)===0) break;

    foreach($rows as $r){
        $email = mb_strtolower(trim($r['email']));
        if($email==='') continue;
        logmsg("Processing {$email}");

        // ====== 1) Distinct active days & streaks ======
        $sqlDates = "SELECT DISTINCT DATE(timestamp) AS d FROM user_actions WHERE email=? AND timestamp>=DATE_SUB(CURDATE(),INTERVAL 60 DAY) ORDER BY d DESC";
        $s=$mysqli->prepare($sqlDates);
        $s->bind_param('s',$email);
        $s->execute();
        $datesArr = $s->get_result()->fetch_all(MYSQLI_ASSOC);
        $s->close();
        $dates=array_map(fn($x)=>$x['d'],$datesArr);

        // consecutive streak
        $streak=0;
        $expected=new DateTime(date('Y-m-d'));
        if(count($dates)===0 || $dates[0]!=$expected->format('Y-m-d')){
            $expected->modify('-1 day');
        }
        foreach($dates as $d){
            if($d==$expected->format('Y-m-d')){
                $streak++;
                $expected->modify('-1 day');
            }else break;
        }

        foreach($STREAK_ACHIEVEMENTS as $code=>$days){
            if(($code==='daily_active_7' && count($dates)>=7) || $streak >= $days){
                $resAward = awardAchievementByEmail($mysqli,$email,$code);
                if(isset($resAward['awarded']) && $resAward['awarded']) logmsg("  Awarded {$code}");
            }
        }

        // ====== 2) Feedback-based achievements ======
        $sqlFb = "SELECT COUNT(*) as cnt FROM feedbacks WHERE email=? OR username=?";
        $s=$mysqli->prepare($sqlFb);
        $s->bind_param('ss',$email,$email);
        $s->execute();
        $cntFb = (int)$s->get_result()->fetch_assoc()['cnt'];
        $s->close();
        foreach($FEEDBACK_ACHIEVEMENTS as $code=>$req){
            if($code==='suggestions_accepted'){
                $sqlSug = "SELECT COUNT(*) as cnt FROM feedbacks WHERE email=? AND target_type='suggestion' AND rating>=4";
                $s=$mysqli->prepare($sqlSug);
                $s->bind_param('s',$email);
                $s->execute();
                $cntSug = (int)$s->get_result()->fetch_assoc()['cnt'];
                $s->close();
                if($cntSug >= $req){
                    $resAward = awardAchievementByEmail($mysqli,$email,$code);
                    if(isset($resAward['awarded']) && $resAward['awarded']) logmsg("  Awarded {$code}");
                }
            } else {
                if($cntFb >= $req){
                    $resAward = awardAchievementByEmail($mysqli,$email,$code);
                    if(isset($resAward['awarded']) && $resAward['awarded']) logmsg("  Awarded {$code}");
                }
            }
        }

        // ====== 3) Recalculate points + evaluate titles ======
        $points = recalcUserPointsByEmail($mysqli,$email);
        $awardedTitles = evaluateTitlesForEmail($mysqli,$email);
        if(!empty($awardedTitles)) logmsg("  Titles awarded: ".implode(',',$awardedTitles));

        usleep($SLEEP_USEC);
    }

    $offset += $BATCH_SIZE;
    usleep(200000);
}

logmsg("Finished nightly_streaks_feedback.");
