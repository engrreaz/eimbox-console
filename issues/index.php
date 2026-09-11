<?php
/**
 * EIMBox Issue Tracker Module Entrypoint
 */
require_once __DIR__ . '/init.php';

api_response('success', 'EIMBox Issue Tracker Module API is active', [
    'endpoints' => [
        'get_all_issues' => 'issues/get-all-issues.php',
        'get_page_issues' => 'issues/get-page-issues.php',
        'manage_issue' => 'issues/manage-issue.php',
        'manage_feature' => 'issues/manage-feature.php',
        'save_dimension' => 'issues/save-dimension.php'
    ]
], 200);
