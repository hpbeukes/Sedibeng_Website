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
$step  = $_SESSION['reg_step'] ?? 1;

/* Only reset session if user explicitly starts fresh */
if (!isset($_SESSION['reg_email']) && !isset($_SESSION['reg_step'])) {
    $_SESSION['reg_step'] = 1;
    $step = 1;
}

/* STEP 1 – Email & password */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 1) {

    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } else {
        /* Check existing user */
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Account already exists';
        } else {
            $tfa = new TwoFactorAuth(new QRServerProvider(), 'Sedibeng Jukskei');
            $_SESSION['reg_email'] = $email;
            $_SESSION['reg_pass']  = password_hash($password, PASSWORD_DEFAULT);
            $_SESSION['reg_secret'] = $tfa->createSecret();
            $_SESSION['reg_step']  = 2;
			$step = 2;
            header('Location: register.php');
            exit;
        }
    }
}

/* STEP 2 – 2FA Verification */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {

    $code = $_POST['code'] ?? '';
    $tfa  = new TwoFactorAuth(new QRServerProvider(), 'Sedibeng Jukskei');

    if ($tfa->verifyCode($_SESSION['reg_secret'], $code)) {

        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "INSERT INTO users (email, password_hash) VALUES (?, ?)"
        );
        $stmt->execute([
            $_SESSION['reg_email'],
            $_SESSION['reg_pass']
        ]);

        $userId = $pdo->lastInsertId();

        $stmt = $pdo->prepare(
            "INSERT INTO user_2fa (user_id, secret) VALUES (?, ?)"
        );
        $stmt->execute([$userId, $_SESSION['reg_secret']]);

        $pdo->commit();

        session_destroy();
        header('Location: login.php');
        exit;

    } else {
        $error = 'Invalid authentication code';
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

<?php if ($error): ?>
<p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($step === 1): ?>
<form method="post">
    <input name="email" required placeholder="Email"><br>
    <input type="password" name="password" required placeholder="Password"><br>
    <button>Create account</button>
</form>

<?php else:
    $tfa = new TwoFactorAuth(new QRServerProvider(), 'Sedibeng Jukskei');
    $qr  = $tfa->getQRCodeImageAsDataUri(
        'Sedibeng Jukskei (' . $_SESSION['reg_email'] . ')',
        $_SESSION['reg_secret'],
        250
    );
?>
<p>Scan QR with Google / Microsoft Authenticator</p>
<img src="<?= $qr ?>"><br>
<form method="post">
    <input name="code" placeholder="6-digit code" required>
    <button>Verify</button>
</form>
<?php endif; ?>

</body>
</html>
