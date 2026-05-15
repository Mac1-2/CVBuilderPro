<?php
$templateModel = new Template();
$action = $route[1] ?? 'index';

if ($action === 'preview' && ($slug = $route[2] ?? null)) {
    $template = $templateModel->getBySlug($slug);
    if (!$template) { redirect(BASE_URL . '/templates'); }

    $pageTitle = 'Preview: ' . $template['name'];
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/templates/preview.php';
    require __DIR__ . '/../views/layouts/footer.php';
    exit;
}

$templates = $templateModel->getAll();
$categories = $templateModel->getCategories();
$selectedCategory = $_GET['category'] ?? null;
if ($selectedCategory) {
    $templates = $templateModel->getAll($selectedCategory);
}

$pageTitle = 'CV Templates';
$extraCss = ['templates.css'];
require __DIR__ . '/../views/layouts/header.php';
require __DIR__ . '/../views/templates/gallery.php';
require __DIR__ . '/../views/layouts/footer.php';
