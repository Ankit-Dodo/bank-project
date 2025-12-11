<link rel="stylesheet" href="/css/main.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="page-container1">

    <div class="section-card withdraw-header">
        <h3>Withdraw-Money</h3>
    </div>

    <?php if (!empty($errorMessage) || !empty($successMessage)): ?>
        <script>
        (function(){
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
document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector(".withdraw-form");
  if (!form) return;
  const amountInput = form.querySelector("input[name='amount']");
  const userSelect = document.getElementById("user_id");
  // find the actual submit button so we can click it programmatically later
  const submitBtn = form.querySelector("button[type='submit'][name='action']");

  // If admin changes the selected user, submit the form immediately WITHOUT confirmation.
  if (userSelect) {
    userSelect.addEventListener("change", function () {
      // set a flag so the submit handler can allow this submission through
      form.dataset.skipConfirm = "true";
      form.submit();
    });
  }

  // Confirmation handler
  function onSubmit(e) {
    // If this submit was triggered by the userSelect change, allow it.
    if (form.dataset.skipConfirm === "true") {
      delete form.dataset.skipConfirm;
      return; // allow normal submit
    }

    // Prevent default and show confirmation
    e.preventDefault();

    // Take the amount exactly as the user entered (no formatting)
    let amount = amountInput ? amountInput.value : '';

    Swal.fire({
      title: "Confirm Withdrawal",
      text: "Are you sure you want to withdraw ₹" + amount + "?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, Withdraw",
      cancelButtonText: "No, Cancel"
    }).then((result) => {
      if (result.isConfirmed) {
        // remove this handler so the next click won't re-trigger the confirmation
        form.removeEventListener('submit', onSubmit);

        // If we have a real submit button, click it so its name/value are included in POST.
        if (submitBtn) {
          submitBtn.click();
        } else {
          // fallback: native submit (may not include button name/value)
          form.submit();
        }
      } else {
        Swal.fire({
          icon: "info",
          title: "Cancelled",
          text: "Withdrawal cancelled."
        });
      }
    });
  }

  form.addEventListener("submit", onSubmit);
});
</script>
