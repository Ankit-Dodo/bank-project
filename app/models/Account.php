<?php

class Account extends Model
{
    /* 
       BANK LEDGER / FINE HELPERS
     */

    // Get system (bank ledger) account id
    public function getSystemAccountId()
    {
        $sql = "SELECT id FROM account WHERE account_type = 'system' LIMIT 1";
        $res = mysqli_query($this->db, $sql);
        if (!$res || mysqli_num_rows($res) === 0) {
            return null;
        }
        $row = mysqli_fetch_assoc($res);
        return (int)$row['id'];
    }

    // Apply fine: debit user account & credit system account
    public function applyFine($fromAccountId, $fineAmount)
    {
        $fromAccountId = (int)$fromAccountId;
        $fineAmount    = (float)$fineAmount;

        if ($fineAmount <= 0) return false;

        $systemAccountId = $this->getSystemAccountId();
        if (!$systemAccountId) return false;

        // debit user
        mysqli_query(
            $this->db,
            "UPDATE account
             SET balance = balance - $fineAmount
             WHERE id = $fromAccountId
             LIMIT 1"
        );

        // credit system (bank ledger)
        mysqli_query(
            $this->db,
            "UPDATE account
             SET balance = balance + $fineAmount
             WHERE id = $systemAccountId
             LIMIT 1"
        );

        // record fine transaction (optional but recommended)
        mysqli_query(
            $this->db,
            "INSERT INTO transaction
             (account_id, transaction_type, amount, transaction_date, performed_by, status)
             VALUES ($fromAccountId, 'fine', $fineAmount, NOW(), 0, 'completed')"
        );

        return true;
    }

    /* 
       EXISTING CODE (UNCHANGED)
     */

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
            return false;
        }

        $row   = mysqli_fetch_assoc($res);
        $count = (int)($row['cnt'] ?? 0);

        return $count > 0;
    }

    public function requireActive($accountId)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $accountId = (int)$accountId;
        $acc = $this->findById($accountId);

        if (!$acc) {
            $_SESSION['error'] = "Account not found.";
            return false;
        }

        if (!isset($acc['status']) || $acc['status'] !== 'Active') {
            $_SESSION['error'] = "Account #{$acc['account_number']} is not active.";
            return false;
        }

        return $acc;
    }

    public function requireActiveBoth($fromAccountId, $toAccountIdentifier)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $from = $this->findById((int)$fromAccountId);
        if (!$from || $from['status'] !== 'Active') {
            $_SESSION['error'] = "Sender account not active.";
            return false;
        }

        $to = is_numeric($toAccountIdentifier)
            ? $this->findById((int)$toAccountIdentifier)
            : $this->findByAccountNumberAnyStatus($toAccountIdentifier);

        if (!$to || $to['status'] !== 'Active') {
            $_SESSION['error'] = "Recipient account not active.";
            return false;
        }

        return ['from' => $from, 'to' => $to];
    }

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
        if (!$res) return [];

        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
        return $rows;
    }

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
        if (!$res || mysqli_num_rows($res) === 0) return null;

        return mysqli_fetch_assoc($res);
    }

    public function findByAccountNumberAnyStatus($accountNumber)
    {
        $accountNumberEsc = mysqli_real_escape_string($this->db, $accountNumber);

        $sql = "
            SELECT a.*, p.user_id
            FROM account a
            INNER JOIN profile p ON p.id = a.profile_id
            WHERE a.account_number = '$accountNumberEsc'
            LIMIT 1
        ";

        $res = mysqli_query($this->db, $sql);
        if (!$res || mysqli_num_rows($res) === 0) return null;

        return mysqli_fetch_assoc($res);
    }

    public function depositToAccount($accountId, $amount, $performedBy)
    {
        $acc = $this->requireActive($accountId);
        if ($acc === false) return false;

        $accountId = (int)$accountId;
        $amount    = (float)$amount;

        mysqli_query($this->db,
            "UPDATE account SET balance = balance + $amount WHERE id = $accountId"
        );

        mysqli_query($this->db,
            "INSERT INTO transaction
             (account_id, transaction_type, amount, transaction_date, performed_by, status)
             VALUES ($accountId, 'deposit', $amount, NOW(), $performedBy, 'completed')"
        );

        return true;
    }

    public function withdrawFromAccount($accountId, $amount, $performedBy)
    {
        $acc = $this->requireActive($accountId);
        if ($acc === false) return false;

        $accountId = (int)$accountId;
        $amount    = (float)$amount;

        mysqli_query($this->db,
            "UPDATE account SET balance = balance - $amount WHERE id = $accountId"
        );

        mysqli_query($this->db,
            "INSERT INTO transaction
             (account_id, transaction_type, amount, transaction_date, performed_by, status)
             VALUES ($accountId, 'withdraw', $amount, NOW(), $performedBy, 'completed')"
        );

        return true;
    }

    public function transferBetweenAccounts($fromAccountId, $toAccountId, $amount, $performedBy)
    {
        $pair = $this->requireActiveBoth($fromAccountId, $toAccountId);
        if ($pair === false) return false;

        mysqli_query($this->db,
            "UPDATE account SET balance = balance - $amount WHERE id = $fromAccountId"
        );

        mysqli_query($this->db,
            "UPDATE account SET balance = balance + $amount WHERE id = $toAccountId"
        );

        mysqli_query($this->db,
            "INSERT INTO transaction
             (account_id, transaction_type, amount, transaction_date, performed_by, status)
             VALUES ($fromAccountId, 'transfer', $amount, NOW(), $performedBy, 'completed')"
        );

        mysqli_query($this->db,
            "INSERT INTO transaction
             (account_id, transaction_type, amount, transaction_date, performed_by, status)
             VALUES ($toAccountId, 'transfer', $amount, NOW(), $performedBy, 'completed')"
        );

        return true;
    }

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
        if (!$res) return [];

        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
        return $rows;
    }
}
