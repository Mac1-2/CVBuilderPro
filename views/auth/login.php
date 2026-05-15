<?php
$pageTitle = 'Login - ' . APP_NAME;
$bodyClass = 'auth-page';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/main.css" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div style="text-align:center;margin-bottom:24px;">
                <i class="fas fa-file-lines" style="font-size:40px;color:var(--primary);"></i>
                <h1 style="margin-top:12px;">Welcome Back</h1>
                <p>Sign in to your CV Builder account</p>
            </div>

            <?php if ($error = ($errors['general'] ?? null)): ?>
            <div class="flash flash-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/auth/login">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" placeholder="you@example.com" required value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="auth-link">
                Don't have an account? <a href="<?= BASE_URL ?>/auth/register">Create one</a>
            </div>
        </div>
    </div>
</body>
</html>
