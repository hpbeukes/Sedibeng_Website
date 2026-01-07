<?php
session_start(); // make sure session is started
$loggedIn = isset($_SESSION['logged_in']);
?>

<header class="site-header">
    <div class="header-top">
        <div class="logo-container">
            <img src="/sedibeng/logo.png" alt="Logo" class="logo">
        </div>

        <h1 class="site-title">Sedibeng Jukskei</h1>
    </div>
	<div class="login-container">
        <?php if(isset($_SESSION['logged_in'])): ?>
            <a href="<?= BASE_URL ?>/auth/logout.php">
                <img src="<?= BASE_URL ?>/images/login-icon.png" class="login-icon" alt="Logout">
            </a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/auth/login.php">
                <img src="<?= BASE_URL ?>/images/login-icon.png" class="login-icon" alt="Login">
            </a>
        <?php endif; ?>
    </div>

	<div class="nav-row">
		<nav>
			<a href="index.php">Home</a>
			<a href="gallery.php">Gallery</a>			
			<a href="calendar.php">Calendar</a>
			<a href="links.php">Links</a> 
			<a href="contact.php">Contact Us</a>
		</nav>
	</div>

    <?php if ($loggedIn): ?>
        <!-- Logged in: show logout icon -->
		<nav>
			<a href="admin/edit-home.php">Dashboard</a>
		</nav>
    <?php endif; ?>
</header>