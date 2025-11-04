<?php
// Navbar component - Include this in all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Navbar -->
<div class="navbar">
  <div class="navbar-left">
    <img src="image/EW.png" alt="EduWell Logo" class="logo">
    <a href="index.php"><button>Home</button></a>
    <a href="profile.php"><button>Profile</button></a>
    <a href="contacts.php"><button>Contact</button></a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <?php
      // Check user role
      include 'db.php';
      $user_id = $_SESSION['user_id'];
      $role_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
      $role_stmt->bind_param("i", $user_id);
      $role_stmt->execute();
      $role_result = $role_stmt->get_result();
      $user_role = $role_result->fetch_assoc()['role'] ?? 'student';
      $role_stmt->close();
      ?>
      
      <?php if ($user_role === 'student'): ?>
        <a href="grades.php"><button>Grades</button></a>
        <a href="reports.php"><button>Reports</button></a>
        <a href="analytics.php"><button>Analytics</button></a>
      <?php elseif ($user_role === 'teacher' || $user_role === 'admin'): ?>
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

