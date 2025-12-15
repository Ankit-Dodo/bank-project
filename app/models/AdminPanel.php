<?php

class AdminPanel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /* DASHBOARD STATS */

    public function getStatistics()
    {
        $data = [
            'total_accounts'   => 0,
            'active_accounts'  => 0,
            'pending_accounts' => 0,
            'total_money'      => 0,
            'total_users'      => 0,
        ];

        // total accounts
        $sql = "SELECT COUNT(*) AS total FROM account";
        $res = mysqli_query($this->db, $sql);
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            $data['total_accounts'] = (int)($row['total'] ?? 0);
        }

        // active accounts
        $sql = "SELECT COUNT(*) AS total_active FROM account WHERE status='Active'";
        $res = mysqli_query($this->db, $sql);
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            $data['active_accounts'] = (int)($row['total_active'] ?? 0);
        }

        // pending accounts
        $sql = "SELECT COUNT(*) AS total_pending FROM account WHERE status='Pending'";
        $res = mysqli_query($this->db, $sql);
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            $data['pending_accounts'] = (int)($row['total_pending'] ?? 0);
        }

        // total money in all accounts
        $sql = "SELECT SUM(balance) AS total_money FROM account";
        $res = mysqli_query($this->db, $sql);
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            $data['total_money'] = (float)($row['total_money'] ?? 0);
        }

        // total users
        $sql = "SELECT COUNT(*) AS total_users FROM users";
        $res = mysqli_query($this->db, $sql);
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            $data['total_users'] = (int)($row['total_users'] ?? 0);
        }

        return $data;
    }

    /*PENDING ACCOUNTS (RESULT RESOURCE FOR VIEW)*/

    public function getPendingAccounts()
    {
        $sql = "
            SELECT
                a.id,
                a.account_number,
                a.account_type,
                a.ifsc_code,
                a.account_date,
                a.balance,
                a.status AS account_status,

                p.full_name,
                p.phone,

                u.username,
                u.email,
                u.status AS user_status
            FROM account a
            INNER JOIN profile p ON p.id = a.profile_id
            INNER JOIN users  u   ON p.user_id = u.id
            WHERE a.status = 'Pending'
            ORDER BY a.account_date DESC, a.id DESC
        ";

        $res = mysqli_query($this->db, $sql);
        if (!$res) {
            die("Error loading pending accounts: " . mysqli_error($this->db));
        }

        // IMPORTANT: return mysqli_result (not array) because view uses mysqli_num_rows / mysqli_fetch_assoc
        return $res;
    }

    /*ALL ACCOUNTS LIST WITH SEARCH + PAGINATION*/

    public function countPages($search, $perPage)
    {
        $searchEsc = $search !== "" ? mysqli_real_escape_string($this->db, $search) : "";

        $where = "WHERE 1=1";

        if ($searchEsc !== "") {
            $where .= " AND (
                p.full_name      LIKE '%$searchEsc%' OR
                u.username       LIKE '%$searchEsc%' OR
                u.email          LIKE '%$searchEsc%' OR
                p.phone          LIKE '%$searchEsc%' OR
                a.account_number LIKE '%$searchEsc%' OR
                a.account_type   LIKE '%$searchEsc%' OR
                a.status         LIKE '%$searchEsc%'
            )";
        }

        $sql = "
            SELECT COUNT(*) AS total
            FROM account a
            INNER JOIN profile p ON p.id = a.profile_id
            INNER JOIN users  u   ON p.user_id = u.id
            $where
        ";

        $res = mysqli_query($this->db, $sql);
        if (!$res) {
            // at least 1 page so pagination doesn't break
            return 1;
        }

        $row   = mysqli_fetch_assoc($res);
        $total = (int)($row['total'] ?? 0);

        return max(1, (int)ceil($total / $perPage));
    }

    public function getAccounts($search, $page, $perPage)
    {
        $searchEsc = $search !== "" ? mysqli_real_escape_string($this->db, $search) : "";

        $where = "WHERE 1=1";

        if ($searchEsc !== "") {
            $where .= " AND (
                p.full_name      LIKE '%$searchEsc%' OR
                u.username       LIKE '%$searchEsc%' OR
                u.email          LIKE '%$searchEsc%' OR
                p.phone          LIKE '%$searchEsc%' OR
                a.account_number LIKE '%$searchEsc%' OR
                a.account_type   LIKE '%$searchEsc%' OR
                a.status         LIKE '%$searchEsc%'
            )";
        }

        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT
                a.id,
                a.account_number,
                a.account_type,
                a.ifsc_code,
                a.account_date,
                a.balance,
                a.status AS account_status,

                p.full_name,
                p.phone,

                u.username,
                u.email,
                u.status AS user_status
            FROM account a
            INNER JOIN profile p ON p.id = a.profile_id
            INNER JOIN users  u   ON p.user_id = u.id
            $where
            ORDER BY a.account_date DESC, a.id DESC
            LIMIT $perPage OFFSET $offset
        ";

        $res = mysqli_query($this->db, $sql);
        if (!$res) {
            die("Error loading accounts: " . mysqli_error($this->db));
        }

        // again: return mysqli_result, view will use mysqli_num_rows / mysqli_fetch_assoc
        return $res;
    }
    // account 
    public function getAccountById($accountId)
    {
        $accountId = (int)$accountId;

        $sql = "SELECT * FROM account WHERE id = $accountId LIMIT 1";
        $res = mysqli_query($this->db, $sql);

        if (!$res || mysqli_num_rows($res) === 0) {
            return null;
        }

        return mysqli_fetch_assoc($res);
    }


    /*ADMIN ACTIONS: APPROVE / DECLINE / DELETE*/

    public function approveAccount($accountId)
    {
        $accountId = (int)$accountId;

        $sql = "UPDATE account
                SET status = 'Active'
                WHERE id = $accountId
                LIMIT 1";

        $res = mysqli_query($this->db, $sql);

        return $res && mysqli_affected_rows($this->db) === 1;
    }
    // declined account
    public function declineAccount($accountId)
    {
        $accountId = (int)$accountId;

        $sql = "UPDATE account
                SET status = 'Declined'
                WHERE id = $accountId
                LIMIT 1";

        $res = mysqli_query($this->db, $sql);

        return $res && mysqli_affected_rows($this->db) === 1;
    }


    public function deleteAccountAndTransactions($accountId)
    {
        $accountId = (int)$accountId;

        // Delete related transactions first (table name: `transaction`)
        $sqlTrans = "DELETE FROM transaction WHERE account_id = $accountId";
        mysqli_query($this->db, $sqlTrans);

        // Delete account
        $sqlAcc = "DELETE FROM account WHERE id = $accountId LIMIT 1";
        $resAcc = mysqli_query($this->db, $sqlAcc);

        return (bool)$resAcc;
    }
}
