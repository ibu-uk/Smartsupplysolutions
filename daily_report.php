<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

require_once __DIR__ . '/dropdowns.php';

$user = current_user();

?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(APP_NAME) ?> - Daily Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(BASE_URL) ?>/assets/app.css" rel="stylesheet">
</head>
<body class="app-bg">
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container">
        <a class="navbar-brand" href="<?= htmlspecialchars(BASE_URL) ?>/daily_report.php"><?= htmlspecialchars(APP_NAME) ?></a>
        <div class="ms-auto d-flex gap-2">
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/reports.php">Reports</a>
            <?php if (is_admin($user)): ?>
                <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/users.php">Users</a>
            <?php endif; ?>
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4" style="max-width: 980px;">
    <div class="card shadow-sm app-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h5 mb-0 app-title">تقرير الزيارات اليومي</h1>
                <div class="text-muted small"><?= htmlspecialchars($user['username'] ?? '') ?></div>
            </div>

            <form method="post" action="<?= htmlspecialchars(BASE_URL) ?>/save_report.php" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">تاريخ الزيارة</label>
                    <input type="date" name="visit_date" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">المنطقة</label>
                    <select name="area" class="form-select" required>
                        <option value="" selected disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['areas'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">اسم العيادة</label>
                    <input type="text" name="clinic_name" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">رقم الزيارة</label>
                    <select name="visit_number" class="form-select" required>
                        <option value="" selected disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['visit_numbers'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">اسم الشخص</label>
                    <input type="text" name="person_name" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">المسمى الوظيفي</label>
                    <select name="job_title" class="form-select" required>
                        <option value="" selected disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['job_titles'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">رقم الموبايل</label>
                    <input type="tel" name="mobile" class="form-control" inputmode="tel">
                </div>

                <div class="col-md-4">
                    <label class="form-label">مستوى الاهتمام</label>
                    <select name="interest" class="form-select" required>
                        <option value="" selected disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['interest'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">نوع الزيارة</label>
                    <select name="visit_type" class="form-select" required id="visit_type">
                        <option value="" selected disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['visit_type'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">نتيجة الزيارة</label>
                    <select name="visit_result" class="form-select" required id="visit_result">
                        <option value="" selected disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['visit_result'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">حالة التنفيذ</label>
                    <select name="execution_status" class="form-select" required>
                        <option value="" selected disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['status'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-app">حفظ</button>
                    <button type="reset" class="btn btn-app-outline">مسح</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const dependencies = <?= json_encode($DEPENDENCIES, JSON_UNESCAPED_UNICODE) ?>;
const allVisitResults = <?= json_encode($DROPDOWNS['visit_result'], JSON_UNESCAPED_UNICODE) ?>;

function setOptions(selectEl, options) {
    const current = selectEl.value;
    selectEl.innerHTML = '<option value="" selected disabled>اختر</option>';
    for (const v of options) {
        const opt = document.createElement('option');
        opt.value = v;
        opt.textContent = v;
        selectEl.appendChild(opt);
    }
    if (options.includes(current)) {
        selectEl.value = current;
    }
}

document.getElementById('visit_type')?.addEventListener('change', (e) => {
    const vt = e.target.value;
    const map = (dependencies && dependencies.visit_result_by_visit_type) ? dependencies.visit_result_by_visit_type : {};
    const options = map[vt] || allVisitResults;
    setOptions(document.getElementById('visit_result'), options);
});
</script>
</body>
</html>
