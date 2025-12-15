<?php

class WithdrawController extends Controller
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

        /* load user */
        $userModel = $this->model("User");
        $user      = $userModel->findById($userId);

        if (!$user) {
            die("User not found.");
        }

        $role = strtolower($user['role']);
        $accountModel = $this->model("Account");

        /* For ADMIN → get list of customers */
        $usersList = ($role === "admin")
            ? $userModel->getAllNonAdminUsers()
            : [];

        /* Form values */
        $selectedUserId    = ($role === "admin") ? 0 : $userId;
        $selectedAccountId = 0;
        $amountValue       = "";
        $userAccounts      = [];
        $successMessage    = "";
        $errorMessage      = "";
        $accountInfo       = null;

        /* POST handling */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /* If admin: read user selection */
            if ($role === "admin") {
                $selectedUserId = (int)($_POST['user_id'] ?? 0);
            }

            /* Always load accounts for selected user */
            if ($selectedUserId > 0) {
                $userAccounts = $accountModel->getByUser($selectedUserId);
            }

            $selectedAccountId = (int)($_POST['account_id'] ?? 0);
            $amountValue       = trim($_POST['amount'] ?? '');
            $action            = $_POST['action'] ?? '';

            if ($action === 'withdraw') {

                if ($selectedUserId <= 0) {
                    $errorMessage = "Please select a user.";
                }
                elseif ($selectedAccountId <= 0) {
                    $errorMessage = "Please select an account.";
                }
                elseif ($amountValue === "") {
                    $errorMessage = "Please enter an amount.";
                }
                elseif (!is_numeric($amountValue)) {
                    $errorMessage = "Amount must be numeric.";
                }
                else {

                    $amount = (float)$amountValue;

                    if ($amount <= 0) {
                        $errorMessage = "Amount must be greater than zero.";
                    }
                    else {

                        $accountInfo = $accountModel->findById($selectedAccountId);

                        if (!$accountInfo) {
                            $errorMessage = "Account not found.";
                        }
                        else {

                            /* Customer safety */
                            if ($role === "customer" && (int)$accountInfo['user_id'] !== $userId) {
                                $errorMessage = "You cannot withdraw from another user's account.";
                            }

                            /* Block ONLY if balance goes below 0 */
                            elseif (($accountInfo['balance'] - $amount) < 0) {
                                $errorMessage = "Insufficient balance. Transaction cancelled.";
                            }

                            else {

                                $ok = $accountModel->withdrawFromAccount(
                                    $selectedAccountId,
                                    $amount,
                                    $userId
                                );

                                if ($ok) {

                                    $afterBalance = $accountInfo['balance'] - $amount;

                                    /* APPLY 1% FINE IF BELOW MIN BALANCE */
                                    if ($afterBalance < $accountInfo['min_balance']) {

                                        $fine = round($amount * 0.01, 2);
                                        $accountModel->applyFine($selectedAccountId, $fine);

                                        $successMessage =
                                            "Rs. " . number_format($amount, 2) .
                                            " withdrawn. Low balance fine of Rs. " .
                                            number_format($fine, 2) . " applied.";
                                    }
                                    else {
                                        $successMessage =
                                            "Successfully withdrawn Rs. " . number_format($amount, 2);
                                    }

                                    $accountInfo  = $accountModel->findById($selectedAccountId);
                                    $userAccounts = $accountModel->getByUser($selectedUserId);
                                }
                                else {
                                    $errorMessage = "Withdrawal failed, try again.";
                                }
                            }
                        }
                    }
                }
            }
        }

        /* VIEW */
        $this->view("withdraw/index", [
            "title"             => "Withdraw",
            "user"              => $user,
            "role"              => $role,
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
