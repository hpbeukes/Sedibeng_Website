<?php include __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Links</title>

    <!-- CSS ALWAYS goes here -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>

<?php include BASE_PATH . '/includes/header.php'; ?>

<main>
<?php
echo nl2br(file_get_contents(__DIR__ . '/content/links.txt'));
?>
</main>

<?php include BASE_PATH . '/includes/footer.php'; ?>

</body>
</html>
