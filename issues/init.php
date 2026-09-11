<?php
/**
 * EIMBox Issue Tracker - Core Init Bridge
 * Location: issues/init.php
 */
require_once __DIR__ . '/../api/v1/bootstrap.php';

if (!function_exists('calc_dim_problem_score')) {
    function calc_dim_problem_score($val) {
        $st = strtolower(trim((string)$val));
        if ($st === 'not tested' || $st === '' || $st === null || $st === 'nottest') return 100.0;
        if ($st === 'error') return 100.0;
        if ($st === 'bug') return 50.0;
        if ($st === 'on progress' || $st === 'ongoing' || $st === 'progress') return 40.0;
        if ($st === 'ok' || $st === 'completed' || $st === 'not applicable' || $st === 'n/a' || $st === 'na') return 0.0;
        return 100.0;
    }
}
