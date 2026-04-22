<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (current_user()) {
    header('Location: ' . BASE_URL . '/daily_report.php');
    exit;
}

$error = '';
$loggedOut = flash_get('logged_out');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } elseif (!login_user($username, $password)) {
        $error = 'Invalid username or password.';
    } else {
        flash_set('welcome', 'مرحباً بك في Smartsupplysolutions');
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

<?php if ($loggedOut): ?>
<div class="modal fade" id="loggedOutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <div class="d-flex justify-content-center mb-3">
          <div class="rounded-circle border d-flex align-items-center justify-content-center" style="width: 72px; height: 72px; border-width: 3px;">
            <span style="font-size: 34px; font-weight: 700;">!</span>
          </div>
        </div>
        <div class="fw-semibold" style="font-size: 18px;">Smartsupplysolutions</div>
        <div class="text-muted mt-2"><?= htmlspecialchars($loggedOut) ?></div>
        <div class="d-flex justify-content-center gap-2 mt-4">
          <button type="button" class="btn btn-app px-3" data-bs-dismiss="modal">حسناً</button>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($loggedOut): ?>
<script>
window.addEventListener('load', () => {
  const el = document.getElementById('loggedOutModal');
  if (!el) return;
  const modal = new bootstrap.Modal(el);
  modal.show();
});
</script>
<?php endif; ?>
</body>
</html>
