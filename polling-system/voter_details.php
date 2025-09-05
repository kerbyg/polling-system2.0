<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require __DIR__ . '/db.php';

// Get poll ID from URL, redirect if not provided
$poll_id = filter_input(INPUT_GET, 'poll_id', FILTER_VALIDATE_INT);
if (!$poll_id) {
    header("Location: poll_results.php");
    exit;
}

// Fetch poll details
$stmt = $pdo->prepare("SELECT question FROM polls WHERE id = ?");
$stmt->execute([$poll_id]);
$poll = $stmt->fetch();

// Redirect if poll not found
if (!$poll) {
    header("Location: poll_results.php");
    exit;
}

// Fetch voter details for this poll
$stmt = $pdo->prepare("
    SELECT v.username AS voter, o.option_text AS choice, vt.voted_at 
    FROM votes vt 
    LEFT JOIN voters v ON vt.voter_id = v.id 
    LEFT JOIN options o ON vt.option_id = o.id 
    WHERE vt.poll_id = ? 
    ORDER BY vt.voted_at DESC
");
$stmt->execute([$poll_id]);
$voterRows = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Voter Details</title>
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
            background: var(--bg-color); margin: 0; padding: 2rem 1rem;
        }
        .container { max-width: 900px; margin: 0 auto; }
        .card { background: var(--card-bg); padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08); }
        h2, h3 { color: var(--text-color); margin-top: 0;}
        h2 { margin-bottom: 0.5rem; font-size: 1.75rem; }
        h3 { font-size: 1.1rem; font-weight: 500; color: var(--muted-color); margin-bottom: 1.5rem; }
        .btn { display: inline-block; padding: 0.6rem 1rem; border: 0; border-radius: 0.75rem; text-decoration: none; cursor: pointer; font-weight: 600; transition: 0.2s ease; }
        .btn:active { transform: scale(0.97); }
        .btn-primary { background: var(--primary-color); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .top-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);}
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color); }
        th { font-size: 0.8rem; text-transform: uppercase; color: var(--muted-color); }
        tbody tr:last-child td { border-bottom: 0; }
        tbody tr:nth-child(even) { background-color: #f9fafb; }
        .muted-text { text-align:center; padding: 2rem; color: var(--muted-color); }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="top-actions">
                <div>
                    <h2>👥 Voter Details</h2>
                    <h3>Poll: <?= htmlspecialchars($poll['question']) ?></h3>
                </div>
                <a href="poll_results.php" class="btn btn-primary">⬅️ Back to Results</a>
            </div>
            
            <?php if ($voterRows): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Voter</th>
                            <th>Choice</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($voterRows as $vr): ?>
                        <tr>
                            <td><?= htmlspecialchars($vr['voter'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($vr['choice'] ?? '—') ?></td>
                            <td><?= htmlspecialchars((new DateTime($vr['voted_at']))->format('M d, Y h:i A')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="muted-text">No votes have been cast for this poll yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
