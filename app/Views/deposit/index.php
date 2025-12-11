<link rel="stylesheet" href="/css/main.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="page-container1">

    <div class="section-card deposit-header">
        <h3>Deposit-Money</h3>
    </div>

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
    if (!form) return;

    const amountInput = form.querySelector("input[name='amount']");
    // find the actual submit button so its name/value are included when clicked
    const submitBtn = form.querySelector("button[type='submit'][name='action']");

    // If userSelect exists, submit form on change but skip confirmation
    if (userSelect) {
        userSelect.addEventListener("change", function () {
            form.dataset.skipConfirm = "true";
            form.submit();
        });
    }

    function onSubmit(e) {
        // If this submit was triggered by userSelect change, allow it
        if (form.dataset.skipConfirm === "true") {
            delete form.dataset.skipConfirm;
            return;
        }

        // Prevent and show confirmation
        e.preventDefault();

        const amount = amountInput ? amountInput.value : '';

        Swal.fire({
            title: "Confirm Deposit",
            text: "Are you sure you want to deposit ₹" + amount + "?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Deposit",
            cancelButtonText: "No, Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                // remove handler to avoid loop, then click real submit button so name/value are sent
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
                    text: "Deposit cancelled."
                });
            }
        });
    }

    form.addEventListener("submit", onSubmit);
});
</script>
