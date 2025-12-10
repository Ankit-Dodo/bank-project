<?php
define('APP_URL', 'http://localhost/bank-project/public');
define('APP_ROOT', dirname(__DIR__));

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
