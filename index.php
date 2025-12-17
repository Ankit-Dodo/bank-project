<?php

use App\Controllers\AccountController;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CustomerController;
use App\Controllers\DashboardController;
use App\Controllers\DepositController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\TransactionController;
use App\Controllers\TransferController;
use App\Controllers\WithdrawController;
use App\Core\Router;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/bootstrap.php';

// core files
require_once APP_ROOT . '/app/Core/Model.php';
require_once APP_ROOT . '/app/Core/Controller.php';
require_once APP_ROOT . '/app/Core/App.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
// start app (router)
// $app = new App();
$router = new Router();
$routes = [
    'GET' => [
        '/' => [HomeController::class, 'index'],
        // auth
        '/home' => [HomeController::class, 'index'],
        '/login' => [AuthController::class, 'login'],
        '/logout' => [AuthController::class, 'logout'],
        '/checkemail'=>[AuthController::class, 'checkEmail'],
        '/register' => [AuthController::class, 'register'],
        '/unauthorized' => [ErrorController::class, 'notFound'],

        // admin panel 
        '/admin' => [AdminController::class, 'index'],
        '/admin/approve' => [AdminController::class, 'approve'],
        '/admin/decline' => [AdminController::class, 'decline'],
        '/admin/deleteaccount' => [AdminController::class, 'deleteaccount'],
        '/admin/edituser' => [AdminController::class, 'edituser'],
        
        //creating bank account
        '/create' => [AccountController::class, 'create'],
        
        // customer profile account
        '/customer' => [CustomerController::class, 'details'],
        
        //dashboard
        '/index' => [DashboardController::class, 'index'],
        

        // deposit
        '/deposit' => [DepositController::class, 'index'],
        // withdraw
        '/withdraw' => [WithdrawController::class, 'index'],
        // transfer
        '/transfer' => [TransferController::class, 'index'],
        // transaction
        '/transaction' => [TransactionController::class, 'index'],

    ],

    'POST' => [
        // auth
        '/login' => [AuthController::class, 'login'],
        '/logout' => [AuthController::class, 'login'],
        '/signup' => [AuthController::class, 'register'],
    //     // admin
        '/admin/approve' => [AdminController::class, 'approve'],
        '/admin/decline' => [AdminController::class, 'decline'],

        //creating bank account
        '/create' => [AccountController::class, 'create'],
        
        // customer profile account
        '/customer' => [CustomerController::class, 'details'],

        // deposit
        '/deposit/index' => [DepositController::class, 'index'],
        // withdraw
        '/withdraw/index' => [WithdrawController::class, 'index'],
        // transfer
        '/transfer/index' => [TransferController::class, 'index'],
        // transaction
        '/transaction/index' => [TransactionController::class, 'index'],
    
    ],
];
    foreach($routes as $method => $paths){
    foreach($paths as $path => $handler) {
        $router->{strtolower($method)}($path, $handler);
    }
}

$router->dispatch();
