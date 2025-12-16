<?php

class Account extends Model
{
    /* 
       SYSTEM / FINE HELPERS
     */

    private function getSystemAccountId()
    {
        $res = mysqli_query(
            $this->db,
            "SELECT id FROM account WHERE account_type = 'system' LIMIT 1"
        );
        if (!$res || mysqli_num_rows($res) === 0) return null;
        return (int) mysqli_fetch_assoc($res)['id'];
    }

    // ✅ BACKWARD-COMPATIBLE
    public function applyFine($fromAccountId, $fineAmount, $performedBy = 0)
    {
        $fineAmount = round((float)$fineAmount, 2);
        if ($fineAmount <= 0) return true;

        $systemId = $this->getSystemAccountId();
        if (!$systemId) return false;

        mysqli_query(
            $this->db,
            "UPDATE account SET balance = balance - $fineAmount WHERE id = $fromAccountId"
        );

        mysqli_query(
            $this->db,
            "UPDATE account SET balance = balance + $fineAmount WHERE id = $systemId"
        );

        mysqli_query(
            $this->db,
            "INSERT INTO transaction
             (account_id, transaction_type, amount, transaction_date, performed_by, status)
             VALUES ($fromAccountId, 'fine', $fineAmount, NOW(), $performedBy, 'completed')"
        );

        return true;
    }

    /* 
       VALIDATION
     */

    public function requireActive($accountId)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $acc = $this->findById((int)$accountId);
        if (!$acc) {
            $_SESSION['error'] = "Account not found.";
            return false;
        }

        if ($acc['status'] !== 'Active') {
            $_SESSION['error'] =
                "Account {$acc['account_number']} is not active.";
            return false;
        }

        return $acc;
    }

    public function requireActiveBoth($fromAccountId, $toAccountId)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $from = $this->findById((int)$fromAccountId);
        $to   = $this->findById((int)$toAccountId);

        if (!$from || $from['status'] !== 'Active') {
            $_SESSION['error'] = "Sender account not active.";
            return false;
        }

        if (!$to || $to['status'] !== 'Active') {
            $_SESSION['error'] = "Recipient account not active.";
            return false;
        }

        return ['from' => $from, 'to' => $to];
    }

    /* 
       FETCH METHODS
     */

    public function getAccountsByUserId($userId)
    {
        $userId = (int)$userId;

        $res = mysqli_query(
            $this->db,
            "SELECT a.*, p.user_id, p.full_name
             FROM account a
             JOIN profile p ON p.id = a.profile_id
             WHERE p.user_id = $userId
             ORDER BY a.id ASC"
        );

        if (!$res) return [];

        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        return $rows;
    }

    public function getByUser($userId)
    {
        return $this->getAccountsByUserId($userId);
    }

    public function getAllAccountsList()
    {
        $res = mysqli_query(
            $this->db,
            "SELECT a.*, p.user_id, p.full_name
             FROM account a
             JOIN profile p ON p.id = a.profile_id
             WHERE a.status = 'Active'
             ORDER BY a.account_number ASC"
        );

        if (!$res) return [];

        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        return $rows;
    }

    public function findById($id)
    {
        $id = (int)$id;
        $res = mysqli_query(
            $this->db,
            "SELECT a.*, p.user_id, p.full_name
             FROM account a
             JOIN profile p ON p.id = a.profile_id
             WHERE a.id = $id
             LIMIT 1"
        );

        return ($res && mysqli_num_rows($res) === 1)
            ? mysqli_fetch_assoc($res)
            : null;
    }

    /* 
       DEPOSIT
     */

    public function depositToAccount($accountId, $amount, $performedBy)
    {
        $acc = $this->requireActive($accountId);
        if (!$acc || $amount <= 0) return false;

        mysqli_query(
            $this->db,
            "UPDATE account SET balance = balance + $amount WHERE id = $accountId"
        );

        mysqli_query(
            $this->db,
            "INSERT INTO transaction
             (account_id, transaction_type, amount, transaction_date, performed_by, status)
             VALUES ($accountId, 'deposit', $amount, NOW(), $performedBy, 'completed')"
        );

        return true;
    }

    /* 
       WITHDRAW (0 LIMIT + FINE)
     */

    public function withdrawFromAccount($accountId, $amount, $performedBy)
    {
        $acc = $this->requireActive($accountId);
        if (!$acc) return false;

        $amount  = round((float)$amount, 2);
        $balance = (float)$acc['balance'];
        $minBal  = (float)$acc['min_balance'];

        if ($amount <= 0 || ($balance - $amount) < 0) {
            $_SESSION['error'] = "Insufficient balance.";
            return false;
        }

        mysqli_begin_transaction($this->db);
        try {
            mysqli_query(
                $this->db,
                "UPDATE account SET balance = balance - $amount WHERE id = $accountId"
            );

            mysqli_query(
                $this->db,
                "INSERT INTO transaction
                 (account_id, transaction_type, amount, transaction_date, performed_by, status)
                 VALUES ($accountId, 'withdraw', $amount, NOW(), $performedBy, 'completed')"
            );

            if (($balance - $amount) < $minBal) {
                $this->applyFine($accountId, $amount * 0.01, $performedBy);
            }

            mysqli_commit($this->db);
            return true;
        } catch (Exception $e) {
            mysqli_rollback($this->db);
            $_SESSION['error'] = $e->getMessage();
            return false;
        }
    }

    /* 
       TRANSFER (0 LIMIT + FINE)
     */

    public function transferBetweenAccounts($fromAccountId, $toAccountId, $amount, $performedBy)
    {
        $pair = $this->requireActiveBoth($fromAccountId, $toAccountId);
        if (!$pair) return false;

        $amount  = round((float)$amount, 2);
        $fromBal = (float)$pair['from']['balance'];
        $minBal  = (float)$pair['from']['min_balance'];

        if ($amount <= 0 || ($fromBal - $amount) < 0) {
            $_SESSION['error'] = "Insufficient balance.";
            return false;
        }

        mysqli_begin_transaction($this->db);
        try {
            mysqli_query(
                $this->db,
                "UPDATE account SET balance = balance - $amount WHERE id = $fromAccountId"
            );

            mysqli_query(
                $this->db,
                "UPDATE account SET balance = balance + $amount WHERE id = $toAccountId"
            );

            mysqli_query(
                $this->db,
                "INSERT INTO transaction
                 (account_id, transaction_type, amount, transaction_date, performed_by, status)
                 VALUES ($fromAccountId, 'transfer', $amount, NOW(), $performedBy, 'completed')"
            );

            mysqli_query(
                $this->db,
                "INSERT INTO transaction
                 (account_id, transaction_type, amount, transaction_date, performed_by, status)
                 VALUES ($toAccountId, 'transfer', $amount, NOW(), $performedBy, 'completed')"
            );

            if (($fromBal - $amount) < $minBal) {
                $this->applyFine($fromAccountId, $amount * 0.01, $performedBy);
            }

            mysqli_commit($this->db);
            return true;
        } catch (Exception $e) {
            mysqli_rollback($this->db);
            $_SESSION['error'] = $e->getMessage();
            return false;
        }
    }

    /* 
       ACCOUNT CREATION
     */

    public function createPendingAccountForUser($userId, $accountType, $minBalance)
    {
        $userId     = (int)$userId;
        $minBalance = (float)$minBalance;
        $typeEsc    = mysqli_real_escape_string($this->db, $accountType);

        $res = mysqli_query(
            $this->db,
            "SELECT id FROM profile WHERE user_id = $userId LIMIT 1"
        );
        if (!$res || mysqli_num_rows($res) === 0) return false;

        $profileId = (int) mysqli_fetch_assoc($res)['id'];

        do {
            $accNo = mt_rand(1000000000, 9999999999);
            $chk = mysqli_query(
                $this->db,
                "SELECT id FROM account WHERE account_number = $accNo LIMIT 1"
            );
        } while ($chk && mysqli_num_rows($chk) > 0);

        return mysqli_query(
            $this->db,
            "INSERT INTO account
             (profile_id, account_type, account_number, balance, min_balance, status, ifsc_code, account_date)
             VALUES
             ($profileId, '$typeEsc', $accNo, 0, $minBalance, 'Pending', 'INDB0000323', NOW())"
        );
    }
}
