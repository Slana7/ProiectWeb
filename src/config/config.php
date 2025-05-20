<?php
require_once __DIR__ . '/EnvLoader.php';

EnvLoader::load(__DIR__ . '/../../.env');

define('BASE_URL', getenv('BASE_URL') ?: '/REM/');
define('APP_NAME', getenv('APP_NAME') ?: 'REM');
define('DEBUG_MODE', getenv('DEBUG_MODE') === 'true');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'REM');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASS') ?: 'STUDENT');
