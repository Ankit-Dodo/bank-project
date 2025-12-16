<?php
define('APP_URL', '/bank-project/public');
define('APP_ROOT', dirname(__DIR__));

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
