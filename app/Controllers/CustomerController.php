<?php

class CustomerController extends Controller
{
    public function details()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: " . APP_URL . "/auth/login");
            exit;
        }

        $userId = (int)$_SESSION['user_id'];

        $profileModel = $this->model("Profile");
        $profile      = $profileModel->getByUserId($userId);

        // Prefill
        $full_name = $profile['full_name'] ?? "";
        $dob       = $profile['dob'] ?? "";
        $address   = $profile['address'] ?? "";
        $phone     = $profile['phone'] ?? "";

        $error = "";
        $success = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $full_name = trim($_POST['full_name']);
            $dob       = trim($_POST['dob']);
            $address   = trim($_POST['address']);
            $phone     = trim($_POST['phone']);

            // Country NOT saved or validated

            if ($full_name === "" || $dob === "" || $address === "" || $phone === "") {
                $error = "All fields are required except country.";
            } else {
                $ok = $profileModel->updateProfile($userId, $full_name, $dob, $address, $phone);
                if ($ok) {
                    $success = "Details updated successfully.";
                } else {
                    $error = "Failed to update details.";
                }
            }
        }

        $this->view("customer/details", [
            "title"     => "Customer Details",
            "full_name" => $full_name,
            "dob"       => $dob,
            "address"   => $address,
            "phone"     => $phone,
            "success"   => $success,
            "error"     => $error
        ]);
    }
}