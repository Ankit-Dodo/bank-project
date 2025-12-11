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
        (function(){
          function show(msgType, msg) {
            if (window.Swal) {
              Swal.fire({
                icon: msgType === 'error' ? 'error' : 'success',
                title: msgType === 'error' ? 'Error' : 'Success',
                text: msg,
                confirmButtonText: 'OK'
              });
            } else {
              var s = document.createElement('script');
              s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
              s.onload = function() {
                Swal.fire({
                  icon: msgType === 'error' ? 'error' : 'success',
                  title: msgType === 'error' ? 'Error' : 'Success',
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
                            <?php foreach ($fromAccounts as $acc): ?>
                                <option value="<?= (int)$acc['id'] ?>"
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


<!-- AUTO-LOAD ACCOUNTS ON USER CHANGE (ADMIN ONLY) + Confirmation -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const userSelect = document.getElementById("user_id");
    const form = document.querySelector(".transfer-form");
    if (!form) return;

    const amountInput = form.querySelector("input[name='amount']");
    const submitBtn = form.querySelector("button[type='submit'][name='action'][value='transfer']");

    // When admin changes user, submit form and skip confirmation
    if (userSelect) {
        userSelect.addEventListener("change", function () {
            form.dataset.skipConfirm = "true";
            form.submit();
        });
    }

    // Confirmation handler
    function onSubmit(e) {
        // If triggered by userSelect change, allow normal submit
        if (form.dataset.skipConfirm === "true") {
            delete form.dataset.skipConfirm;
            return;
        }

        e.preventDefault();

        // Amount exactly as typed
        const amount = amountInput ? amountInput.value : '';

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
