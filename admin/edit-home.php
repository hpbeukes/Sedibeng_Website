<?php
include __DIR__ . '/../includes/config.php';
session_start();

/* -------- BASIC AUTH -------- */
if (!isset($_SESSION['admin'])) {
    if (($_POST['password'] ?? '') !== 'CHANGE_THIS_PASSWORD') {
        echo '<form method="post" style="max-width:300px;margin:50px auto">
                <h3>Admin Login</h3>
                <input type="password" name="password" style="width:100%;padding:8px">
                <button style="margin-top:10px">Login</button>
              </form>';
        exit;
    }
    $_SESSION['admin'] = true;
}
/* ---------------------------- */

$file = BASE_PATH . '/content/home.md';

/* Save content */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    file_put_contents($file, $_POST['content']);
    $saved = true;
}

/* Load content */
$content = file_exists($file) ? file_get_contents($file) : '';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Home Page</title>

    <!-- EasyMDE -->
    <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
    <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>

    <style>
        body { font-family: Arial; padding: 20px; }
        .container { max-width: 900px; margin: auto; }
        .saved { color: green; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Edit Home Page</h1>

    <?php if (!empty($saved)): ?>
        <div class="saved">✔ Content saved</div>
    <?php endif; ?>

    <form method="post">
        <textarea id="editor" name="content"><?= htmlspecialchars($content) ?></textarea>
        <button style="margin-top:15px;padding:10px 20px;">Save</button>
    </form>
</div>

<script>
    const editor = new EasyMDE({
        element: document.getElementById("editor"),
        spellChecker: false,
        autofocus: true,
        status: false
    });
</script>

</body>
</html>
