<div class="hero">
    <div class="hero-text">
        <h1>Welcome to Indian Bank Simulator</h1>
        <p>
            Practice deposits, withdrawals, transfers and account management
            in a safe environment — perfect for learning PHP, SQL and banking workflows.
        </p>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- NOT LOGGED IN: show Login + Register -->
            <div class="hero-buttons">
                <a href="index.php?url=auth/login" class="btn btn-primary">Login</a>
                <a href="index.php?url=auth/register" class="btn btn-secondary">Register</a>
            </div>
        <?php else: ?>
            <!-- LOGGED IN: show Dashboard + Logout -->
            <div class="hero-buttons">
                <a href="index.php?url=dashboard/index" class="btn btn-primary">Go to Dashboard</a>
                <a href="index.php?url=auth/logout" class="btn btn-secondary">Logout</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="hero-panel">
        <h3>What can you practice?</h3>
        <ul>
            <li>Open and manage customer bank accounts</li>
            <li>Perform deposits, withdrawals and transfers</li>
            <li>View transaction history</li>
            <li>Role based access (Admin / Customer)</li>
        </ul>
    </div>
</div>

