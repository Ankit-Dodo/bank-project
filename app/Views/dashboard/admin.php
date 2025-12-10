<link rel="stylesheet" href="/css/admin.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (!empty($msg)): ?>
<script>
Swal.fire({
    title: <?php echo json_encode($err ? 'Error' : 'Success'); ?>,
    text: <?php echo json_encode($msg); ?>,
    icon: <?php echo json_encode($err ? 'error' : 'success'); ?>,
    confirmButtonText: "OK"
});
</script>
<?php endif; ?>

<div class="page-container">
    <div class="dashboard-header">
        <div>
            <h3>Dashboard</h3>

            <p class="welcome-text">
                Welcome,
                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                <?php if (strtolower($user['role']) === 'admin'): ?>
                    <span class="role-pill">ADMIN</span>
                <?php endif; ?>
            </p>

            <p class="last-login">
                Last login: <?php echo htmlspecialchars($user['last_login']); ?>
            </p>
        </div>
    </div>

    <!-- STATS SECTION -->
    <div class="stats-wrapper">

        <div class="stat-box">
            <span class="stat-label">Total Accounts</span>
            <span class="stat-value">
                <?php echo (int)$stats['total_accounts']; ?>
            </span>
        </div>

        <div class="stat-box">
            <span class="stat-label">Active Accounts</span>
            <span class="stat-value">
                <?php echo (int)$stats['active_accounts']; ?>
            </span>
        </div>

        <div class="stat-box">
            <span class="stat-label">Pending Accounts</span>
            <span class="stat-value">
                <?php echo (int)$stats['pending_accounts']; ?>
            </span>
        </div>

        <div class="stat-box">
            <span class="stat-label">Total Money in Bank</span>
            <span class="stat-value">
                Rs.<?php echo number_format((float)$stats['total_money'], 2); ?>
            </span>
        </div>

        <div class="stat-box">
            <span class="stat-label">Total Users</span>
            <span class="stat-value">
                <?php echo (int)$stats['total_users']; ?>
            </span>
        </div>

    </div>

    <!-- TITLE + EDIT USER BUTTON -->
    <div class="admin-top-row">
        <h4 class="section-title">Manage Accounts / Users</h4>
        <!-- change href when you create edit users page -->
        <a href="#" class="btn-secondary">Edit User Details</a>
    </div>

    <!-- TABS -->
    <div class="tab-card">
        <div class="tab-buttons">
            <button type="button" onclick="showAdminTab('pending')" id="btn-tab-pending">
                Pending Requests
            </button>

            <button type="button" onclick="showAdminTab('all')" id="btn-tab-all" class="active-tab">
                All Accounts
            </button>
        </div>
    </div>

    <!-- PENDING ACCOUNTS TAB -->
    <div id="admin-tab-pending" class="section-card" style="display:none;">

        <?php if ($pendingAccounts && mysqli_num_rows($pendingAccounts) > 0): ?>

        <table class="ui-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Account No</th>
                    <th>Type</th>
                    <th>IFSC</th>
                    <th>Date</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = mysqli_fetch_assoc($pendingAccounts)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['account_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['ifsc_code']); ?></td>
                    <td><?php echo htmlspecialchars($row['account_date']); ?></td>
                    <td><?php echo number_format((float)$row['balance'], 2); ?></td>
                    <td><span class="status-pending">Pending</span></td>
                    <td>
                        <a class="btn-approve"
                           href="index.php?url=admin/approve&id=<?php echo (int)$row['id']; ?>">
                           Approve
                        </a>           
                        <a class="btn-decline"
                           href="index.php?url=admin/decline&id=<?php echo (int)$row['id']; ?>">
                           Decline
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php else: ?>
            <p>No pending accounts.</p>
        <?php endif; ?>

    </div>

    <!-- ALL ACCOUNTS TAB -->
    <div id="admin-tab-all" class="section-card">

        <!-- top bar with search on right -->
        <div class="admin-table-top">
            <div></div>
            <form method="get" class="search-bar">
                <input
                    type="text"
                    name="search"
                    placeholder="Search here..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <input type="hidden" name="url" value="admin/index">
                <input type="hidden" name="page" value="1">
                <button type="submit">Search</button>
            </form>
        </div>

        <?php if ($accounts && mysqli_num_rows($accounts) > 0): ?>

        <table class="ui-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Account No</th>
                    <th>Type</th>
                    <th>IFSC</th>
                    <th>Date</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = mysqli_fetch_assoc($accounts)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['account_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['ifsc_code']); ?></td>
                    <td><?php echo htmlspecialchars($row['account_date']); ?></td>
                    <td><?php echo number_format((float)$row['balance'], 2); ?></td>
                    <td>
                        <?php
                            if (strtolower($row['user_status']) === 'inactive') {
                                $displayStatus = 'Inactive';
                            } else {
                                $displayStatus = $row['account_status'];
                            }
                            $statusClass = strtolower($displayStatus);
                        ?>
                        <span class="status <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($displayStatus); ?>
                        </span>
                    </td>
                    <td>
                        <a class="btn-delete"
                           href="javascript:void(0);"
                           onclick="confirmDelete(<?php echo (int)$row['id']; ?>);">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <?php
                $paginationBaseUrl    = "index.php";
                $paginationPage       = $currentPage;
                $paginationTotalPages = $totalPages;
                $paginationParams     = [
                    'url'    => 'admin/index',
                    'search' => $search,
                ];
            
                require __DIR__ . '/../partials/pagination.php';
            ?>
        <?php endif; ?>

        <!-- GO BACK BUTTON WHEN SEARCH ACTIVE -->
        <?php if ($search !== ""): ?>
            <div class="back-box">
                <a href="index.php?url=admin/index" class="back-btn">Go Back</a>
            </div>
        <?php endif; ?>

        <?php else: ?>
            <p>No accounts found.</p>
        <?php endif; ?>

    </div>
</div>

<script>
function showAdminTab(tab) {
    var pending = document.getElementById('admin-tab-pending');
    var allTab  = document.getElementById('admin-tab-all');
    var btnP    = document.getElementById('btn-tab-pending');
    var btnA    = document.getElementById('btn-tab-all');

    if (tab === 'pending') {
        pending.style.display = 'block';
        allTab.style.display  = 'none';
        btnP.classList.add('active-tab');
        btnA.classList.remove('active-tab');
    } else {
        pending.style.display = 'none';
        allTab.style.display  = 'block';
        btnA.classList.add('active-tab');
        btnP.classList.remove('active-tab');
    }
}

function confirmDelete(accountId) {
    Swal.fire({
        title: "Are you sure?",
        text: "This will attempt to delete the account.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "index.php?url=admin/deleteAccount&id=" + accountId;
        }
    });
}
</script>
