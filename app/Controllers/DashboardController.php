<?php

class DashboardController extends Controller
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // must be logged in
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }

        $userId = (int)$_SESSION['user_id'];

        // load user
        $userModel = $this->model("User");
        $user      = $userModel->findById($userId);

        if (!$user) {
            die("User not found.");
        }

        // save role in session for nav strip etc.
        $_SESSION['user_role'] = $user['role'] ?? '';

        // if admin → send to admin controller (use existing admin panel)
        if (strtolower($user['role'] ?? '') === 'admin') {
            header("Location: index.php?url=admin/index");
            exit;
        }

        // CUSTOMER DASHBOARD LOGIC
        $accountModel = $this->model("Account");   // simple customer accounts model
        $accounts     = $accountModel->getByUser($userId);

        $this->view("dashboard/customer", array(
            "title"    => "Customer Dashboard",
            "user"     => $user,
            "accounts" => $accounts
        ));
    }
}
