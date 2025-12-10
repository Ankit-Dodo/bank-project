<link rel="stylesheet" href="/css/auth.css">


<div class="auth-container">
    <h3 class="auth-title">Login</h3>

    <?php if (!empty($message)): ?>
        <div class="alert-error">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="form-group">
            <label for="email">Email:</label>
            <input
                type="email"
                id="email"
                name="email"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >
        </div>

        <button type="submit" class="btn-primary">Login</button>
    </form>

    <p class="auth-switch">
        No account?
        <a href="index.php?url=auth/register">Register</a>
    </p>
</div>
