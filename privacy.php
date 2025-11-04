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
  <title>Privacy Policy - EduWell</title>
  <link rel="stylesheet" href="css/yawa.css">
  <style>
    .privacy-container {
      max-width: 800px;
      margin: 40px auto;
      padding: 0 20px;
    }
    
    .privacy-section {
      background: white;
      border-radius: 10px;
      padding: 30px;
      margin-bottom: 30px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .privacy-section h2 {
      color: #007bff;
      margin-bottom: 20px;
      border-bottom: 2px solid #007bff;
      padding-bottom: 10px;
    }
    
    .privacy-section h3 {
      color: #333;
      margin-top: 25px;
      margin-bottom: 15px;
    }
    
    .privacy-section p {
      line-height: 1.6;
      margin-bottom: 15px;
      color: #333;
    }
    
    .privacy-section ul {
      margin-bottom: 15px;
      padding-left: 20px;
    }
    
    .privacy-section li {
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

  <!-- Privacy Content -->
  <div class="privacy-container">
    <div class="last-updated">
      <p><strong>Last Updated:</strong> January 2025</p>
    </div>

    <div class="privacy-section">
      <h2>Privacy Policy</h2>
      <p>At EduWell, we are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, and safeguard your information when you use our platform.</p>
    </div>

    <div class="privacy-section">
      <h3>Information We Collect</h3>
      <p>We collect information that you provide directly to us, such as when you:</p>
      <ul>
        <li>Create an account or profile</li>
        <li>Use our study tracking and task management features</li>
        <li>Upload profile pictures or other content</li>
        <li>Contact us for support</li>
        <li>Participate in surveys or feedback forms</li>
      </ul>
      
      <p><strong>Types of information we collect include:</strong></p>
      <ul>
        <li>Personal information (name, email address, profile information)</li>
        <li>Academic information (study logs, task lists, progress data)</li>
        <li>Usage data (how you interact with our platform)</li>
        <li>Device information (browser type, operating system)</li>
      </ul>
    </div>

    <div class="privacy-section">
      <h3>How We Use Your Information</h3>
      <p>We use the information we collect to:</p>
      <ul>
        <li>Provide and improve our educational services</li>
        <li>Personalize your learning experience</li>
        <li>Track your academic progress and provide insights</li>
        <li>Send you important updates about our services</li>
        <li>Respond to your inquiries and provide customer support</li>
        <li>Ensure the security and integrity of our platform</li>
        <li>Comply with legal obligations</li>
      </ul>
    </div>

    <div class="privacy-section">
      <h3>Information Sharing and Disclosure</h3>
      <p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except in the following circumstances:</p>
      <ul>
        <li><strong>Service Providers:</strong> We may share information with trusted third-party service providers who assist us in operating our platform</li>
        <li><strong>Legal Requirements:</strong> We may disclose information when required by law or to protect our rights and safety</li>
        <li><strong>Business Transfers:</strong> In the event of a merger or acquisition, your information may be transferred to the new entity</li>
        <li><strong>Consent:</strong> We may share information with your explicit consent</li>
      </ul>
    </div>

    <div class="privacy-section">
      <h3>Data Security</h3>
      <p>We implement appropriate security measures to protect your personal information:</p>
      <ul>
        <li>Encryption of data in transit and at rest</li>
        <li>Regular security assessments and updates</li>
        <li>Access controls and authentication measures</li>
        <li>Secure data storage and backup procedures</li>
        <li>Staff training on data protection practices</li>
      </ul>
    </div>

    <div class="privacy-section">
      <h3>Your Rights and Choices</h3>
      <p>You have the following rights regarding your personal information:</p>
      <ul>
        <li><strong>Access:</strong> Request a copy of the personal information we hold about you</li>
        <li><strong>Correction:</strong> Update or correct inaccurate information</li>
        <li><strong>Deletion:</strong> Request deletion of your personal information</li>
        <li><strong>Portability:</strong> Request a copy of your data in a portable format</li>
        <li><strong>Opt-out:</strong> Unsubscribe from marketing communications</li>
        <li><strong>Account Deletion:</strong> Request complete deletion of your account and associated data</li>
      </ul>
    </div>

    <div class="privacy-section">
      <h3>Cookies and Tracking</h3>
      <p>We use cookies and similar technologies to:</p>
      <ul>
        <li>Remember your preferences and settings</li>
        <li>Analyze how you use our platform</li>
        <li>Improve our services and user experience</li>
        <li>Provide personalized content and features</li>
      </ul>
      <p>You can control cookie settings through your browser preferences.</p>
    </div>

    <div class="privacy-section">
      <h3>Children's Privacy</h3>
      <p>EduWell is designed for students and educational use. We do not knowingly collect personal information from children under 13 without parental consent. If you believe we have collected information from a child under 13, please contact us immediately.</p>
    </div>

    <div class="privacy-section">
      <h3>Changes to This Privacy Policy</h3>
      <p>We may update this Privacy Policy from time to time. We will notify you of any significant changes by:</p>
      <ul>
        <li>Posting the updated policy on our website</li>
        <li>Sending you an email notification</li>
        <li>Displaying a notice on our platform</li>
      </ul>
      <p>Your continued use of our services after changes are posted constitutes acceptance of the updated policy.</p>
    </div>

    <div class="privacy-section">
      <h3>Contact Us</h3>
      <p>If you have any questions about this Privacy Policy or our data practices, please contact us:</p>
      <ul>
        <li><strong>Email:</strong> privacy@eduwell.com</li>
        <li><strong>Address:</strong> EduWell Privacy Team, 123 Education Street, Learning City, LC 12345</li>
        <li><strong>Phone:</strong> 1-800-EDUWELL</li>
      </ul>
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
