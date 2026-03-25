<?php
require_once 'auth_guard.php';

$conn = getConnection();

// Stats
$total_voters    = $conn->query("SELECT COUNT(*) AS c FROM voters")->fetch_assoc()['c'];
$total_voted     = $conn->query("SELECT COUNT(*) AS c FROM voters WHERE has_voted = 1")->fetch_assoc()['c'];
$total_candidates = $conn->query("SELECT COUNT(*) AS c FROM candidates")->fetch_assoc()['c'];
$total_votes     = $conn->query("SELECT COUNT(*) AS c FROM votes")->fetch_assoc()['c'];

// Results by position
$results = [];
$pos_query = $conn->query("SELECT DISTINCT position FROM candidates ORDER BY position");
while ($p = $pos_query->fetch_assoc()) {
    $pos = $p['position'];
    $cand_query = $conn->prepare("SELECT full_name, votes FROM candidates WHERE position = ? ORDER BY votes DESC");
    $cand_query->bind_param("s", $pos);
    $cand_query->execute();
    $cand_result = $cand_query->get_result();
    $results[$pos] = [];
    while ($c = $cand_result->fetch_assoc()) {
        $results[$pos][] = $c;
    }
    $cand_query->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">

<?php include 'navbar.php'; ?>

<div class="admin-content">
    <h1 class="page-title">Dashboard</h1>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-blue">
            <div class="stat-number"><?= $total_voters ?></div>
            <div class="stat-label">Total Voters</div>
        </div>
        <div class="stat-card stat-green">
            <div class="stat-number"><?= $total_voted ?></div>
            <div class="stat-label">Votes Cast</div>
        </div>
        <div class="stat-card stat-orange">
            <div class="stat-number"><?= $total_voters - $total_voted ?></div>
            <div class="stat-label">Yet to Vote</div>
        </div>
        <div class="stat-card stat-purple">
            <div class="stat-number"><?= $total_candidates ?></div>
            <div class="stat-label">Candidates</div>
        </div>
    </div>

    <!-- Voter Turnout Bar -->
    <?php if ($total_voters > 0): ?>
    <div class="turnout-section">
        <h2>Voter Turnout</h2>
        <div class="turnout-bar-bg">
            <?php $pct = round(($total_voted / $total_voters) * 100); ?>
            <div class="turnout-bar-fill" style="width: <?= $pct ?>%"><?= $pct ?>%</div>
        </div>
        <p><?= $total_voted ?> out of <?= $total_voters ?> voters have voted.</p>
    </div>
    <?php endif; ?>

    <!-- Results by Position -->
    <h2 class="section-title">Vote Results</h2>
    <?php if (empty($results)): ?>
        <div class="alert alert-info">No candidates added yet.</div>
    <?php else: ?>
        <?php foreach ($results as $position => $candidates): ?>
            <div class="results-card">
                <h3 class="results-position"><?= htmlspecialchars($position) ?></h3>
                <?php
                    $max_votes = max(array_column($candidates, 'votes'));
                ?>
                <?php foreach ($candidates as $c): ?>
                    <?php
                        $bar_pct = ($total_votes > 0 && $c['votes'] > 0)
                            ? round(($c['votes'] / max($total_votes, 1)) * 100)
                            : 0;
                        $is_leader = ($c['votes'] === $max_votes && $max_votes > 0);
                    ?>
                    <div class="result-row <?= $is_leader ? 'result-leader' : '' ?>">
                        <div class="result-name">
                            <?= $is_leader ? '🏆 ' : '' ?>
                            <?= htmlspecialchars($c['full_name']) ?>
                        </div>
                        <div class="result-bar-wrap">
                            <div class="result-bar" style="width: <?= $bar_pct ?>%"></div>
                        </div>
                        <div class="result-count"><?= $c['votes'] ?> vote<?= $c['votes'] !== 1 ? 's' : '' ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
