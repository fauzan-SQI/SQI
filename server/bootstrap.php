<?php
// server/bootstrap.php - Bootstrap wrapper untuk konfigurasi utama

$GLOBALS['SQI_SKIP_AUTOLOAD'] = true;
require_once __DIR__ . '/../safe_config.php';
unset($GLOBALS['SQI_SKIP_AUTOLOAD']);

if (!defined('SQI_SERVER_BOOTSTRAPPED')) {
    define('SQI_SERVER_BOOTSTRAPPED', true);
}
