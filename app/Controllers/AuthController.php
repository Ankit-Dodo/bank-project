<?php

class AuthController extends Controller
{
    public function login()
    {
        // If already logged in → go to dashboard
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php?url=dashboard/index");
            exit;
        }

        $message = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            if ($email === '' || $password === '') {
                $message = "All fields are required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Please enter a valid email address.";
            } else {
                $userModel = $this->model("User");
                $user      = $userModel->findByEmail($email);

                if (!$user) {
                    $message = "Invalid email or password.";
                } else {
                    if (isset($user['status']) && strtolower($user['status']) !== 'active') {
                        $message = "Your account is inactive.";
                    } else {
                        if (!password_verify($password, $user['password_hash'])) {
                            $message = "Invalid email or password.";
                        } else {
                            $userModel->updateLastLogin($user['id']);

                            $_SESSION['user_id']   = $user['id'];
                            $_SESSION['user_role'] = isset($user['role']) ? $user['role'] : '';
                            $_SESSION['email']     = $user['email'];
                            $_SESSION['username']  = $user['username'];

                            header("Location: index.php?url=dashboard/index");
                            exit;
                        }
                    }
                }
            }
        }

        $this->view("auth/login", array(
            "title"   => "Login",
            "message" => $message
        ));
    }

    public function register()
    {
        $message = "";
        $messageType = ""; // "success" or "error"

        $oldUsername = "";
        $oldEmail    = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            $oldUsername = $username;
            $oldEmail    = $email;

            if ($username === '' || $email === '' || $password === '') {
                $message = "All fields are required.";
                $messageType = "error";
            } elseif (strlen($username) < 3) {
                $message = "Username must be at least 3 characters long.";
                $messageType = "error";
            } elseif (strlen($username) > 50) {
                $message = "Username is too long. Maximum 50 characters allowed.";
                $messageType = "error";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Invalid email format.";
                $messageType = "error";
            } elseif (strlen($email) > 25) {
                $message = "Email is too long. Maximum 25 characters allowed.";
                $messageType = "error";
            } else {
                $upper  = preg_match('/[A-Z]/', $password);
                $lower  = preg_match('/[a-z]/', $password);
                $number = preg_match('/[0-9]/', $password);

                if (!$upper || !$lower || !$number || strlen($password) < 8) {
                    $message = "Password must be at least 8 characters long and include a capital letter, a small letter, and a number.";
                    $messageType = "error";
                } elseif (strlen($password) > 50) {
                    $message = "Password is too long. Maximum 50 characters allowed.";
                    $messageType = "error";
                } else {
                    $userModel = $this->model("User");

                    if ($userModel->emailExists($email)) {
                        $message = "This email is already registered.";
                        $messageType = "error";
                    } else {
                        $ok = $userModel->createUser($username, $email, $password);

                        if ($ok) {
                            $message = "Your account has been created successfully.";
                            $messageType = "success";
                            $oldUsername = "";
                            $oldEmail    = "";
                        } else {
                            $message = "Registration failed. Please try again.";
                            $messageType = "error";
                        }
                    }
                }
            }
        }

        $this->view("auth/register", array(
            "title"       => "Register",
            "message"     => $message,
            "messageType" => $messageType,
            "oldUsername" => $oldUsername,
            "oldEmail"    => $oldEmail
        ));
    }

    // AJAX email check: index.php?url=auth/checkEmail
    public function checkEmail()
    {
        header("Content-Type: application/json");

        $raw  = file_get_contents("php://input");
        $data = json_decode($raw, true);

        $email = "";
        if (is_array($data) && isset($data['email'])) {
            $email = trim($data['email']);
        }

        $exists = false;

        if ($email !== "") {
            $userModel = $this->model("User");
            $exists    = $userModel->emailExists($email);
        }

        echo json_encode(array("exists" => $exists));
        exit;
    }

    public function logout()
    {
        session_destroy();
        header("Location: index.php?url=auth/login");
        exit;
    }
}
