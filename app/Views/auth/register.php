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
            <label for="reg_email">Email:</label>
            <input
                type="email"
                id="reg_email"
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

        <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                required
            >
            <small class="error-message" id="confirmPasswordError"></small>
        </div>

        <button type="submit" class="btn-primary" id="registerSubmit">Register</button>
    </form>

    <p class="auth-switch">
        Already have an account? <a href="/login">Login</a>
    </p>
</div>

<script>
    const form = document.getElementById('registerForm');
    const usernameInput = document.getElementById('username');
    const emailInput    = document.getElementById('reg_email');
    const passwordInput = document.getElementById('password');
    const confirmInput  = document.getElementById('confirm_password');
    const submitBtn     = document.getElementById('registerSubmit');

    const usernameError = document.getElementById('usernameError');
    const emailError    = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const confirmError  = document.getElementById('confirmPasswordError');

    function clearErrors() {
        usernameError.textContent = '';
        emailError.textContent    = '';
        passwordError.textContent = '';
        confirmError.textContent  = '';
    }

    // Check if email already exists (AJAX)
    async function checkEmailExists(email) {
        try {
            const response = await fetch("/checkEmail", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email: email })
            });

            if (!response.ok) return false;
            const data = await response.json();
            return data.exists || false;
        } catch (err) {
            // network error — assume not exists (server will still validate)
            return false;
        }
    }

    // Debounce helper
    function debounce(fn, delay) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    // Live-check email (optional UX): show immediate inline message if already registered
    const debouncedEmailCheck = debounce(async function () {
        const email = emailInput.value.trim();
        if (!email) return;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            emailError.textContent = 'Please enter a valid email address.';
            return;
        }
        emailError.textContent = 'Checking...';
        const exists = await checkEmailExists(email);
        if (exists) {
            emailError.textContent = 'This email is already registered.';
        } else {
            emailError.textContent = '';
        }
    }, 600);

    emailInput.addEventListener('input', function () {
        emailError.textContent = '';
        debouncedEmailCheck();
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors();

        let isValid = true;

        // Username validation
        const username = usernameInput.value.trim();
        if (username.length < 3) {
            usernameError.textContent = 'Username must be at least 3 characters.';
            isValid = false;
        } else if (!/^[A-Za-z0-9_]{3,30}$/.test(username)) {
            usernameError.textContent = 'Username may contain letters, numbers and underscore only.';
            isValid = false;
        }

        // Email validation (format)
        const email = emailInput.value.trim();
        const emailPattern = /^[\w.-]{1,25}@([\w-]+\.)+[\w-]{2,4}$/;
        if (!emailPattern.test(email)) {
            emailError.textContent = 'Please enter a valid email address.';
            isValid = false;
        }

        // Password validation
        const password = passwordInput.value;
        const confirm  = confirmInput.value;
        const hasUpper = /[A-Z]/.test(password);
        const hasLower = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);

        if (!hasUpper || !hasLower || !hasNumber || password.length < 8) {
            passwordError.textContent =
                'Password must be at least 8 characters and include a capital letter, a small letter, and a number.';
            isValid = false;
        }

        // Confirm password
        if (password !== confirm) {
            confirmError.textContent = 'Passwords do not match.';
            isValid = false;
        }

        if (!isValid) {
            return;
        }

        // disable submit while checking
        submitBtn.disabled = true;
        submitBtn.textContent = "Please wait...";

        // Check if email is already registered (final check before submit)
        const exists = await checkEmailExists(email);
        if (exists) {
            emailError.textContent = "This email is already registered.";
            submitBtn.disabled = false;
            submitBtn.textContent = "Register";
            return;
        }
        form.submit();
    });
</script>
