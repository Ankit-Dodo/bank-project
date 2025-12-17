<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentUrl = isset($_GET['url']) ? $_GET['url'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>
        <?php
        if (isset($title)) {
            echo htmlspecialchars($title);
        } else {
            echo "Indian Bank";
        }
        ?>
    </title>

    <!-- CSS relative to public/ -->
    <link rel="stylesheet" href="/css/style.css">
    <link rel="icon" type="image/png" href="/images/logo.png">
</head>
<body>

<header class="navbar">
    <div class="nav-left">
        <img src="/images/logo.png" class="logo" alt="logo">
        <span class="brand">Indian Bank</span>
    </div>

    <div class="nav-right">
        <a href="/"
           class="nav-link <?php echo ($currentUrl === 'home/index') ? 'nav-link-active' : ''; ?>">
            Home
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php
            $username    = isset($_SESSION['username']) ? $_SESSION['username'] : "User";
            $firstLetter = strtoupper(substr($username, 0, 1));
            ?>

            <a href="/index"
               class="nav-link <?php echo (strpos($currentUrl, 'dashboard') === 0 || strpos($currentUrl, 'admin') === 0) ? 'nav-link-active' : ''; ?>">
                Dashboard
            </a>

            <!-- PROFILE DROPDOWN -->
            <div class="profile-wrapper">
                <button type="button" class="profile-toggle">
                    <?php echo htmlspecialchars($firstLetter); ?>
                </button>

                <div class="profile-dropdown">
                    <div class="profile-name dropdown-item">
                        <img src="images/user.png" class="dropdown-icon" alt="User icon">
                        <?php echo htmlspecialchars($username); ?>
                    </div>

                    <a href="/customer" class="dropdown-item">
                        <img src="images/user-details.png" class="dropdown-icon">
                        Customer Details
                    </a>

                    <a href="/create" class="dropdown-item">
                        <img src="images/add-user.png" class="dropdown-icon" alt="Create account icon">
                        Create New Account
                    </a>

                    <a href="/logout" class="dropdown-item">
                        <img src="images/quit.png" class="dropdown-icon" alt="Logout icon">
                        Logout
                    </a>
                </div>
            </div>
        <?php else: ?>
            <a href="/login" class="nav-link">Login</a>
            <a href="/register" class="nav-link nav-highlight">Register</a>
        <?php endif; ?>
    </div>
</header>

<?php
// detect user role
$userRole = $_SESSION['user_role'] ?? ($_SESSION['role'] ?? null);
?>

<?php if (isset($_SESSION['user_id'])): ?>
<div class="tile-nav">

    <!-- Home -->
    <a href="/index"
       class="tile-link <?php echo ($currentUrl === 'dashboard') ? 'active-tile' : ''; ?>">
       Dashboard
    </a>

    <!-- Deposit (ADMIN ONLY) -->
    <?php if ($userRole === 'admin'): ?>
        <a href="/deposit"
           class="tile-link <?php echo (strpos($currentUrl,'deposit') === 0) ? 'active-tile' : ''; ?>">
           Deposit
        </a>
    <?php endif; ?>

    <!-- Withdraw -->
    <a href="/withdraw"
       class="tile-link <?php echo (strpos($currentUrl,'withdraw') === 0) ? 'active-tile' : ''; ?>">
       Withdraw
    </a>

    <!-- Transfer -->
    <a href="/transfer"
       class="tile-link <?php echo (strpos($currentUrl,'transfer') === 0) ? 'active-tile' : ''; ?>">
       Transfer
    </a>

    <!-- Transactions -->
    <a href="/transaction"
       class="tile-link <?php echo (strpos($currentUrl,'transaction') === 0) ? 'active-tile' : ''; ?>">
       Transactions
    </a>

</div>
<?php endif; ?>


<main class="page-main">
    <?php require $viewFile; ?>
</main>

<footer class="bank-footer">
    <div class="footer-container">
        <div class="footer-section">
            <h4>
                <img src="images/circle.png" alt="Phone Icon">
                Contact Us
            </h4>
            <p>Phone: +91 98765 43210<br>
            Customer Support: 1800-111-222</p>
        </div>

        <div class="footer-section">
            <h4>
                <img src="images/mail.png" alt="Email Icon">
                Email
            </h4>
            <a class="email-link" href="mailto:support@indianbank.com">support@indianbank.com</a><br>
            <a class="email-link" href="mailto:queries@indianbank.com">queries@indianbank.com</a>
        </div>

        <div class="footer-section">
            <h4>
                <img src="images/map-circle.png" alt="Location Icon">
                Location
            </h4>
            <p>Indian Bank Headquarters<br>
            Hanuman Road, New Delhi, India</p>
        </div>

        <div class="footer-section">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1506.7943452706313!2d77.21607821698458!3d28.6321186496815!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390cfd49f9e8084d%3A0x7b5b53134d7a00e0!2sIndian%20Bank!5e1!3m2!1sen!2sin!4v1764659161541!5m2!1sen!2sin"
                width="600"
                height="450"
                style="border:0;"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>

    <hr class="footer-line">
    <p class="footer-copy">© 2025 Indian Bank — All Rights Reserved</p>
</footer>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const toggle = document.querySelector(".profile-toggle");
    const dropdown = document.querySelector(".profile-dropdown");

    if (toggle && dropdown) {
        toggle.addEventListener("click", function (e) {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        });

        document.addEventListener("click", function (e) {
            if (!dropdown.contains(e.target) && !toggle.contains(e.target)) {
                dropdown.style.display = "none";
            }
        });
    }
});
</script>

</body>
</html>
