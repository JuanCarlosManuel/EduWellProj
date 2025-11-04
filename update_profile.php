<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: Registration/signin_new.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);

$profile_pic = null;
if (!empty($_FILES['profile_pic']['name'])) {
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = basename($_FILES["profile_pic"]["name"]);
    $targetFilePath = $targetDir . time() . "_" . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array(strtolower($fileType), $allowedTypes)) {
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFilePath);
        $profile_pic = basename($targetFilePath);
    }
}

if ($profile_pic) {
    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, profile_pic=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $profile_pic, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
    $stmt->bind_param("ssi", $name, $email, $user_id);
}
$stmt->execute();
$stmt->close();

// Update session data so index.php reflects changes instantly
$_SESSION['user_name'] = $name;

// If user changed their profile picture, update it in the session too
if ($profile_pic) {
    $_SESSION['profile_pic'] = $profile_pic;
}

// Set a flag to indicate profile was updated
$_SESSION['profile_updated'] = true;
$_SESSION['profile_update_time'] = time();

header("Location: index.php?updated=1");
exit();

