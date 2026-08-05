<?php
/******************************************************************************
 *
 * EIMBox Exam Analytics Engine
 *
 * File        : analytics-config.php
 * Version     : 1.0.0
 * Engine      : 1.0.0
 * Formula     : 1.0.0
 *
 * Purpose:
 * ----------
 * Central configuration file for the Analytics Engine.
 *
 ******************************************************************************/

if (!defined('ANALYTICS_ENGINE_LOADED')) {
    define('ANALYTICS_ENGINE_LOADED', true);
}

/*=========================================================================
    ENGINE INFORMATION
=========================================================================*/

define('ANALYTICS_ENGINE_NAME', 'EIMBox Exam Analytics Engine');
define('ANALYTICS_ENGINE_VERSION', '1.0.0');
define('ANALYTICS_FORMULA_VERSION', '1.0.0');


/*=========================================================================
    RESULT CONFIGURATION
=========================================================================*/

define('ANALYTICS_PASS_MARK', 33);


/*=========================================================================
    DECIMAL CONFIGURATION
=========================================================================*/

define('ANALYTICS_DECIMAL_PRECISION', 2);


/*=========================================================================
    BATCH PROCESSING
=========================================================================*/

define('ANALYTICS_INSERT_BATCH_SIZE', 1000);


/*=========================================================================
    PHP RUNTIME
=========================================================================*/

define('ANALYTICS_MEMORY_LIMIT', '512M');
define('ANALYTICS_MAX_EXECUTION_TIME', 0);


/*=========================================================================
    ENGINE OPTIONS
=========================================================================*/

define('ANALYTICS_ENABLE_TRANSACTION', true);
define('ANALYTICS_ENABLE_ERROR_LOG', true);
define('ANALYTICS_ENABLE_DEBUG', false);


/*=========================================================================
    CALCULATION OPTIONS
=========================================================================*/

define('ANALYTICS_ENABLE_VARIANCE', true);
define('ANALYTICS_ENABLE_STDDEV', true);
define('ANALYTICS_ENABLE_CV', true);
define('ANALYTICS_ENABLE_DIFFICULTY_INDEX', true);

define('ANALYTICS_ENABLE_COMBINED_MERIT', true);
define('ANALYTICS_ENABLE_GENDER_MERIT', true);


/*=========================================================================
    GRADE LIST
=========================================================================*/

$GLOBALS['ANALYTICS_GRADE_LIST'] = array(

    'A+',
    'A',
    'A-',
    'B',
    'C',
    'D',
    'F'

);


/*=========================================================================
    ANALYTICS STATUS
=========================================================================*/

define('ANALYTICS_STATUS_RUNNING', 1);
define('ANALYTICS_STATUS_COMPLETED', 2);
define('ANALYTICS_STATUS_FAILED', 3);
define('ANALYTICS_STATUS_WARNING', 4);


/*=========================================================================
    LOG TYPES
=========================================================================*/

define('ANALYTICS_LOG_INFO', 'INFO');
define('ANALYTICS_LOG_WARNING', 'WARNING');
define('ANALYTICS_LOG_ERROR', 'ERROR');


/*=========================================================================
    DEFAULT VALUES
=========================================================================*/

define('ANALYTICS_DEFAULT_LOWEST_MARK', 999999);
define('ANALYTICS_DEFAULT_HIGHEST_MARK', -1);


/*=========================================================================
    DATE FORMAT
=========================================================================*/

define('ANALYTICS_DATETIME_FORMAT', 'Y-m-d H:i:s');


/*=========================================================================
    APPLY PHP SETTINGS
=========================================================================*/

ini_set('memory_limit', ANALYTICS_MEMORY_LIMIT);
set_time_limit(ANALYTICS_MAX_EXECUTION_TIME);
date_default_timezone_set('Asia/Dhaka');