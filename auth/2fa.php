<?php
session_start();
require __DIR__ . '/../includes/config.php';
require BASE_PATH . '/includes/db.php';
require BASE_PATH . '/vendor/autoload.php';

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider; 

if (!isset($_SESSION['2fa_user'])) {
    header("Location: login.php");
    exit;
}

$error = '';

$stmt = $pdo->prepare("SELECT secret FROM user_2fa WHERE user_id=? AND enabled=1");
$stmt->execute([$_SESSION['2fa_user']]);
$secret = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    $tfa = new TwoFactorAuth(new QRServerProvider(), "SedibengJukskei");

    if ($tfa->verifyCode($secret, $code)) {
        $_SESSION['user_id'] = $_SESSION['2fa_user'];
        unset($_SESSION['2fa_user']);
        session_regenerate_id(true);
		$_SESSION['authed'] = true;
		$_SESSION['user_id'] = $userId;
		$_SESSION['user_email'] = $email;
		$_SESSION['login_time'] = time(); // optional for timeout checks
        header("Location: " . BASE_URL . "/index.php");
        exit;
    } else {
        $error = 'Invalid code';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Two-Factor Authentication</title>
</head>
<body>
<img src="<?= BASE_URL ?>/logo.png" height="60">

<h2>Enter Authentication Code</h2>

<?php if ($error): ?>
<p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post">
    <input name="code" placeholder="6-digit code" required>
    <button>Verify</button>
</form>

</body>
</html>
