<?php
$action = $route[1] ?? 'login';

if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { json_response(['error' => 'Invalid CSRF token'], 403); }

    $errors = [];
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email address';
    if (strlen($password) < 8) $errors['password'] = 'Password must be at least 8 characters';
    if ($password !== $passwordConfirm) $errors['password_confirm'] = 'Passwords do not match';

    if (empty($errors)) {
        $userModel = new User();
        $result = $userModel->register($email, $password, $firstName, $lastName);
        if ($result['success']) {
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            redirect(BASE_URL . '/dashboard');
        }
        $errors['general'] = $result['error'];
    }
    require __DIR__ . '/../views/auth/register.php';
    exit;
}

if ($action === 'register') {
    $errors = [];
    require __DIR__ . '/../views/auth/register.php';
    exit;
}

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { json_response(['error' => 'Invalid CSRF token'], 403); }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors = [];

    $userModel = new User();
    $result = $userModel->login($email, $password);
    if ($result['success']) {
        redirect(BASE_URL . '/dashboard');
    }
    $errors['general'] = $result['error'];
    require __DIR__ . '/../views/auth/login.php';
    exit;
}

if ($action === 'logout') {
    (new User())->logout();
    redirect(BASE_URL . '/auth/login');
}

$errors = [];
require __DIR__ . '/../views/auth/login.php';
