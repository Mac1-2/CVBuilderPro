<?php
$cvModel = new Cv();
$templateModel = new Template();
$action = $route[1] ?? 'index';
$id = $route[2] ?? null;

if ($action === 'create') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf()) json_response(['error' => 'Invalid CSRF token'], 403);
        $title = trim($_POST['title'] ?? 'Untitled CV');
        $templateId = (int)($_POST['template_id'] ?? 1);
        $cvId = $cvModel->create($_SESSION['user_id'], $title, $templateId);
        redirect(BASE_URL . "/cv/edit/$cvId");
    }

    $pageTitle = 'Create New CV';
    $templates = $templateModel->getAll();
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/cv/create.php';
    require __DIR__ . '/../views/layouts/footer.php';
    exit;
}

if ($action === 'edit' && $id) {
    $cv = $cvModel->getFullData((int)$id, $_SESSION['user_id']);
    if (!$cv) { redirect(BASE_URL . '/dashboard'); }

    $mode = $_GET['mode'] ?? 'wizard';
    $templates = $templateModel->getAll();

    $pageTitle = 'Edit: ' . $cv['title'];
    $extraCss = ['editor.css'];
    $extraJs = ['editor.js'];

    require __DIR__ . '/../views/layouts/header.php';

    if ($mode === 'wysiwyg') {
        require __DIR__ . '/../views/cv/wysiwyg/index.php';
    } else {
        require __DIR__ . '/../views/cv/wizard/index.php';
    }

    require __DIR__ . '/../views/layouts/footer.php';
    exit;
}

if ($action === 'preview' && $id) {
    $cv = $cvModel->getFullData((int)$id, $_SESSION['user_id']);
    if (!$cv) { redirect(BASE_URL . '/dashboard'); }

    $pageTitle = 'Preview: ' . $cv['title'];
    require __DIR__ . '/../views/cv/preview.php';
    exit;
}

redirect(BASE_URL . '/dashboard');
