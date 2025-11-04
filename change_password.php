<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: Registration/signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Fetch old password
$stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($hashedPassword);
$stmt->fetch();
$stmt->close();

if (!password_verify($current_password, $hashedPassword)) {
    die("❌ Incorrect current password.");
}

if ($new_password !== $confirm_password) {
    die("❌ New passwords do not match.");
}

$newHashed = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
$stmt->bind_param("si", $newHashed, $user_id);
$stmt->execute();
$stmt->close();

header("Location: profile.php");
exit();
?>
