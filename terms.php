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
  <title>Terms of Service - EduWell</title>
  <link rel="stylesheet" href="css/yawa.css">
  <style>
    .terms-container {
      max-width: 800px;
      margin: 40px auto;
      padding: 0 20px;
    }
    
    .terms-section {
      background: white;
      border-radius: 10px;
      padding: 30px;
      margin-bottom: 30px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .terms-section h2 {
      color: #007bff;
      margin-bottom: 20px;
      border-bottom: 2px solid #007bff;
      padding-bottom: 10px;
    }
    
    .terms-section h3 {
      color: #333;
      margin-top: 25px;
      margin-bottom: 15px;
    }
    
    .terms-section p {
      line-height: 1.6;
      margin-bottom: 15px;
      color: #333;
    }
    
    .terms-section ul {
      margin-bottom: 15px;
      padding-left: 20px;
    }
    
    .terms-section li {
      margin-bottom: 8px;
      line-height: 1.5;
    }
    
    .last-updated {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      border-left: 4px solid #007bff;
      margin-bottom: 30px;
    }
    
    .highlight-box {
      background: #fff3cd;
      border: 1px solid #ffeaa7;
      border-radius: 8px;
      padding: 15px;
      margin: 20px 0;
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

  <!-- Terms Content -->
  <div class="terms-container">
    <div class="last-updated">
      <p><strong>Last Updated:</strong> January 2025</p>
    </div>

    <div class="terms-section">
      <h2>Terms of Service</h2>
      <p>Welcome to EduWell! These Terms of Service ("Terms") govern your use of our educational platform and services. By accessing or using EduWell, you agree to be bound by these Terms.</p>
    </div>

    <div class="terms-section">
      <h3>1. Acceptance of Terms</h3>
      <p>By creating an account, accessing, or using EduWell, you acknowledge that you have read, understood, and agree to be bound by these Terms and our Privacy Policy. If you do not agree to these Terms, you may not use our services.</p>
    </div>

    <div class="terms-section">
      <h3>2. Description of Service</h3>
      <p>EduWell is an educational platform that provides:</p>
      <ul>
        <li>Study management and task tracking tools</li>
        <li>Mental health support resources</li>
        <li>Personal dashboard and progress monitoring</li>
        <li>Educational content and resources</li>
        <li>Community features and support</li>
      </ul>
    </div>

    <div class="terms-section">
      <h3>3. User Accounts</h3>
      <p>To use certain features of EduWell, you must create an account. You agree to:</p>
      <ul>
        <li>Provide accurate, current, and complete information</li>
        <li>Maintain and update your account information</li>
        <li>Keep your password secure and confidential</li>
        <li>Notify us immediately of any unauthorized use</li>
        <li>Be responsible for all activities under your account</li>
      </ul>
    </div>

    <div class="terms-section">
      <h3>4. Acceptable Use</h3>
      <p>You agree to use EduWell only for lawful purposes and in accordance with these Terms. You may not:</p>
      <ul>
        <li>Use the service for any illegal or unauthorized purpose</li>
        <li>Violate any applicable laws or regulations</li>
        <li>Transmit any harmful, threatening, or offensive content</li>
        <li>Attempt to gain unauthorized access to our systems</li>
        <li>Interfere with or disrupt our services</li>
        <li>Use automated systems to access our platform without permission</li>
        <li>Share your account credentials with others</li>
      </ul>
    </div>

    <div class="terms-section">
      <h3>5. User Content</h3>
      <p>You retain ownership of content you create and share on EduWell. By posting content, you grant us a license to:</p>
      <ul>
        <li>Display and distribute your content on our platform</li>
        <li>Modify content as necessary for technical purposes</li>
        <li>Use content to improve our services</li>
      </ul>
      <p>You are responsible for ensuring your content does not violate any laws or infringe on others' rights.</p>
    </div>

    <div class="terms-section">
      <h3>6. Privacy and Data Protection</h3>
      <p>Your privacy is important to us. Our collection and use of personal information is governed by our Privacy Policy. By using EduWell, you consent to the collection and use of information as described in our Privacy Policy.</p>
    </div>

    <div class="terms-section">
      <h3>7. Intellectual Property</h3>
      <p>EduWell and its content are protected by intellectual property laws. You may not:</p>
      <ul>
        <li>Copy, modify, or distribute our content without permission</li>
        <li>Use our trademarks or logos without authorization</li>
        <li>Reverse engineer or attempt to extract source code</li>
        <li>Create derivative works based on our platform</li>
      </ul>
    </div>

    <div class="terms-section">
      <h3>8. Service Availability</h3>
      <p>We strive to provide reliable service, but we cannot guarantee uninterrupted access. We may:</p>
      <ul>
        <li>Perform scheduled maintenance</li>
        <li>Update or modify our services</li>
        <li>Suspend service for technical reasons</li>
        <li>Discontinue features with reasonable notice</li>
      </ul>
    </div>

    <div class="terms-section">
      <h3>9. Limitation of Liability</h3>
      <div class="highlight-box">
        <p><strong>Important:</strong> EduWell is provided "as is" without warranties of any kind. We are not liable for any indirect, incidental, or consequential damages arising from your use of our services.</p>
      </div>
      <p>Our total liability to you for any claims related to our services shall not exceed the amount you paid us in the 12 months preceding the claim.</p>
    </div>

    <div class="terms-section">
      <h3>10. Mental Health Disclaimer</h3>
      <p>EduWell provides educational resources and support tools, but we are not a substitute for professional mental health care. If you are experiencing a mental health crisis:</p>
      <ul>
        <li>Contact emergency services (911) immediately</li>
        <li>Reach out to a mental health professional</li>
        <li>Use crisis hotlines and support services</li>
        <li>Seek help from trusted friends or family</li>
      </ul>
    </div>

    <div class="terms-section">
      <h3>11. Termination</h3>
      <p>We may terminate or suspend your account if you violate these Terms. You may also terminate your account at any time. Upon termination:</p>
      <ul>
        <li>Your right to use our services ceases immediately</li>
        <li>We may delete your account and associated data</li>
        <li>Certain provisions of these Terms survive termination</li>
      </ul>
    </div>

    <div class="terms-section">
      <h3>12. Changes to Terms</h3>
      <p>We may update these Terms from time to time. We will notify you of significant changes by:</p>
      <ul>
        <li>Posting updated Terms on our website</li>
        <li>Sending email notifications</li>
        <li>Displaying notices on our platform</li>
      </ul>
      <p>Your continued use of our services after changes constitutes acceptance of the updated Terms.</p>
    </div>

    <div class="terms-section">
      <h3>13. Governing Law</h3>
      <p>These Terms are governed by the laws of the jurisdiction where EduWell operates. Any disputes will be resolved in the appropriate courts of that jurisdiction.</p>
    </div>

    <div class="terms-section">
      <h3>14. Contact Information</h3>
      <p>If you have questions about these Terms, please contact us:</p>
      <ul>
        <li><strong>Email:</strong> legal@eduwell.com</li>
        <li><strong>Address:</strong> EduWell Legal Team, 123 Education Street, Learning City, LC 12345</li>
        <li><strong>Phone:</strong> 1-800-EDUWELL</li>
      </ul>
    </div>

    <div class="terms-section">
      <h3>15. Severability</h3>
      <p>If any provision of these Terms is found to be unenforceable, the remaining provisions will remain in full force and effect.</p>
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
