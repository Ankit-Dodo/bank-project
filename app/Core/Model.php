<?php
namespace App\Core;

class Model
{
    protected $db; // we will use this in all models

    public function __construct()
    {
        // include db config, which creates $conn
        require APP_ROOT . "/config/db.php";

        if (!isset($conn) || !$conn) {
            die("Database connection not available.");
        }

        // save mysqli connection in $this->db
        $this->db = $conn;
    }
}
