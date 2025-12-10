<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';

// core files
require_once APP_ROOT . '/app/Core/Model.php';
require_once APP_ROOT . '/app/Core/Controller.php';
require_once APP_ROOT . '/app/Core/App.php';

// start app (router)
$app = new App();
