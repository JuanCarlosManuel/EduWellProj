<?php
// Start session at the top of every page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EduWell Dashboard</title>
  <link rel="stylesheet" href="css/yawa.css">
</head>

<body>
  <!-- Navbar -->
  <div class="navbar">
    <div class="navbar-left">
      <img src="image/EW.png" alt="replacement" class="logo">
      <a href="index.php"><button>Home</button></a>
      <a href="profile.php"><button>Profile</button></a>
      <a href="contacts.php"><button>Contact</button></a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <?php
        include 'db.php';
        $nav_user_id = $_SESSION['user_id'];
        $nav_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $nav_stmt->bind_param("i", $nav_user_id);
        $nav_stmt->execute();
        $nav_result = $nav_stmt->get_result();
        $nav_role = $nav_result->fetch_assoc()['role'] ?? 'student';
        $nav_stmt->close();
        ?>
        <?php if ($nav_role === 'student'): ?>
          <a href="grades.php"><button>Grades</button></a>
          <a href="reports.php"><button>Reports</button></a>
          <a href="analytics.php"><button>Analytics</button></a>
        <?php elseif ($nav_role === 'teacher' || $nav_role === 'admin'): ?>
          <a href="teacher_dashboard.php"><button>Teacher Dashboard</button></a>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <div class="navbar-right">
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="Registration/signin_new.php"><button>Login</button></a>
        <a href="Registration/register_new.php"><button>Sign-up</button></a>
      <?php else: ?>
        <a href="logout.php"><button>Logout</button></a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Dashboard Content -->
  <div class="container">
    
    <!-- Left Section -->
    <div class="card profile">
  <?php
  include 'db.php'; // Make sure db.php is included to fetch user data
  
  $profile_pic = 'image/user icon.jpg'; // default
  $user_name = 'User';

  if (isset($_SESSION['user_id'])) {
      $user_id = $_SESSION['user_id'];
      $stmt = $conn->prepare("SELECT profile_pic, name FROM users WHERE id = ? LIMIT 1");
      $stmt->bind_param("i", $user_id);
      $stmt->execute();
      $stmt->bind_result($profile_pic_db, $name_db);
      $stmt->fetch();
      $stmt->close();

      // If user has uploaded a profile picture, use it
      if (!empty($profile_pic_db)) {
          $profile_pic = 'uploads/' . $profile_pic_db;
      }
      
      // Use database name if available, otherwise session name
      if (!empty($name_db)) {
          $user_name = $name_db;
      } elseif (isset($_SESSION['user_name'])) {
          $user_name = $_SESSION['user_name'];
      }
  }
  ?>
  
  <div class="profile-picture-container">
    <img id="profile-picture" src="<?= htmlspecialchars($profile_pic); ?>" alt="User" class="profile-picture">
    <div class="profile-picture-overlay">
      <span class="update-indicator">✓</span>
    </div>
  </div>

  <div class="welcome-message">
    <p id="welcome-text"><b>Welcome back, <span id="user-name"><?= htmlspecialchars($user_name); ?></span>!</b></p>
  </div>

  <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
    <div class="update-success-message">
      <p>✅ Profile updated successfully!</p>
    </div>
  <?php endif; ?>

  <div class="quick-actions">
    <button onclick="openStudyModal()">Add Study Log</button>
    <button onclick="openTrackerModal()">View Tracker</button>
    <button onclick="window.location.href='profile.php'">Go to Profile</button>
  </div>
</div>


    <!-- Middle Section -->
    <div>
      <div class="card">
        <h3>Announcements</h3>
        <div id="announcement-area">
          <p>🔔 Stay focused today! Don’t forget to check your study log and update your progress.</p>
          <p>📘 Tip: Manage your anxiety by listing tasks one at a time!</p>
        </div>
      </div>

<div class="card" style="margin-top:20px;">
  <div class="task-header">
    <h2>My Tasks</h2>
    <div class="task-stats">
      <span id="task-count">0 tasks</span>
    </div>
  </div>
  
  <div class="task-filters">
    <button class="filter-btn active" data-filter="all">All</button>
    <button class="filter-btn" data-filter="pending">Pending</button>
    <button class="filter-btn" data-filter="completed">Completed</button>
  </div>
  
  <div id="task-list" class="task-list">
    <div class="no-tasks-message">
      <p>No tasks yet. Add your first task!</p>
    </div>
  </div>
</div>
    </div>

    <!-- Right Section -->
    <div>
      <?php if (isset($_SESSION['user_id'])): ?>
        <?php
        // Show Grade Summary for students
        $rs_user_id = $_SESSION['user_id'];
        $rs_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $rs_stmt->bind_param("i", $rs_user_id);
        $rs_stmt->execute();
        $rs_result = $rs_stmt->get_result();
        $rs_role = $rs_result->fetch_assoc()['role'] ?? 'student';
        $rs_stmt->close();
        if ($rs_role === 'student') {
          $gs = $conn->prepare("SELECT AVG(g.percentage) as avg_score, COUNT(DISTINCT g.id) as graded_count, COUNT(DISTINCT a.id) as total_assignments FROM enrollments e JOIN courses c ON e.course_id = c.id LEFT JOIN assignments a ON a.course_id = c.id LEFT JOIN grades g ON g.assignment_id = a.id AND g.student_id = ? WHERE e.student_id = ?");
          $gs->bind_param("ii", $rs_user_id, $rs_user_id);
          $gs->execute();
          $grade_summary = $gs->get_result()->fetch_assoc();
          $gs->close();
        }
        ?>
        <?php if (isset($rs_role) && $rs_role === 'student'): ?>
          <div class="card">
            <h3>Grade Summary</h3>
            <div style="padding: 15px;">
              <?php if (!empty($grade_summary['avg_score'])): ?>
                <p style="font-size: 32px; font-weight: bold; color: #007bff; margin: 10px 0;">
                  <?= number_format($grade_summary['avg_score'], 2) ?>%
                </p>
                <p style="color: #666; margin: 5px 0;">Overall Average</p>
                <p style="color: #666; margin: 5px 0;">
                  <small><?= $grade_summary['graded_count'] ?>/<?= $grade_summary['total_assignments'] ?> assignments graded</small>
                </p>
                <a href="grades.php" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">
                  View All Grades →
                </a>
              <?php else: ?>
                <p style="color: #666;">No grades yet. Check back after assignments are graded.</p>
                <a href="grades.php" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">
                  View Grades →
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      <?php endif; ?>
      <div class="card">
        <h3>Resources</h3>
        <div class="resources">
          <div class="resource-card" onclick="redirectToSupport('depressed')">
            <div class="resource-icon">😔</div>
            <div class="resource-text">
              <h4>If you feel depressed...</h4>
              <p>Find support and resources</p>
            </div>
          </div>
          <div class="resource-card" onclick="redirectToSupport('drained')">
            <div class="resource-icon">😴</div>
            <div class="resource-text">
              <h4>If you feel drained...</h4>
              <p>Get help recharging</p>
            </div>
          </div>
          <div class="resource-card" onclick="redirectToSupport('anxious')">
            <div class="resource-icon">😰</div>
            <div class="resource-text">
              <h4>If you feel anxious...</h4>
              <p>Get help managing anxiety</p>
            </div>
          </div>
          <div class="resource-card" onclick="redirectToSupport('stressed')">
            <div class="resource-icon">😤</div>
            <div class="resource-text">
              <h4>If you feel stressed...</h4>
              <p>Learn stress management</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Study Log Modal -->
  <div id="studyModal" class="modal">
    <div class="modal-content">
      <h3>Add Study Task</h3>
      <label for="study-date"><b>Select Date:</b></label><br>
      <input type="date" id="study-date"><br><br>
      <textarea id="study-task" placeholder="Write what you need to do..."></textarea>
      <div class="modal-buttons">
        <button onclick="saveStudyLog()">Save</button>
        <button class="cancel" onclick="closeStudyModal()">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Tracker Modal -->
  <div id="trackerModal" class="modal">
    <div class="modal-content">
      <h3>Study Tracker (History)</h3>
      <div id="tracker-list"></div>
      <div class="modal-buttons">
        <button class="cancel" onclick="closeTrackerModal()">Close</button>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-content">
      <div class="footer-left">
        <p><b>EduWell</b> © 2025 | All Rights Reserved</p>
      </div>
      
      <div class="footer-right">
        <a href="about.php">About</a>
        <a href="privacy.php">Privacy</a>
        <a href="terms.php">Terms</a>
      </div>
    </div>
  </footer>

  <script src="css/yawa.js"></script>
  <script>
    function redirectToSupport(type) {
      // Redirect to contacts.php with the support type as a parameter
      window.location.href = 'contacts.php?support=' + type;
    }
  </script>
</body>
</html>
