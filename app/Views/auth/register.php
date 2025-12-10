<link rel="stylesheet" href="css/auth.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="auth-container">
    <h3 class="auth-title">Register</h3>

    <?php if (!empty($message)): ?>
        <script>
            Swal.fire({
                title: "<?php echo $messageType === 'success' ? 'Registration Successful!' : 'Error'; ?>",
                text: "<?php echo htmlspecialchars($message); ?>",
                icon: "<?php echo $messageType === 'success' ? 'success' : 'error'; ?>",
                confirmButtonText: "<?php echo $messageType === 'success' ? 'Go to Login' : 'OK'; ?>"
            }).then(() => {
                <?php if ($messageType === 'success'): ?>
                    // redirect to login on success
                    window.location.href = "index.php?url=auth/login";
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>

    <form method="post" action="" id="registerForm" novalidate>
        <div class="form-group">
            <label for="username">Username:</label>
            <input
                type="text"
                id="username"
                name="username"
                required
                minlength="3"
                value="<?php echo isset($oldUsername) ? htmlspecialchars($oldUsername) : ''; ?>"
            >
            <small class="error-message" id="usernameError"></small>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input
                type="email"
                id="email"
                name="email"
                required
                value="<?php echo isset($oldEmail) ? htmlspecialchars($oldEmail) : ''; ?>"
            >
            <small class="error-message" id="emailError"></small>
        </div>

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

        <button type="submit" class="btn-primary">Register</button>
    </form>

    <p class="auth-switch">
        Already have an account? <a href="index.php?url=auth/login">Login</a>
    </p>
</div>

<script>
    const form = document.getElementById('registerForm');
    const usernameInput = document.getElementById('username');
    const emailInput    = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    const usernameError = document.getElementById('usernameError');
    const emailError    = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');

    function clearErrors() {
        usernameError.textContent = '';
        emailError.textContent    = '';
        passwordError.textContent = '';
    }

    // Check if email already exists (AJAX)
    async function checkEmailExists(email) {
        const response = await fetch("index.php?url=auth/checkEmail", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email: email })
        });

        const data = await response.json();
        return data.exists || false;
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors();

        let isValid = true;

        // Username validation
        const username = usernameInput.value.trim();
        if (username.length < 3) {
            usernameError.textContent = 'Username must be at least 3 characters.';
            isValid = false;
        }

        // Email validation (format)
        const email = emailInput.value.trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            emailError.textContent = 'Please enter a valid email address.';
            isValid = false;
        }

        // Password validation
        const password = passwordInput.value;
        const hasUpper = /[A-Z]/.test(password);
        const hasLower = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);

        if (!hasUpper || !hasLower || !hasNumber || password.length < 8) {
            passwordError.textContent =
                'Password must be at least 8 characters and include a capital letter, a small letter, and a number.';
            isValid = false;
        }

        if (!isValid) {
            return;
        }

        // Check if email is already registered
        const exists = await checkEmailExists(email);
        if (exists) {
            emailError.textContent = "This email is already registered.";
            return;
        }

        // Submit form if everything is fine
        form.submit();
    });
</script>
