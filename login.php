<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (current_user()) {
    header('Location: ' . BASE_URL . '/daily_report.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } elseif (!login_user($username, $password)) {
        $error = 'Invalid username or password.';
    } else {
        header('Location: ' . BASE_URL . '/daily_report.php');
        exit;
    }
}

?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(APP_NAME) ?> - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(BASE_URL) ?>/assets/app.css" rel="stylesheet">
</head>
<body class="app-bg">
<div class="container py-5" style="max-width: 420px;">
    <div class="card shadow-sm app-card">
        <div class="card-body p-4">
            <h1 class="h5 mb-3 app-title">تسجيل الدخول</h1>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger mb-3"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label">اسم المستخدم</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-app w-100" type="submit">دخول</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
