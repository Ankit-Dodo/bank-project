<link rel="stylesheet" href="css/form_style.css">

<div class="form-card">
    <h3 class="form-details-title">Customer Details</h3>

    <?php if (!empty($error)): ?>
        <div class="form-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="form-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?url=customer/details">

        <label>Full Name:</label>
        <input type="text" name="full_name" required
               value="<?= htmlspecialchars($full_name) ?>"
               pattern="[A-Za-z\s.']{5,100}"
               maxlength="100">

        <label>Date of Birth:</label>
        <input type="date" name="dob" required
               value="<?= htmlspecialchars($dob) ?>"
               max="<?= date('Y-m-d', strtotime('-18 years')) ?>">

        <label>Address:</label>
        <textarea name="address" required minlength="10" maxlength="255"><?= htmlspecialchars($address) ?></textarea>

        <!-- Country is DISPLAY-ONLY (not used in backend) -->
        <label>Select Country:</label>
        <select id="countrySelect" name="country" required>
            <option value="">Loading countries...</option>
        </select>

        <label>Phone Number:</label>
        <input type="text" name="phone" required
               value="<?= htmlspecialchars($phone) ?>"
               pattern="[0-9]{10}"
               maxlength="10">

        <button type="submit" class="form-submit">Save Details</button>
    </form>
</div>

<script>
async function loadCountries() {
    try {
        const res = await fetch("https://api.first.org/data/v1/countries");
        const data = await res.json();
        const select = document.getElementById("countrySelect");

        select.innerHTML = "<option value=''>-- Select Country --</option>";

        for (const code in data.data) {
            const name = data.data[code].country;
            const opt = document.createElement("option");
            opt.value = name;
            opt.textContent = name;

            select.appendChild(opt);
        }
    } catch (err) {
        console.error("Error loading countries:", err);
        document.getElementById("countrySelect").innerHTML =
            "<option value=''>Unable to load countries</option>";
    }
}

loadCountries();
</script>
