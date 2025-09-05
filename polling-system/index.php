<?php
session_start();
if (!isset($_SESSION['voter_id'])) {
    header("Location: voter_login.php");
    exit;
}
require __DIR__ . '/db.php';

// Get the poll ID from the URL. If it's not set, get the latest active poll.
$poll_id = filter_input(INPUT_GET, 'poll_id', FILTER_VALIDATE_INT);
if (!$poll_id) {
    // If no poll ID is provided in the URL, fetch the latest active poll
    $latest = $pdo->query("SELECT id FROM polls WHERE is_active = 1 ORDER BY created_at DESC, id DESC LIMIT 1")->fetch();
    if (!$latest) {
        echo "No active poll found.";
        exit;
    }
    $poll_id = (int)$latest['id'];
}

// Fetch the poll details based on the determined poll ID
$stmt = $pdo->prepare("SELECT * FROM polls WHERE id = ? AND is_active = 1");
$stmt->execute([$poll_id]);
$poll = $stmt->fetch();
if (!$poll) {
    echo "Poll not found or is not active.";
    exit;
}

// Get options for the fetched poll
$stmt = $pdo->prepare("SELECT * FROM options WHERE poll_id = ? ORDER BY id ASC");
$stmt->execute([$poll['id']]);
$options = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Online Poll</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    /*
      Modern UI improvements:
      - Clean color palette and variables for easy customization.
      - Better spacing and typography hierarchy.
      - Interactive and visually distinct form elements.
      - Soft shadows for a modern, 'floating' card effect.
    */
    :root {
      --primary-color: #2b6cb0;
      --secondary-color: #2c5282;
      --bg-color: #f0f4f8;
      --card-bg: #fff;
      --border-color: #e2e8f0;
      --shadow-color: rgba(0, 0, 0, 0.08);
      --link-color: #4a5568;
    }
    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      background: var(--bg-color);
      margin: 0;
      padding: 2rem 1rem;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }
    .card {
      max-width: 560px;
      width: 100%;
      background: var(--card-bg);
      border-radius: 1rem;
      padding: 2rem;
      box-shadow: 0 10px 25px var(--shadow-color);
    }
    h1 {
      font-size: 1.5rem;
      font-weight: 600;
      color: #1a202c;
      margin-top: 0;
      margin-bottom: 1.5rem;
    }
    .option {
      display: flex;
      align-items: center;
      margin: 0 0 0.75rem 0;
      padding: 0.75rem 1rem;
      border: 1px solid var(--border-color);
      border-radius: 0.75rem;
      cursor: pointer;
      transition: all 0.2s ease-in-out;
      font-size: 1rem;
      font-weight: 500;
      color: #4a5568;
    }
    .option:hover {
      background: var(--bg-color);
      border-color: var(--primary-color);
    }
    .option input[type="radio"] {
      -webkit-appearance: none;
      -moz-appearance: none;
      appearance: none;
      width: 18px;
      height: 18px;
      border: 2px solid var(--border-color);
      border-radius: 50%;
      margin-right: 0.75rem;
      transition: all 0.2s ease-in-out;
      cursor: pointer;
      position: relative;
    }
    .option input[type="radio"]:checked {
      border-color: var(--primary-color);
      background-color: var(--primary-color);
    }
    .option input[type="radio"]:checked::after {
      content: '';
      width: 8px;
      height: 8px;
      background: var(--card-bg);
      border-radius: 50%;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }
    .actions {
      margin-top: 2rem;
      display: flex;
      gap: 0.75rem;
      flex-wrap: wrap;
    }
    .btn {
      padding: 0.75rem 1rem;
      border: none;
      border-radius: 0.75rem;
      cursor: pointer;
      text-decoration: none;
      font-size: 1rem;
      font-weight: 600;
      transition: all 0.2s ease-in-out;
    }
    .primary {
      background: var(--primary-color);
      color: #fff;
    }
    .primary:hover {
      background: var(--secondary-color);
    }
    .ghost {
      background: #f1f5f9;
      color: var(--link-color);
    }
    .ghost:hover {
      background: #e2e8f0;
    }
    .note {
      margin-top: 1.5rem;
      color: #718096;
      font-size: 0.875rem;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="card">
    <h1><?= htmlspecialchars($poll['question']) ?></h1>

    <form action="vote.php" method="post">
      <input type="hidden" name="poll_id" value="<?= (int)$poll['id'] ?>">
      <?php foreach ($options as $opt): ?>
        <label class="option">
          <input type="radio" name="option_id" value="<?= (int)$opt['id'] ?>" required>
          <?= htmlspecialchars($opt['option_text']) ?>
        </label>
      <?php endforeach; ?>

      <div class="actions">
        <button class="btn primary" type="submit">Vote</button>
        <a class="btn ghost" href="results.php?poll_id=<?= (int)$poll['id'] ?>">View Results</a>
        <a class="btn ghost" href="voter_dashboard.php">⬅ Back</a>
      </div>
    </form>

    <p class="note">I only allow one vote per voter account.</p>
  </div>
</body>
</html>