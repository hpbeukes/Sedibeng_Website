<?php
declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require __DIR__ . '/../includes/config.php';
require BASE_PATH . '/includes/db.php';
require BASE_PATH . '/vendor/autoload.php';

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;

$error = '';
$ip = $_SERVER['REMOTE_ADDR'];

/* =========================================
   RATE LIMIT: 5 attempts / 10 minutes
========================================= */
$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM login_attempts
     WHERE ip_address=?
     AND attempt_time > (NOW() - INTERVAL 10 MINUTE)"
);
$stmt->execute([$ip]);

if ($stmt->fetchColumn() >= 5) {
    die('Too many attempts. Try again later.');
}

/* =========================================
   LOGIN PROCESS
========================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';
    $code  = trim($_POST['code'] ?? '');

    /* Fetch user including enabled + 2FA flag */
    $stmt = $pdo->prepare(
        "SELECT id, password_hash, enabled, twofa_enabled
         FROM users
         WHERE email=?"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    /* Invalid credentials */
    if (!$user || !password_verify($pass, $user['password_hash'])) {
        $error = 'Invalid login';
    } else {

        /* ===============================
           NOT MANUALLY VERIFIED
        =============================== */
        if ((int)$user['enabled'] === 0) {
            header('Location: notverified.php');
            exit;
        }

        /* ===============================
           2FA ENABLED
        =============================== */
        if ((int)$user['twofa_enabled'] === 1) {

            if (empty($code)) {
                $error = 'Enter 2FA code';
            } else {

                $stmt = $pdo->prepare(
                    "SELECT secret FROM user_2fa WHERE user_id=?"
                );
                $stmt->execute([$user['id']]);
                $fa = $stmt->fetch();

                if (!$fa) {
                    $error = '2FA configuration error';
                } else {

                    $tfa = new TwoFactorAuth(
                        new QRServerProvider(),
                        'Sedibeng Jukskei'
                    );

                    if ($tfa->verifyCode($fa['secret'], $code)) {

                        session_regenerate_id(true);

                        $_SESSION['user_id']   = $user['id'];
                        $_SESSION['logged_in'] = true;

                        header('Location: ../index.php');
                        exit;

                    } else {
                        $error = 'Invalid 2FA code';
                    }
                }
            }

        } else {
            /* ===============================
               2FA NOT ENABLED → LOGIN
            =============================== */

            session_regenerate_id(true);

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['logged_in'] = true;

            header('Location: ../index.php');
            exit;
        }
    }

    /* Log failed attempt */
    $pdo->prepare(
        "INSERT INTO login_attempts (ip_address, email)
         VALUES (?, ?)"
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
    <input type="password" name="password"
           placeholder="Password" required><br>
    <input name="code"
           placeholder="2FA Code (only if enabled)"><br>
    <button>Login</button>
</form>

<hr style="margin:20px 0;">

<form action="register.php" method="get">
    <button type="submit">Register</button>
</form>

</body>
</html>
