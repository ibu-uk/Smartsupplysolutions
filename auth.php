<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function flash_set(string $key, string $message): void
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }
    if (!array_key_exists($key, $_SESSION['flash'])) {
        return null;
    }
    $msg = (string)$_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function current_user(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, username, is_admin FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function require_login(): void
{
    if (!current_user()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function reminders_count(?array $user = null): int
{
    $u = $user ?? current_user();
    if (!$u) {
        return 0;
    }

    $sql = "SELECT COUNT(*) AS c FROM (
                SELECT r.id
                FROM reminders r
                JOIN daily_visits dv ON dv.id = r.daily_visit_id
                WHERE r.follow_up_date <= CURDATE() AND r.status = 'next'

                UNION ALL

                SELECT dv.id
                FROM daily_visits dv
                WHERE dv.follow_up_date IS NOT NULL
                  AND dv.follow_up_date <= CURDATE()
                  AND (dv.follow_up_status IS NULL OR dv.follow_up_status = 'next')
                  AND NOT EXISTS (SELECT 1 FROM reminders r2 WHERE r2.daily_visit_id = dv.id)
            ) x";

    $stmt = db()->prepare($sql);
    $stmt->execute();
    return (int)($stmt->fetch()['c'] ?? 0);
}

function is_admin(?array $user = null): bool
{
    $u = $user ?? current_user();
    if (!$u) {
        return false;
    }

    if (array_key_exists('is_admin', $u)) {
        return (int)$u['is_admin'] === 1;
    }

    return ($u['username'] ?? null) === 'admin';
}

function require_admin(): void
{
    $u = current_user();
    if (!$u) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
    if (!is_admin($u)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function login_user(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT id, username, password_hash FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }

    $_SESSION['user_id'] = (int)$user['id'];
    unset($_SESSION['reminders_popup_shown']);
    return true;
}

function logout_user(): void
{
    unset($_SESSION['user_id']);
    unset($_SESSION['reminders_popup_shown']);
    session_regenerate_id(true);
}
