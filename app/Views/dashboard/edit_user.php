<link rel="stylesheet" href="/css/edit_user.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<h3 class="page-title-center">Manage Accounts / Users - Edit User Details</h3>

<div class="form-container-center admin-edit-container">

    <?php if (!empty($successMsg)): ?>
        <script>
        document.addEventListener("DOMContentLoaded", function () {
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: <?= json_encode($successMsg) ?>,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = "index.php?url=admin/index";
            });
        });
        </script>
    <?php endif; ?>

    <?php if (!empty($errorMsg)): ?>
        <script>
        document.addEventListener("DOMContentLoaded", function () {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: <?= json_encode($errorMsg) ?>,
                confirmButtonColor: "#d33"
            });
        });
        </script>
    <?php endif; ?>

    <!-- Select user dropdown -->
    <form method="post" class="admin-edit-select-form">
        <label for="user_id" class="admin-edit-label"><strong>Select User</strong></label>
        <select name="user_id" id="user_id" required class="admin-edit-select">
            <option value="">-- Choose User --</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= (int)$u['id'] ?>" <?= ($selectedUserId == (int)$u['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['username']) ?>
                    <?php if (!empty($u['full_name'])): ?>
                        (<?= htmlspecialchars($u['full_name']) ?>)
                    <?php endif; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="load_user" class="btn-primary admin-edit-load-btn">Load User</button>
    </form>

    <?php if (!empty($editUser)): ?>
    <form method="post" id="editUserForm" class="admin-edit-form">
        <input type="hidden" name="edit_user_id" value="<?= (int)$editUser['id'] ?>">

        <div class="form-group">
            <label for="username"><strong>Username</strong></label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($editUser['username']) ?>" required>
        </div>

        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($editUser['full_name']) ?>" required>
            <span class="error-message" id="fullNameError"></span>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($editUser['email']) ?>" required>
            <span class="error-message" id="emailError"></span>
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($editUser['phone']) ?>">
            <span class="error-message" id="phoneError"></span>
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="3"><?= htmlspecialchars($editUser['address']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="user_status">User Status</label>
            <select id="user_status" name="user_status">
                <option value="Active"   <?= $editUser['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Hold" <?= $editUser['status'] === 'Hold' ? 'selected' : '' ?>>Hold</option>
            </select>
            <span class="error-message" id="statusError"></span>
        </div>

        <hr>

        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password">
            <span class="error-message" id="newPasswordError"></span>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password">
            <span class="error-message" id="confirmPasswordError"></span>
        </div>

        <button type="submit" name="save_user" class="btn-primary">Save Changes</button>
    </form>
    <?php endif; ?>

</div>

<script>
const form = document.getElementById("editUserForm");

if (form) {
    const fullNameInput = document.getElementById("full_name");
    const emailInput    = document.getElementById("email");
    const phoneInput    = document.getElementById("phone");
    const statusSelect  = document.getElementById("user_status");
    const newPassInput  = document.getElementById("new_password");
    const confPassInput = document.getElementById("confirm_password");

    const errFull  = document.getElementById("fullNameError");
    const errEmail = document.getElementById("emailError");
    const errPhone = document.getElementById("phoneError");
    const errStat  = document.getElementById("statusError");
    const errNew   = document.getElementById("newPasswordError");
    const errConf  = document.getElementById("confirmPasswordError");

    phoneInput.addEventListener("input", function () {
        this.value = this.value.replace(/\D/g, "").slice(0, 10);
    });

    function validate() {
        let ok = true;

        errFull.textContent = "";
        errEmail.textContent = "";
        errPhone.textContent = "";
        errStat.textContent = "";
        errNew.textContent = "";
        errConf.textContent = "";

        const fullVal = fullNameInput.value.trim();
        const emailVal = emailInput.value.trim();
        const phoneVal = phoneInput.value.trim();
        const statusVal = statusSelect.value;
        const p1 = newPassInput.value.trim();
        const p2 = confPassInput.value.trim();

        if (!fullVal || fullVal.length < 2) {
            errFull.textContent = "Full name must be at least 2 characters.";
            ok = false;
        }

        const emailRegex = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
        if (!emailRegex.test(emailVal)) {
            errEmail.textContent = "Invalid email format.";
            ok = false;
        }

        if (phoneVal && phoneVal.length !== 10) {
            errPhone.textContent = "Phone number must be exactly 10 digits.";
            ok = false;
        }

        if (statusVal !== "Active" && statusVal !== "Hold") {
            errStat.textContent = "Invalid status.";
            ok = false;
        }

        const strongPasswordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
        if (p1 !== '') {
            if (p1.length < 8 || !strongPasswordRegex.test(p1)) {
                errNew.textContent = "Password must be at least 8 characters long and include: one uppercase letter, one lowercase letter, and one number.";
                ok = false;
            }
            if (p1 !== p2) {
                errConf.textContent = "New password and confirm password do not match.";
                ok = false;
            }
        }

        return ok;
    }

    form.addEventListener("submit", function (e) {
        if (!validate()) {
            e.preventDefault();
        }
    });
}
</script>
