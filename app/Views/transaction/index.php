<?php
// Route URL for this page
$baseUrl = "index.php?url=transaction/index";
?>

<link rel="stylesheet" href="css/transaction.css">

<h3 class="page-title">
    <?= $isAdmin ? "All Transactions" : "Your Transactions"; ?>
</h3>

<div class="transactions-container">

    <div class="search-row">

        <!-- LEFT → Go Back button (only when search is active) -->
        <?php if ($search !== "" || $filterType !== "" || $filterName !== "" || $filterFrom !== "" || $filterTo !== "" || $sort !== ""): ?>
            <a href="<?= htmlspecialchars($baseUrl) ?>" class="back-btn">← Go Back</a>
        <?php else: ?>
            <div></div>
        <?php endif; ?>

        <!-- RIGHT → Search + filter icon + export button -->
        <form method="GET" class="search-bar" action="<?= htmlspecialchars($baseUrl) ?>">
            <input type="hidden" name="url" value="transaction/index">

            <input type="text" name="search" placeholder="Search here..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>

            <input type="hidden" name="page" value="1">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="filter_type" value="<?= htmlspecialchars($filterType) ?>">
            <input type="hidden" name="filter_name" value="<?= htmlspecialchars($filterName) ?>">
            <input type="hidden" name="filter_from" value="<?= htmlspecialchars($filterFrom) ?>">
            <input type="hidden" name="filter_to" value="<?= htmlspecialchars($filterTo) ?>">

            <button type="button" class="filter-trigger" id="filterToggle">
                <img src="images/filter.png" alt="Filter">
            </button>

            <button type="submit" name="export" value="csv" class="export-btn">
                Export CSV
            </button>

            <!-- FILTER POPUP -->
            <div class="filter-popup" id="filterPopup">
                <h4>View Options</h4>

                <div class="filter-section">
                    <strong>Sort by Name</strong>
                    <label><input type="radio" name="sort" value="name_asc" <?= $sort === 'name_asc' ? 'checked' : '' ?>> A → Z</label>
                    <label><input type="radio" name="sort" value="name_desc" <?= $sort === 'name_desc' ? 'checked' : '' ?>> Z → A</label>
                    <label><input type="radio" name="sort" value="" <?= $sort === '' ? 'checked' : '' ?>> Default</label>
                </div>

                <div class="filter-section">
                    <strong>Filter</strong>

                    <label><input type="radio" name="filter_type" value="date" <?= $filterType === 'date' ? 'checked' : '' ?>> By Date</label>
                    <div class="filter-sub" id="filterDateFields">
                        <small>Date range</small>
                        <div class="filter-row">
                            <input type="date" name="filter_from" value="<?= htmlspecialchars($filterFrom) ?>">
                            <span>to</span>
                            <input type="date" name="filter_to" value="<?= htmlspecialchars($filterTo) ?>">
                        </div>
                    </div>

                    <label><input type="radio" name="filter_type" value="name" <?= $filterType === 'name' ? 'checked' : '' ?>> By Name</label>
                    <div class="filter-sub" id="filterNameFields">
                        <input type="text" name="filter_name" placeholder="Account holder name" value="<?= htmlspecialchars($filterName) ?>">
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-apply">Apply</button>
                    <button type="button" class="btn-clear" id="filterClear">Clear</button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($queryError !== null): ?>
        <div class="no-data">SQL Error: <?= htmlspecialchars($queryError); ?></div>

    <?php elseif ($totalRows === 0): ?>
        <div class="no-data">No transactions found.</div>

    <?php else: ?>

        <table class="transactions-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Account No.</th>
                    <th>Account Holder</th>
                    <th>Username</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Date &amp; Time</th>
                    <th>Performed By</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= (int)$row['id']; ?></td>
                    <td><?= htmlspecialchars($row['account_number']); ?></td>
                    <td><?= htmlspecialchars($row['full_name']); ?></td>
                    <td><?= htmlspecialchars($row['username']); ?></td>

                    <!-- AMOUNT -->
                    <td>
                        <?php if ($row['transaction_type'] === 'fine'): ?>
                            <span style="color:#b30000;font-weight:600;">
                                - ₹<?= number_format((float)$row['amount'], 2); ?>
                            </span>
                        <?php else: ?>
                            ₹<?= number_format((float)$row['amount'], 2); ?>
                        <?php endif; ?>
                    </td>

                    <!-- TYPE -->
                    <td>
                        <?php
                            if ($row['transaction_type'] === 'fine') {
                                echo "<span class='status declined'>Fine (1% Penalty)</span>";
                            } else {
                                echo htmlspecialchars(ucfirst($row['transaction_type']));
                            }
                        ?>
                    </td>

                    <td><?= htmlspecialchars($row['status']); ?></td>
                    <td><?= htmlspecialchars($row['transaction_date']); ?></td>

                    <!-- PERFORMED BY -->
                    <td>
                        <?= $row['transaction_type'] === 'fine'
                            ? 'System'
                            : htmlspecialchars($row['performed_by_username']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <?php
                $paginationBaseUrl = "index.php";
                $paginationPage    = $page;
                $paginationTotalPages = $totalPages;
                $paginationParams  = [
                    'url'         => 'transaction/index',
                    'search'      => $search,
                    'sort'        => $sort,
                    'filter_type' => $filterType,
                    'filter_name' => $filterName,
                    'filter_from' => $filterFrom,
                    'filter_to'   => $filterTo,
                ];
                require __DIR__ . '/../partials/pagination.php';
            ?>
        <?php endif; ?>

    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const toggle = document.getElementById("filterToggle");
    const popup  = document.getElementById("filterPopup");
    const clear  = document.getElementById("filterClear");

    if (toggle) {
        toggle.addEventListener("click", e => {
            e.stopPropagation();
            popup.style.display = popup.style.display === "block" ? "none" : "block";
        });
    }

    document.addEventListener("click", e => {
        if (popup && !popup.contains(e.target) && !toggle.contains(e.target)) {
            popup.style.display = "none";
        }
    });

    if (clear) {
        clear.addEventListener("click", () => {
            document.querySelector(".search-bar").submit();
        });
    }
});
</script>
