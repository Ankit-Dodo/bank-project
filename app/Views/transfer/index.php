<link rel="stylesheet" href="/css/main.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="page-container1">

    <!-- HEADER -->
    <div class="section-card transfer-header">
        <h3>Transfer-Money</h3>
    </div>

    <!-- SERVER MESSAGES -> SweetAlert -->
    <?php if (!empty($errorMessage) || !empty($successMessage)): ?>
        <script>
            (function() {
                function show(msgType, msg) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: msgType === 'error' ? 'error' : 'success',
                            title: msgType === 'error' ? 'Transaction Failed' : 'Success',
                            text: msg,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        var s = document.createElement('script');
                        s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                        s.onload = function() {
                            Swal.fire({
                                icon: msgType === 'error' ? 'error' : 'success',
                                title: msgType === 'error' ? 'Transaction Failed' : 'Success',
                                text: msg,
                                confirmButtonText: 'OK'
                            });
                        };
                        document.head.appendChild(s);
                    }
                }

                document.addEventListener('DOMContentLoaded', function() {
                    <?php if (!empty($errorMessage)): ?>
                        show('error', <?php echo json_encode($errorMessage); ?>);
                    <?php endif; ?>

                    <?php if (!empty($successMessage)): ?>
                        show('success', <?php echo json_encode($successMessage); ?>);
                    <?php endif; ?>
                });
            })();
        </script>
    <?php endif; ?>

    <!-- FORM CARD -->
    <div class="section-card">
        <h4 class="section-title">Transfer Between Accounts</h4>

        <form method="post" class="transfer-form">

            <!-- ROW 1: ADMIN USER SELECT + FROM ACCOUNT -->
            <div class="transfer-row">

                <?php if ($role === 'admin'): ?>
                    <div class="transfer-col">
                        <label for="user_id">1. Select User</label>
                        <select name="user_id" id="user_id" class="transfer-input">
                            <option value="">-- Select User --</option>
                            <?php foreach ($usersList as $u): ?>
                                <option value="<?= (int)$u['id'] ?>"
                                    <?= ($selectedUserId == $u['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- FROM ACCOUNT -->
                <div class="transfer-col">
                    <label for="from_account_id">
                        <?= ($role === 'admin') ? '2. From Account' : '1. From Account'; ?>
                    </label>

                    <select name="from_account_id" id="from_account_id" class="transfer-input">
                        <?php if (empty($fromAccounts)): ?>
                            <option value="">-- No Accounts Found --</option>
                        <?php else: ?>
                            <option value="">-- Select Account --</option>
                            <?php foreach ($fromAccounts as $acc):
                                // prepare data attributes for client-side checks
                                $bal = isset($acc['balance']) ? number_format((float)$acc['balance'], 2, '.', '') : '0.00';
                                $min = isset($acc['min_balance']) ? number_format((float)$acc['min_balance'], 2, '.', '') : '0.00';
                            ?>
                                <option value="<?= (int)$acc['id'] ?>"
                                    data-balance="<?= $bal; ?>"
                                    data-min="<?= $min; ?>"
                                    <?= ($fromAccountId == $acc['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($acc['account_number']) ?>
                                    - <?= htmlspecialchars($acc['account_type']) ?>
                                    (₹<?= number_format((float)$acc['balance'], 2) ?>)
                                    - <?= htmlspecialchars($acc['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

            </div>

            <!-- ROW 2: TO ACCOUNT + AMOUNT -->
            <div class="transfer-row">

                <!-- TO ACCOUNT -->
                <div class="transfer-col">
                    <label for="to_account_id">
                        <?= ($role === 'admin') ? '3. To Account' : '2. To Account'; ?>
                    </label>

                    <select name="to_account_id" id="to_account_id" class="transfer-input">
                        <option value="">-- Select Destination Account --</option>

                        <?php foreach ($toAccounts as $acc): ?>
                            <?php if ((int)$acc['id'] === (int)$fromAccountId) continue; ?>
                            <option value="<?= (int)$acc['id'] ?>"
                                <?= ($toAccountId == $acc['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($acc['account_number']) ?>
                                - <?= htmlspecialchars($acc['account_type']) ?>
                                (₹<?= number_format((float)$acc['balance'], 2) ?>)
                                - <?= htmlspecialchars($acc['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- AMOUNT -->
                <div class="transfer-col">
                    <label for="amount">
                        <?= ($role === 'admin') ? '4. Amount (Rs.)' : '3. Amount (Rs.)'; ?>
                    </label>
                    <input type="number" step="0.01" min="0"
                        id="amount" name="amount"
                        class="transfer-input"
                        value="<?= htmlspecialchars($amountValue) ?>">
                </div>

            </div>

            <!-- ACTION BUTTON -->
            <div class="transfer-actions">
                <button type="submit" name="action" value="transfer" class="transfer-btn">
                    Submit Transfer
                </button>
            </div>

        </form>
    </div>
</div>


<!-- AUTO-LOAD ACCOUNTS ON USER CHANGE (ADMIN ONLY) + Confirmation + Low-balance checks -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const userSelect = document.getElementById("user_id");
        const form = document.querySelector(".transfer-form");
        if (!form) return;

        const amountInput = form.querySelector("input[name='amount']");
        const submitBtn = form.querySelector("button[type='submit'][name='action'][value='transfer']");
        const fromSelect = document.getElementById("from_account_id");
        const toSelect = document.getElementById("to_account_id");

        // When admin changes user, submit form and skip confirmation
        if (userSelect) {
            userSelect.addEventListener("change", function() {
                form.dataset.skipConfirm = "true";
                form.submit();
            });
        }

        // helper: show low-balance confirmation, returns Promise<boolean>
        function showLowBalanceConfirm(currentBal, minBal, amount) {
            const fine = (amount * 0.01).toFixed(2);

            const html =
                "<div style='text-align:left'>" +
                "<strong>Current balance:</strong> ₹" + parseFloat(currentBal).toFixed(2) + "<br>" +
                "<strong>Minimum balance:</strong> ₹" + parseFloat(minBal).toFixed(2) + "<br>" +
                "<strong>After transfer:</strong> ₹" + (parseFloat(currentBal) - parseFloat(amount)).toFixed(2) + "<br><br>" +
                "<span style='color:#c0392b'><strong>⚠ Penalty:</strong> ₹" + fine +
                " (1% fine will be deducted from your account)</span>" +
                "</div>";

            return Swal.fire({
                title: "Low balance warning",
                html: html,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, proceed & accept fine",
                cancelButtonText: "Cancel"
            }).then(res => res.isConfirmed);
        }


        // Confirmation handler (async to allow low-balance confirm)
        async function onSubmit(e) {
            // If triggered by userSelect change, allow normal submit
            if (form.dataset.skipConfirm === "true") {
                delete form.dataset.skipConfirm;
                return;
            }

            e.preventDefault();

            // amount exactly as typed
            const amount = amountInput ? parseFloat(amountInput.value || 0) : 0;

            // validate selections
            const fromOpt = fromSelect ? fromSelect.options[fromSelect.selectedIndex] : null;
            const toOpt = toSelect ? toSelect.options[toSelect.selectedIndex] : null;

            if (!fromOpt || !fromOpt.value) {
                Swal.fire({
                    icon: "error",
                    title: "No source account",
                    text: "Please select a source (From) account."
                });
                return;
            }

            if (!toOpt || !toOpt.value) {
                Swal.fire({
                    icon: "error",
                    title: "No destination account",
                    text: "Please select a destination (To) account."
                });
                return;
            }

            if (fromOpt.value === toOpt.value) {
                Swal.fire({
                    icon: "error",
                    title: "Same account selected",
                    text: "Source and destination accounts cannot be the same."
                });
                return;
            }

            if (!amount || amount <= 0) {
                Swal.fire({
                    icon: "error",
                    title: "Invalid amount",
                    text: "Please enter a valid transfer amount greater than 0."
                });
                return;
            }

            // read balances from data attributes on from account
            const currentBal = parseFloat(fromOpt.dataset.balance || "0");
            const minBal = parseFloat(fromOpt.dataset.min || "0");

            // If this would go below zero -> show error and stop
            if ((currentBal - amount) < 0) {
                Swal.fire({
                    icon: "error",
                    title: "Insufficient funds",
                    text: "This transfer would make your balance go below 0. Transaction cancelled."
                });
                return;
            }

            // If this would go below min but not below 0 -> show low-balance confirmation first
            if ((currentBal - amount) < minBal) {
                const proceed = await showLowBalanceConfirm(currentBal, minBal, amount);
                if (!proceed) {
                    Swal.fire({
                        icon: "info",
                        title: "Cancelled",
                        text: "Transfer cancelled."
                    });
                    return;
                }
            }

            // Now show the original confirm transfer dialog
            Swal.fire({
                title: "Confirm Transfer",
                text: "Are you sure you want to transfer ₹" + amount + "?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, Transfer",
                cancelButtonText: "No, Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    // remove handler to avoid re-trigger, then click real submit button
                    form.removeEventListener('submit', onSubmit);
                    if (submitBtn) {
                        submitBtn.click();
                    } else {
                        form.submit();
                    }
                } else {
                    Swal.fire({
                        icon: "info",
                        title: "Cancelled",
                        text: "Transfer cancelled."
                    });
                }
            });
        }

        form.addEventListener("submit", onSubmit);
    });
</script>