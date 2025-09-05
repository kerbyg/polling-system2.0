<?php
// delete_admin.php
require __DIR__ . '/db.php';

// First, delete the old 'admin' user
$stmt = $pdo->prepare("DELETE FROM admins WHERE username = ?");
$stmt->execute(['admin']);

// Then, create the new 'admin' user with the desired password
$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
$stmt->execute([$username, $password]);

echo "Admin user reset and created with new password.";
?>