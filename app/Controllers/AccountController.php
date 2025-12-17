<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Account;
use App\Models\User;

class AccountController extends Controller
{
    public function create()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: " . APP_URL ."auth/login");
            exit;
        }

        $userId = (int)$_SESSION['user_id'];

        // load current user
        $userModel = new User();
        $user      = $userModel->findById($userId);

        if (!$user) {
            die("User not found.");
        }

        $accountModel = new Account();


        $successMessage = "";
        $errorMessage   = "";
        $accountType    = "";
        $minBalance     = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accountType = trim($_POST['account_type'] ?? '');
            $minBalance  = trim($_POST['min_balance'] ?? '');

            if ($accountType === '') {
                $errorMessage = "Please select account type.";
            } else {
                if ($minBalance === '') {
                    if ($accountType === 'savings') {
                        $minBalance = 1000;
                    } elseif ($accountType === 'current') {
                        $minBalance = 2000;
                    } else { // salary
                        $minBalance = 0;
                    }
                }

                if (!is_numeric($minBalance) || (float)$minBalance < 0) {
                    $errorMessage = "Minimum balance must be a non-negative number.";
                } else {
                    $ok = $accountModel->createPendingAccountForUser(
                        $userId,
                        $accountType,
                        (float)$minBalance
                    );

                    if ($ok) {
                        $successMessage = "Account request submitted to admin for approval.";
                        $accountType    = "";
                        $minBalance     = "";
                    } else {
                        $errorMessage = "Failed to submit account request. Please try again.";
                    }
                }
            }
        }

        $this->view("account/create", [
            "title"          => "Create Account",
            "user"           => $user,
            "accountType"    => $accountType,
            "minBalance"     => $minBalance,
            "successMessage" => $successMessage,
            "errorMessage"   => $errorMessage
        ]);
    }
}
