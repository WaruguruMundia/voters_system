<?php
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['voter_id'])) {
    header("Location: vote.php");
    exit();
}
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voters System – Home</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="landing-body">

<div class="landing-wrapper">
    <div class="landing-hero">
        <div class="logo-icon">🗳️</div>
        <h1>Voters System</h1>
        <p class="tagline">A secure and simple electronic voting platform</p>
    </div>

    <div class="landing-cards">
        <a href="voter_login.php" class="card card-voter">
            <div class="card-icon">👤</div>
            <h2>Voter Login</h2>
            <p>Cast your vote using your Voter ID</p>
            <span class="card-btn">Login to Vote →</span>
        </a>

        <a href="admin/login.php" class="card card-admin">
            <div class="card-icon">🛡️</div>
            <h2>Admin Panel</h2>
            <p>Manage voters, candidates, and results</p>
            <span class="card-btn">Admin Login →</span>
        </a>
    </div>

    <footer class="landing-footer">
        <p>COMP 440 – Group Project &nbsp;|&nbsp; Voters System</p>
    </footer>
</div>

</body>
</html>
