<?php
$host     = "localhost";
$dbUser   = "phpmyadmin";          
$dbPass   = "Passw0rd!123";              
$dbName   = "bank_simulator";  

$conn = mysqli_connect($host, $dbUser, $dbPass, $dbName);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
