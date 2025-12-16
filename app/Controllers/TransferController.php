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
            header("Location: " . APP_URL . "/auth/login");
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

        // For admin, show list of non-admin users
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

        // Load TO accounts (all active accounts)
        $toAccounts = $accountModel->getAllAccountsList();

        // For customers, load FROM accounts on GET
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $role !== 'admin') {
            $fromAccounts = $accountModel->getByUser($currentUserId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Admin can choose user
            if ($role === 'admin') {
                $selectedUserId = (int)($_POST['user_id'] ?? 0);
            } else {
                $selectedUserId = $currentUserId;
            }

            // Load FROM accounts
            if ($selectedUserId > 0) {
                $fromAccounts = $accountModel->getByUser($selectedUserId);
            }

            $fromAccountId = (int)($_POST['from_account_id'] ?? 0);
            $toAccountId   = (int)($_POST['to_account_id'] ?? 0);
            $amountValue   = trim($_POST['amount'] ?? '');
            $action        = $_POST['action'] ?? '';

            if ($action === 'transfer') {

                // Validation
                if ($role === 'admin' && $selectedUserId <= 0)
                    $errorMessage = "Please select a user.";

                elseif ($fromAccountId <= 0)
                    $errorMessage = "Please select a From account.";

                elseif ($toAccountId <= 0)
                    $errorMessage = "Please select a To account.";

                elseif ($fromAccountId === $toAccountId)
                    $errorMessage = "You cannot transfer to the same account.";

                elseif ($amountValue === "")
                    $errorMessage = "Please enter an amount.";

                elseif (!is_numeric($amountValue))
                    $errorMessage = "Amount must be numeric.";

                else {

                    $amount = (float)$amountValue;

                    if ($amount <= 0)
                        $errorMessage = "Amount must be greater than zero.";

                    else {

                        $fromAccountInfo = $accountModel->findById($fromAccountId);
                        $toAccountInfo   = $accountModel->findById($toAccountId);

                        if (!$fromAccountInfo)
                            $errorMessage = "From account not found.";

                        elseif (!$toAccountInfo)
                            $errorMessage = "To account not found.";

                        else {

                            // customer safety
                            if ($role !== 'admin' && (int)$fromAccountInfo['user_id'] !== $currentUserId) {
                                $errorMessage = "You cannot transfer from another user's account.";
                            }

                            elseif (strcasecmp($fromAccountInfo['status'], 'Active') !== 0)
                                $errorMessage = "From account is not active.";

                            elseif (strcasecmp($toAccountInfo['status'], 'Active') !== 0)
                                $errorMessage = "To account is not active.";

                            else {

                                $currentBalance = (float)$fromAccountInfo['balance'];
                                $afterBalance   = $currentBalance - $amount;

                                // Block only if balance < 0
                                if ($afterBalance < 0) {
                                    $errorMessage = "Insufficient balance. Transfer cancelled.";
                                } else {

                                    $ok = $accountModel->transferBetweenAccounts(
                                        $fromAccountId,
                                        $toAccountId,
                                        $amount,
                                        $currentUserId
                                    );

                                    if ($ok) {

                                        $successMessage =
                                            "Transfer of Rs. " . number_format($amount, 2) .
                                            " completed successfully.";

                                        // refresh data
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
