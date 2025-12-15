<?php

class AuthController extends Controller
{
    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // If already logged in then go to dashboard
        if (!empty($_SESSION['user_id'])) {
            header("Location: index.php?url=dashboard/index");
            exit;
        }

        $message = "";
        $messageType = ""; // "success" or "error"

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            // SERVER-SIDE VALIDATION
            if ($email === '' || $password === '') {
                $message = "Both email and password are required.";
                $messageType = "error";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Please enter a valid email address.";
                $messageType = "error";
            } else {
                $userModel = $this->model("User");
                $user      = $userModel->findByEmail($email);

                if (!$user) {
                    $message = "Invalid email or password.";
                    $messageType = "error";
                } else {
                    // Block users on Hold
                    $status = isset($user['status']) ? strtolower($user['status']) : 'active';
                    if ($status === 'hold') {
                        $message = "Your account is on hold. Contact admin.";
                        $messageType = "error";
                    } else {
                        if (!password_verify($password, $user['password_hash'])) {
                            $message = "Invalid email or password.";
                            $messageType = "error";
                        } else {
                            // successful login
                            $userModel->updateLastLogin($user['id']);

                            $_SESSION['user_id']   = (int)$user['id'];
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

        $this->view("auth/login", [
            "title"       => "Login",
            "message"     => $message,
            "messageType" => $messageType
        ]);
    }

    public function register()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $message = "";
        $messageType = ""; // "success" or "error"

        $oldUsername = "";
        $oldEmail    = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username        = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email           = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password        = isset($_POST['password']) ? $_POST['password'] : '';
            $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

            $oldUsername = $username;
            $oldEmail    = $email;

            // SERVER-SIDE VALIDATION

            // required
            if ($username === '' || $email === '' || $password === '' || $confirmPassword === '') {
                $message = "All fields are required.";
                $messageType = "error";

            // username rules
            } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
                $message = "Username must be 3-30 characters and contain only letters, numbers or underscore.";
                $messageType = "error";

            // email format & reasonable length
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 25) {
                $message = "Invalid or too long email address.";
                $messageType = "error";

            // password match
            } elseif ($password !== $confirmPassword) {
                $message = "Password and confirm password do not match.";
                $messageType = "error";

            // password complexity & length
            } else {
                $hasUpper  = preg_match('/[A-Z]/', $password);
                $hasLower  = preg_match('/[a-z]/', $password);
                $hasNumber = preg_match('/[0-9]/', $password);
                $hasSymbol = preg_match('/[\W_]/', $password); // optional

                if (strlen($password) < 8) {
                    $message = "Password must be at least 8 characters long.";
                    $messageType = "error";
                } elseif (strlen($password) > 255) {
                    $message = "Password is too long.";
                    $messageType = "error";
                } elseif (!$hasUpper || !$hasLower || !$hasNumber) {
                    $message = "Password must include at least one uppercase letter, one lowercase letter and one number.";
                    $messageType = "error";
                } else {
                    // uniqueness checks & creation
                    $userModel = $this->model("User");

                    if ($userModel->emailExists($email)) {
                        $message = "This email is already registered.";
                        $messageType = "error";
                    } elseif ($userModel->usernameExists($username)) {
                        $message = "This username is already taken.";
                        $messageType = "error";
                    } else {
                        $ok = $userModel->createUser($username, $email, $password);

                        if ($ok) {
                            $message = "Your account has been created successfully. You may login now.";
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

        $this->view("auth/register", [
            "title"       => "Register",
            "message"     => $message,
            "messageType" => $messageType,
            "oldUsername" => $oldUsername,
            "oldEmail"    => $oldEmail
        ]);
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

       
        session_destroy();

        header("Location: index.php?url=auth/login");
        exit;
    }
}
