<?php
include __DIR__ . '/includes/config.php';
include BASE_PATH . '/includes/Parsedown.php';

$Parsedown = new Parsedown();
$Parsedown->setSafeMode(true);
$content = file_get_contents(BASE_PATH . '/content/home.md');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sedibeng Jukskei</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>


<?php include BASE_PATH . '/includes/header.php'; ?>

<main class="main-body">
    <?= $Parsedown->text($content); ?>
</main>

<?php include BASE_PATH . '/includes/footer.php'; ?>


</body>
</html>
