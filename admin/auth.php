<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function admin_require_login(): void
{
    admin_start_session();

    if (
        empty($_SESSION['admin_user_id'])
        || empty($_SESSION['admin_username'])
        || empty($_SESSION['admin_role'])
    ) {
        header('Location: login.php');
        exit;
    }
}

function admin_is_super_admin(): bool
{
    return ($_SESSION['admin_role'] ?? '') === 'super_admin';
}