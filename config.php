<?php
// Database configuration
define('DB_HOST', '127.0.0.1:3307'); // Default XAMPP MySQL host and port
define('DB_USER', 'root');        // Default XAMPP MySQL user
define('DB_PASS', '');            // Default XAMPP MySQL password (empty)
define('DB_NAME', 'voters_system');

// Create connection
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("
        <div style='font-family:sans-serif;padding:40px;text-align:center;'>
            <h2 style='color:#c0392b;'>Database Connection Failed</h2>
            <p>" . $conn->connect_error . "</p>
            <p>Make sure XAMPP MySQL is running and the database is set up.</p>
        </div>
        ");
    }

    return $conn;
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
