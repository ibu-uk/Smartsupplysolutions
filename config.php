<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'smartsupplysolutions');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Daily Visits Report');

define('BASE_URL', '/smartsupplysolutions');
