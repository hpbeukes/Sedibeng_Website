<?php
session_start(); // make sure session is started
$loggedIn = isset($_SESSION['logged_in']);
?>

<header>
    <img src="logo.png" class="logo" alt="Logo">

    <nav>
        <a href="index.php">Home</a>
        <a href="gallery.php">Gallery</a>
        <a href="contact.php">Contact Us</a>
        <a href="calendar.php">Calendar</a>
        <a href="links.php">Links</a>
        <a href="about.php">About Us</a>        
    </nav>

    <?php if ($loggedIn): ?>
        <!-- Logged in: show logout icon -->
		<nav>
			<a href="admin/edit-home.php">Admin</a>
		</nav>
        <a href="auth/logout.php">
            <img src="images/logout-icon.png" class="login" alt="Logout" title="Logout">
        </a>
    <?php else: ?>
        <!-- Not logged in: show login icon -->
        <a href="auth/login.php">
            <img src="images/login-icon.png" class="login" alt="Login" title="Login">
        </a>
    <?php endif; ?>
</header>