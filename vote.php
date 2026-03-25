<?php
require_once 'config.php';

if (!isset($_SESSION['voter_id'])) {
    header("Location: voter_login.php");
    exit();
}

$voter_id   = $_SESSION['voter_id'];
$voter_name = $_SESSION['voter_name'];
$conn       = getConnection();

// Double-check has_voted in DB
$chk = $conn->prepare("SELECT has_voted FROM voters WHERE voter_id = ?");
$chk->bind_param("s", $voter_id);
$chk->execute();
$chk->bind_result($has_voted);
$chk->fetch();
$chk->close();

if ($has_voted) {
    session_destroy();
    header("Location: voter_login.php?msg=already_voted");
    exit();
}

$success = '';
$error   = '';

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect selections: one per position
    $selections = $_POST['candidate'] ?? [];  // ['President' => id, 'Vice President' => id]

    // Fetch distinct positions
    $pos_result = $conn->query("SELECT DISTINCT position FROM candidates ORDER BY position");
    $positions  = [];
    while ($row = $pos_result->fetch_assoc()) {
        $positions[] = $row['position'];
    }

    // Validate all positions voted
    $missing = [];
    foreach ($positions as $pos) {
        if (empty($selections[$pos])) {
            $missing[] = $pos;
        }
    }

    if (!empty($missing)) {
        $error = 'Please select a candidate for: ' . implode(', ', $missing);
    } else {
        // Begin transaction
        $conn->begin_transaction();
        try {
            foreach ($selections as $position => $candidate_id) {
                $candidate_id = (int) $candidate_id;

                // Verify candidate belongs to position (security check)
                $verify = $conn->prepare("SELECT id FROM candidates WHERE id = ? AND position = ?");
                $verify->bind_param("is", $candidate_id, $position);
                $verify->execute();
                $verify->store_result();
                if ($verify->num_rows === 0) {
                    throw new Exception("Invalid candidate selection.");
                }
                $verify->close();

                // Record vote
                $ins = $conn->prepare("INSERT INTO votes (voter_id, candidate_id) VALUES (?, ?)");
                $ins->bind_param("si", $voter_id, $candidate_id);
                $ins->execute();
                $ins->close();

                // Increment candidate vote count
                $upd = $conn->prepare("UPDATE candidates SET votes = votes + 1 WHERE id = ?");
                $upd->bind_param("i", $candidate_id);
                $upd->execute();
                $upd->close();
            }

            // Mark voter as voted
            $mark = $conn->prepare("UPDATE voters SET has_voted = 1 WHERE voter_id = ?");
            $mark->bind_param("s", $voter_id);
            $mark->execute();
            $mark->close();

            $conn->commit();

            // Clear session and redirect to thank-you
            session_destroy();
            header("Location: thankyou.php");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error = 'An error occurred while submitting your vote. Please try again.';
        }
    }
}

// Fetch candidates grouped by position
$cand_result = $conn->query("SELECT * FROM candidates ORDER BY position, full_name");
$by_position = [];
while ($row = $cand_result->fetch_assoc()) {
    $by_position[$row['position']][] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cast Your Vote – Voters System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="vote-body">

<div class="vote-wrapper">
    <div class="vote-header">
        <h1>🗳️ Ballot Paper</h1>
        <p>Welcome, <strong><?= htmlspecialchars($voter_name) ?></strong> &nbsp;|&nbsp; ID: <?= htmlspecialchars($voter_id) ?></p>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="vote.php" onsubmit="return confirmVote()">
        <?php foreach ($by_position as $position => $candidates): ?>
            <div class="ballot-section">
                <h2 class="ballot-position"><?= htmlspecialchars($position) ?></h2>
                <p class="ballot-instruction">Select ONE candidate</p>

                <div class="candidates-list">
                    <?php foreach ($candidates as $c): ?>
                        <label class="candidate-option">
                            <input
                                type="radio"
                                name="candidate[<?= htmlspecialchars($position) ?>]"
                                value="<?= $c['id'] ?>"
                                required
                            >
                            <span class="candidate-info">
                                <span class="candidate-avatar"><?= strtoupper(substr($c['full_name'], 0, 1)) ?></span>
                                <span class="candidate-name"><?= htmlspecialchars($c['full_name']) ?></span>
                            </span>
                            <span class="checkmark">✔</span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($by_position)): ?>
            <div class="alert alert-info">No candidates have been added yet. Please contact the admin.</div>
        <?php else: ?>
            <div class="vote-submit">
                <p class="vote-warning">⚠️ Once submitted, your vote cannot be changed.</p>
                <button type="submit" class="btn btn-primary btn-large">Submit My Vote</button>
            </div>
        <?php endif; ?>
    </form>
</div>

<script>
function confirmVote() {
    return confirm("Are you sure you want to submit your vote?\nThis action cannot be undone.");
}
</script>

</body>
</html>
