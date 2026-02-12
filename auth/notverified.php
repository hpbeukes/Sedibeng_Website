<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/../includes/config.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Not Verified</title>
    <link rel="stylesheet" href="/sedibeng/css/style.css">

</head>
<body>

<?php include BASE_PATH . '/includes/header_users.php'; ?>

<div style="width:100%; text-align:center;">
    <h2 class="auth_h2_class">
        Account Not Yet Verified
    </h2>
</div>

<div style="max-width:600px;margin:0 auto;text-align:center;">

    <p>
        Thank you for registering with <strong>Sedibeng Jukskei</strong>.
    </p>

    <p>
        Your account has been created successfully, but it is still awaiting
        manual verification by an administrator.
    </p>

    <p>
        Verification is normally completed within <strong>24 hours</strong>.
    </p>

    <p>
        If it has been more than 24 hours since you registered,
        please send an email to:
    </p>

    <p>
        <strong>
            <a href="mailto:info@sedibengjukskei.co.za">
                info@sedibengjukskei.co.za
            </a>
        </strong>
    </p>

    <br>

     <!-- Home button styled like header nav links -->
    <a href="../index.php" class="main-nav-link" style="display:inline-block; margin-top:20px;">
        Home
    </a>

</div>

</body>
</html>
