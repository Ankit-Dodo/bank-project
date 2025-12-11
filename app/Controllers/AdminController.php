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

    public function editUser()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }
    
        $current_user_id = (int)$_SESSION['user_id'];
    
        // DB connection used by this page
        require_once __DIR__ . "/../../config/db.php"; // ../.. from app/Controllers to /config/db.php
    
        /* ensure user is admin */
        $roleSql = "SELECT role FROM users WHERE id = $current_user_id LIMIT 1";
        $roleRes = mysqli_query($conn, $roleSql);
    
        if (!$roleRes || mysqli_num_rows($roleRes) === 0) {
            die("Current user not found.");
        }
    
        $roleRow = mysqli_fetch_assoc($roleRes);
        if (strtolower($roleRow['role'] ?? '') !== 'admin') {
            die("Only admin can access this page.");
        }
    
        /* dropdown for list of users */
        $users = [];
        $usersSql = "
            SELECT u.id, u.username, COALESCE(p.full_name, '') AS full_name
            FROM users u
            LEFT JOIN profile p ON p.user_id = u.id
            ORDER BY u.username ASC
        ";
        $usersRes = mysqli_query($conn, $usersSql);
        if ($usersRes && mysqli_num_rows($usersRes) > 0) {
            while ($row = mysqli_fetch_assoc($usersRes)) {
                $users[] = $row;
            }
        }
    
        /* form actions */
        $selectedUserId = null;
        $editUser   = null;
        $successMsg = '';
        $errorMsg   = '';
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
            // LOAD USER
            if (isset($_POST['load_user'])) {
                $selectedUserId = (int)($_POST['user_id'] ?? 0);
            }
        
            // SAVE USER CHANGES
            if (isset($_POST['save_user'])) {
                $selectedUserId = (int)($_POST['edit_user_id'] ?? 0);
            
                $username    = trim($_POST['username'] ?? '');
                $full_name   = trim($_POST['full_name'] ?? '');
                $email       = trim($_POST['email'] ?? '');
                $phone       = trim($_POST['phone'] ?? '');
                $address     = trim($_POST['address'] ?? '');
                $newPass     = trim($_POST['new_password'] ?? '');
                $confirmPass = trim($_POST['confirm_password'] ?? '');
                $user_status = trim($_POST['user_status'] ?? 'Active');

                $user_status = ucfirst(strtolower($user_status));
                // allow Active and Hold only
                if (!in_array($user_status, ['Active', 'Hold'], true)) {
                    $user_status = 'Active';
                }

            
                // BACKEND VALIDATIONS
                if ($selectedUserId <= 0) {
                    $errorMsg = "Invalid user selected.";
                } elseif ($username === '') {
                    $errorMsg = "Username cannot be empty.";
                } elseif ($full_name === '' || $email === '') {
                    $errorMsg = "Full name and email are required.";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errorMsg = "Invalid email format.";
                } elseif ($phone !== '' && !preg_match('/^[0-9]{10}$/', $phone)) {
                    $errorMsg = "Phone number must be exactly 10 digits.";
                } elseif ($newPass !== '' && $newPass !== $confirmPass) {
                    $errorMsg = "New password and confirm password do not match.";
                } else {
                
                    // UPDATE users
                    $usernameEsc = mysqli_real_escape_string($conn, $username);
                    $emailEsc    = mysqli_real_escape_string($conn, $email);
                    $statusEsc   = mysqli_real_escape_string($conn, $user_status);
                
                    if ($newPass !== '') {
                        $hash    = password_hash($newPass, PASSWORD_DEFAULT);
                        $hashEsc = mysqli_real_escape_string($conn, $hash);
                        $userUpdateSql = "
                            UPDATE users
                            SET username = '$usernameEsc',
                                email = '$emailEsc',
                                password_hash = '$hashEsc',
                                status = '$statusEsc'
                            WHERE id = $selectedUserId
                            LIMIT 1
                        ";
                    } else {
                        $userUpdateSql = "
                            UPDATE users
                            SET username = '$usernameEsc',
                                email = '$emailEsc',
                                status = '$statusEsc'
                            WHERE id = $selectedUserId
                            LIMIT 1
                        ";
                    }
                
                    if (!mysqli_query($conn, $userUpdateSql)) {
                        $errorMsg = "Failed to update user: " . mysqli_error($conn);
                    } else {
                    
                        // UPDATE / INSERT profile
                        $fullEsc  = mysqli_real_escape_string($conn, $full_name);
                        $phoneEsc = mysqli_real_escape_string($conn, $phone);
                        $addrEsc  = mysqli_real_escape_string($conn, $address);
                    
                        $checkProfileSql = "SELECT id FROM profile WHERE user_id = $selectedUserId LIMIT 1";
                        $profRes = mysqli_query($conn, $checkProfileSql);
                    
                        if ($profRes && mysqli_num_rows($profRes) > 0) {
                            $updateProfileSql = "
                                UPDATE profile
                                SET full_name = '$fullEsc',
                                    phone     = '$phoneEsc',
                                    address   = '$addrEsc'
                                WHERE user_id = $selectedUserId
                                LIMIT 1
                            ";
                            mysqli_query($conn, $updateProfileSql);
                        } else {
                            $insertProfileSql = "
                                INSERT INTO profile (user_id, full_name, phone, address)
                                VALUES ($selectedUserId, '$fullEsc', '$phoneEsc', '$addrEsc')
                            ";
                            mysqli_query($conn, $insertProfileSql);
                        }
                    
                        $successMsg = "User details updated successfully.";
                    }
                }
            }
        }
    
        // load selected user for editing
        if ($selectedUserId) {
            $editSql = "
                SELECT 
                    u.id,
                    u.username,
                    u.email,
                    COALESCE(u.status, 'Active') AS status,
                    COALESCE(p.full_name, '') AS full_name,
                    COALESCE(p.phone, '')     AS phone,
                    COALESCE(p.address, '')   AS address
                FROM users u
                LEFT JOIN profile p ON p.user_id = u.id
                WHERE u.id = $selectedUserId
                LIMIT 1
            ";
        
            $editRes = mysqli_query($conn, $editSql);
            if ($editRes && mysqli_num_rows($editRes) === 1) {
                $editUser = mysqli_fetch_assoc($editRes);
            } else {
                $errorMsg = "Selected user not found.";
            }
        }
    
        $this->view("dashboard/edit_user", [
            "title"          => "Edit User Details",
            "users"          => $users,
            "selectedUserId" => $selectedUserId,
            "editUser"       => $editUser,
            "successMsg"     => $successMsg,
            "errorMsg"       => $errorMsg
        ]);
    }

}
