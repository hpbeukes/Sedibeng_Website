<?php
session_start();
$loggedIn = isset($_SESSION['logged_in']);
?>

<header class="site-header">

  <!-- Top row -->
  <div class="header-top">
      <div class="logo-container">
          <img src="/sedibeng/logo.png" alt="Logo" class="logo">
      </div>
      <div class="title-container">
          <h1 class="site-title">Sedibeng Jukskei</h1>
      </div>
      <div class="login-container">
          <?php if ($loggedIn): ?>
              <a href="<?= BASE_URL ?>/auth/logout.php">
                  <img src="<?= BASE_URL ?>/images/login-icon.png" class="login-icon" alt="Logout">
              </a>
          <?php else: ?>
              <a href="<?= BASE_URL ?>/auth/login.php">
                  <img src="<?= BASE_URL ?>/images/login-icon.png" class="login-icon" alt="Login">
              </a>
          <?php endif; ?>
      </div>
  </div>

  <!-- Nav row -->
  <div class="nav-row">
      <input type="checkbox" id="nav-toggle" class="nav-toggle">
      <label for="nav-toggle" class="hamburger">&#9776;</label>

      <nav class="main-nav">
          <a href="index.php">Home</a>
          <a href="gallery.php">Gallery</a>
          <a href="calendar.php">Calendar</a>
          <a href="links.php">Links</a>
          <a href="contact.php">Contact Us</a>
          <?php if ($loggedIn): ?>
              <a href="admin/edit-home.php">Dashboard</a>
          <?php endif; ?>
      </nav>
  </div>
</header>




