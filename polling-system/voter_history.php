<?php
session_start();
if (!isset($_SESSION['voter_id'])) {
    header("Location: login.php");
    exit;
}
require __DIR__ . '/db.php';

// Get voter's history
$voter_id = $_SESSION['voter_id'];
$stmt = $pdo->prepare("
    SELECT p.question, o.option_text, v.voted_at
    FROM votes v
    JOIN polls p ON v.poll_id = p.id
    JOIN options o ON v.option_id = o.id
    WHERE v.voter_id = ?
    ORDER BY v.voted_at DESC
");
$stmt->execute([$voter_id]);
$votingHistory = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Your Voting History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-dark: #2563eb;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-color: #1f2937;
            --muted-color: #6b7280;
            --border-color: #e5e7eb;
        }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: var(--bg-color);
            margin: 0;
            padding: 2rem 1rem;
        }
        .container {
            max-width: 720px;
            margin: 0 auto;
        }
        .card {
            background: var(--card-bg);
            padding: 2rem;
            margin-bottom: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
        }
        h1, h2 {
            font-size: 1.875rem;
            color: var(--text-color);
            margin-top: 0;
            margin-bottom: 1rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.25rem;
            margin-top: 1rem;
            background: var(--primary-color);
            color: #fff;
            border-radius: 0.75rem;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.2s ease-in-out;
        }
        .btn:hover {
            background: var(--primary-dark);
        }
        .history-item {
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
        }
        .history-item:last-child {
            border-bottom: none;
        }
        .history-item h3 {
            margin: 0 0 0.5rem;
            font-size: 1.125rem;
            color: var(--text-color);
        }
        .history-item p {
            margin: 0;
            font-size: 0.875rem;
            color: var(--muted-color);
        }
        .voted-choice {
            font-weight: 600;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <a href="voter_dashboard.php" class="btn">← Back to Dashboard</a>
            <h2 style="margin-top: 1.5rem;">🗳️ Your Voting History</h2>
            <?php if ($votingHistory): ?>
                <?php foreach ($votingHistory as $vote): ?>
                    <div class="history-item">
                        <h3><?= htmlspecialchars($vote['question']) ?></h3>
                        <p>You voted for: <span class="voted-choice"><?= htmlspecialchars($vote['option_text']) ?></span></p>
                        <p><small>Voted on: <?= date("F j, Y, g:i a", strtotime($vote['voted_at'])) ?></small></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>You have not cast any votes yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>