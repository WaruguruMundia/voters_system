<?php
require_once 'config.php';

$mode = $_GET['mode'] ?? 'register'; // default = register
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voter_id = trim($_POST['voter_id'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');

    $conn = getConnection();

    // ================= LOGIN =================
    if ($_POST['action'] === 'login') {

        if (empty($voter_id)) {
            $error = 'Please enter your Voter ID.';
        } else {
            $stmt = $conn->prepare("SELECT * FROM voters WHERE voter_id = ?");
            $stmt->bind_param("s", $voter_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $voter = $result->fetch_assoc();

                if ($voter['has_voted'] == 1) {
                    $error = 'You have already voted.';
                } else {
                    $_SESSION['voter_id']   = $voter['voter_id'];
                    $_SESSION['voter_name'] = $voter['full_name'];
                    header("Location: vote.php");
                    exit();
                }
            } else {
                $error = 'Invalid Voter ID.';
            }

            $stmt->close();
        }
    }

    // ================= REGISTER =================
    if ($_POST['action'] === 'register') {

        if (empty($voter_id) || empty($full_name)) {
            $error = 'Please fill in all fields.';
        } else {
            $check = $conn->prepare("SELECT voter_id FROM voters WHERE voter_id = ?");
            $check->bind_param("s", $voter_id);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = 'Voter ID already exists.';
            } else {
                $stmt = $conn->prepare("INSERT INTO voters (voter_id, full_name, has_voted) VALUES (?, ?, 0)");
                $stmt->bind_param("ss", $voter_id, $full_name);

                if ($stmt->execute()) {
                    $success = 'Registered successfully! You can now log in.';
                    $mode = 'login'; // switch to login after register
                } else {
                    $error = 'Registration failed.';
                }

                $stmt->close();
            }

            $check->close();
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Voter System</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">

<div class="auth-card">

    <h1><?= $mode === 'login' ? 'Voter Login' : 'Register Voter' ?></h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">

        <input type="hidden" name="action" value="<?= $mode ?>">

        <div class="form-group">
            <label>Voter ID</label>
            <input type="text" name="voter_id" required>
        </div>

        <?php if ($mode === 'register'): ?>
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" required>
        </div>
        <?php endif; ?>

        <button class="btn btn-primary btn-full">
            <?= $mode === 'login' ? 'Login to Vote' : 'Register' ?>
        </button>

    </form>

    <!-- TOGGLE -->
    <div style="margin-top:20px;text-align:center;">
        <?php if ($mode === 'register'): ?>
            <p>Already a voter?</p>
            <a href="?mode=login" class="btn btn-secondary btn-full">Login Instead</a>
        <?php else: ?>
            <p>New voter?</p>
            <a href="?mode=register" class="btn btn-secondary btn-full">Register Instead</a>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
