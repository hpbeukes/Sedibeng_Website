<?php
include __DIR__ . '/includes/config.php';
include BASE_PATH . '/includes/Parsedown.php';

$Parsedown = new Parsedown();
$Parsedown->setSafeMode(true);
$content = file_get_contents(BASE_PATH . '/content/contact.md');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sedibeng * Contact Us</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>

<?php include BASE_PATH . '/includes/header.php'; ?>

<main class="main-nav">
    <?= $Parsedown->text($content); ?>
</main>

<?php include BASE_PATH . '/includes/footer.php'; ?>

</body>
</html>