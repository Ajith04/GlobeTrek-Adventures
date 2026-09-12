<?php
require_once __DIR__ . '/config.php';

function require_management(): void
{
    $u = current_user();
    if (!$u || !in_array($u['role'] ?? '', ['staff', 'admin'], true)) {
        go(app_url('login.php'));
    }
}
function require_management_role(string $role): void
{
    require_management();
    if ((current_user()['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Access denied.');
    }
}
