<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Base Path
|--------------------------------------------------------------------------
|
| Ganti nilai __APP_BASE_PATH__ dengan absolute path project Laravel kamu.
| Contoh: /home/username/sistem_ujian
|
*/
$appBasePath = '__APP_BASE_PATH__';

if (file_exists($maintenance = $appBasePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $appBasePath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $appBasePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
