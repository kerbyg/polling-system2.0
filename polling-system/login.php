<?php
session_start();
require __DIR__ . '/db.php';

$error = "";
$success = "";

// Check for a success message from registration
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear the message after displaying it
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        // First, try to log in as a voter
        $stmt = $pdo->prepare("SELECT * FROM voters WHERE username = ?");
        $stmt->execute([$username]);
        $voter = $stmt->fetch();

        if ($voter && password_verify($password, $voter['password'])) {
            $_SESSION['voter_id'] = $voter['id'];
            $_SESSION['voter_username'] = $voter['username'];
            header("Location: voter_dashboard.php");
            exit;
        }

        // If not a voter, try to log in as an admin
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            header("Location: admin.php");
            exit;
        }
        
        // If both attempts fail
        $error = "Invalid username or password.";

    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login</title>
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
            margin-bottom: 1rem;
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
        <div class="logo">🔐</div>
        <h2>Sign in to your account</h2>
        <?php if ($error): ?>
            <div class="msg error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="msg success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="input" placeholder="Enter your username" required>
            </div>
            
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="input" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>
        
        <p class="footer-text">
            Don't have an account? <a href="voter_register.php">Register here</a>
        </p>
    </div>
</body>
</html>
