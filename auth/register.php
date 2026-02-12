<?php
declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');

define('RECAPTCHA_SITE_KEY', '6LeT2mgsAAAAALLbN1M54UnfCFv-C1zYLjTds-ci');
define('RECAPTCHA_SECRET_KEY', '6LeT2mgsAAAAANPCoaDgbXPl17rkFkPI3XvIGqfX');


require __DIR__ . '/../includes/config.php';
require BASE_PATH . '/includes/db.php';
require BASE_PATH . '/vendor/autoload.php';

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;

$error = '';
$step  = $_SESSION['reg_step'] ?? 1;

/* CSRF */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* Reset flow if fresh */
if (!isset($_SESSION['reg_email'])) {
    $_SESSION['reg_step'] = 1;
    $step = 1;
}

/* =========================================================
   STEP 1 – EMAIL + PASSWORD + CAPTCHA
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 1) {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    if (!empty($_POST['website'])) {
        die('Bot detected');
    }

    /* reCAPTCHA validation */
    $recaptcha = $_POST['g-recaptcha-response'] ?? '';

    if (empty($recaptcha)) {
        $error = 'Please complete the captcha';
    } else {

        $verify = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?secret=" .
            RECAPTCHA_SECRET_KEY .
            "&response=" . $recaptcha .
            "&remoteip=" . $_SERVER['REMOTE_ADDR']
        );

        $captchaData = json_decode($verify);

        if (!$captchaData || !$captchaData->success) {
            $error = 'Captcha verification failed';
        }
    }

    if (!$error) {

        $email     = strtolower(trim($_POST['email'] ?? ''));
        $password  = $_POST['password'] ?? '';
        $enable2fa = isset($_POST['enable_2fa']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters';
        } else {

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $error = 'Account already exists';
            } else {

                $_SESSION['reg_email'] = $email;
                $_SESSION['reg_pass']  = password_hash($password, PASSWORD_DEFAULT);
                $_SESSION['reg_enable_2fa'] = $enable2fa;

                if ($enable2fa) {
                    $tfa = new TwoFactorAuth(new QRServerProvider(), 'Sedibeng Jukskei');
                    $_SESSION['reg_secret'] = $tfa->createSecret();
                    $_SESSION['reg_step'] = 2;
                } else {
                    $_SESSION['reg_step'] = 3;
                }

                header('Location: register.php');
                exit;
            }
        }
    }
}

/* =========================================================
   STEP 2 – VERIFY 2FA
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $code = trim($_POST['code'] ?? '');
    $tfa  = new TwoFactorAuth(new QRServerProvider(), 'Sedibeng Jukskei');

    if ($tfa->verifyCode($_SESSION['reg_secret'], $code)) {
        $_SESSION['reg_step'] = 3;
        header('Location: register.php');
        exit;
    } else {
        $error = 'Invalid authentication code';
    }
}

/* =========================================================
   STEP 3 – CREATE ACCOUNT
========================================================= */
if ($step === 3 && isset($_SESSION['reg_email'])) {

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "INSERT INTO users (email, password_hash, twofa_enabled)
         VALUES (?, ?, ?)"
    );

    $stmt->execute([
        $_SESSION['reg_email'],
        $_SESSION['reg_pass'],
        $_SESSION['reg_enable_2fa'] ? 1 : 0
    ]);

    $userId = $pdo->lastInsertId();

    if ($_SESSION['reg_enable_2fa']) {
        $stmt = $pdo->prepare(
            "INSERT INTO user_2fa (user_id, secret)
             VALUES (?, ?)"
        );
        $stmt->execute([$userId, $_SESSION['reg_secret']]);
    }

    $pdo->commit();

    session_unset();
    session_destroy();

    header('Location: login.php?registered=1');
    exit;
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

    <input type="hidden" name="csrf_token"
           value="<?= $_SESSION['csrf_token'] ?>">

    <!-- Honeypot -->
    <input type="text" name="website"
           style="display:none">

    <input name="email" required placeholder="Email"><br>

    <input type="password" name="password"
           required placeholder="Password (min 8 characters)"><br><br>

    <label>
        <input type="checkbox" name="enable_2fa" value="1">
        Enable Two-Factor Authentication (Recommended)
    </label><br><br>

    <!-- reCAPTCHA -->
    <div class="g-recaptcha"
         data-sitekey="<?= RECAPTCHA_SITE_KEY ?>"></div>
    <br>

    <button>Create account</button>
</form>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<?php elseif ($step === 2): ?>

<?php
$tfa = new TwoFactorAuth(new QRServerProvider(), 'Sedibeng Jukskei');
$qr  = $tfa->getQRCodeImageAsDataUri(
    'Sedibeng Jukskei (' . $_SESSION['reg_email'] . ')',
    $_SESSION['reg_secret'],
    250
);
?>

<p>Scan this QR code with Google / Microsoft Authenticator:</p>
<img src="<?= $qr ?>"><br><br>

<form method="post">
    <input type="hidden" name="csrf_token"
           value="<?= $_SESSION['csrf_token'] ?>">

    <input name="code"
           placeholder="Enter 6-digit code"
           required><br><br>

    <button>Verify</button>
</form>

<?php endif; ?>

</body>
</html>
