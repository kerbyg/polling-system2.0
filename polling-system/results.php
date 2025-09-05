<?php
// results.php
session_start();
require __DIR__ . '/db.php';

$poll_id = filter_input(INPUT_GET, 'poll_id', FILTER_VALIDATE_INT);

// Default to latest poll if none provided
if (!$poll_id) {
    $latest = $pdo->query("SELECT id FROM polls ORDER BY created_at DESC, id DESC LIMIT 1")->fetch();
    if (!$latest) {
        echo "No poll found.";
        exit;
    }
    $poll_id = (int)$latest['id'];
}

$msg = $_GET['msg'] ?? null;

// Fetch poll
$stmt = $pdo->prepare("SELECT * FROM polls WHERE id = ?");
$stmt->execute([$poll_id]);
$poll = $stmt->fetch();
if (!$poll) {
    echo "Poll not found.";
    exit;
}

// Fetch options
$stmt = $pdo->prepare("SELECT id, option_text FROM options WHERE poll_id = ? ORDER BY id ASC");
$stmt->execute([$poll_id]);
$options = $stmt->fetchAll();

// Fetch vote counts from votes table
$stmt = $pdo->prepare("SELECT option_id, COUNT(*) as vote_count FROM votes WHERE poll_id = ? GROUP BY option_id");
$stmt->execute([$poll_id]);
$voteCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // option_id => vote_count

$total_votes = array_sum($voteCounts);

// Prepare data for Chart.js
$chartLabels = [];
$chartData = [];
$optionTextMap = [];
foreach ($options as $o) {
    $count = $voteCounts[$o['id']] ?? 0;
    $chartLabels[] = $o['option_text'];
    $chartData[] = $count;
    $optionTextMap[$o['id']] = $o['option_text'];
}

function pct($count, $total) {
    if ($total <= 0) return 0;
    return round(($count / $total) * 100, 2);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Poll Results</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;background:#f7f7f7;margin:0;padding:24px;}
    .card{max-width:640px;margin:0 auto;background:#fff;border-radius:16px;padding:24px;box-shadow:0 6px 20px rgba(0,0,0,.06);}
    h1{font-size:20px;margin:0 0 16px}
    .bar{height:14px;background:#e5e7eb;border-radius:999px;overflow:hidden}
    .fill{height:100%;background:#2b6cb0}
    .row{margin:14px 0}
    .top{display:flex;justify-content:space-between;margin-bottom:6px;font-size:14px}
    .muted{color:#666;font-size:13px;margin-top:4px}
    .actions{margin-top:16px}
    .btn{display:inline-block;padding:10px 14px;border:0;border-radius:12px;background:#f1f5f9;text-decoration:none}
    .info{background:#ecfeff;border:1px solid #a5f3fc;color:#0e7490;padding:10px 12px;border-radius:10px;margin-bottom:12px;font-size:14px}
    .success{background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;padding:10px 12px;border-radius:10px;margin-bottom:12px;font-size:14px}
    .error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 12px;border-radius:10px;margin-bottom:12px;font-size:14px}
  </style>
</head>
<body>
  <div class="card">
    <h1>Results: <?= htmlspecialchars($poll['question']) ?></h1>

    <?php if ($msg === 'already_voted'): ?>
      <div class="error">⚠️ You have already voted in this poll. Showing current results.</div>
    <?php elseif ($msg === 'success'): ?>
      <div class="success">✅ Your vote has been recorded. Thank you!</div>
    <?php endif; ?>

    <div class="muted">Total votes: <strong><?= (int)$total_votes ?></strong></div>

    <div style="margin: 20px 0;">
      <canvas id="voteChart"></canvas>
    </div>

    <div class="actions">
      <a class="btn" href="index.php">⬅ Back to poll</a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <script>
    // Pass PHP data to JavaScript
    const chartLabels = <?= json_encode($chartLabels) ?>;
    const chartData = <?= json_encode($chartData) ?>;

    const ctx = document.getElementById('voteChart').getContext('2d');
    const voteChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: chartLabels,
        datasets: [{
          label: 'Number of Votes',
          data: chartData,
          backgroundColor: 'rgba(43, 108, 176, 0.8)',
          borderColor: 'rgba(43, 108, 176, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
                precision: 0
            }
          }
        },
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                let label = context.dataset.label || '';
                if (label) {
                  label += ': ';
                }
                label += context.raw;
                const total = <?= (int)$total_votes ?>;
                const percentage = (total > 0) ? (context.raw / total * 100).toFixed(2) : 0;
                return `${label} (${percentage}%)`;
              }
            }
          }
        }
      }
    });
  </script>
</body>
</html>