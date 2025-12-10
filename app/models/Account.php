<?php

class Account extends Model
{
    // ---------- NEW: check if user has any account ----------
    public function userHasAnyAccount($userId)
    {
        $userId = (int)$userId;

        $sql = "
            SELECT COUNT(*) AS cnt
            FROM account a
            INNER JOIN profile p ON p.id = a.profile_id
            WHERE p.user_id = $userId
        ";

        $res = mysqli_query($this->db, $sql);
        if (!$res) {
            // on error, treat as no accounts (forces user to profile first)
            return false;
        }

        $row   = mysqli_fetch_assoc($res);
        $count = (int)($row['cnt'] ?? 0);

        return $count > 0;
    }
    // account update
    public function getAccountsByUserId($userId)
    {
        $userId = (int)$userId;

        $sql = "SELECT * FROM account WHERE user_id = $userId";
        $res = mysqli_query($this->db, $sql);

        return $res; // you can loop in the view
    }

    public function userHasDeclinedAccount($userId)
    {
        $userId = (int)$userId;

        $sql = "SELECT id FROM account
                WHERE user_id = $userId
                  AND status = 'Declined'
                LIMIT 1";

        $res = mysqli_query($this->db, $sql);

        return $res && mysqli_num_rows($res) > 0;
    }


    // All accounts of a given user (by users.id)
    public function getByUser($userId)
    {
        $userId = (int)$userId;

        $sql = "
            SELECT a.*, p.user_id, p.full_name
            FROM account a
            INNER JOIN profile p ON p.id = a.profile_id
            WHERE p.user_id = $userId
            ORDER BY a.id ASC
        ";

        $res = mysqli_query($this->db, $sql);
        if (!$res) {
            return [];
        }

        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
        return $rows;
    }

    // Find an account by id, include user_id via profile join
    public function findById($id)
    {
        $id = (int)$id;
        $sql = "
            SELECT a.*, p.user_id, p.full_name
            FROM account a
            INNER JOIN profile p ON p.id = a.profile_id
            WHERE a.id = $id
            LIMIT 1
        ";

        $res = mysqli_query($this->db, $sql);
        if (!$res || mysqli_num_rows($res) === 0) {
            return null;
        }

        return mysqli_fetch_assoc($res);
    }

    // Find active account by account_number
    public function findByAccountNumber($accountNumber)
    {
        $accountNumberEsc = mysqli_real_escape_string($this->db, $accountNumber);

        $sql = "
            SELECT a.*, p.user_id
            FROM account a
            INNER JOIN profile p ON p.id = a.profile_id
            WHERE a.account_number = '$accountNumberEsc'
              AND a.status = 'Active'
            LIMIT 1
        ";

        $res = mysqli_query($this->db, $sql);
        if (!$res || mysqli_num_rows($res) === 0) {
            return null;
        }

        return mysqli_fetch_assoc($res);
    }

    // Any-status account lookup (your old comment says "any" but query still uses Active)
    public function findByAccountNumberAnyStatus($accountNumber)
    {
        $accountNumberEsc = mysqli_real_escape_string($this->db, $accountNumber);

        $sql = "
            SELECT a.*, p.user_id
            FROM account a
            INNER JOIN profile p ON p.id = a.profile_id
            WHERE a.account_number = '$accountNumberEsc'
              AND a.status = 'Active'
            LIMIT 1
        ";

        $res = mysqli_query($this->db, $sql);
        if (!$res || mysqli_num_rows($res) === 0) {
            return null;
        }

        return mysqli_fetch_assoc($res);
    }

    // Deposit money
    public function depositToAccount($accountId, $amount, $performedBy)
    {
        $accountId   = (int)$accountId;
        $amount      = (float)$amount;
        $performedBy = (int)$performedBy;

        $sql = "
            UPDATE account
            SET balance = balance + $amount
            WHERE id = $accountId
            LIMIT 1
        ";

        $res = mysqli_query($this->db, $sql);
        if (!$res) {
            return false;
        }

        $sql2 = "
            INSERT INTO transaction (account_id, transaction_type, amount, transaction_date, performed_by, status)
            VALUES ($accountId, 'deposit', $amount, NOW(), $performedBy, 'completed')
        ";

        $res2 = mysqli_query($this->db, $sql2);
        if (!$res2) {
            return false;
        }

        return true;
    }

    // Withdraw money
    public function withdrawFromAccount($accountId, $amount, $performedBy)
    {
        $accountId   = (int)$accountId;
        $amount      = (float)$amount;
        $performedBy = (int)$performedBy;

        $sql = "
            UPDATE account
            SET balance = balance - $amount
            WHERE id = $accountId
            LIMIT 1
        ";

        $res = mysqli_query($this->db, $sql);
        if (!$res) {
            return false;
        }

        $sql2 = "
            INSERT INTO transaction (account_id, transaction_type, amount, transaction_date, performed_by, status)
            VALUES ($accountId, 'withdraw', $amount, NOW(), $performedBy, 'completed')
        ";

        $res2 = mysqli_query($this->db, $sql2);
        if (!$res2) {
            return false;
        }

        return true;
    }

    // Transfer between two accounts
    public function transferBetweenAccounts($fromAccountId, $toAccountId, $amount, $performedBy)
    {
        $fromAccountId = (int)$fromAccountId;
        $toAccountId   = (int)$toAccountId;
        $amount        = (float)$amount;
        $performedBy   = (int)$performedBy;

        // subtract from source
        $sql1 = "
            UPDATE account
            SET balance = balance - $amount
            WHERE id = $fromAccountId
            LIMIT 1
        ";
        $res1 = mysqli_query($this->db, $sql1);
        if (!$res1) {
            return false;
        }

        // add to destination
        $sql2 = "
            UPDATE account
            SET balance = balance + $amount
            WHERE id = $toAccountId
            LIMIT 1
        ";
        $res2 = mysqli_query($this->db, $sql2);
        if (!$res2) {
            return false;
        }

        // transaction row for source
        $sql3 = "
            INSERT INTO transaction (account_id, transaction_type, amount, transaction_date, performed_by, status)
            VALUES ($fromAccountId, 'transfer', $amount, NOW(), $performedBy, 'completed')
        ";
        $res3 = mysqli_query($this->db, $sql3);
        if (!$res3) {
            return false;
        }

        // transaction row for destination
        $sql4 = "
            INSERT INTO transaction (account_id, transaction_type, amount, transaction_date, performed_by, status)
            VALUES ($toAccountId, 'transfer', $amount, NOW(), $performedBy, 'completed')
        ";
        $res4 = mysqli_query($this->db, $sql4);
        if (!$res4) {
            return false;
        }

        return true;
    }

    // All active accounts for dropdowns
    public function getAllAccountsList()
    {
        $sql = "
            SELECT a.*, p.user_id, p.full_name
            FROM account a
            INNER JOIN profile p ON p.id = a.profile_id
            WHERE a.status = 'Active'
            ORDER BY a.account_number ASC
        ";

        $res = mysqli_query($this->db, $sql);
        if (!$res) {
            return [];
        }

        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }

        return $rows;
    }

    // Create pending account for logged-in user
    public function createPendingAccountForUser($userId, $accountType, $minBalance)
    {
        $userId         = (int)$userId;
        $minBalance     = (float)$minBalance;
        $accountTypeEsc = mysqli_real_escape_string($this->db, $accountType);

        // find profile for this user
        $sqlProfile = "SELECT id FROM profile WHERE user_id = $userId LIMIT 1";
        $resProfile = mysqli_query($this->db, $sqlProfile);
        if (!$resProfile || mysqli_num_rows($resProfile) === 0) {
            return false;
        }
        $profileRow = mysqli_fetch_assoc($resProfile);
        $profileId  = (int)$profileRow['id'];

        // generate unique 10-digit account number
        $accountNumber = 0;
        for ($i = 0; $i < 5; $i++) {
            $candidate = mt_rand(1000000000, 9999999999);
            $sqlCheck  = "SELECT id FROM account WHERE account_number = $candidate LIMIT 1";
            $resCheck  = mysqli_query($this->db, $sqlCheck);
            if ($resCheck && mysqli_num_rows($resCheck) === 0) {
                $accountNumber = $candidate;
                break;
            }
        }
        if (!$accountNumber) {
            return false;
        }

        $ifsc = 'INDB0000323';

        $sqlInsert = "
            INSERT INTO account
                (profile_id, account_type, account_number, balance, min_balance, status, ifsc_code, account_date)
            VALUES
                ($profileId, '$accountTypeEsc', $accountNumber, 0, $minBalance, 'Pending', '$ifsc', NOW())
        ";

        $resInsert = mysqli_query($this->db, $sqlInsert);
        if (!$resInsert) {
            return false;
        }

        return true;
    }
}
