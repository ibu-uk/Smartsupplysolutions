<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (current_user()) {
    header('Location: ' . BASE_URL . '/daily_report.php');
    exit;
}

header('Location: ' . BASE_URL . '/login.php');
exit;
