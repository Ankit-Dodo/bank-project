<?php
$host     = $_ENV['DB_HOST'] ?? "localhost";
$dbUser   = $_ENV['DB_USER'] ?? "phpmyadmin";          
$dbPass   = $ENV['DB_PASS'] ?? "Passw0rd!123";              
$dbName   = $ENV['DB_NAME'] ?? "bank_simulator";  

$conn = mysqli_connect($host, $dbUser, $dbPass, $dbName);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
