<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

require_once __DIR__ . '/dropdowns.php';

$user = current_user();
$reminders_count = reminders_count($user);
$welcome = flash_get('welcome');

?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Smartsupplysolutions</title>
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
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/reminders.php">Reminders<?= $reminders_count > 0 ? ' (' . (int)$reminders_count . ')' : '' ?></a>
            <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/reports.php">Reports</a>
            <?php if (is_admin($user)): ?>
                <a class="btn btn-sm btn-nav" href="<?= htmlspecialchars(BASE_URL) ?>/users.php">Users</a>
            <?php endif; ?>
            <a class="btn btn-sm btn-nav" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4" style="max-width: 1200px;">
    <div class="card shadow-sm app-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h5 mb-0 app-title">تقرير الزيارات اليومي</h1>
                <div class="text-muted small"><?= htmlspecialchars($user['username'] ?? '') ?></div>
            </div>

            <form method="post" action="<?= htmlspecialchars(BASE_URL) ?>/save_report.php" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">تاريخ الزيارة</label>
                    <input type="text" name="visit_date" class="form-control js-date" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">تاريخ متابعة</label>
                    <input type="text" name="follow_up_date" class="form-control js-date">
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
                    <label class="form-label">العنوان</label>
                    <input type="text" name="address" class="form-control" placeholder="العنوان">
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

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0">جهات الاتصال</label>
                        <button type="button" class="btn btn-app-outline btn-sm" id="addContactBtn">+</button>
                    </div>
                    <div id="contactsContainer" class="row g-3 mt-1">
                        <div class="col-md-4">
                            <input type="text" name="contacts_name[]" class="form-control" placeholder="اسم الشخص" required>
                        </div>
                        <div class="col-md-4">
                            <select name="contacts_job_title[]" class="form-select" required>
                                <option value="" selected disabled>المسمى الوظيفي</option>
                                <?php foreach ($DROPDOWNS['job_titles'] as $v): ?>
                                    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="tel" name="contacts_mobile[]" class="form-control" placeholder="رقم الموبايل" inputmode="tel">
                        </div>
                    </div>
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

<?php if ($reminders_count > 0): ?>
<div class="modal fade" id="remindersModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">متابعات مستحقة</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        يوجد لديك عدد <strong><?= (int)$reminders_count ?></strong> متابعة مستحقة / متأخرة.
      </div>
      <div class="modal-footer">
        <a class="btn btn-app" href="<?= htmlspecialchars(BASE_URL) ?>/reminders.php">عرض المتابعات</a>
        <button type="button" class="btn btn-app-outline" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($welcome): ?>
<div class="modal fade" id="welcomeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <div class="d-flex justify-content-center mb-3">
          <div class="app-modal-icon app-modal-icon--info">
            <i class="bi bi-info-lg" style="font-size: 34px;"></i>
          </div>
        </div>
        <div class="fw-semibold" style="font-size: 18px;">مرحباً</div>
        <div class="text-muted mt-2"><?= htmlspecialchars($welcome) ?></div>
        <div class="d-flex justify-content-center gap-2 mt-4">
          <button type="button" class="btn btn-app px-3" data-bs-dismiss="modal">حسناً</button>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

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
<?php if ($reminders_count > 0): ?>
<script>
window.addEventListener('load', () => {
  const el = document.getElementById('remindersModal');
  if (!el) return;
  const modal = new bootstrap.Modal(el, { backdrop: 'static' });
  modal.show();
});
</script>
<?php endif; ?>

<?php if ($welcome): ?>
<script>
window.addEventListener('load', () => {
  const el = document.getElementById('welcomeModal');
  if (!el) return;
  const modal = new bootstrap.Modal(el);
  modal.show();
});
</script>
<?php endif; ?>
</body>
</html>
