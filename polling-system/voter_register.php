<?php
session_start();
require __DIR__ . '/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Validate input
    if (!$username || !$password || !$confirm) {
        $error = "⚠️ All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "⚠️ Passwords do not match.";
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM voters WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "⚠️ Username already taken.";
        } else {
            // Insert voter
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO voters (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashed]);
            $success = "✅ Account created successfully! You can now login.";
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Voter Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --primary-color: #2b6cb0;
            --secondary-color: #2c5282;
            --light-gray: #f5f5f5;
            --border-color: #e2e8f0;
            --shadow-color: rgba(0, 0, 0, 0.08);
            --error-bg: #fee2e2;
            --error-text: #991b1b;
            --success-bg: #d1fae5;
            --success-text: #065f46;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: var(--light-gray);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
        }
        .card {
            background: #fff;
            padding: 3rem 2rem;
            border-radius: 1rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 25px var(--shadow-color);
            text-align: center;
        }
        .card h2 {
            margin: 0 0 1rem;
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
        }
        .input-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        .input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
            box-sizing: border-box;
        }
        .input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(43, 108, 176, 0.2);
        }
        label {
            display: block;
            font-size: 0.875rem;
            color: #666;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .btn {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 0.5rem;
            background: var(--primary-color);
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }
        .btn:hover {
            background: var(--secondary-color);
        }
        .btn:active {
            transform: scale(0.98);
        }
        .msg {
            margin-top: 1rem;
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            text-align: left;
        }
        .error {
            background: var(--error-bg);
            color: var(--error-text);
        }
        .success {
            background: var(--success-bg);
            color: var(--success-text);
        }
        .footer-text {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.875rem;
            color: #718096;
        }
        .footer-text a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        .footer-text a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        .logo {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">👤</div>
        <h2>Create a Voter Account</h2>
        <?php if ($error): ?>
            <p class="msg error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="msg success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>
        <form method="post">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="input" placeholder="Enter a username" required>
            </div>
            
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="input" placeholder="Create a password" required>
            </div>

            <div class="input-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="input" placeholder="Confirm your password" required>
            </div>

            <button type="submit" class="btn">Register</button>
        </form>
        
        <p class="footer-text">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</body>
</html>