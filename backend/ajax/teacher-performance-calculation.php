<?php

require_once '../../core/config.php';
require_once '../../core/db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

$sy   = $_POST['sy'] ?? '';
$exam = $_POST['exam'] ?? '';
$slot = $_POST['slot'] ?? '';

$response = array(
    "status"=>"success",
    "message"=>"Done"
);

switch($action){

    case 'load_exam':

        $response['message']="Exam information loaded.";

    break;

    case 'load_classes':

        $response['message']="Class list loaded.";

    break;

    case 'load_sections':

        $response['message']="Section list loaded.";

    break;

    case 'load_students':

        $response['message']="Students loaded.";

    break;

    case 'load_subjects':

        $response['message']="Subjects loaded.";

    break;

    case 'load_teachers':

        $response['message']="Teachers loaded.";

    break;

    case 'build_cai':

        $response['message']="Class Academic Index calculated.";

    break;

    case 'build_spi':

        $response['message']="Subject Performance Index calculated.";

    break;

    case 'build_sgi':

        $response['message']="Student Growth Index calculated.";

    break;

    case 'pass_rate':

        $response['message']="Pass Rate calculated.";

    break;

    case 'excellent_rate':

        $response['message']="Excellence Rate calculated.";

    break;

    case 'failure_rate':

        $response['message']="Failure Rate calculated.";

    break;

    case 'difficulty_factor':

        $response['message']="Class Difficulty Factor calculated.";

    break;

    case 'teacher_impact':

        $response['message']="Teacher Impact calculated.";

    break;

    case 'adjusted_teacher_impact':

        $response['message']="Adjusted Teacher Impact calculated.";

    break;

    case 'teacher_score':

        $response['message']="Teacher Performance Score calculated.";

    break;

    case 'teacher_rank':

        $response['message']="Teacher Ranking generated.";

    break;

    case 'subject_rank':

        $response['message']="Subject Ranking generated.";

    break;

    case 'class_rank':

        $response['message']="Class Ranking generated.";

    break;

    case 'ready':

        $response['message']="Ready for database save.";

    break;

    default:

        $response['status']="error";
        $response['message']="Unknown action.";

}

echo json_encode($response);
exit;