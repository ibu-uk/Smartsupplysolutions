<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();
require_admin();

function detect_delimiter_from_file($fh): string
{
    $delims = [',', '،', ';', '؛', "\t"];
    $scores = array_fill_keys($delims, 0);

    $pos = ftell($fh);
    $linesChecked = 0;
    while ($linesChecked < 25 && ($line = fgets($fh)) !== false) {
        $line = trim((string)$line);
        if ($line === '' || preg_match('/^[,;،؛\s]+$/u', $line)) {
            continue;
        }
        foreach ($delims as $d) {
            $scores[$d] += substr_count($line, $d);
        }
        $linesChecked++;
    }

    fseek($fh, $pos);

    $best = ',';
    $bestScore = -1;
    foreach ($scores as $d => $score) {
        if ($score > $bestScore) {
            $bestScore = $score;
            $best = $d;
        }
    }
    return $best;
}

function detect_csv_delimiter(string $line): string
{
    $counts = [
        ',' => substr_count($line, ','),
        '،' => substr_count($line, '،'),
        ';' => substr_count($line, ';'),
        '؛' => substr_count($line, '؛'),
        "\t" => substr_count($line, "\t"),
    ];

    $best = ',';
    $bestCount = -1;
    foreach ($counts as $d => $c) {
        if ($c > $bestCount) {
            $bestCount = $c;
            $best = $d;
        }
    }
    return $best;
}

function to_utf8(string $s): string
{
    // remove UTF-8 BOM
    $s = preg_replace('/^\xEF\xBB\xBF/', '', $s) ?? $s;

    // handle UTF-16 (common when saving CSV from Excel)
    if (str_contains($s, "\x00")) {
        $bom = substr($s, 0, 2);
        if ($bom === "\xFF\xFE") {
            $converted = @iconv('UTF-16LE', 'UTF-8//IGNORE', $s);
            if (is_string($converted) && $converted !== '') {
                return $converted;
            }
        }
        if ($bom === "\xFE\xFF") {
            $converted = @iconv('UTF-16BE', 'UTF-8//IGNORE', $s);
            if (is_string($converted) && $converted !== '') {
                return $converted;
            }
        }

        $converted = @iconv('UTF-16', 'UTF-8//IGNORE', $s);
        if (is_string($converted) && $converted !== '') {
            return $converted;
        }
    }

    // If already UTF-8, keep it.
    if (function_exists('mb_check_encoding') && mb_check_encoding($s, 'UTF-8')) {
        return $s;
    }

    // Try common Arabic encodings.
    foreach (['Windows-1256', 'ISO-8859-6'] as $enc) {
        $converted = @iconv($enc, 'UTF-8//IGNORE', $s);
        if (is_string($converted) && $converted !== '') {
            return $converted;
        }
    }

    return $s;
}

function normalize_utf8(string $s): string
{
    $s = trim(to_utf8($s));
    return trim($s);
}

function normalize_header_token(string $s): string
{
    $s = normalize_utf8($s);
    // remove all whitespace (handles headers like: "ت ا ر ي خ الزيارة")
    $s = preg_replace('/\s+/u', '', $s) ?? $s;
    return $s;
}

function parse_date_to_ymd(string $s): ?string
{
    $s = trim($s);
    if ($s === '' || $s === '—' || $s === '-') {
        return null;
    }

    $s = str_replace(['\\', '.'], ['/', '/'], $s);

    $dt = DateTime::createFromFormat('d/m/Y', $s);
    if ($dt instanceof DateTime) {
        return $dt->format('Y-m-d');
    }

    $dt = DateTime::createFromFormat('d/m/y', $s);
    if ($dt instanceof DateTime) {
        return $dt->format('Y-m-d');
    }

    $dt = DateTime::createFromFormat('Y-m-d', $s);
    if ($dt instanceof DateTime) {
        return $dt->format('Y-m-d');
    }

    return null;
}

function normalize_cell(?string $v): string
{
    $v = trim((string)$v);
    if ($v === '—' || $v === '-') {
        return '';
    }
    return $v;
}

function split_area_address(string $s): array
{
    $s = trim($s);
    if ($s === '' || $s === '—' || $s === '-') {
        return ['', null];
    }

    $parts = preg_split('/\s*-\s*/u', $s, 2);
    if (is_array($parts) && count($parts) === 2) {
        $area = trim((string)$parts[0]);
        $addr = trim((string)$parts[1]);
        return [$area, ($addr !== '' ? $addr : null)];
    }

    return [$s, null];
}

$expectedHeaders = [
    'تاريخ الزيارة',
    'تاريخ المتابعة',
    'المنطقة + الطابق',
    'اسم العيادة',
    'رقم الزيارة',
    'اسم الشخص',
    'المسمى الوظيفي',
    'رقم الموبايل',
    'مستوى الاهتمام',
    'نوع الزيارة',
    'نتيجة الزيارة',
    'حالة التنفيذ',
    'تواريخ كل الزيارات',
    'ملاحظات',
    'ملاحظات المتابعة',
];

$result = null;
$error = '';
$errorDetail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['csv']) || !is_array($_FILES['csv']) || ($_FILES['csv']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = 'Upload failed.';
    } else {
        $uploadedName = (string)($_FILES['csv']['name'] ?? '');
        $tmpPath = (string)($_FILES['csv']['tmp_name'] ?? '');
        $raw = @file_get_contents($tmpPath);
        if (!is_string($raw) || $raw === '') {
            $error = 'Cannot read uploaded file.';
        } else {
            $utf8 = to_utf8($raw);
            $convertedPath = tempnam(sys_get_temp_dir(), 'ss_csv_');
            if (!is_string($convertedPath) || $convertedPath === '') {
                $error = 'Cannot create temp file.';
            } else {
                file_put_contents($convertedPath, $utf8);
                $fh = fopen($convertedPath, 'rb');
            }
        }
        if ($fh === false) {
            $error = 'Cannot read uploaded file.';
        } else {
            $headerRow = null;
            $delimiter = detect_delimiter_from_file($fh);
            rewind($fh);

            $normalizedExpected = array_map(static fn($h) => normalize_utf8($h), $expectedHeaders);
            $firstDataRow = null;

            // Read first non-empty row using fgetcsv (supports quoted fields with newlines)
            while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {
                if (!is_array($row)) {
                    continue;
                }

                $allEmpty = true;
                foreach ($row as $cell) {
                    if (trim((string)$cell) !== '') {
                        $allEmpty = false;
                        break;
                    }
                }
                if ($allEmpty) {
                    continue;
                }

                $row = array_map(static fn($c) => normalize_utf8((string)$c), $row);

                $isHeader = false;
                foreach ($row as $cell) {
                    $token = normalize_header_token((string)$cell);
                    if ($token !== '' && str_contains($token, 'تاريخالزيارة')) {
                        $isHeader = true;
                        break;
                    }
                }

                if ($isHeader) {
                    $headerRow = $row;
                } else {
                    // No header row found -> assume fixed order = expected headers
                    $headerRow = $normalizedExpected;
                    $firstDataRow = $row;
                }

                break;
            }

            if ($headerRow === null) {
                $error = 'CSV has no data.';
            } elseif (count($headerRow) < count($normalizedExpected)) {
                $error = 'CSV header has fewer columns than expected.';
                $result = [
                    'debug' => [
                        'uploaded_name' => $uploadedName,
                        'detected_delimiter' => ($delimiter === "\t" ? 'TAB' : ($delimiter === '،' ? 'ARABIC_COMMA' : ($delimiter === '؛' ? 'ARABIC_SEMICOLON' : $delimiter))),
                        'header_columns' => count($headerRow),
                        'expected_columns' => count($normalizedExpected),
                        'header_preview' => implode(' | ', array_slice($headerRow, 0, 8)),
                    ],
                ];
            } else {
                $headerSlice = array_slice($headerRow, 0, count($normalizedExpected));
                $mismatch = [];
                foreach ($normalizedExpected as $i => $h) {
                    if (normalize_header_token((string)($headerSlice[$i] ?? '')) !== normalize_header_token((string)$h)) {
                        $mismatch[] = [$i, $h, (string)($headerSlice[$i] ?? '')];
                    }
                }

                if ($mismatch) {
                    $error = 'Header mismatch. Please make sure the first row headers match exactly.';
                    $result = ['mismatch' => $mismatch];
                } else {
                    $idx = array_flip($headerSlice);

                        $pdo = db();
                        $ok = 0;
                        $fail = 0;
                        $failRows = [];

                        $pdo->beginTransaction();
                        try {
                            if (is_array($firstDataRow)) {
                                $r = array_map(static fn($c) => normalize_utf8((string)$c), $firstDataRow);

                                $visitDateRaw = normalize_cell($r[$idx['تاريخ الزيارة']] ?? '');
                                $clinicName = normalize_cell($r[$idx['اسم العيادة']] ?? '');
                                $areaFloor = normalize_cell($r[$idx['المنطقة + الطابق']] ?? '');

                                if ($visitDateRaw !== '' || $clinicName !== '' || $areaFloor !== '') {
                                    $visit_date = parse_date_to_ymd($visitDateRaw);
                                    $follow_up_date = parse_date_to_ymd(normalize_cell($r[$idx['تاريخ المتابعة']] ?? ''));
                                    [$area, $address] = split_area_address($areaFloor);

                                    $visit_number = normalize_cell($r[$idx['رقم الزيارة']] ?? '');
                                    $person_name = normalize_cell($r[$idx['اسم الشخص']] ?? '');
                                    $job_title = normalize_cell($r[$idx['المسمى الوظيفي']] ?? '');
                                    $mobile = normalize_cell($r[$idx['رقم الموبايل']] ?? '');
                                    $interest = normalize_cell($r[$idx['مستوى الاهتمام']] ?? '');
                                    $visit_type = normalize_cell($r[$idx['نوع الزيارة']] ?? '');
                                    $visit_result = normalize_cell($r[$idx['نتيجة الزيارة']] ?? '');
                                    $execution_status = normalize_cell($r[$idx['حالة التنفيذ']] ?? '');

                                    $notesMain = normalize_cell($r[$idx['ملاحظات']] ?? '');
                                    $notesFollow = normalize_cell($r[$idx['ملاحظات المتابعة']] ?? '');

                                    if ($visit_date === null || $area === '' || $clinicName === '' || $visit_number === '' || $interest === '' || $visit_type === '' || $visit_result === '' || $execution_status === '') {
                                        $fail++;
                                        $failRows[] = ['row' => $r, 'reason' => 'Missing required fields or invalid date.'];
                                    } else {
                                        if ($person_name === '') {
                                            $person_name = 'غير محدد';
                                        }

                                        $stmt = $pdo->prepare(
                                            'INSERT INTO daily_visits (user_id, visit_date, follow_up_date, follow_up_status, follow_up_done_at, follow_up_action_note, area, address, clinic_name, visit_number, person_name, job_title, mobile, interest, visit_type, visit_result, execution_status, notes)
                                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                                        );

                                        $hasFollowUp = ($follow_up_date !== null);
                                        $stmt->execute([
                                            (int)current_user()['id'],
                                            $visit_date,
                                            $follow_up_date,
                                            ($hasFollowUp ? 'next' : null),
                                            null,
                                            ($notesFollow !== '' ? $notesFollow : null),
                                            $area,
                                            ($address !== null && $address !== '' ? $address : null),
                                            $clinicName,
                                            $visit_number,
                                            $person_name,
                                            ($job_title !== '' ? $job_title : ''),
                                            ($mobile !== '' ? $mobile : null),
                                            $interest,
                                            $visit_type,
                                            $visit_result,
                                            $execution_status,
                                            ($notesMain !== '' ? $notesMain : null),
                                        ]);

                                        $visitId = (int)$pdo->lastInsertId();

                                        if ($person_name !== '' && $person_name !== 'غير محدد') {
                                            $cStmt = $pdo->prepare('INSERT INTO daily_visit_contacts (daily_visit_id, person_name, job_title, mobile) VALUES (?, ?, ?, ?)');
                                            $cStmt->execute([
                                                $visitId,
                                                $person_name,
                                                ($job_title !== '' ? $job_title : ''),
                                                ($mobile !== '' ? $mobile : null),
                                            ]);
                                        }

                                        $ok++;
                                    }
                                }
                            }
                            while (($r = fgetcsv($fh, 0, $delimiter)) !== false) {
                                if (!is_array($r)) {
                                    continue;
                                }

                                $r = array_map(static fn($c) => normalize_utf8((string)$c), $r);

                                $visitDateRaw = normalize_cell($r[$idx['تاريخ الزيارة']] ?? '');
                                $clinicName = normalize_cell($r[$idx['اسم العيادة']] ?? '');
                                $areaFloor = normalize_cell($r[$idx['المنطقة + الطابق']] ?? '');

                                if ($visitDateRaw === '' && $clinicName === '' && $areaFloor === '') {
                                    continue;
                                }

                                $visit_date = parse_date_to_ymd($visitDateRaw);
                                $follow_up_date = parse_date_to_ymd(normalize_cell($r[$idx['تاريخ المتابعة']] ?? ''));
                                [$area, $address] = split_area_address($areaFloor);

                                $visit_number = normalize_cell($r[$idx['رقم الزيارة']] ?? '');
                                $person_name = normalize_cell($r[$idx['اسم الشخص']] ?? '');
                                $job_title = normalize_cell($r[$idx['المسمى الوظيفي']] ?? '');
                                $mobile = normalize_cell($r[$idx['رقم الموبايل']] ?? '');
                                $interest = normalize_cell($r[$idx['مستوى الاهتمام']] ?? '');
                                $visit_type = normalize_cell($r[$idx['نوع الزيارة']] ?? '');
                                $visit_result = normalize_cell($r[$idx['نتيجة الزيارة']] ?? '');
                                $execution_status = normalize_cell($r[$idx['حالة التنفيذ']] ?? '');

                                $notesMain = normalize_cell($r[$idx['ملاحظات']] ?? '');
                                $notesFollow = normalize_cell($r[$idx['ملاحظات المتابعة']] ?? '');

                                if ($visit_date === null || $area === '' || $clinicName === '' || $visit_number === '' || $interest === '' || $visit_type === '' || $visit_result === '' || $execution_status === '') {
                                    $fail++;
                                    $failRows[] = ['row' => $r, 'reason' => 'Missing required fields or invalid date.'];
                                    continue;
                                }

                                if ($person_name === '') {
                                    $person_name = 'غير محدد';
                                }

                                $stmt = $pdo->prepare(
                                    'INSERT INTO daily_visits (user_id, visit_date, follow_up_date, follow_up_status, follow_up_done_at, follow_up_action_note, area, address, clinic_name, visit_number, person_name, job_title, mobile, interest, visit_type, visit_result, execution_status, notes)
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                                );

                                $hasFollowUp = ($follow_up_date !== null);
                                $stmt->execute([
                                    (int)current_user()['id'],
                                    $visit_date,
                                    $follow_up_date,
                                    ($hasFollowUp ? 'next' : null),
                                    null,
                                    ($notesFollow !== '' ? $notesFollow : null),
                                    $area,
                                    ($address !== null && $address !== '' ? $address : null),
                                    $clinicName,
                                    $visit_number,
                                    $person_name,
                                    ($job_title !== '' ? $job_title : ''),
                                    ($mobile !== '' ? $mobile : null),
                                    $interest,
                                    $visit_type,
                                    $visit_result,
                                    $execution_status,
                                    ($notesMain !== '' ? $notesMain : null),
                                ]);

                                $visitId = (int)$pdo->lastInsertId();

                                if ($person_name !== '' && $person_name !== 'غير محدد') {
                                    $cStmt = $pdo->prepare('INSERT INTO daily_visit_contacts (daily_visit_id, person_name, job_title, mobile) VALUES (?, ?, ?, ?)');
                                    $cStmt->execute([
                                        $visitId,
                                        $person_name,
                                        ($job_title !== '' ? $job_title : ''),
                                        ($mobile !== '' ? $mobile : null),
                                    ]);
                                }

                                $ok++;
                            }

                            $pdo->commit();
                            $result = ['ok' => $ok, 'fail' => $fail, 'failRows' => $failRows, 'uploaded_name' => $uploadedName];
                        } catch (Throwable $e) {
                            $pdo->rollBack();
                            $error = 'DB error during import.';
                            $errorDetail = $e->getMessage();
                        }
                }
            }

            fclose($fh);
            if (isset($convertedPath) && is_string($convertedPath) && $convertedPath !== '' && file_exists($convertedPath)) {
                @unlink($convertedPath);
            }
        }
    }
}

?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(APP_NAME) ?> - Import CSV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(BASE_URL) ?>/assets/app.css" rel="stylesheet">
</head>
<body class="app-bg">
<div class="container py-4" style="max-width: 980px;">
    <div class="card shadow-sm app-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h5 mb-0">استيراد CSV</h1>
                <a class="btn btn-app-outline btn-sm" href="<?= htmlspecialchars(BASE_URL) ?>/reports.php">رجوع</a>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($errorDetail !== ''): ?>
                <div class="alert alert-warning"><pre class="mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($errorDetail) ?></pre></div>
            <?php endif; ?>

            <?php if (is_array($result) && isset($result['debug']) && is_array($result['debug'])): ?>
                <div class="alert alert-warning">
                    <div class="fw-semibold mb-2">Debug</div>
                    <div class="small text-muted">File: <?= htmlspecialchars((string)($result['debug']['uploaded_name'] ?? '')) ?></div>
                    <div class="small text-muted">Delimiter: <?= htmlspecialchars((string)($result['debug']['detected_delimiter'] ?? '')) ?></div>
                    <div class="small text-muted">Header columns: <?= (int)($result['debug']['header_columns'] ?? 0) ?> / Expected: <?= (int)($result['debug']['expected_columns'] ?? 0) ?></div>
                    <div class="small text-muted">Header preview:</div>
                    <pre class="mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars((string)($result['debug']['header_preview'] ?? '')) ?></pre>
                </div>
            <?php endif; ?>

            <?php if (is_array($result) && isset($result['ok'])): ?>
                <div class="alert alert-success">تم الاستيراد: <?= (int)$result['ok'] ?> | فشل: <?= (int)$result['fail'] ?><?php if ((string)($result['uploaded_name'] ?? '') !== ''): ?> | الملف: <?= htmlspecialchars((string)$result['uploaded_name']) ?><?php endif; ?></div>
            <?php endif; ?>

            <?php if (is_array($result) && isset($result['failRows']) && is_array($result['failRows']) && count($result['failRows']) > 0): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="fw-semibold mb-2">أمثلة على السجلات الفاشلة</div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>السبب</th>
                                        <th>تاريخ الزيارة</th>
                                        <th>المنطقة</th>
                                        <th>اسم العيادة</th>
                                        <th>رقم الزيارة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($result['failRows'], 0, 10) as $fr): ?>
                                        <?php $rr = (array)($fr['row'] ?? []); ?>
                                        <tr>
                                            <td><?= htmlspecialchars((string)($fr['reason'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string)($rr[0] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string)($rr[2] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string)($rr[3] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string)($rr[4] ?? '')) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (is_array($result) && isset($result['mismatch'])): ?>
                <div class="alert alert-warning">
                    <div class="fw-semibold mb-2">Header mismatch details:</div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Expected</th>
                                    <th>Found</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result['mismatch'] as $m): ?>
                                    <tr>
                                        <td><?= (int)($m[0] + 1) ?></td>
                                        <td><?= htmlspecialchars((string)$m[1]) ?></td>
                                        <td><?= htmlspecialchars((string)$m[2]) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="mt-3">
                <div class="mb-3">
                    <label class="form-label">CSV file</label>
                    <input type="file" name="csv" accept=".csv,text/csv" class="form-control" required>
                </div>
                <button class="btn btn-app" type="submit">استيراد</button>
            </form>

            <div class="text-muted small mt-3">
                يجب أن تكون عناوين الأعمدة (Header) مطابقة تماماً للملف الحالي.
            </div>
        </div>
    </div>
</div>
</body>
</html>
