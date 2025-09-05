<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require __DIR__ . '/db.php';

$success = $error = "";

// Handle form submit (create poll)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question'] ?? '');
    $options  = $_POST['options'] ?? [];

    if ($question && count(array_filter($options)) >= 2) {
        try {
            $pdo->beginTransaction();

            // Insert poll
            $stmt = $pdo->prepare("INSERT INTO polls (question, is_active) VALUES (?, 1)");
            $stmt->execute([$question]);
            $poll_id = $pdo->lastInsertId();

            // Insert options
            $stmtOpt = $pdo->prepare("INSERT INTO options (poll_id, option_text) VALUES (?, ?)");
            foreach ($options as $opt) {
                $opt = trim($opt);
                if ($opt !== '') {
                    $stmtOpt->execute([$poll_id, $opt]);
                }
            }

            $pdo->commit();
            $success = "✅ Poll created successfully!";
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = "❌ Error creating poll. Please try again.";
        }
    } else {
        $error = "⚠️ Please enter a question and at least 2 options.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin - Create Poll</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-dark: #2563eb;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-color: #1f2937;
            --border-color: #d1d5db;
        }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: var(--bg-color);
            margin: 0;
            padding: 2rem 1rem;
        }
        .card {
            max-width: 640px;
            margin: 0 auto;
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }
        h1 {
            margin: 0 0 1rem;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-color);
        }
        label {
            display: block;
            margin: 1rem 0 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }
        input[type=text] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            transition: border-color 0.2s ease;
        }
        input[type=text]:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        .option-input {
            margin-bottom: 0.5rem;
        }
        button, .btn {
            display: inline-block;
            padding: 0.75rem 1.25rem;
            margin-top: 1.5rem;
            background: var(--primary-color);
            color: #fff;
            border: 0;
            border-radius: 0.75rem;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            font-weight: 600;
            transition: background-color 0.2s ease-in-out;
        }
        button:hover, .btn:hover {
            background: var(--primary-dark);
        }
        .btn-secondary {
            background: #e5e7eb;
            color: var(--text-color);
        }
        .btn-secondary:hover {
            background: #d1d5db;
        }
        .msg {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .top-actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="top-actions">
            <a href="poll_results.php" class="btn">View All Polls</a>
            <a href="logout.php" class="btn">Logout</a>
        </div>

        <h1>Create a New Poll</h1>

        <?php if (!empty($success)): ?>
            <div class="msg success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="msg error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="q">Poll Question</label>
            <input type="text" id="q" name="question" placeholder= >

            <label>Options (at least 2)</label>
            <input class="option-input" type="text" name="options[]" placeholder="Option 1" required>
            <input class="option-input" type="text" name="options[]" placeholder="Option 2" required>
            <input class="option-input" type="text" name="options[]" placeholder="Option 3" required>
            <input class="option-input" type="text" name="options[]" placeholder="Option 4" required>

            <button type="submit">Create Poll</button>
        </form>
    </div>
</body>
</html>