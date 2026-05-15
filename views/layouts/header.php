<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@400;600&family=Poppins:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&family=Montserrat:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/main.css" rel="stylesheet">
    <?php if (isset($extraCss)): ?><?php foreach ($extraCss as $css): ?><link href="<?= ASSETS_URL ?>/css/<?= e($css) ?>" rel="stylesheet"><?php endforeach; ?><?php endif; ?>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
    <?php if (!empty($_SESSION['user_id'])): ?>
    <?php require __DIR__ . '/nav.php'; ?>
    <?php endif; ?>
    <main class="main-content">
        <?php
        $flash = get_flash_message();
        if ($flash):
        ?>
        <div class="flash flash-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
            <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
        <?php endif; ?>
