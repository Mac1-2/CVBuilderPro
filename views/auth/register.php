<?php
$pageTitle = 'Register - ' . APP_NAME;
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
                <h1 style="margin-top:12px;">Create Account</h1>
                <p>Start building professional CVs today</p>
            </div>

            <?php if ($error = ($errors['general'] ?? null)): ?>
            <div class="flash flash-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/auth/register">
                <?= csrf_field() ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-input" placeholder="John" required value="<?= e($_POST['first_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-input" placeholder="Doe" required value="<?= e($_POST['last_name'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" placeholder="you@example.com" required value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Min 8 characters" required minlength="8">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirm" class="form-input" placeholder="Repeat password" required minlength="8">
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-link">
                Already have an account? <a href="<?= BASE_URL ?>/auth/login">Sign in</a>
            </div>
        </div>
    </div>
</body>
</html>
