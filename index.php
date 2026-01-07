<?php
include __DIR__ . '/includes/config.php';
include BASE_PATH . '/includes/Parsedown.php';

$Parsedown = new Parsedown();
$Parsedown->setSafeMode(true);
$content = file_get_contents(BASE_PATH . '/content/home.md');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sedibeng Jukskei</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>


<?php include BASE_PATH . '/includes/header.php'; ?>

<main class="main-nav">
    <?= $Parsedown->text($content); ?>
</main>

<?php include BASE_PATH . '/includes/footer.php'; ?>


</body>
</html>
