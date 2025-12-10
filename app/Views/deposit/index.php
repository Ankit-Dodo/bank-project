<link rel="stylesheet" href="/css/main.css">

<div class="page-container1">

    <div class="section-card deposit-header">
        <h3>Deposit-Money</h3>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="deposit-error"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="deposit-success"><?php echo $successMessage; ?></div>
    <?php endif; ?>

    <div class="section-card">
        <h4 class="section-title">Deposit into Account</h4>

        <form method="post" class="deposit-form">

            <div class="deposit-row">
                <div class="deposit-col">
                    <label>1. Select User</label>
                    <select name="user_id" id="user_id" class="deposit-input">
                        <option value="">-- Select User --</option>

                        <?php foreach ($usersList as $u): ?>
                            <option value="<?php echo $u['id']; ?>"
                                <?php echo ($selectedUserId == $u['id']) ? "selected" : "";?>>
                                <?php echo htmlspecialchars($u['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="deposit-col">
                    <label>2. Select Account</label>
                    <select name="account_id" id="account_id" class="deposit-input">
                        <?php if ($selectedUserId == 0): ?>
                            <option value="">-- Select a user first --</option>

                        <?php elseif (empty($userAccounts)): ?>
                            <option value="">-- No accounts found --</option>

                        <?php else: ?>
                            <option value="">-- Select Account --</option>
                            <?php foreach ($userAccounts as $acc): ?>
                                <option value="<?php echo $acc['id']; ?>"
                                    <?php echo ($selectedAccountId == $acc['id']) ? "selected" : ""; ?>>
                                    <?php echo htmlspecialchars($acc['account_type'] . 
                                        " - " . $acc['account_number'] .
                                        " (₹" . number_format($acc['balance'], 2) . ")"); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="deposit-row">
                <div class="deposit-col">
                    <label>3. Amount (Rs.)</label>
                    <input type="number" step="0.01" min="0" name="amount"
                        class="deposit-input" value="<?php echo $amountValue; ?>">
                </div>
            </div>

            <div class="deposit-actions">
                <button type="submit" name="action" value="deposit" class="deposit-btn">
                    Deposit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const userSelect = document.getElementById("user_id");
    const form = document.querySelector(".deposit-form");

    userSelect.addEventListener("change", function () {
        form.submit(); // auto-load accounts
    });
});
</script>


