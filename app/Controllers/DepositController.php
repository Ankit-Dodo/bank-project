<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Account;
use App\Models\User;

class DepositController extends Controller
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: " . APP_URL . "/auth/login");
            exit;
        }

        $adminId = (int)$_SESSION['user_id'];

        $userModel = new User();
        $admin     = $userModel->findById($adminId);

        if (!$admin) die("User not found.");

        if (strtolower($admin['role']) !== 'admin')
            die("Only admin can access the deposit page.");

        $accountModel = new Account();
        $usersList    = $userModel->getAllNonAdminUsers();

        $successMessage    = "";
        $errorMessage      = "";
        $accountInfo       = null;
        $userAccounts      = [];
        $selectedUserId    = 0;
        $selectedAccountId = 0;
        $amountValue       = "";

        // Handle form POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $selectedUserId    = (int)($_POST['user_id'] ?? 0);
            $selectedAccountId = (int)($_POST['account_id'] ?? 0);
            $amountValue       = trim($_POST['amount'] ?? '');
            $action            = $_POST['action'] ?? '';

            // ALWAYS load accounts for selected user (auto load)
            if ($selectedUserId > 0) {
                $userAccounts = $accountModel->getByUser($selectedUserId);
            }

            // Handle deposit action
            if ($action === 'deposit') {

                if ($selectedUserId <= 0) {
                    $errorMessage = "Please select a user.";
                } elseif (empty($userAccounts)) {
                    $errorMessage = "No accounts found for this user.";
                } elseif ($selectedAccountId <= 0) {
                    $errorMessage = "Please select an account.";
                } elseif ($amountValue === '') {
                    $errorMessage = "Amount is required.";
                } elseif (!is_numeric($amountValue)) {
                    $errorMessage = "Amount must be numeric.";
                } else {
                    $amount = (float)$amountValue;

                    if ($amount <= 0) {
                        $errorMessage = "Amount must be greater than zero.";
                    } else {

                        $accountInfo = $accountModel->findById($selectedAccountId);
                        if (!$accountInfo) {
                            $errorMessage = "Account not found.";
                        } else {
                            // Verify account belongs to the selected user
                            $valid = false;
                            foreach ($userAccounts as $acc) {
                                if ((int)$acc['id'] === $selectedAccountId) {
                                    $valid = true;
                                    break;
                                }
                            }

                            if (!$valid) {
                                $errorMessage = "Selected account does not belong to this user.";
                            } else {
                                $ok = $accountModel->depositToAccount(
                                    $selectedAccountId,
                                    $amount,
                                    $adminId
                                );

                                if ($ok) {
                                    $accountInfo    = $accountModel->findById($selectedAccountId);
                                    $successMessage = "Deposit of Rs." . number_format($amount, 2) . " successful!";
                                    $userAccounts   = $accountModel->getByUser($selectedUserId);
                                } else {
                                    $errorMessage = "Deposit failed, try again.";
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->view("deposit/index", [
            "title"             => "Deposit",
            "user"              => $admin,
            "usersList"         => $usersList,
            "selectedUserId"    => $selectedUserId,
            "selectedAccountId" => $selectedAccountId,
            "userAccounts"      => $userAccounts,
            "amountValue"       => $amountValue,
            "successMessage"    => $successMessage,
            "errorMessage"      => $errorMessage,
            "accountInfo"       => $accountInfo
        ]);
    }
}
