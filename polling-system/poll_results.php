<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

require __DIR__ . '/db.php';

// Helper
function pct($count, $total) {
    return $total > 0 ? round(($count / $total) * 100, 2) : 0;
}

// ✅ Handle delete poll request
if (isset($_GET['delete_poll'])) {
    $poll_id = (int) $_GET['delete_poll'];

    try {
        $pdo->beginTransaction();

        // Delete votes first (to maintain FK integrity)
        $pdo->prepare("DELETE FROM votes WHERE poll_id = ?")->execute([$poll_id]);

        // Delete options
        $pdo->prepare("DELETE FROM options WHERE poll_id = ?")->execute([$poll_id]);

        // Delete poll itself
        $pdo->prepare("DELETE FROM polls WHERE id = ?")->execute([$poll_id]);

        $pdo->commit();
        header("Location: poll_results.php?msg=deleted");
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = "❌ Error deleting poll.";
    }
}

$polls = $pdo->query("SELECT * FROM polls ORDER BY created_at DESC, id DESC")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin - Poll Results</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {font-family: system-ui, sans-serif; background:#f7f7f7; margin:0; padding:24px;}
        .card {max-width:900px; margin:0 auto 20px; background:#fff; padding:24px;
                border-radius:16px; box-shadow:0 6px 20px rgba(0,0,0,.06);}
        h2 {margin-top:0;}
        .btn {display:inline-block; margin:0 6px 12px 0; padding:10px 16px;
                background:#2b6cb0; color:#fff; border:0; border-radius:12px;
                text-decoration:none; cursor:pointer;}
        .btn:hover {background:#225080;}
        .btn-danger {background:#dc2626;}
        .btn-danger:hover {background:#991b1b;}
        .btn-edit {background:#f59e0b;}
        .btn-edit:hover {background:#d97706;}
        .bar {height:14px; background:#e5e7eb; border-radius:999px; overflow:hidden; margin-top:6px;}
        .fill {height:100%; background:#2b6cb0;}
        .row {margin:12px 0;}
        .top {display:flex; justify-content:space-between; font-size:14px;}
        .muted {color:#666; font-size:13px;}
        .small-link {font-size:13px; color:#2b6cb0; text-decoration:none; cursor:pointer;}
        .voter-list {margin-top:8px; padding:8px; border-radius:8px; background:#fbfcfe; display:none;}
        table {width:100%; border-collapse:collapse; margin-top:8px;}
        th, td {text-align:left; padding:6px; border-bottom:1px solid #f1f1f1;}
        .msg {margin-bottom:12px; padding:10px; border-radius:8px; font-size:14px;}
        .success {background:#ecfdf5; color:#065f46; border:1px solid #6ee7b7;}
        .error {background:#fef2f2; color:#991b1b; border:1px solid #fecaca;}

        /* Updated CSS for top buttons */
        .top-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
    <script>
        function toggleVoters(id){
            const el = document.getElementById('voters-' + id);
            if(!el) return;
            el.style.display = (el.style.display === 'block') ? 'none' : 'block';
        }
        function confirmDelete(pollId){
            if(confirm("Are you sure you want to delete this poll? This action cannot be undone.")){
                window.location.href = "poll_results.php?delete_poll=" + pollId;
            }
        }
    </script>
</head>
<body>
    <div class="card">
        <div class="top-actions">
            <div>
                <a href="admin.php" class="btn">➕ Create New Poll</a>
            </div>
            <div>
                <a href="logout.php" class="btn">Logout</a>
            </div>
        </div>
        
        <h2>📊 Poll Results</h2>

        <?php if (!empty($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="msg success">✅ Poll deleted successfully.</div>
        <?php endif; ?>

        <?php if ($polls): ?>
            <?php foreach ($polls as $poll): ?>
                <?php
                    $stmt = $pdo->prepare("SELECT id, option_text FROM options WHERE poll_id = ? ORDER BY id ASC");
                    $stmt->execute([$poll['id']]);
                    $options = $stmt->fetchAll();

                    $stmt = $pdo->prepare("SELECT option_id, COUNT(*) AS vote_count FROM votes WHERE poll_id = ? GROUP BY option_id");
                    $stmt->execute([$poll['id']]);
                    $voteCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                    $total_votes = array_sum($voteCounts);
                ?>
                <div class="poll-block" style="margin-bottom:20px;">
                    <h3><?= htmlspecialchars($poll['question']) ?></h3>
                    <p class="muted">Total votes: <strong><?= (int)$total_votes ?></strong></p>

                    <?php foreach ($options as $opt):
                        $count = $voteCounts[$opt['id']] ?? 0;
                        $p = pct($count, $total_votes);
                    ?>
                        <div class="row">
                            <div class="top">
                                <div><?= htmlspecialchars($opt['option_text']) ?></div>
                                <div><?= $p ?>% (<?= $count ?>)</div>
                            </div>
                            <div class="bar"><div class="fill" style="width:<?= $p ?>%"></div></div>
                        </div>
                    <?php endforeach; ?>

                    <p>
                        <a class="small-link" onclick="toggleVoters(<?= (int)$poll['id'] ?>)">View voter details</a>
                    </p>

                    <div id="voters-<?= (int)$poll['id'] ?>" class="voter-list">
                        <?php
                            $stmt = $pdo->prepare("
                                SELECT v.username AS voter, o.option_text AS choice, vt.voted_at
                                FROM votes vt
                                LEFT JOIN voters v ON vt.voter_id = v.id
                                LEFT JOIN options o ON vt.option_id = o.id
                                WHERE vt.poll_id = ?
                                ORDER BY vt.voted_at ASC
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
                                        <td><?= htmlspecialchars($vr['voted_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="muted">No votes yet for this poll.</p>
                        <?php endif; ?>
                    </div>
                    <div style="margin-top: 15px;">
                        <a class="btn btn-edit" href="edit_poll.php?poll_id=<?= (int)$poll['id'] ?>">✏️ Edit Poll</a>
                        <button class="btn btn-danger" onclick="confirmDelete(<?= (int)$poll['id'] ?>)">🗑 Delete Poll</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No polls available yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>