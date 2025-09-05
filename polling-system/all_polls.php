<?php
session_start();
if (!isset($_SESSION['voter_id'])) {
    header("Location: login.php");
    exit;
}
require __DIR__ . '/db.php';

// Get all active polls
$activePolls = $pdo->query("SELECT * FROM polls WHERE is_active = 1 ORDER BY created_at DESC, id DESC")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>All Active Polls</title>
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
            --shadow-color: rgba(0,0,0,.08);
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
            box-shadow: 0 4px 20px var(--shadow-color);
            border: 1px solid var(--border-color);
        }
        h1 {
            margin-top: 0;
            font-size: 1.875rem;
            color: var(--text-color);
        }
        h2 {
            font-size: 1.5rem;
            margin-top: 0;
            margin-bottom: 1rem;
            color: var(--text-color);
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.25rem;
            background: var(--primary-color);
            color: #fff;
            border-radius: 0.75rem;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.2s ease-in-out;
            border: none;
            cursor: pointer;
            text-align: center;
        }
        .btn:hover {
            background: var(--primary-dark);
        }
        .btn-secondary {
            background: var(--border-color);
            color: var(--text-color);
        }
        .btn-secondary:hover {
            background: #d1d5db;
        }
        .btn-back {
            display: inline-block;
            padding: 0.75rem 1rem;
            background: var(--primary-color); /* Changed to primary color */
            color: #fff; /* Changed to white text */
            border: 1px solid var(--primary-color);
            border-radius: 0.75rem;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out, background-color 0.2s ease-in-out;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--shadow-color);
            background: var(--primary-dark);
        }
        .poll-question {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-color);
        }
        .poll-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="voter_dashboard.php" class="btn-back">← Back to Dashboard</a>

        <div class="card">
            <h1>All Active Polls 📊</h1>
            <p style="margin-bottom: 0;">Select a poll to participate or view results.</p>
        </div>

        <?php if ($activePolls): ?>
            <?php foreach ($activePolls as $poll): ?>
                <div class="card">
                    <p class="poll-question"><?= htmlspecialchars($poll['question']) ?></p>
                    <div class="poll-actions">
                        <a class="btn" href="index.php?poll_id=<?= (int)$poll['id'] ?>">Vote Now</a>
                        <a class="btn btn-secondary" href="results.php?poll_id=<?= (int)$poll['id'] ?>">View Results</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <h2>No active polls</h2>
                <p>Please check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>