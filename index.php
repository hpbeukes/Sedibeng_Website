<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
<?php
echo nl2br(file_get_contents(__DIR__ . '/content/home.txt'));
?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
