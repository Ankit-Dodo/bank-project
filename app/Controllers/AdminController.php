<?php

class AdminController extends Controller
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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

        // only admin
        if (strtolower($user['role']) !== 'admin') {
            die("Only admin can access this page.");
        }

        // which tab: pending / all
        $tab = isset($_GET['tab']) ? strtolower(trim($_GET['tab'])) : 'pending';
        if ($tab !== 'all') {
            $tab = 'pending';
        }

        $adminModel = $this->model("AdminPanel");

        // stats
        $stats = $adminModel->getStatistics();

        // search + pagination for all accounts
        $search  = isset($_GET['search']) ? trim($_GET['search']) : "";
        $page    = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0
            ? (int)$_GET['page']
            : 1;
        $perPage = 10;

        $totalPages = $adminModel->countPages($search, $perPage);
        if ($totalPages <= 0) {
            $totalPages = 1;
        }
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        // data for tables
        $pendingAccounts = $adminModel->getPendingAccounts();
        $accounts        = $adminModel->getAccounts($search, $page, $perPage);

        // messages from actions (approve / decline / delete)
        $msg  = isset($_GET['msg']) ? $_GET['msg'] : "";
        $err  = isset($_GET['err']) ? (int)$_GET['err'] : 0;

        $this->view("dashboard/admin", [
            "title"           => "Admin Panel",
            "user"            => $user,
            "stats"           => $stats,
            "pendingAccounts" => $pendingAccounts,
            "accounts"        => $accounts,
            "totalPages"      => $totalPages,
            "currentPage"     => $page,
            "search"          => $search,
            "msg"             => $msg,
            "err"             => $err,
            "tab"             => $tab
        ]);
    }

    public function approve()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }

        $userId = (int)$_SESSION['user_id'];

        $userModel = $this->model("User");
        $user      = $userModel->findById($userId);

        if (!$user || strtolower($user['role']) !== 'admin') {
            die("Only admin can perform this action.");
        }

        $accountId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($accountId <= 0) {
            header("Location: index.php?url=admin/index&msg=Invalid+account+id&err=1");
            exit;
        }

        $adminModel = $this->model("AdminPanel");
        $ok = $adminModel->approveAccount($accountId);

        if ($ok) {
            header("Location: index.php?url=admin/index&msg=Account+approved+successfully&err=0");
        } else {
            header("Location: index.php?url=admin/index&msg=Failed+to+approve+account&err=1");
        }
        exit;
    }

    public function decline()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }

        $userId = (int)$_SESSION['user_id'];

        $userModel = $this->model("User");
        $user      = $userModel->findById($userId);

        if (!$user || strtolower($user['role']) !== 'admin') {
            die("Only admin can perform this action.");
        }

        $accountId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($accountId <= 0) {
            header("Location: index.php?url=admin/index&msg=Invalid+account+id&err=1");
            exit;
        }

        $adminModel = $this->model("AdminPanel");
        $ok = $adminModel->declineAccount($accountId);

        if ($ok) {
            header("Location: index.php?url=admin/index&msg=Account+request+declined&err=0");
        } else {
            header("Location: index.php?url=admin/index&msg=Failed+to+decline+account&err=1");
        }
        exit;
    }

    public function deleteAccount()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }

        $userId = (int)$_SESSION['user_id'];

        $userModel = $this->model("User");
        $user      = $userModel->findById($userId);

        if (!$user || strtolower($user['role']) !== 'admin') {
            die("Only admin can perform this action.");
        }

        $accountId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($accountId <= 0) {
            header("Location: index.php?url=admin/index&msg=Invalid+account+id&err=1");
            exit;
        }

        $adminModel = $this->model("AdminPanel");

        // 1) Get account info (especially balance)
        $account = $adminModel->getAccountById($accountId);

        if (!$account) {
            header("Location: index.php?url=admin/index&msg=Account+not+found&err=1");
            exit;
        }

        // 2) Check balance
        $balance = (float)$account['balance'];

        if ($balance > 0) {
            //  Do NOT delete, ask admin to withdraw first
            $msg = "Cannot delete account with balance Rs+$balance.+Please+withdraw+all+money+before+deletion.";
            header("Location: index.php?url=admin/index&msg=" . $msg . "&err=1");
            exit;
        }

        // 3) Safe to delete (balance == 0)
        $ok = $adminModel->deleteAccountAndTransactions($accountId);

        if ($ok) {
            header("Location: index.php?url=admin/index&msg=Account+deleted+successfully&err=0");
        } else {
            header("Location: index.php?url=admin/index&msg=Failed+to+delete+account&err=1");
        }
        exit;
    }
}
