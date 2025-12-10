<?php

class User extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function findById($id)
    {
        $id = (int)$id;

        $sql = "SELECT * FROM users WHERE id = $id LIMIT 1";
        $result = mysqli_query($this->db, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            return mysqli_fetch_assoc($result);
        }

        return null;
    }

    public function findByEmail($email)
    {
        $emailEsc = mysqli_real_escape_string($this->db, $email);

        $sql = "SELECT * FROM users WHERE email = '$emailEsc' LIMIT 1";
        $result = mysqli_query($this->db, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            return mysqli_fetch_assoc($result);
        }

        return null;
    }

    public function emailExists($email)
    {
        $emailEsc = mysqli_real_escape_string($this->db, $email);

        $sql = "SELECT id FROM users WHERE email = '$emailEsc' LIMIT 1";
        $result = mysqli_query($this->db, $sql);

        return ($result && mysqli_num_rows($result) > 0);
    }

    public function createUser($username, $email, $password)
    {
        $usernameEsc = mysqli_real_escape_string($this->db, $username);
        $emailEsc    = mysqli_real_escape_string($this->db, $email);

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $passwordEsc  = mysqli_real_escape_string($this->db, $passwordHash);

        $sql = "
            INSERT INTO users (username, email, password_hash, role, status)
            VALUES ('$usernameEsc', '$emailEsc', '$passwordEsc', 'customer', 'active')
        ";

        $result = mysqli_query($this->db, $sql);

        if (!$result) {
            die("Create user failed: " . mysqli_error($this->db));
        }

        return true;
    }

    public function updateLastLogin($id)
    {
        $id = (int)$id;

        $sql = "UPDATE users SET last_login = NOW() WHERE id = $id";
        $result = mysqli_query($this->db, $sql);

        if (!$result) {
            die("Update last login failed: " . mysqli_error($this->db));
        }
    }
    public function getAllNonAdminUsers()
    {
        $sql = "
            SELECT id, username, role
            FROM users
            WHERE role <> 'admin'
            ORDER BY username ASC
        ";
    
        $res = mysqli_query($this->db, $sql);
        if (!$res) {
            return array();
        }
    
        $rows = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
        return $rows;
    }

}
