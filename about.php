<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About - EduWell</title>
  <link rel="stylesheet" href="css/yawa.css">
  <style>
    .about-container {
      max-width: 800px;
      margin: 40px auto;
      padding: 0 20px;
    }
    
    .about-section {
      background: white;
      border-radius: 10px;
      padding: 30px;
      margin-bottom: 30px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .about-section h2 {
      color: #007bff;
      margin-bottom: 20px;
      border-bottom: 2px solid #007bff;
      padding-bottom: 10px;
    }
    
    .about-section p {
      line-height: 1.6;
      margin-bottom: 15px;
      color: #333;
    }
    
    .team-member {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 8px;
    }
    
    .team-member img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      margin-right: 15px;
      object-fit: cover;
    }
    
    .team-member-info h4 {
      margin: 0 0 5px 0;
      color: #333;
    }
    
    .team-member-info p {
      margin: 0;
      color: #666;
      font-size: 14px;
    }
    
    .values-list {
      list-style: none;
      padding: 0;
    }
    
    .values-list li {
      padding: 10px 0;
      border-bottom: 1px solid #eee;
      position: relative;
      padding-left: 30px;
    }
    
    .values-list li:before {
      content: "✓";
      position: absolute;
      left: 0;
      color: #007bff;
      font-weight: bold;
    }
    
    .values-list li:last-child {
      border-bottom: none;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <div class="navbar">
    <div class="navbar-left">
      <img src="image/EW.png" alt="EduWell Logo" class="logo">
      <a href="index.php"><button>Home</button></a>
      <a href="profile.php"><button>Profile</button></a>
      <a href="contacts.php"><button>Contact</button></a>
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

  <!-- About Content -->
  <div class="about-container">
    <div class="about-section">
      <h2>About EduWell</h2>
      <p>EduWell is a comprehensive educational platform designed to support students' academic success and mental well-being. We understand that learning goes beyond textbooks and exams – it's about creating a supportive environment where students can thrive both academically and personally.</p>
      
      <p>Our mission is to provide students with the tools, resources, and support they need to manage their studies effectively while maintaining their mental health and overall well-being.</p>
    </div>

    <div class="about-section">
      <h2>Our Mission</h2>
      <p>To empower students with innovative tools and resources that promote both academic excellence and mental wellness, creating a holistic learning environment where every student can succeed.</p>
    </div>

    <div class="about-section">
      <h2>Our Vision</h2>
      <p>To be the leading platform that bridges the gap between academic achievement and mental health support, ensuring that every student has access to the resources they need to thrive in their educational journey.</p>
    </div>

    <div class="about-section">
      <h2>Our Values</h2>
      <ul class="values-list">
        <li><strong>Student-Centered:</strong> Every feature and resource is designed with students' needs in mind</li>
        <li><strong>Mental Health First:</strong> We prioritize mental wellness as the foundation for academic success</li>
        <li><strong>Accessibility:</strong> Our platform is designed to be accessible to all students regardless of their background</li>
        <li><strong>Innovation:</strong> We continuously improve and innovate to better serve our community</li>
        <li><strong>Support:</strong> We provide comprehensive support for both academic and personal challenges</li>
        <li><strong>Community:</strong> We foster a supportive community where students can connect and help each other</li>
      </ul>
    </div>

    <div class="about-section">
      <h2>What We Offer</h2>
      <p><strong>Study Management Tools:</strong> Task tracking, study logs, and progress monitoring to help you stay organized and focused.</p>
      
      <p><strong>Mental Health Support:</strong> Resources and guidance for managing stress, anxiety, depression, and other mental health challenges that can impact your studies.</p>
      
      <p><strong>Personal Dashboard:</strong> A personalized space where you can track your progress, set goals, and access resources tailored to your needs.</p>
      
      <p><strong>Resource Library:</strong> Curated resources and articles to help you develop better study habits and coping strategies.</p>
    </div>

    <div class="about-section">
      <h2>Our Commitment</h2>
      <p>We are committed to providing a safe, supportive, and inclusive environment for all students. We believe that mental health is just as important as academic achievement, and we're here to support you in both areas.</p>
      
      <p>If you're struggling with your studies or mental health, remember that seeking help is a sign of strength, not weakness. EduWell is here to support you every step of the way.</p>
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
</body>
</html>
