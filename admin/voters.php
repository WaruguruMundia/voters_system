<?php
require_once 'auth_guard.php';

$conn = getConnection();
$success = '';
$error   = '';

// ADD VOTER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $full_name = trim($_POST['full_name'] ?? '');
    $voter_id  = strtoupper(trim($_POST['voter_id'] ?? ''));

    if (empty($full_name) || empty($voter_id)) {
        $error = 'Both Voter ID and Full Name are required.';
    } else {
        // Check uniqueness
        $check = $conn->prepare("SELECT id FROM voters WHERE voter_id = ?");
        $check->bind_param("s", $voter_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Voter ID '$voter_id' already exists.";
        } else {
            $ins = $conn->prepare("INSERT INTO voters (voter_id, full_name) VALUES (?, ?)");
            $ins->bind_param("ss", $voter_id, $full_name);
            if ($ins->execute()) {
                $success = "Voter '$full_name' added successfully with ID: $voter_id";
            } else {
                $error = 'Failed to add voter. Please try again.';
            }
            $ins->close();
        }
        $check->close();
    }
}

// DELETE VOTER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $del_id = (int) ($_POST['voter_db_id'] ?? 0);

    if ($del_id > 0) {
        // Also remove their votes
        $get_vid = $conn->prepare("SELECT voter_id FROM voters WHERE id = ?");
        $get_vid->bind_param("i", $del_id);
        $get_vid->execute();
        $get_vid->bind_result($vid);
        $get_vid->fetch();
        $get_vid->close();

        // Decrement candidate votes
        $dec = $conn->prepare("
            UPDATE candidates SET votes = votes - 1
            WHERE id IN (SELECT candidate_id FROM votes WHERE voter_id = ?)
            AND votes > 0
        ");
        $dec->bind_param("s", $vid);
        $dec->execute();
        $dec->close();

        // Delete audit votes
        $dv = $conn->prepare("DELETE FROM votes WHERE voter_id = ?");
        $dv->bind_param("s", $vid);
        $dv->execute();
        $dv->close();

        // Delete voter
        $del = $conn->prepare("DELETE FROM voters WHERE id = ?");
        $del->bind_param("i", $del_id);
        if ($del->execute()) {
            $success = 'Voter deleted successfully.';
        } else {
            $error = 'Failed to delete voter.';
        }
        $del->close();
    }
}

// Fetch all voters
$voters = $conn->query("SELECT * FROM voters ORDER BY voter_id");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Voters – Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">

<?php include 'navbar.php'; ?>

<div class="admin-content">
    <h1 class="page-title">Manage Voters</h1>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Add Voter Form -->
    <div class="admin-card">
        <h2>Add New Voter</h2>
        <form method="POST" action="voters.php" class="inline-form">
            <input type="hidden" name="action" value="add">
            <div class="form-row">
                <div class="form-group">
                    <label>Voter ID</label>
                    <input type="text" name="voter_id" placeholder="e.g. VOT-006" required>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="e.g. John Doe" required>
                </div>
                <div class="form-group form-group-btn">
                    <button type="submit" class="btn btn-primary">Add Voter</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Voters Table -->
    <div class="admin-card">
        <h2>All Voters (<?= $voters->num_rows ?>)</h2>
        <?php if ($voters->num_rows === 0): ?>
            <div class="alert alert-info">No voters registered yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Voter ID</th>
                            <th>Full Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while ($v = $voters->fetch_assoc()): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><code><?= htmlspecialchars($v['voter_id']) ?></code></td>
                                <td><?= htmlspecialchars($v['full_name']) ?></td>
                                <td>
                                    <?php if ($v['has_voted']): ?>
                                        <span class="badge badge-success">Voted</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending">Not Yet Voted</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="voters.php" onsubmit="return confirm('Delete this voter? Their votes will also be removed.')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="voter_db_id" value="<?= $v['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
