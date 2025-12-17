<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Account;
use App\Models\User;
use Exception;

class DashboardController extends Controller
{
    public function index()
    {

        try {



            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }



            // must be logged in
            if (!isset($_SESSION['user_id'])) {
                header("Location: " . APP_URL . "/login");
                exit;
            }

            $userId = (int)$_SESSION['user_id'];

            // load user
            $userModel = new User();
            $user      = $userModel->findById($userId);

            if (!$user) {
                die("User not found.");
            }

            // save role in session for nav strip etc.
            $_SESSION['user_role'] = $user['role'] ?? '';


            // if admin → send to admin controller (use existing admin panel)
            if (strtolower($user['role']) === 'admin') {
                header("Location: /admin");
                exit;
            }

            // CUSTOMER DASHBOARD LOGIC
            $accountModel = new Account();   // simple customer accounts model
            $accounts     = $accountModel->getByUser($userId);

            $this->view("dashboard/customer", array(
                "title"    => "Customer Dashboard",
                "user"     => $user,
                "accounts" => $accounts
            ));
        } catch (\Exception $e) {

            echo "<pre>";
            print_r($e->getMessage());
            die;
        }
    }
}
