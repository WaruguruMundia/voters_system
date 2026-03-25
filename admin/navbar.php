<nav class="admin-nav">
    <div class="nav-brand">🗳️ Admin Panel</div>
    <div class="nav-links">
        <a href="dashboard.php" <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'class="active"' : '' ?>>Dashboard</a>
        <a href="voters.php"    <?= basename($_SERVER['PHP_SELF']) === 'voters.php'    ? 'class="active"' : '' ?>>Voters</a>
        <a href="candidates.php" <?= basename($_SERVER['PHP_SELF']) === 'candidates.php' ? 'class="active"' : '' ?>>Candidates</a>
        <a href="logout.php" class="nav-logout">Logout (<?= htmlspecialchars($_SESSION['admin_user']) ?>)</a>
    </div>
</nav>
