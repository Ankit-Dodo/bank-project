<link rel="stylesheet" href="/css/auth.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="auth-container">
    <h3 class="auth-title">Login</h3>

    <!-- SERVER-SIDE VALIDATION MESSAGE -->
    <?php if (!empty($message)): ?>
        <script>
            Swal.fire({
                icon: "error",
                title: "Login Failed",
                text: <?= json_encode($message) ?>,
                confirmButtonText: "OK"
            });
        </script>
    <?php endif; ?>

    <form method="post" action="" id="loginForm" novalidate>

        <!-- EMAIL -->
        <div class="form-group">
            <label for="email">Email:</label>
            <input
                type="email"
                id="email"
                name="email"
                required
            >
            <small class="error-message" id="emailError"></small>
        </div>

        <!-- PASSWORD -->
        <div class="form-group">
            <label for="password">Password:</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >
            <small class="error-message" id="passwordError"></small>
        </div>

        <button type="submit" class="btn-primary">Login</button>
    </form>

    <p class="auth-switch">
        No account?
        <a href="index.php?url=auth/register">Register</a>
    </p>
</div>

<script>
const form = document.getElementById("loginForm");
const emailInput = document.getElementById("email");
const passwordInput = document.getElementById("password");

const emailError = document.getElementById("emailError");
const passwordError = document.getElementById("passwordError");

// Clear error messages
function clearErrors() {
    emailError.textContent = "";
    passwordError.textContent = "";
}

// VALIDATION BEFORE SUBMIT
form.addEventListener("submit", function (e) {
    clearErrors();
    let valid = true;

    const email = emailInput.value.trim();
    const password = passwordInput.value.trim();

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Email required + format validation
    if (email === "") {
        emailError.textContent = "Email is required.";
        valid = false;
    } 
    else if (!emailPattern.test(email)) {
        emailError.textContent = "Enter a valid email address.";
        valid = false;
    }

    // Password required
    if (password === "") {
        passwordError.textContent = "Password is required.";
        valid = false;
    }

    if (!valid) {
        e.preventDefault();
    }
});
</script>
