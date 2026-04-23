<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

 $user = current_user();

require_once __DIR__ . '/dropdowns.php';

$id = (string)($_GET['id'] ?? '');
if ($id === '' || !ctype_digit($id)) {
    http_response_code(400);
    echo 'Invalid id';
    exit;
}

$params = [(int)$id];
$sql = 'SELECT dv.*, u.username FROM daily_visits dv JOIN users u ON u.id = dv.user_id WHERE dv.id = ?';
if (!is_admin($user)) {
    $sql .= ' AND dv.user_id = ?';
    $params[] = (int)$user['id'];
}
$sql .= ' LIMIT 1';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$r = $stmt->fetch();

if (!$r) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$cStmt = db()->prepare('SELECT person_name, job_title, mobile FROM daily_visit_contacts WHERE daily_visit_id = ? ORDER BY id ASC');
$cStmt->execute([(int)$id]);
$contacts = $cStmt->fetchAll();
if (!$contacts) {
    $contacts = [[
        'person_name' => (string)($r['person_name'] ?? ''),
        'job_title' => (string)($r['job_title'] ?? ''),
        'mobile' => (string)($r['mobile'] ?? ''),
    ]];
}

?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(APP_NAME) ?> - Edit Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="<?= htmlspecialchars(BASE_URL) ?>/assets/app.css" rel="stylesheet">
</head>
<body class="app-bg">
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container position-relative">
        <div class="position-absolute top-50 start-50 translate-middle text-white fw-semibold text-center" style="font-size: 14px; width: 100%; pointer-events: none;">
            <div class="d-inline-flex align-items-center gap-2" style="transform: translateY(-1px);">
                <img src="<?= htmlspecialchars(BASE_URL . '/assets/' . rawurlencode('Screenshot 2026-04-22 153610.png')) ?>" alt="Smartsupplysolutions" style="height: 26px; width: auto; background: #fff; border-radius: 6px; padding: 2px 6px; opacity: .95;">
                <span>Smartsupplysolutions</span>
            </div>
        </div>
        <a class="navbar-brand position-relative" style="z-index: 1;" href="<?= htmlspecialchars(BASE_URL) ?>/daily_report.php"><?= htmlspecialchars(APP_NAME) ?></a>
        <div class="ms-auto d-flex gap-2 position-relative" style="z-index: 1;">
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/reports.php">Reports</a>
            <a class="btn btn-sm btn-nav" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4" style="max-width: 1200px;">
    <div class="card shadow-sm app-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h5 mb-0 app-title">تعديل التقرير #<?= (int)$r['id'] ?></h1>
                <div class="text-muted small"><?= htmlspecialchars($user['username'] ?? '') ?></div>
            </div>

            <form method="post" action="<?= htmlspecialchars(BASE_URL) ?>/update_report.php" class="row g-3">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">

                <div class="col-md-4">
                    <label class="form-label">تاريخ الزيارة</label>
                    <input type="text" name="visit_date" class="form-control js-date" required value="<?= htmlspecialchars((string)$r['visit_date']) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">تاريخ متابعة</label>
                    <input type="text" name="follow_up_date" class="form-control js-date" value="<?= htmlspecialchars((string)($r['follow_up_date'] ?? '')) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">المنطقة</label>
                    <select name="area" class="form-select" required>
                        <option value="" disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['areas'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>" <?= ((string)$r['area'] === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">العنوان</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars((string)($r['address'] ?? '')) ?>" placeholder="العنوان">
                </div>

                <div class="col-md-4">
                    <label class="form-label">اسم العيادة</label>
                    <input type="text" name="clinic_name" class="form-control" required value="<?= htmlspecialchars((string)$r['clinic_name']) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">رقم الزيارة</label>
                    <select name="visit_number" class="form-select" required>
                        <option value="" disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['visit_numbers'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>" <?= ((string)$r['visit_number'] === (string)$v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0">جهات الاتصال</label>
                        <button type="button" class="btn btn-app-outline btn-sm" id="addContactBtn">+</button>
                    </div>
                    <div id="contactsContainer" class="row g-3 mt-1">
                        <?php foreach ($contacts as $idx => $c): ?>
                            <div class="col-md-4">
                                <input type="text" name="contacts_name[]" class="form-control" placeholder="اسم الشخص" required value="<?= htmlspecialchars((string)($c['person_name'] ?? '')) ?>">
                            </div>
                            <div class="col-md-4">
                                <select name="contacts_job_title[]" class="form-select" required>
                                    <option value="" disabled>المسمى الوظيفي</option>
                                    <?php foreach ($DROPDOWNS['job_titles'] as $v): ?>
                                        <option value="<?= htmlspecialchars($v) ?>" <?= ((string)($c['job_title'] ?? '') === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                                <input type="tel" name="contacts_mobile[]" class="form-control" placeholder="رقم الموبايل" inputmode="tel" value="<?= htmlspecialchars((string)($c['mobile'] ?? '')) ?>">
                                <?php if ($idx > 0): ?>
                                    <button type="button" class="btn btn-danger btn-sm" data-remove-contact="1" aria-label="Remove">×</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">مستوى الاهتمام</label>
                    <select name="interest" class="form-select" required>
                        <option value="" disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['interest'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>" <?= ((string)$r['interest'] === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">نوع الزيارة</label>
                    <select name="visit_type" class="form-select" required>
                        <option value="" disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['visit_type'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>" <?= ((string)$r['visit_type'] === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">نتيجة الزيارة</label>
                    <select name="visit_result" class="form-select" required>
                        <option value="" disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['visit_result'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>" <?= ((string)$r['visit_result'] === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">حالة التنفيذ</label>
                    <select name="execution_status" class="form-select" required>
                        <option value="" disabled>اختر</option>
                        <?php foreach ($DROPDOWNS['status'] as $v): ?>
                            <option value="<?= htmlspecialchars($v) ?>" <?= ((string)$r['execution_status'] === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars((string)$r['notes']) ?></textarea>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-app">حفظ التعديل</button>
                    <a class="btn btn-app-outline" href="<?= htmlspecialchars(BASE_URL) ?>/reports.php">رجوع</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('[data-remove-contact]')?.forEach((btn) => {
    btn.addEventListener('click', () => {
        const mobileCol = btn.closest('.col-md-4');
        const titleCol = mobileCol?.previousElementSibling;
        const nameCol = titleCol?.previousElementSibling;
        nameCol?.remove();
        titleCol?.remove();
        mobileCol?.remove();
    });
});

document.getElementById('addContactBtn')?.addEventListener('click', () => {
    const container = document.getElementById('contactsContainer');
    if (!container) return;

    const nameCol = document.createElement('div');
    nameCol.className = 'col-md-4';
    nameCol.innerHTML = '<input type="text" name="contacts_name[]" class="form-control" placeholder="اسم الشخص" required>';

    const titleCol = document.createElement('div');
    titleCol.className = 'col-md-4';
    const jobTitleOptions = <?= json_encode($DROPDOWNS['job_titles'], JSON_UNESCAPED_UNICODE) ?>;
    let titleHtml = '<select name="contacts_job_title[]" class="form-select" required>';
    titleHtml += '<option value="" selected disabled>المسمى الوظيفي</option>';
    for (const v of jobTitleOptions) {
        const escaped = String(v)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
        titleHtml += `<option value="${escaped}">${escaped}</option>`;
    }
    titleHtml += '</select>';
    titleCol.innerHTML = titleHtml;

    const mobileCol = document.createElement('div');
    mobileCol.className = 'col-md-4 d-flex gap-2';
    mobileCol.innerHTML = '<input type="tel" name="contacts_mobile[]" class="form-control" placeholder="رقم الموبايل" inputmode="tel">' +
        '<button type="button" class="btn btn-danger btn-sm" aria-label="Remove">×</button>';

    mobileCol.querySelector('button')?.addEventListener('click', () => {
        nameCol.remove();
        titleCol.remove();
        mobileCol.remove();
    });

    container.appendChild(nameCol);
    container.appendChild(titleCol);
    container.appendChild(mobileCol);
});
</script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.querySelectorAll('.js-date').forEach((el) => {
  flatpickr(el, {
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'd/m/Y',
    allowInput: true,
  });
});
</script>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <div class="d-flex justify-content-center mb-3">
          <div class="rounded-circle border d-flex align-items-center justify-content-center" style="width: 72px; height: 72px; border-width: 3px;">
            <i class="bi bi-question-lg" style="font-size: 34px;"></i>
          </div>
        </div>
        <div class="fw-semibold" style="font-size: 18px;">تأكيد تسجيل الخروج</div>
        <div class="text-muted mt-2">هل أنت متأكد أنك تريد تسجيل الخروج؟</div>
        <div class="d-flex justify-content-center gap-2 mt-4">
          <a class="btn btn-danger px-3" href="<?= htmlspecialchars(BASE_URL) ?>/logout.php">نعم، تسجيل الخروج</a>
          <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">إلغاء</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
