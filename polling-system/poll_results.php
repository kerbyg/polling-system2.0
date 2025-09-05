<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

require __DIR__ . '/db.php';

// Helper function to calculate percentage
function pct($count, $total) {
    return $total > 0 ? round(($count / $total) * 100, 2) : 0;
}

// Handle delete poll request
if (isset($_GET['delete_poll'])) {
    $poll_id = (int) $_GET['delete_poll'];
    $error = "";

    try {
        $pdo->beginTransaction();

        // Delete associated votes first
        $pdo->prepare("DELETE FROM votes WHERE poll_id = ?")->execute([$poll_id]);

        // Delete associated options
        $pdo->prepare("DELETE FROM options WHERE poll_id = ?")->execute([$poll_id]);

        // Delete the poll itself
        $pdo->prepare("DELETE FROM polls WHERE id = ?")->execute([$poll_id]);

        $pdo->commit();
        header("Location: poll_results.php?msg=deleted");
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        // You might want to log the actual error: error_log($e->getMessage());
        $error = "❌ Error deleting poll.";
    }
}

// Fetch all polls
$polls = $pdo->query("SELECT * FROM polls ORDER BY created_at DESC, id DESC")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin - Poll Results</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-dark: #2563eb;
            --danger-color: #ef4444;
            --danger-dark: #b91c1c;
            --warning-color: #f59e0b;
            --warning-dark: #b45309;
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
            max-width: 900px;
            margin: 0 auto;
        }

        .card {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        h2 {
            margin: 0 0 1.5rem;
            font-size: 1.75rem;
            color: var(--text-color);
        }
        
        h3 {
            margin-top: 0;
            font-size: 1.25rem;
        }

        .btn {
            display: inline-block;
            padding: 0.6rem 1rem;
            border: 0;
            border-radius: 0.75rem;
            text-decoration: none;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s ease-in-out, transform 0.1s ease;
        }
        .btn:active {
            transform: scale(0.97);
        }
        
        .btn-primary { background: var(--primary-color); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-danger { background: var(--danger-color); color: #fff; }
        .btn-danger:hover { background: var(--danger-dark); }
        .btn-edit { background: var(--warning-color); color: #fff; }
        .btn-edit:hover { background: var(--warning-dark); }

        .top-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .poll-card {
            background: #fff;
            padding: 1.5rem;
            margin-top: 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: 1rem;
        }

        .poll-header {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .muted {
            color: var(--muted-color);
            font-size: 0.875rem;
        }

        .option-row { margin: 1rem 0; }
        .option-top { display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .option-bar { height: 16px; background: var(--border-color); border-radius: 999px; overflow: hidden; }
        .option-fill { height: 100%; background: var(--primary-color); }

        .small-link {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--primary-color);
            text-decoration: none;
            cursor: pointer;
        }
        .small-link:hover { text-decoration: underline; }

        .voter-list {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 0.75rem;
            background: var(--bg-color);
            display: none;
            border: 1px solid var(--border-color);
        }
        table { width: 100%; border-collapse: collapse; margin-top: 0.5rem; }
        th, td { text-align: left; padding: 0.75rem; border-bottom: 1px solid var(--border-color); }
        th { font-size: 0.8rem; text-transform: uppercase; color: var(--muted-color); }
        tbody tr:last-child td { border-bottom: 0; }
        tbody tr:nth-child(even) { background-color: #fff; }
        
        .poll-actions {
            margin-top: 1.5rem;
            display: flex;
            gap: 0.75rem;
        }

        .msg {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 0.75rem;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    </style>
    <script>
        function toggleVoters(id) {
            const el = document.getElementById('voters-' + id);
            if (!el) return;
            el.style.display = (el.style.display === 'block') ? 'none' : 'block';
        }

        function confirmDelete(pollId) {
            if (confirm("Are you sure you want to delete this poll? This action cannot be undone.")) {
                window.location.href = "poll_results.php?delete_poll=" + pollId;
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="top-actions">
                <a href="admin.php" class="btn btn-primary">➕ Create New Poll</a>
                <a href="logout.php" class="btn btn-primary">Logout</a>
            </div>
            
            <h2>📊 Poll Results</h2>

            <?php if (!empty($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div class="msg success">✅ Poll deleted successfully.</div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="msg error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($polls): ?>
                <?php foreach ($polls as $poll): ?>
                    <?php
                        // Fetch options for the poll
                        $stmt = $pdo->prepare("SELECT id, option_text FROM options WHERE poll_id = ? ORDER BY id ASC");
                        $stmt->execute([$poll['id']]);
                        $options = $stmt->fetchAll();

                        // Fetch vote counts
                        $stmt = $pdo->prepare("SELECT option_id, COUNT(*) AS vote_count FROM votes WHERE poll_id = ? GROUP BY option_id");
                        $stmt->execute([$poll['id']]);
                        $voteCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                        $total_votes = array_sum($voteCounts);
                    ?>
                    <div class="poll-card">
                        <div class="poll-header">
                            <h3><?= htmlspecialchars($poll['question']) ?></h3>
                            <p class="muted" style="margin: 0;">Total votes: <strong><?= (int)$total_votes ?></strong></p>
                        </div>

                        <?php foreach ($options as $opt):
                            $count = $voteCounts[$opt['id']] ?? 0;
                            $p = pct($count, $total_votes);
                        ?>
                            <div class="option-row">
                                <div class="option-top">
                                    <div><?= htmlspecialchars($opt['option_text']) ?></div>
                                    <div><strong><?= $p ?>%</strong> (<?= $count ?>)</div>
                                </div>
                                <div class="option-bar"><div class="option-fill" style="width:<?= $p ?>%"></div></div>
                            </div>
                        <?php endforeach; ?>

                        <p style="margin-top: 1.5rem;">
                            <a class="small-link" onclick="toggleVoters(<?= (int)$poll['id'] ?>)">Show/Hide Voter Details</a>
                        </p>

                        <div id="voters-<?= (int)$poll['id'] ?>" class="voter-list">
                            <?php
                                $stmt = $pdo->prepare("
                                    SELECT v.username AS voter, o.option_text AS choice, vt.voted_at
                                    FROM votes vt
                                    LEFT JOIN voters v ON vt.voter_id = v.id
                                    LEFT JOIN options o ON vt.option_id = o.id
                                    WHERE vt.poll_id = ?
                                    ORDER BY vt.voted_at DESC
                                ");
                                $stmt->execute([$poll['id']]);
                                $voterRows = $stmt->fetchAll();
                            ?>

                            <?php if ($voterRows): ?>
                                <table>
                                    <thead><tr><th>Voter</th><th>Choice</th><th>Time</th></tr></thead>
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
                                <p class="muted" style="text-align:center; margin: 0;">No votes have been cast for this poll yet.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="poll-actions">
                            <a class="btn btn-edit" href="edit_poll.php?poll_id=<?= (int)$poll['id'] ?>">✏️ Edit</a>
                            <button class="btn btn-danger" onclick="confirmDelete(<?= (int)$poll['id'] ?>)">🗑 Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No polls have been created yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
