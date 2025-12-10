<link rel="stylesheet" href="/css/main.css">
<div class="page-container1">

    <div class="section-card withdraw-header">
        <h3>Withdraw-Money</h3>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="withdraw-error"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="withdraw-success"><?php echo $successMessage; ?></div>
    <?php endif; ?>

    <div class="section-card">

        <h4 class="section-title">Withdraw Funds</h4>

        <form method="post" class="withdraw-form">

            <div class="withdraw-row">

                <?php if ($role === "admin"): ?>
                <div class="withdraw-col">
                    <label>Select User</label>
                    <select name="user_id" id="user_id" class="withdraw-input">
                        <option value="">-- Select User --</option>
                        <?php foreach ($usersList as $u): ?>
                            <option value="<?php echo $u['id']; ?>"
                                <?php echo ($selectedUserId == $u['id']) ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($u['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="withdraw-col">
                    <label>Select Account</label>
                    <select name="account_id" id="account_id" class="withdraw-input">
                        <?php if (empty($userAccounts)): ?>
                            <option value="">-- No Accounts Found --</option>
                        <?php else: ?>
                            <option value="">-- Select Account --</option>
                            <?php foreach ($userAccounts as $acc): ?>
                                <option value="<?php echo $acc['id']; ?>">
                                    <?php echo $acc['account_type'] . " - " . $acc['account_number'] .
                                        " (₹" . number_format($acc['balance'], 2) . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

            </div>

            <div class="withdraw-row">
                <div class="withdraw-col">
                    <label>Amount (Rs.)</label>
                    <input type="number" step="0.01" min="0" name="amount"
                        class="withdraw-input" value="<?php echo $amountValue; ?>">
                </div>
            </div>

            <div class="withdraw-actions">
                <button type="submit" name="action" value="withdraw" class="withdraw-btn">
                    Withdraw
                </button>
            </div>

        </form>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const userSelect = document.getElementById("user_id");
    if (userSelect) {
        userSelect.addEventListener("change", function() {
            document.querySelector(".withdraw-form").submit();
        });
    }
});
</script>
