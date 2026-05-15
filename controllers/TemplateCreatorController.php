<?php
require_once __DIR__ . '/../includes/BlockRenderer.php';

$blockModel = new TemplateBlock();
$templateModel = new Template();
$action = $route[1] ?? 'index';
$id = (int)($route[2] ?? 0);

if ($action === 'new') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
        $name = trim($_POST['name'] ?? 'My Custom Template');
        $baseId = (int)($_POST['base_template_id'] ?? 0);
        $newId = $blockModel->createCustomTemplate($_SESSION['user_id'], $name, $baseId ?: null);
        redirect(BASE_URL . "/creator/edit/$newId");
    }
    $templates = $templateModel->getAll();
    $pageTitle = 'Create Custom Template';
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/template-creator/new.php';
    require __DIR__ . '/../views/layouts/footer.php';
    exit;
}

if ($action === 'clone' && $id) {
    $base = $templateModel->getById($id);
    if (!$base) redirect(BASE_URL . '/templates');
    $newName = $base['name'] . ' (Copy)';
    $newId = $blockModel->cloneTemplateWithBlocks($id, $_SESSION['user_id'], $newName);
    redirect(BASE_URL . "/creator/edit/$newId");
}

if ($action === 'edit' && $id) {
    $template = $templateModel->getById($id);
    if (!$template) redirect(BASE_URL . '/templates');
    $blocks = $blockModel->getByTemplate($id);
    $graphics = $blockModel->getGraphics($id);
    $globalStyles = $blockModel->getGlobalStyles($id) ?? [];
    $pageTitle = 'Edit Template: ' . $template['name'];
    $extraCss = ['template-creator.css'];
    $extraJs = ['template-creator.js'];
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/template-creator/index.php';
    require __DIR__ . '/../views/layouts/footer.php';
    exit;
}

if ($action === 'preview' && $id) {
    $template = $templateModel->getById($id);
    if (!$template) redirect(BASE_URL . '/templates');
    $pageTitle = 'Preview: ' . $template['name'];
    require __DIR__ . '/../views/template-creator/preview.php';
    exit;
}

redirect(BASE_URL . '/templates');
