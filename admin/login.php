<?php
require_once '../config.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $conn = getConnection();
        $hashed = md5($password);
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $hashed);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Invalid username or password.';
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – Voters System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-body">

<div class="auth-card">
    <a href="../index.php" class="back-link">← Back to Home</a>

    <div class="auth-header">
        <div class="auth-icon">🛡️</div>
        <h1>Admin Login</h1>
        <p>Sign in to manage the voting system</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                placeholder="admin"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                autofocus
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary btn-full">Login</button>
    </form>

    <p class="hint-text">Default: admin / admin123</p>
</div>

</body>
</html>
