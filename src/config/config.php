<?php
require_once __DIR__ . '/EnvLoader.php';

EnvLoader::load(__DIR__ . '/../../.env');

date_default_timezone_set('Europe/Bucharest');

$base_url = getenv('BASE_URL') ?: '/';
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/REM/') !== false) {
    $base_url = '/REM/';
}
define('BASE_URL', $base_url);

define('APP_NAME', getenv('APP_NAME') ?: 'REM');
define('DEBUG_MODE', getenv('DEBUG_MODE') === 'true');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'REM');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASS') ?: 'STUDENT');
