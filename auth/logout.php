<?php
session_start();
session_unset();
session_destroy();

// redirect to homepage after logout
header("Location: ../index.php");
exit;
