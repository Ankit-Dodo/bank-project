<link rel="stylesheet" href="/css/style.css">

<div class="page-container">

    <div class="dashboard-header">
        <div>
            <h3>Dashboard</h3>

            <p class="welcome-text">
                Welcome,
                <strong><?php echo htmlspecialchars($user['username']); ?></strong>

                <?php if ($role === "admin"): ?>
                    <span class="role-pill">ADMIN</span>
                <?php endif; ?>
            </p>

            <p class="last-login">
                Last login: <?php echo htmlspecialchars($user['last_login']); ?>
            </p>
        </div>
    </div>

    <div class="dashboard-content">

        <?php if ($role === 'admin'): ?>
            <?php include __DIR__ . "/admin.php"; ?>
        <?php else: ?>
            <?php include __DIR__ . "/customer.php"; ?>
        <?php endif; ?>

    </div>


</div>
