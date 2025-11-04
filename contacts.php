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
  <title>EduWell Contacts</title>
  <link rel="stylesheet" href="css/contacts.css">
  <style>
    /* Optional: makes hidden buttons actually disappear, not just disabled */
    .hidden {
      display: none !important;
    }
  </style>
</head>
<body>
  <button onclick="window.history.back()" style="
    position: fixed;
    top: 17.5px;
    left: 35px;
    background: #007bff;
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    z-index: 999;
      ">← Back</button>

  <!-- Navbar -->
  <div class="navbar">
    <div class="navbar-left">
      <img src="image/EW.png" alt="replacement" class="logo">
      <a href="index.php"><button>Home</button></a>
      <a href="profile.php"><button>Profile</button></a>
      <a href="contacts.php"><button>Contact</button></a>
    </div>

    <div class="navbar-right">
      <a href="Registration/signin_new.php" class="<?= isset($_SESSION['user_id']) ? 'hidden' : '' ?>"><button>Login</button></a>
      <a href="Registration/register_new.php" class="<?= isset($_SESSION['user_id']) ? 'hidden' : '' ?>"><button>Sign-up</button></a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="card-container">
    <div class="card">
      <h3>Depressed</h3>
      <p><b>Description:</b> If you're feeling down, sad, or hopeless, we're here to help you find support and resources.</p>
      <button class="see-more" onclick="redirectToHelp('depressed')">See More</button>
    </div>

    <div class="card">
      <h3>Exhausted</h3>
      <p><b>Description:</b> Feeling exhausted, burned out, or emotionally drained? Find ways to recharge and recover.</p>
      <button class="see-more" onclick="redirectToHelp('drained')">See More</button>
    </div>

    <div class="card">
      <h3>Anxious</h3>
      <p><b>Description:</b> Struggling with worry, fear, or anxiety? Discover techniques to help you feel more calm and in control.</p>
      <button class="see-more" onclick="redirectToHelp('anxious')">See More</button>
    </div>

    <div class="card">
      <h3>Stressed</h3>
      <p><b>Description:</b> Overwhelmed by stress? Learn effective strategies to manage and reduce stress in your daily life.</p>
      <button class="see-more" onclick="redirectToHelp('stressed')">See More</button>
    </div>
  </div>

  <script>
    function redirectToHelp(type) {
      // Define external links for each support type
      const helpLinks = {
        'depressed': 'https://www.nimh.nih.gov/health/topics/depression',
        'drained': 'https://www.helpguide.org/articles/stress/burnout-prevention-and-recovery.htm',
        'anxious': 'https://www.nimh.nih.gov/health/topics/anxiety-disorders',
        'stressed': 'https://www.mayoclinic.org/healthy-lifestyle/stress-management/basics/stress-basics/hlv-20049495'
      };
      
      // Redirect to the appropriate external link
      if (helpLinks[type]) {
        window.open(helpLinks[type], '_blank');
      }
    }
  </script>


</body>
</html>
