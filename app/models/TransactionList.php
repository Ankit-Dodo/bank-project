<?php

class TransactionList extends Model
{
    private function buildBaseFrom()
    {
        return "
            FROM `transaction` t
            JOIN account a ON t.account_id = a.id
            JOIN profile p ON a.profile_id = p.id
            JOIN users u ON p.user_id = u.id        -- account owner
            JOIN users pu ON t.performed_by = pu.id -- performer
        ";
    }

    /**
     * Build WHERE clause string based on filters.
     */
    private function buildWhereClause($isAdmin, $userId, $search, $filterType, $filterName, $filterFrom, $filterTo)
    {
        $where = $isAdmin ? "WHERE 1=1" : "WHERE u.id = " . (int)$userId;

        $searchEsc     = $search      !== "" ? mysqli_real_escape_string($this->db, $search)      : "";
        $filterNameEsc = $filterName  !== "" ? mysqli_real_escape_string($this->db, $filterName)  : "";
        $filterFromEsc = $filterFrom  !== "" ? mysqli_real_escape_string($this->db, $filterFrom)  : "";
        $filterToEsc   = $filterTo    !== "" ? mysqli_real_escape_string($this->db, $filterTo)    : "";

        // global search
        if ($searchEsc !== "") {
            $where .= " AND (
                p.full_name        LIKE '%$searchEsc%' OR
                p.phone           LIKE '%$searchEsc%' OR
                u.username        LIKE '%$searchEsc%' OR
                u.email           LIKE '%$searchEsc%' OR
                a.account_number  LIKE '%$searchEsc%' OR
                t.transaction_type LIKE '%$searchEsc%' OR
                t.status          LIKE '%$searchEsc%' OR
                pu.username       LIKE '%$searchEsc%' OR
                t.amount          LIKE '%$searchEsc%'
            )";
        }

        // filter by name
        if ($filterType === 'name' && $filterNameEsc !== '') {
            $where .= " AND p.full_name LIKE '%$filterNameEsc%'";
        }

        // filter by date range (transaction_date)
        if ($filterType === 'date') {
            if ($filterFromEsc !== '') {
                $where .= " AND t.transaction_date >= '{$filterFromEsc} 00:00:00'";
            }
            if ($filterToEsc !== '') {
                $where .= " AND t.transaction_date <= '{$filterToEsc} 23:59:59'";
            }
        }

        return [$where, $searchEsc, $filterNameEsc, $filterFromEsc, $filterToEsc];
    }

    /**
     * Count transactions for pagination.
     */
    public function countTransactions($isAdmin, $userId, $search, $filterType, $filterName, $filterFrom, $filterTo)
    {
        list($where) = $this->buildWhereClause(
            $isAdmin,
            $userId,
            $search,
            $filterType,
            $filterName,
            $filterFrom,
            $filterTo
        );

        $baseFrom = $this->buildBaseFrom();
        $sql = "SELECT COUNT(*) AS total " . $baseFrom . " " . $where;

        $res = mysqli_query($this->db, $sql);
        if (!$res) {
            return [0, mysqli_error($this->db)];
        }

        $row = mysqli_fetch_assoc($res);
        $total = (int)($row['total'] ?? 0);

        return [$total, null];
    }

    /**
     * Fetch paginated transactions.
     */
    public function getTransactions($isAdmin, $userId, $search, $sort, $filterType, $filterName, $filterFrom, $filterTo, $page, $perPage)
    {
        list($where) = $this->buildWhereClause(
            $isAdmin,
            $userId,
            $search,
            $filterType,
            $filterName,
            $filterFrom,
            $filterTo
        );

        // ORDER BY logic
        $orderBy = "ORDER BY t.transaction_date DESC";
        if ($sort === 'name_asc') {
            $orderBy = "ORDER BY p.full_name ASC, t.transaction_date DESC";
        } elseif ($sort === 'name_desc') {
            $orderBy = "ORDER BY p.full_name DESC, t.transaction_date DESC";
        }

        $offset = ($page - 1) * $perPage;
        $baseFrom = $this->buildBaseFrom();

        $sql = "
            SELECT
                t.id,
                t.account_id,
                t.transaction_type,
                t.amount,
                t.transaction_date,
                t.status,
                t.performed_by,
                a.account_number,
                p.full_name,
                u.username,
                pu.username AS performed_by_username
            " . $baseFrom . "
            " . $where . "
            $orderBy
            LIMIT $perPage OFFSET $offset
        ";

        $res = mysqli_query($this->db, $sql);
        if (!$res) {
            return [[], mysqli_error($this->db)];
        }

        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }

        return [$rows, null];
    }

    /**
     * Fetch ALL matching transactions for CSV export (no pagination).
     */
    public function getAllTransactions($isAdmin, $userId, $search, $sort, $filterType, $filterName, $filterFrom, $filterTo)
    {
        list($where) = $this->buildWhereClause(
            $isAdmin,
            $userId,
            $search,
            $filterType,
            $filterName,
            $filterFrom,
            $filterTo
        );

        // ORDER BY logic same as listing
        $orderBy = "ORDER BY t.transaction_date DESC";
        if ($sort === 'name_asc') {
            $orderBy = "ORDER BY p.full_name ASC, t.transaction_date DESC";
        } elseif ($sort === 'name_desc') {
            $orderBy = "ORDER BY p.full_name DESC, t.transaction_date DESC";
        }

        $baseFrom = $this->buildBaseFrom();

        $sql = "
            SELECT
                t.id,
                t.account_id,
                t.transaction_type,
                t.amount,
                t.transaction_date,
                t.status,
                t.performed_by,
                a.account_number,
                p.full_name,
                u.username,
                pu.username AS performed_by_username
            " . $baseFrom . "
            " . $where . "
            $orderBy
        ";

        $res = mysqli_query($this->db, $sql);
        if (!$res) {
            return [[], mysqli_error($this->db)];
        }

        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }

        return [$rows, null];
    }
}
