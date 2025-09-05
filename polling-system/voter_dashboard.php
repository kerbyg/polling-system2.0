<?php
session_start();
if (!isset($_SESSION['voter_id'])) {
    header("Location: login.php");
    exit;
}
require __DIR__ . '/db.php';

// Get latest active poll (only one)
$latestPoll = $pdo->query("SELECT * FROM polls WHERE is_active = 1 ORDER BY created_at DESC, id DESC LIMIT 1")->fetch();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Voter Dashboard</title>
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
        p {
            color: var(--muted-color);
            line-height: 1.5;
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
        .btn-secondary {
            background: var(--border-color);
            color: var(--text-color);
        }
        .btn-secondary:hover {
            background: #d1d5db;
        }
        .top-banner {
            background: var(--primary-color);
            color: #fff;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,.1);
            position: relative;
        }
        .top-banner h1 {
            font-size: 2.25rem;
            font-weight: 700;
            margin: 0;
            color: #fff;
        }
        .top-banner p {
            color: #e5e7eb;
            margin-top: 0.5rem;
        }
        .btn-logout {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            padding: 0.5rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 0.5rem;
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .btn-logout:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        .poll-question {
            font-weight: 600;
            font-size: 1.25rem;
            color: var(--text-color);
        }
        .card-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 600px) {
            .card-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-banner">
            <a href="voter_logout.php" class="btn-logout">Logout</a>
            <h1>Hello, <?= htmlspecialchars($_SESSION['voter_username']) ?> 👋</h1>
            <p>Welcome to your personal poll dashboard.</p>
        </div>
        
        <?php if ($latestPoll): ?>
            <div class="card">
                <h2>📊 Latest Poll</h2>
                <p class="poll-question"><?= htmlspecialchars($latestPoll['question']) ?></p>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <a class="btn" href="index.php?poll_id=<?= (int)$latestPoll['id'] ?>">Vote Now</a>
                    <a class="btn btn-secondary" href="results.php?poll_id=<?= (int)$latestPoll['id'] ?>">View Results</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <h2>No Active Polls</h2>
                <p>There are no active polls at the moment. Please check back later.</p>
            </div>
        <?php endif; ?>

        <div class="card-grid">
            <div class="card">
                <h2>🗂 Poll Archives</h2>
                <p>Explore past polls and their results.</p>
                <a class="btn" href="all_polls.php">View All Polls</a>
            </div>
            <div class="card">
                <h2>🗳️ Your History</h2>
                <p>See a list of all the polls you have voted in.</p>
                <a class="btn" href="voter_history.php">View Your History</a>
            </div>
        </div>
    </div>
</body>
</html>