<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function current_user(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, username FROM users WHERE id = ?');
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

function is_admin(?array $user = null): bool
{
    $u = $user ?? current_user();
    if (!$u) {
        return false;
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
    return true;
}

function logout_user(): void
{
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
}
