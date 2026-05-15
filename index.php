<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Cv.php';
require_once __DIR__ . '/models/Template.php';
require_once __DIR__ . '/models/Phrase.php';
require_once __DIR__ . '/models/TemplateBlock.php';

$page = $_GET['page'] ?? 'home';
$page = rtrim($page, '/');

$route = explode('/', $page);
$controller = $route[0] ?? 'home';
$action = $route[1] ?? 'index';

switch ($controller) {
    case 'home':
        if (empty($_SESSION['user_id'])) {
            require __DIR__ . '/views/auth/login.php';
        } else {
            redirect(BASE_URL . '/dashboard');
        }
        break;

    case 'auth':
        require __DIR__ . '/controllers/AuthController.php';
        break;

    case 'dashboard':
        require_auth();
        require __DIR__ . '/controllers/DashboardController.php';
        break;

    case 'cv':
        require_auth();
        require __DIR__ . '/controllers/CvController.php';
        break;

    case 'templates':
        require_auth();
        require __DIR__ . '/controllers/TemplateController.php';
        break;

    case 'creator':
        require_auth();
        require __DIR__ . '/controllers/TemplateCreatorController.php';
        break;

    case 'phrases':
        require_auth();
        require __DIR__ . '/controllers/PhraseController.php';
        break;

    case 'ajax':
        require_auth();
        header('Content-Type: application/json');
        require __DIR__ . '/controllers/AjaxController.php';
        break;

    case 'export':
        require_auth();
        require __DIR__ . '/controllers/ExportController.php';
        break;

    case 'import':
        require_auth();
        require __DIR__ . '/controllers/ImportController.php';
        break;

    default:
        http_response_code(404);
        echo '<h1>404 - Page Not Found</h1>';
        break;
}
