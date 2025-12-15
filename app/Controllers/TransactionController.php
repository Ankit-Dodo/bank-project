<?php

class TransactionController extends Controller
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }

        $userId = (int)$_SESSION['user_id'];

        // load user to check role
        $userModel = $this->model("User");
        $user      = $userModel->findById($userId);

        if (!$user) {
            die("User not found.");
        }

        $isAdmin = (strtolower($user['role'] ?? '') === 'admin');

        // GET params
        $search     = isset($_GET['search']) ? trim($_GET['search']) : "";
        $sort       = $_GET['sort'] ?? '';
        $filterType = $_GET['filter_type'] ?? '';
        $filterName = isset($_GET['filter_name']) ? trim($_GET['filter_name']) : '';
        $filterFrom = $_GET['filter_from'] ?? '';
        $filterTo   = $_GET['filter_to'] ?? '';
        $export     = $_GET['export'] ?? '';

        // validate sort
        $allowedSort = ['name_asc', 'name_desc'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = '';
        }

        // validate filterType
        $allowedFilterType = ['date', 'name'];
        if (!in_array($filterType, $allowedFilterType, true)) {
            $filterType = '';
        }

        $transactionModel = $this->model("TransactionList");

        // Export to CSV
        if ($export === 'csv') {

            list($allRows, $exportError) = $transactionModel->getAllTransactions(
                $isAdmin,
                $userId,
                $search,
                $sort,
                $filterType,
                $filterName,
                $filterFrom,
                $filterTo
            );

            if ($exportError !== null) {
                // simple error dump for now
                header('Content-Type: text/plain; charset=utf-8');
                echo "Failed to export CSV. SQL Error: " . $exportError;
                exit;
            }

            // send CSV headers
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="transactions_' . date('Ymd_His') . '.csv"');

            $out = fopen('php://output', 'w');

            // header row
            fputcsv($out, [
                'ID',
                'Account Number',
                'Account Holder',
                'Username',
                'Amount',
                'Type',
                'Status',
                'Date & Time',
                'Performed By'
            ]);

            foreach ($allRows as $r) {
                fputcsv($out, [
                    $r['id'],
                    $r['account_number'],
                    $r['full_name'],
                    $r['username'],
                    $r['amount'],
                    $r['transaction_type'],
                    $r['status'],
                    $r['transaction_date'],
                    $r['performed_by_username']
                ]);
            }

            fclose($out);
            exit;
        }
        /*  END EXPORT CSV BRANCH  */

        // pagination
        $perPage = 25;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0
            ? (int)$_GET['page']
            : 1;

        // total count
        list($totalRows, $countError) = $transactionModel->countTransactions(
            $isAdmin,
            $userId,
            $search,
            $filterType,
            $filterName,
            $filterFrom,
            $filterTo
        );

        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $rows = [];
        $queryError = $countError;

        if ($queryError === null && $totalRows > 0) {
            list($rows, $queryError) = $transactionModel->getTransactions(
                $isAdmin,
                $userId,
                $search,
                $sort,
                $filterType,
                $filterName,
                $filterFrom,
                $filterTo,
                $page,
                $perPage
            );
        }

        $this->view("transaction/index", [
            "title"       => $isAdmin ? "All Transactions" : "Your Transactions",
            "user"        => $user,
            "isAdmin"     => $isAdmin,
            "rows"        => $rows,
            "search"      => $search,
            "sort"        => $sort,
            "filterType"  => $filterType,
            "filterName"  => $filterName,
            "filterFrom"  => $filterFrom,
            "filterTo"    => $filterTo,
            "page"        => $page,
            "perPage"     => $perPage,
            "totalRows"   => $totalRows,
            "totalPages"  => $totalPages,
            "queryError"  => $queryError
        ]);
    }
}
