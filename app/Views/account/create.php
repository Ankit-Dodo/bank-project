<?php
// expected vars: $accountType, $minBalance, $successMessage, $errorMessage
?>
<link rel="stylesheet" href="css/form_style.css">

<div class="form-card">
    <h3 class="form-create-title">Create Account</h3>

    <?php if (!empty($errorMessage)): ?>
        <div class="form-error">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="form-success">
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php?url=account/create">

        <label for="account_type">Account Type:</label>
        <select name="account_type" id="account_type" required>
            <option value="">-- Select Type --</option>
            <option value="savings" <?= $accountType === 'savings' ? 'selected' : '' ?>>Savings</option>
            <option value="current" <?= $accountType === 'current' ? 'selected' : '' ?>>Current</option>
            <option value="salary"  <?= $accountType === 'salary'  ? 'selected' : '' ?>>Salary</option>
        </select>

        <label for="min_balance">Minimum Balance:</label>
        <input
            type="number"
            name="min_balance"
            id="min_balance"
            min="0"
            placeholder="e.g. 2000"
            value="<?= htmlspecialchars($minBalance) ?>"
        >

        <button type="submit" class="form-submit">Submit Account Request</button>

        <p class="form-note">
            Note: Your account request will go to admin for approval.
        </p>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const accountType = document.getElementById('account_type');
    const minBalance  = document.getElementById('min_balance');

    function updateMinBalance() {
        switch (accountType.value) {
            case 'current':
                minBalance.value = 2000;
                minBalance.readOnly = true;
                break;
            case 'salary':
                minBalance.value = 0;
                minBalance.readOnly = false;
                break;
            case 'savings':
                minBalance.value = 1000;
                minBalance.readOnly = true;
                break;
            default:
                minBalance.value = '';
                minBalance.readOnly = false;
        }
    }

    accountType.addEventListener('change', updateMinBalance);

    if (accountType.value !== '') {
        updateMinBalance();
    }
});
</script>
