<?php
declare(strict_types=1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();

require __DIR__ . '/../includes/config.php';
require BASE_PATH . '/includes/db.php';
require BASE_PATH . '/vendor/autoload.php';

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;

$error = '';
$ip = $_SERVER['REMOTE_ADDR'];

/* Rate limit: 5 attempts per 10 minutes */
$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM login_attempts 
     WHERE ip_address=? AND attempt_time > (NOW() - INTERVAL 10 MINUTE)"
);
$stmt->execute([$ip]);
if ($stmt->fetchColumn() >= 5) {
    die('Too many attempts. Try again later.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = strtolower(trim($_POST['email']));
    $pass  = $_POST['password'];
    $code  = $_POST['code'];

    $stmt = $pdo->prepare(
        "SELECT u.id, u.password_hash, f.secret
         FROM users u
         JOIN user_2fa f ON f.user_id = u.id
         WHERE u.email=?"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($pass, $user['password_hash'])) {
        $error = 'Invalid login';
    } else {
        $tfa = new TwoFactorAuth(new QRServerProvider(), 'Sedibeng Jukskei');
        if ($tfa->verifyCode($user['secret'], $code)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['logged_in'] = true;
            header('Location: ../index.php');
            exit;
        } else {
            $error = 'Invalid 2FA code';
        }
    }

    /* Log attempt */
    $pdo->prepare(
        "INSERT INTO login_attempts (ip_address, email) VALUES (?, ?)"
    )->execute([$ip, $email]);
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
<?php if ($error): ?>
<p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post">
    <input name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <input name="code" placeholder="2FA Code" required><br>
    <button>Login</button>
</form>

<hr style="margin:20px 0;">

<form action="register.php" method="get">
    <button type="submit">Register</button>
</form>

</body>
</html>
