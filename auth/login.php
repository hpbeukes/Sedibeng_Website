<?php
session_start();
require __DIR__ . '/../includes/config.php';
require BASE_PATH . '/includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare(
        "SELECT * FROM users WHERE email=? AND is_active=1"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['2fa_user'] = $user['id'];
        header("Location: 2fa.php");
        exit;
    } else {
        $error = 'Invalid login';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
	<link rel="stylesheet" href="/sedibeng/css/style.css">
</head>
<body>
<?php include BASE_PATH . '/includes/header_users.php'; ?>

<h2 class="auth_h2_class">Login</h2>
<?php if ($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>

<form method="post">
    <input name="email" placeholder="Email" required><br>
    <input name="password" type="password" placeholder="Password" required><br>
    <button>Login</button>
</form>

<p><a href="register.php">Register</a></p>

</body>
</html>
