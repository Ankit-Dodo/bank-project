<?php
// $user: current user row
// $accounts: list of this customer's accounts

$totalAccounts = is_array($accounts) ? count($accounts) : 0;
$totalBalance  = 0;
if (!empty($accounts)) {
    foreach ($accounts as $acc) {
        $totalBalance += (float)($acc['balance'] ?? 0);
    }
}
?>

<div class="page-container">
<link rel="stylesheet" href="/css/customer.css">
    <!-- HEADER -->
    <div class="section-card dashboard-header">
        <h3>Dashboard</h3>

        <p class="welcome-text">
            Welcome,
            <strong><?= htmlspecialchars($user['username'] ?? 'Customer'); ?></strong>
        </p>

        <?php if (!empty($user['last_login'])): ?>
            <p class="last-login">
                Last login:
                <?= htmlspecialchars($user['last_login']); ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- STATS -->
    <div class="stats-wrapper">
        <div class="stat-box">
            <div class="stat-label">Your Accounts</div>
            <div class="stat-value"><?= $totalAccounts; ?></div>
        </div>

        <div class="stat-box">
            <div class="stat-label">Total Balance</div>
            <div class="stat-value">
                Rs.<?= number_format($totalBalance, 2); ?>
            </div>
        </div>
    </div>

    <!-- ACCOUNTS TABLE -->
    <div class="section-card">
        <h4 class="section-title">Your Accounts</h4>

        <?php if ($totalAccounts === 0): ?>
            <p>You do not have any accounts yet.</p>
        <?php else: ?>
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Account No</th>
                        <th>Type</th>
                        <th>IFSC</th>
                        <th>Date</th>
                        <th>Balance</th>
                        <th>Min Balance</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($accounts as $acc): ?>
                <tr>
                    <td data-label="Account No"><?= $acc['account_number']; ?></td>
                    <td data-label="Type"><?= $acc['account_type']; ?></td>
                    <td data-label="IFSC"><?= $acc['ifsc_code']; ?></td>
                    <td data-label="Date"><?= $acc['account_date']; ?></td>
                    <td data-label="Balance">₹<?= number_format($acc['balance'], 2); ?></td>
                    <td data-label="Min Balance">₹<?= number_format(($acc['min_balance'] ?? 0), 2); ?></td>
                    <td data-label="Status">
                        <span class="status-badge status-<?= strtolower($acc['status']); ?>">
                            <?= $acc['status']; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>

            </table>
        <?php endif; ?>
    </div>

</div>
