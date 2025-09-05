<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

require __DIR__ . '/db.php';

$error = '';
$success = '';

$poll_id = filter_input(INPUT_GET, 'poll_id', FILTER_VALIDATE_INT);
if (!$poll_id) {
    $error = "❌ Invalid poll ID.";
}

// Fetch existing poll data
$stmt = $pdo->prepare("SELECT * FROM polls WHERE id = ?");
$stmt->execute([$poll_id]);
$poll = $stmt->fetch();

if (!$poll) {
    $error = "❌ Poll not found.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $new_question = trim($_POST['question'] ?? '');
    $new_options = $_POST['options'] ?? [];

    if (empty($new_question)) {
        $error = "⚠️ Poll question cannot be empty.";
    } elseif (count(array_filter($new_options)) < 2) {
        $error = "⚠️ Please provide at least two options.";
    } else {
        try {
            $pdo->beginTransaction();

            // Update the poll question
            $stmt = $pdo->prepare("UPDATE polls SET question = ? WHERE id = ?");
            $stmt->execute([$new_question, $poll_id]);

            // Track existing option IDs to delete old ones
            $existing_option_ids = [];
            $stmt = $pdo->prepare("SELECT id FROM options WHERE poll_id = ?");
            $stmt->execute([$poll_id]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
                $existing_option_ids[$id] = true;
            }

            foreach ($new_options as $option_id => $option_text) {
                $option_text = trim($option_text);
                if (empty($option_text)) continue;

                if (is_numeric($option_id) && isset($existing_option_ids[(int)$option_id])) {
                    // Update existing option
                    $stmt = $pdo->prepare("UPDATE options SET option_text = ? WHERE id = ?");
                    $stmt->execute([$option_text, (int)$option_id]);
                    unset($existing_option_ids[(int)$option_id]);
                } else {
                    // Insert new option
                    $stmt = $pdo->prepare("INSERT INTO options (poll_id, option_text) VALUES (?, ?)");
                    $stmt->execute([$poll_id, $option_text]);
                }
            }

            // Delete options that were removed from the form
            if (!empty($existing_option_ids)) {
                $placeholders = implode(',', array_fill(0, count($existing_option_ids), '?'));
                $ids_to_delete = array_keys($existing_option_ids);
                $pdo->prepare("DELETE FROM options WHERE id IN ($placeholders)")->execute($ids_to_delete);
            }

            $pdo->commit();
            $success = "✅ Poll updated successfully!";
            
            // Re-fetch the updated poll data
            $stmt = $pdo->prepare("SELECT * FROM polls WHERE id = ?");
            $stmt->execute([$poll_id]);
            $poll = $stmt->fetch();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = "❌ An error occurred: " . $e->getMessage();
        }
    }
}

// Re-fetch options after any update or initial load
$stmt = $pdo->prepare("SELECT * FROM options WHERE poll_id = ? ORDER BY id ASC");
$stmt->execute([$poll_id]);
$options = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Poll</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {font-family: system-ui, sans-serif; background:#f7f7f7; padding:24px; max-width:700px; margin:0 auto;}
        .card {background:#fff; padding:24px; border-radius:16px; box-shadow:0 6px 20px rgba(0,0,0,.06);}
        h1 {margin:0 0 16px; font-size:24px;}
        input[type="text"] {width:100%; padding:10px; margin-bottom:12px; border:1px solid #ccc; border-radius:8px;}
        .options-container {margin-top:12px;}
        .option-row {display:flex; align-items:center; margin-bottom:8px;}
        .option-row input {flex-grow:1;}
        .btn-remove {background:#ef4444; color:#fff; border:0; padding:8px 12px; border-radius:8px; cursor:pointer;}
        .btn-add {background:#22c55e; color:#fff; border:0; padding:8px 12px; border-radius:8px; cursor:pointer;}
        .actions {margin-top:20px; display:flex; gap:10px;}
        .btn-submit {background:#2b6cb0; color:#fff; padding:12px 20px; border:0; border-radius:10px; cursor:pointer;}
        .msg {padding:10px; border-radius:8px; margin-bottom:15px; font-size:14px;}
        .success {background:#ecfdf5; color:#065f46; border:1px solid #6ee7b7;}
        .error {background:#fef2f2; color:#991b1b; border:1px solid #fecaca;}
    </style>
</head>
<body>
    <div class="card">
        <h1>Edit Poll: <?= htmlspecialchars($poll['question']) ?></h1>

        <?php if ($success): ?>
            <div class="msg success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="msg error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($poll): ?>
            <form action="edit_poll.php?poll_id=<?= (int)$poll_id ?>" method="post">
                <label for="question">Poll Question:</label>
                <input type="text" id="question" name="question" value="<?= htmlspecialchars($poll['question']) ?>" required>

                <label>Options:</label>
                <div id="options-container" class="options-container">
                    <?php foreach ($options as $opt): ?>
                        <div class="option-row">
                            <input type="text" name="options[<?= (int)$opt['id'] ?>]" value="<?= htmlspecialchars($opt['option_text']) ?>" required>
                            <button type="button" class="btn-remove" onclick="removeOption(this)">-</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn-add" onclick="addOption()">+ Add Option</button>

                <div class="actions">
                    <button type="submit" class="btn-submit">Save Changes</button>
                    <a href="poll_results.php" class="btn-submit" style="background:#666; text-decoration:none;">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function addOption() {
            const container = document.getElementById('options-container');
            const newDiv = document.createElement('div');
            newDiv.className = 'option-row';
            newDiv.innerHTML = `
                <input type="text" name="options[]" placeholder="New Option" required>
                <button type="button" class="btn-remove" onclick="removeOption(this)">-</button>
            `;
            container.appendChild(newDiv);
        }

        function removeOption(button) {
            const container = document.getElementById('options-container');
            if (container.children.length > 2) {
                button.parentElement.remove();
            } else {
                alert("A poll must have at least two options.");
            }
        }
    </script>
</body>
</html>