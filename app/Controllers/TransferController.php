<?php

class TransferController extends Controller
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

        $currentUserId = (int)$_SESSION['user_id'];

        // load current user
        $userModel = $this->model("User");
        $user      = $userModel->findById($currentUserId);

        if (!$user) {
            die("User not found.");
        }

        $role         = strtolower($user['role'] ?? '');
        $accountModel = $this->model("Account");

        // For admin, show list of non-admin users; for customer, empty
        $usersList = ($role === 'admin')
            ? $userModel->getAllNonAdminUsers()
            : [];

        // Defaults
        $selectedUserId  = ($role === 'admin') ? 0 : $currentUserId;
        $fromAccounts    = [];
        $toAccounts      = [];
        $fromAccountId   = 0;
        $toAccountId     = 0;
        $amountValue     = "";
        $successMessage  = "";
        $errorMessage    = "";
        $fromAccountInfo = null;
        $toAccountInfo   = null;

        // Load TO accounts list (all active accounts, any user)
        $toAccounts = $accountModel->getAllAccountsList();

        // For customers (GET), immediately load their FROM accounts
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $role !== 'admin') {
            $fromAccounts = $accountModel->getByUser($currentUserId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // For admin: take selected user from form, for customer: fixed
            if ($role === 'admin') {
                $selectedUserId = (int)($_POST['user_id'] ?? 0);
            } else {
                $selectedUserId = $currentUserId;
            }

            // Load FROM accounts for that user
            if ($selectedUserId > 0) {
                $fromAccounts = $accountModel->getByUser($selectedUserId);
            } else {
                $fromAccounts = [];
            }

            $fromAccountId = (int)($_POST['from_account_id'] ?? 0);
            $toAccountId   = (int)($_POST['to_account_id'] ?? 0);
            $amountValue   = trim($_POST['amount'] ?? '');
            $action        = $_POST['action'] ?? '';

            if ($action === 'transfer') {

                // Validation
                if ($role === 'admin' && $selectedUserId <= 0) {
                    $errorMessage = "Please select a user.";
                } elseif (empty($fromAccounts)) {
                    $errorMessage = "No accounts available for the selected user.";
                } elseif ($fromAccountId <= 0) {
                    $errorMessage = "Please select a 'From' account.";
                } elseif ($toAccountId <= 0) {
                    $errorMessage = "Please select a 'To' account.";
                } elseif ($toAccountId === $fromAccountId) {
                    $errorMessage = "You cannot transfer between the same account.";
                } elseif ($amountValue === "") {
                    $errorMessage = "Please enter an amount.";
                } elseif (!is_numeric($amountValue)) {
                    $errorMessage = "Amount must be numeric.";
                } else {
                    $amount = (float)$amountValue;

                    if ($amount <= 0) {
                        $errorMessage = "Amount must be greater than zero.";
                    } else {
                        // Load accounts
                        $fromAccountInfo = $accountModel->findById($fromAccountId);
                        $toAccountInfo   = $accountModel->findById($toAccountId);

                        if (!$fromAccountInfo) {
                            $errorMessage = "From account not found.";
                        } elseif (!$toAccountInfo) {
                            $errorMessage = "To account not found.";
                        } else {
                            // If customer, ensure FROM account belongs to them
                            if ($role !== 'admin' && (int)$fromAccountInfo['user_id'] !== $currentUserId) {
                                $errorMessage = "You cannot transfer from another user's account.";
                            } elseif (strcasecmp($fromAccountInfo['status'] ?? '', 'Active') !== 0) {
                                $errorMessage = "From account is not active.";
                            } elseif (strcasecmp($toAccountInfo['status'] ?? '', 'Active') !== 0) {
                                $errorMessage = "To account is not active.";
                            } else {
                                // Check minimum balance
                                $currentBalance = (float)($fromAccountInfo['balance'] ?? 0);
                                $minBalance     = (float)($fromAccountInfo['min_balance'] ?? 0);
                                $newBalance     = $currentBalance - $amount;

                                if ($newBalance < $minBalance) {
                                    $errorMessage = "Cannot transfer. This would go below the minimum balance.";
                                } else {
                                    // Perform transfer
                                    $ok = $accountModel->transferBetweenAccounts(
                                        $fromAccountId,
                                        $toAccountId,
                                        $amount,
                                        $currentUserId
                                    );

                                    if ($ok) {
                                        $successMessage  = "Transfer of Rs. " . number_format($amount, 2) . " completed successfully.";
                                        $fromAccountInfo = $accountModel->findById($fromAccountId);
                                        $toAccountInfo   = $accountModel->findById($toAccountId);
                                        $fromAccounts    = $accountModel->getByUser($selectedUserId);
                                        $toAccounts      = $accountModel->getAllAccountsList();
                                    } else {
                                        $errorMessage = "Transfer failed. Please try again.";
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->view("transfer/index", [
            "title"           => "Transfer",
            "user"            => $user,
            "role"            => $role,
            "usersList"       => $usersList,
            "selectedUserId"  => $selectedUserId,
            "fromAccounts"    => $fromAccounts,
            "toAccounts"      => $toAccounts,
            "fromAccountId"   => $fromAccountId,
            "toAccountId"     => $toAccountId,
            "amountValue"     => $amountValue,
            "successMessage"  => $successMessage,
            "errorMessage"    => $errorMessage,
            "fromAccountInfo" => $fromAccountInfo,
            "toAccountInfo"   => $toAccountInfo
        ]);
    }
}
