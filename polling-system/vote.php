<?php
session_start();
require __DIR__ . '/db.php';

// Ensure voter is logged in
if (empty($_SESSION['voter_id'])) {
    die("❌ Error: Voter is not logged in.");
}

$voter_id  = (int) $_SESSION['voter_id']; // secure integer
$poll_id   = filter_input(INPUT_POST, 'poll_id', FILTER_VALIDATE_INT);
$option_id = filter_input(INPUT_POST, 'option_id', FILTER_VALIDATE_INT);

// Validate inputs
if (!$poll_id || !$option_id) {
    header("Location: index.php?msg=invalid_input");
    exit;
}

// ✅ Check if this voter already voted in this poll
$stmt = $pdo->prepare("SELECT id FROM votes WHERE poll_id = ? AND voter_id = ?");
$stmt->execute([$poll_id, $voter_id]);
$existing = $stmt->fetch();

if ($existing) {
    // Already voted → redirect with message
    header("Location: results.php?poll_id=$poll_id&msg=already_voted");
    exit;
}

// ✅ Insert new vote
$stmt = $pdo->prepare("INSERT INTO votes (poll_id, option_id, voter_id) VALUES (?, ?, ?)");
$stmt->execute([$poll_id, $option_id, $voter_id]);

// Redirect to results
header("Location: results.php?poll_id=$poll_id&msg=success");
exit;
