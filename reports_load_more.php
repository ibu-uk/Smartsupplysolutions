<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

require_once __DIR__ . '/dropdowns.php';

$user = current_user();

$from = (string)($_GET['from'] ?? '');
$to = (string)($_GET['to'] ?? '');
$name = trim((string)($_GET['name'] ?? ''));
$area = trim((string)($_GET['area'] ?? ''));
$user_id = trim((string)($_GET['user_id'] ?? ''));
$weekday = trim((string)($_GET['weekday'] ?? ''));

$offset = (int)($_GET['offset'] ?? 0);
$limit = (int)($_GET['limit'] ?? 50);
if ($offset < 0) {
    $offset = 0;
}
if ($limit < 1 || $limit > 200) {
    $limit = 50;
}

$where = [];
$params = [];

$weekdayOptions = [
    '' => 'الكل',
    '1' => 'الأحد',
    '2' => 'الإثنين',
    '3' => 'الثلاثاء',
    '4' => 'الأربعاء',
    '5' => 'الخميس',
    '6' => 'الجمعة',
    '7' => 'السبت',
];

if ($from !== '') {
    $where[] = 'visit_date >= ?';
    $params[] = $from;
}
if ($to !== '') {
    $where[] = 'visit_date <= ?';
    $params[] = $to;
}

if ($name !== '') {
    $where[] = '(dv.clinic_name LIKE ? OR dv.person_name LIKE ?)';
    $like = '%' . $name . '%';
    $params[] = $like;
    $params[] = $like;
}

if ($area !== '') {
    $where[] = 'dv.area = ?';
    $params[] = $area;
}

if ($user_id !== '') {
    $where[] = 'dv.user_id = ?';
    $params[] = (int)$user_id;
}

if ($weekday !== '' && isset($weekdayOptions[$weekday])) {
    $where[] = 'DAYOFWEEK(dv.visit_date) = ?';
    $params[] = (int)$weekday;
}

$whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

$countStmt = db()->prepare('SELECT COUNT(*) AS c FROM daily_visits dv' . $whereSql);
$countStmt->execute($params);
$totalCount = (int)($countStmt->fetch()['c'] ?? 0);

$sql = 'SELECT dv.*, u.username, (SELECT COUNT(*) FROM daily_visit_contacts c WHERE c.daily_visit_id = dv.id) AS contacts_count
        FROM daily_visits dv JOIN users u ON u.id = dv.user_id' . $whereSql . ' ORDER BY dv.visit_date DESC, dv.id DESC LIMIT ? OFFSET ?';
$stmt = db()->prepare($sql);
$stmt->execute(array_merge($params, [$limit, $offset]));
$rows = $stmt->fetchAll();

$contactsByVisitId = [];
if ($rows) {
    $ids = array_map(static fn($r) => (int)$r['id'], $rows);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $cStmt = db()->prepare('SELECT daily_visit_id, person_name, job_title, mobile FROM daily_visit_contacts WHERE daily_visit_id IN (' . $placeholders . ') ORDER BY id ASC');
    $cStmt->execute($ids);
    $cRows = $cStmt->fetchAll();
    foreach ($cRows as $c) {
        $vid = (int)$c['daily_visit_id'];
        if (!isset($contactsByVisitId[$vid])) {
            $contactsByVisitId[$vid] = [];
        }
        $contactsByVisitId[$vid][] = $c;
    }
}

ob_start();
foreach ($rows as $r) {
    $id = (int)$r['id'];
    $cs = $contactsByVisitId[$id] ?? [];
    ?>
    <tr>
        <td><?= $id ?></td>
        <td class="no-print text-end text-nowrap">
            <div class="d-flex justify-content-end">
                <div class="btn-group" role="group">
                    <a class="btn btn-app-outline btn-sm" title="تصدير Excel" aria-label="تصدير Excel" href="<?= htmlspecialchars(BASE_URL) ?>/export_report.php?id=<?= $id ?>">
                        <i class="bi bi-file-earmark-excel"></i>
                    </a>
                    <a class="btn btn-app-outline btn-sm" title="طباعة" aria-label="طباعة" target="_blank" href="<?= htmlspecialchars(BASE_URL) ?>/print.php?autoprint=1&mode=single&id=<?= $id ?>">
                        <i class="bi bi-printer"></i>
                    </a>
                    <?php if (is_admin($user)): ?>
                        <a class="btn btn-app-outline btn-sm" title="تعديل" aria-label="تعديل" href="<?= htmlspecialchars(BASE_URL) ?>/edit_report.php?id=<?= $id ?>">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form method="post" action="<?= htmlspecialchars(BASE_URL) ?>/delete_report.php" onsubmit="return confirm('حذف التقرير؟');" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button class="btn btn-danger btn-sm" type="submit" title="حذف" aria-label="حذف">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </td>
        <td><?= htmlspecialchars((string)$r['area']) ?></td>
        <td><?= htmlspecialchars((string)($r['address'] ?? '')) ?></td>
        <td><?= htmlspecialchars((string)$r['clinic_name']) ?></td>
        <td>
            <?php if ($cs): ?>
                <?php foreach ($cs as $c): ?>
                    <div>
                        <?= htmlspecialchars((string)($c['person_name'] ?? '')) ?>
                        <?php if ((string)($c['job_title'] ?? '') !== ''): ?>
                            <span class="text-muted">(<?= htmlspecialchars((string)$c['job_title']) ?>)</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?= htmlspecialchars((string)$r['person_name']) ?>
            <?php endif; ?>
        </td>
        <td class="d-none d-lg-table-cell">
            <?php if ($cs): ?>
                <?php foreach ($cs as $c): ?>
                    <div><?= htmlspecialchars((string)($c['job_title'] ?? '')) ?></div>
                <?php endforeach; ?>
            <?php else: ?>
                <?= htmlspecialchars((string)$r['job_title']) ?>
            <?php endif; ?>
        </td>
        <td class="d-none d-lg-table-cell">
            <?php if ($cs): ?>
                <?php foreach ($cs as $c): ?>
                    <div class="text-nowrap"><?= htmlspecialchars((string)($c['mobile'] ?? '')) ?></div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-nowrap"><?= htmlspecialchars((string)$r['mobile']) ?></div>
            <?php endif; ?>
        </td>
        <td class="text-nowrap"><?= htmlspecialchars((string)$r['visit_date']) ?></td>
        <td class="text-nowrap"><?= htmlspecialchars((string)$r['visit_number']) ?></td>
        <td class="d-none d-lg-table-cell"><?= htmlspecialchars((string)$r['interest']) ?></td>
        <td class="d-none d-lg-table-cell"><?= htmlspecialchars((string)$r['visit_type']) ?></td>
        <td class="d-none d-lg-table-cell"><?= htmlspecialchars((string)$r['visit_result']) ?></td>
        <td class="d-none d-lg-table-cell"><?= htmlspecialchars((string)$r['execution_status']) ?></td>
        <td class="d-none d-xl-table-cell" style="min-width: 260px; max-width: 420px; white-space: normal;">
            <?= htmlspecialchars((string)$r['notes']) ?>
        </td>
        <td class="d-none d-lg-table-cell"><?= htmlspecialchars((string)$r['username']) ?></td>
    </tr>
    <?php
}
$html = (string)ob_get_clean();

$shownCount = (int)min($offset + count($rows), $totalCount);
$hasMore = ($offset + $limit) < $totalCount;

$baseQuery = [
    'from' => $from,
    'to' => $to,
    'name' => $name,
    'area' => $area,
    'user_id' => $user_id,
    'weekday' => $weekday,
];

$nextUrl = null;
if ($hasMore) {
    $nextUrl = BASE_URL . '/reports_load_more.php?' . http_build_query(array_merge($baseQuery, [
        'offset' => $offset + $limit,
        'limit' => $limit,
    ]));
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'html' => $html,
    'shownCount' => $shownCount,
    'hasMore' => $hasMore,
    'nextUrl' => $nextUrl,
], JSON_UNESCAPED_UNICODE);
