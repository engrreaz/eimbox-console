<?php
/**
 * EIMBox REST API — GPA Grading Scale & Evaluation Rules
 * Route: GET /api/v1/settings/gpa-rules.php
 * Query Params: ?sccode={sccode}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// Standard National Secondary & Higher Secondary Education Board (NCTB / Bangladesh) GPA Scale
$gradingScale = [
    [
        'grade_letter' => 'A+',
        'grade_point' => 5.0,
        'mark_from' => 80,
        'mark_to' => 100,
        'remarks' => 'Outstanding'
    ],
    [
        'grade_letter' => 'A',
        'grade_point' => 4.0,
        'mark_from' => 70,
        'mark_to' => 79,
        'remarks' => 'Excellent'
    ],
    [
        'grade_letter' => 'A-',
        'grade_point' => 3.5,
        'mark_from' => 60,
        'mark_to' => 69,
        'remarks' => 'Very Good'
    ],
    [
        'grade_letter' => 'B',
        'grade_point' => 3.0,
        'mark_from' => 50,
        'mark_to' => 59,
        'remarks' => 'Good'
    ],
    [
        'grade_letter' => 'C',
        'grade_point' => 2.0,
        'mark_from' => 40,
        'mark_to' => 49,
        'remarks' => 'Satisfactory'
    ],
    [
        'grade_letter' => 'D',
        'grade_point' => 1.0,
        'mark_from' => 33,
        'mark_to' => 39,
        'remarks' => 'Passing'
    ],
    [
        'grade_letter' => 'F',
        'grade_point' => 0.0,
        'mark_from' => 0,
        'mark_to' => 32,
        'remarks' => 'Failed'
    ]
];

$passMarkPercentage = 33;
$fourthSubjectGracePoints = 2.0;

api_response('success', 'GPA grading rules loaded.', [
    'sccode' => $sccode,
    'scale_name' => 'Bangladesh National Standard (NCTB 5.0 Scale)',
    'pass_mark_percentage' => $passMarkPercentage,
    'fourth_subject_grace_point' => $fourthSubjectGracePoints,
    'grading_scale' => $gradingScale
]);
