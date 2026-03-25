<?php
require_once 'auth_guard.php';

$conn    = getConnection();
$success = '';
$error   = '';

// ADD CANDIDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $full_name = trim($_POST['full_name'] ?? '');
    $position  = trim($_POST['position']  ?? '');

    if (empty($full_name) || empty($position)) {
        $error = 'Both Full Name and Position are required.';
    } else {
        $ins = $conn->prepare("INSERT INTO candidates (full_name, position) VALUES (?, ?)");
        $ins->bind_param("ss", $full_name, $position);
        if ($ins->execute()) {
            $success = "Candidate '$full_name' added for position: $position";
        } else {
            $error = 'Failed to add candidate. Please try again.';
        }
        $ins->close();
    }
}

// DELETE CANDIDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $del_id = (int) ($_POST['candidate_id'] ?? 0);

    if ($del_id > 0) {
        // Remove votes for this candidate
        $dv = $conn->prepare("DELETE FROM votes WHERE candidate_id = ?");
        $dv->bind_param("i", $del_id);
        $dv->execute();
        $dv->close();

        // Delete candidate
        $del = $conn->prepare("DELETE FROM candidates WHERE id = ?");
        $del->bind_param("i", $del_id);
        if ($del->execute()) {
            $success = 'Candidate deleted successfully.';
        } else {
            $error = 'Failed to delete candidate.';
        }
        $del->close();
    }
}

// Fetch all candidates grouped by position
$cand_result = $conn->query("SELECT * FROM candidates ORDER BY position, full_name");
$by_position = [];
$all_candidates = [];
while ($row = $cand_result->fetch_assoc()) {
    $by_position[$row['position']][] = $row;
    $all_candidates[] = $row;
}

// Fetch existing positions for datalist
$positions_result = $conn->query("SELECT DISTINCT position FROM candidates ORDER BY position");
$existing_positions = [];
while ($p = $positions_result->fetch_assoc()) {
    $existing_positions[] = $p['position'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates – Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">

<?php include 'navbar.php'; ?>

<div class="admin-content">
    <h1 class="page-title">Manage Candidates</h1>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Add Candidate Form -->
    <div class="admin-card">
        <h2>Add New Candidate</h2>
        <form method="POST" action="candidates.php" class="inline-form">
            <input type="hidden" name="action" value="add">
            <div class="form-row">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="e.g. Jane Muthoni" required>
                </div>
                <div class="form-group">
                    <label>Position</label>
                    <input
                        type="text"
                        name="position"
                        placeholder="e.g. President"
                        list="positions_list"
                        required
                    >
                    <datalist id="positions_list">
                        <?php foreach ($existing_positions as $pos): ?>
                            <option value="<?= htmlspecialchars($pos) ?>">
                        <?php endforeach; ?>
                        <option value="President">
                        <option value="Vice President">
                        <option value="Secretary">
                        <option value="Treasurer">
                    </datalist>
                    <small>Type a new position or pick an existing one.</small>
                </div>
                <div class="form-group form-group-btn">
                    <button type="submit" class="btn btn-primary">Add Candidate</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Candidates Table -->
    <div class="admin-card">
        <h2>All Candidates (<?= count($all_candidates) ?>)</h2>

        <?php if (empty($by_position)): ?>
            <div class="alert alert-info">No candidates added yet.</div>
        <?php else: ?>
            <?php foreach ($by_position as $position => $candidates): ?>
                <h3 class="position-group-title"><?= htmlspecialchars($position) ?></h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Full Name</th>
                                <th>Position</th>
                                <th>Votes Received</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($candidates as $c): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($c['full_name']) ?></td>
                                    <td><?= htmlspecialchars($c['position']) ?></td>
                                    <td>
                                        <span class="vote-count-badge"><?= $c['votes'] ?></span>
                                    </td>
                                    <td>
                                        <form method="POST" action="candidates.php"
                                              onsubmit="return confirm('Delete this candidate? Their votes will also be removed.')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="candidate_id" value="<?= $c['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
