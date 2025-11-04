<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: Registration/signin_new.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $conn->prepare("SELECT name, email, member_since, profile_pic FROM users WHERE id=? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $member_since, $profile_pic);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile</title>
  <link rel="stylesheet" href="css/profile.css">
  <style>
    .hidden {
      display: none;
    }
    form {
      margin-top: 20px;
      text-align: left;
    }
    form h3 {
      margin-bottom: 10px;
      color: #333;
    }
    form input[type="text"],
    form input[type="email"],
    form input[type="password"],
    form input[type="file"] {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    form button {
      background: #007bff;
      color: white;
      border: none;
      padding: 10px;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
    }
    form button:hover {
      background: #0056b3;
    }
    .preview-img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      margin-top: 10px;
      border: 3px solid #007bff;
    }
  </style>
</head>
<body>
  <button onclick="window.history.back()" style="
    position: fixed;
    top: 15px;
    left: 15px;
    background: #007bff;
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    z-index: 999;
      ">← Back</button>



  <div class="profile-card">
    <img id="profilePic" src="<?= $profile_pic ? 'uploads/'.$profile_pic : 'image/user icon.png' ?>" alt="">
    <h2><?= htmlspecialchars($name) ?></h2>
    <p>Email: <?= htmlspecialchars($email) ?></p>
    <p>Member since: <?= date("F j, Y", strtotime($member_since)) ?></p>

    <div class="profile-actions">
      <button type="button" id="editBtn">Edit Profile</button>
      <button type="button" id="settingsBtn">Settings</button>
      <form method="POST" action="logout.php">
        <button type="submit">Log Out</button>
      </form>
    </div>

    <!-- Edit Profile Form -->
    <form id="editForm" class="hidden" method="POST" action="update_profile.php" enctype="multipart/form-data">
      <h3>Edit Profile</h3>
      <label>Change Username:</label>
      <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
      
      <label>Change Email:</label>
      <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

      <label>Change Profile Picture:</label>
      <input type="file" name="profile_pic" accept="image/*" onchange="previewImage(event)">
      <img id="preview" class="preview-img hidden" alt="Preview">

      <button type="submit">Save Changes</button>
    </form>

    <!-- Settings Form -->
    <form id="settingsForm" class="hidden" method="POST" action="change_password.php">
      <h3>Change Password</h3>
      <label>Current Password:</label>
      <input type="password" name="current_password" required>

      <label>New Password:</label>
      <input type="password" name="new_password" required>

      <label>Confirm New Password:</label>
      <input type="password" name="confirm_password" required>

      <button type="submit">Update Password</button>
    </form>
  </div>

  <script>
    const editBtn = document.getElementById('editBtn');
    const settingsBtn = document.getElementById('settingsBtn');
    const editForm = document.getElementById('editForm');
    const settingsForm = document.getElementById('settingsForm');

    editBtn.addEventListener('click', () => {
      editForm.classList.toggle('hidden');
      settingsForm.classList.add('hidden');
    });

    settingsBtn.addEventListener('click', () => {
      settingsForm.classList.toggle('hidden');
      editForm.classList.add('hidden');
    });

    function previewImage(event) {
      const reader = new FileReader();
      reader.onload = function() {
        const preview = document.getElementById('preview');
        preview.src = reader.result;
        preview.classList.remove('hidden');
      };
      reader.readAsDataURL(event.target.files[0]);
    }
  </script>
</body>
</html>
