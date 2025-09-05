<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require __DIR__ . '/db.php';

$success = $error = "";

// Handle form submission to create a new poll
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question'] ?? '');
    // Filter out any empty option fields submitted
    $options  = isset($_POST['options']) ? array_filter(array_map('trim', $_POST['options'])) : [];

    // Validate that there is a question and at least two options
    if ($question && count($options) >= 2) {
        try {
            $pdo->beginTransaction();

            // Insert the poll question into the 'polls' table
            $stmt = $pdo->prepare("INSERT INTO polls (question, is_active) VALUES (?, 1)");
            $stmt->execute([$question]);
            $poll_id = $pdo->lastInsertId();

            // Prepare to insert the options into the 'options' table
            $stmtOpt = $pdo->prepare("INSERT INTO options (poll_id, option_text) VALUES (?, ?)");
            foreach ($options as $opt) {
                $stmtOpt->execute([$poll_id, $opt]);
            }

            $pdo->commit();
            $success = "✅ Poll created successfully!";
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            // For debugging, you might want to log the error: error_log($e->getMessage());
            $error = "❌ An error occurred while creating the poll. Please try again.";
        }
    } else {
        $error = "⚠️ Please provide a question and at least 2 non-empty options.";
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
            --danger-color: #ef4444;
            --danger-dark: #b91c1c;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-color: #1f2937;
            --muted-color: #6b7280;
            --border-color: #d1d5db;
        }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: var(--bg-color);
            margin: 0;
            padding: 2rem 1rem;
        }
        .container {
            max-width: 640px;
            margin: 0 auto;
        }
        .card {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }
        h1 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--text-color);
            font-family: Georgia, serif;
        }
        label {
            display: block;
            margin: 1.25rem 0 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            box-sizing: border-box;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        .option-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.25rem;
            margin-top: 0.5rem;
            background: var(--primary-color);
            color: #fff;
            border: 0;
            border-radius: 0.75rem;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            font-weight: 600;
            transition: background-color 0.2s ease-in-out, transform 0.1s ease;
        }
        .btn:active {
            transform: scale(0.97);
        }
        .btn:hover { background: var(--primary-dark); }
        .btn-secondary { background: #e5e7eb; color: var(--text-color); }
        .btn-secondary:hover { background: #d1d5db; }
        .btn-danger { background: var(--danger-color); color: #fff; }
        .btn-danger:hover { background: var(--danger-dark); }
        .btn-small { padding: 0.3rem 0.6rem; font-size: 0.8rem; }
        
        .msg {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 0.75rem;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        .top-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .form-actions .btn {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="top-actions">
                <h1>Create a New Poll</h1>
                <a href="logout.php" class="btn">Logout</a>
            </div>

            <?php if (!empty($success)): ?>
                <div class="msg success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="msg error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <div>
                    <label for="q">Poll Question</label>
                    <input type="text" id="q" name="question" class="form-input" placeholder required>
                </div>

                <div>
                    <label>Options</label>
                    <div id="options-container">
                        <div class="option-group">
                            <input class="form-input" type="text" name="options[]" placeholder="Option 1" required>
                        </div>
                        <div class="option-group">
                            <input class="form-input" type="text" name="options[]" placeholder="Option 2" required>
                        </div>
                    </div>
                    <button type="button" id="add-option" class="btn btn-secondary">➕ Add Option</button>
                </div>
                
                <hr style="margin: 2rem 0; border: 0; border-top: 1px solid var(--border-color);">
                
                <div class="form-actions">
                    <button type="submit" class="btn">Create Poll</button>
                    <a href="poll_results.php" class="btn">View All Polls</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const optionsContainer = document.getElementById('options-container');
            const addOptionBtn = document.getElementById('add-option');

            addOptionBtn.addEventListener('click', function () {
                const optionCount = optionsContainer.getElementsByClassName('option-group').length;
                
                const newOptionGroup = document.createElement('div');
                newOptionGroup.className = 'option-group';

                const newOptionInput = document.createElement('input');
                newOptionInput.type = 'text';
                newOptionInput.name = 'options[]';
                newOptionInput.className = 'form-input';
                newOptionInput.placeholder = 'Option ' + (optionCount + 1);
                
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-danger btn-small';
                removeBtn.textContent = '✖';
                removeBtn.onclick = function() {
                    newOptionGroup.remove();
                };

                newOptionGroup.appendChild(newOptionInput);
                newOptionGroup.appendChild(removeBtn);
                optionsContainer.appendChild(newOptionGroup);
            });
        });
    </script>
</body>
</html>

