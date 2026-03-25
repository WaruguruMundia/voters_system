<?php
require_once 'config.php';

if (isset($_SESSION['voter_id'])) {
    header("Location: vote.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voter_id = trim($_POST['voter_id'] ?? '');

    if (empty($voter_id)) {
        $error = 'Please enter your Voter ID.';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT * FROM voters WHERE voter_id = ?");
        $stmt->bind_param("s", $voter_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $voter = $result->fetch_assoc();

            if ($voter['has_voted'] == 1) {
                $error = 'You have already cast your vote. Each voter can only vote once.';
            } else {
                $_SESSION['voter_id']   = $voter['voter_id'];
                $_SESSION['voter_name'] = $voter['full_name'];
                header("Location: vote.php");
                exit();
            }
        } else {
            $error = 'Invalid Voter ID. Please check with the admin.';
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
    <title>Voter Login – Voters System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">

<div class="auth-card">
    <a href="index.php" class="back-link">← Back to Home</a>

    <div class="auth-header">
        <div class="auth-icon">👤</div>
        <h1>Voter Login</h1>
        <p>Enter your Voter ID to access the ballot</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="voter_login.php">
        <div class="form-group">
            <label for="voter_id">Voter ID</label>
            <input
                type="text"
                id="voter_id"
                name="voter_id"
                placeholder="e.g. VOT-001"
                value="<?= htmlspecialchars($_POST['voter_id'] ?? '') ?>"
                autofocus
                required
            >
            <small>Your Voter ID can be obtained from the Admin Panel.</small>
        </div>

        <button type="submit" class="btn btn-primary btn-full">Login to Vote</button>
    </form>
</div>

</body>
</html>
