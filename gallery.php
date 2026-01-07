<?php include __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sedibeng Jukskei * Gallery</title>

    <!-- CSS ALWAYS goes here -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>

<?php include BASE_PATH . '/includes/header.php'; ?>

<main>
    <div class="gallery">
        <?php
        $folder = BASE_PATH . '/images/gallery/';
        $urlFolder = BASE_URL . '/images/gallery/';
        $types = ['jpg','jpeg','png','gif','webp'];

        foreach (scandir($folder) as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $types)) {
                echo "<img src='{$urlFolder}{$file}'>";
            }
        }
        ?>
    </div>
</main>

<?php include BASE_PATH . '/includes/footer.php'; ?>

</body>
</html>
