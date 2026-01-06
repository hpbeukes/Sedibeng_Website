<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// If user just opened the page normally, reset registration step
if (!isset($_POST['email']) && !isset($_POST['code'])) {
    $_SESSION['reg_step'] = 1;
    unset($_SESSION['reg_user']);
    unset($_SESSION['reg_secret']);
}

require __DIR__ . '/../includes/config.php';
require BASE_PATH . '/includes/db.php';
require BASE_PATH . '/vendor/autoload.php';

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider; 

$error = '';
$step = $_SESSION['reg_step'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // STEP 1: Create account
    if ($step === 1) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address';
        } else {
            // Hash the password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
            $stmt->execute([$email, $hash]);
            $userId = $pdo->lastInsertId();

            // Create TOTP with latest v2.x
            $tfa = new TwoFactorAuth(new QRServerProvider(), "SedibengJukskei");
            $secret = $tfa->createSecret();

            // Save 2FA secret
            $pdo->prepare("INSERT INTO user_2fa (user_id, secret) VALUES (?, ?)")->execute([$userId, $secret]);
			
            // Save session for step 2
            $_SESSION['reg_user'] = $userId;
            $_SESSION['reg_secret'] = $secret;
			$_SESSION['reg_email'] = $email;
            $_SESSION['reg_step'] = 2;
			$step = 2;
        }
    }

    // STEP 2: Verify 2FA
	if ($step === 2 && isset($_POST['code'])) {
		$code = $_POST['code'];
		$tfa = new TwoFactorAuth(new QRServerProvider(), "SedibengJukskei");

		if ($tfa->verifyCode($_SESSION['reg_secret'], $code)) {
			// Activate user
			$pdo->prepare("UPDATE users SET is_active=1 WHERE id=?")->execute([$_SESSION['reg_user']]);
			$pdo->prepare("UPDATE user_2fa SET enabled=1 WHERE user_id=?")->execute([$_SESSION['reg_user']]);

			session_unset();
			header("Location: login.php");
			exit;
		} else {
			$error = 'Invalid authentication code';
		}
	}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
	<link rel="stylesheet" href="/sedibeng/css/style.css">
</head>
<body>
<?php include BASE_PATH . '/includes/header_users.php'; ?>

<h2 class="auth_h2_class">Register</h2>

<?php if ($error): ?>
<p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($step === 1): ?>
<form method="post">
    <input name="email" placeholder="Email" required><br>
    <input name="password" type="password" placeholder="Password" required><br>
    <button>Create Account</button>
</form>

<?php else: 
    $tfa = new TwoFactorAuth(new QRServerProvider(), "SedibengJukskei");
    $qrDataUri = $tfa->getQRCodeImageAsDataUri($_SESSION['reg_email'], $_SESSION['reg_secret']);
?>
<p>Scan this QR code with Google Authenticator or Microsoft Authenticator:</p>
<img src="<?= $qrDataUri ?>" alt="QR Code"><br>
<form method="post">
    <input name="code" placeholder="6-digit code" required>
    <button>Verify</button>
</form>
<?php endif; ?>

</body>
</html>
