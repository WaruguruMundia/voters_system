<?php
function getConnection() {
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $db_name = "voters_system";

    try {
        $conn = new PDO("mysql:host=$hostname;dbname=$db_name", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
        } catch(PDOException $e) {
        error_log($e->getMessage());
        die("Database connection failed: ");
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
