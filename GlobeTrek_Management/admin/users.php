<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../management_auth.php';
require_management_role('admin');
verify_csrf();
$title = 'User & Staff Management';
$portal = 'admin';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    if ($action === 'create') {
        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = $_POST['role'] ?? 'staff';
        $pass = $_POST['password'] ?? '';
        if (
            !$name ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            !in_array($role, ['staff', 'admin'], true) ||
            strlen($pass) < 8
        ) {
            flash('danger', 'Enter valid details; password must be at least 8 characters.');
        } else {
            try {
                $q = db()->prepare(
                    "INSERT INTO users(full_name,email,phone,password_hash,role,status) VALUES(?,?,?,?,?,'active')",
                );
                $q->execute([$name, $email, $phone, password_hash($pass, PASSWORD_DEFAULT), $role]);
                audit('create', 'user', (string) db()->lastInsertId(), ['role' => $role]);
                flash('success', 'Account created.');
            } catch (PDOException $e) {
                flash('danger', 'An account with that email and role already exists.');
            };
        }
    } else {
        $role = $_POST['role'] ?? 'staff';
        $status = $_POST['status'] ?? 'active';
        $account = db()->prepare("SELECT id FROM users WHERE id=? AND role IN ('staff','admin')");
        $account->execute([$id]);
        if (!$account->fetch()) {
            flash('danger', 'That management account does not exist.');
        } elseif (!in_array($role, ['staff', 'admin'], true) || !in_array($status, ['active', 'inactive'], true)) {
            flash('danger', 'Select a valid management role and status.');
        } elseif ($id === user()['id'] && ($role !== 'admin' || $status !== 'active')) {
            flash('danger', 'You cannot remove your own administrator access.');
        } else {
            try {
                db()
                    ->prepare('UPDATE users SET role=?,status=? WHERE id=?')
                    ->execute([$role, $status, $id]);
                audit('update', 'user', (string) $id, ['role' => $role, 'status' => $status]);
                flash('success', 'User access updated.');
            } catch (PDOException $e) {
                flash('danger', 'An account with that email and role already exists.');
            };
        }
    }
    go(app_url('admin/users.php'));
}
$rows = db()
    ->query("SELECT id,full_name,email,phone,role,status,created_at,last_login_at FROM users WHERE role IN ('staff','admin') ORDER BY created_at DESC")
    ->fetchAll();
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Users · GlobeTrek</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="../assets/style.css?v=<?= filemtime(__DIR__ . '/../assets/style.css') ?>" rel="stylesheet"></head><body><?php
$navActive = 'users';
$navBase = '../';
include __DIR__ . '/../includes/sidebar.php';
?><div class="ops-main"><header class="ops-top"><strong>People & access</strong><span class="ops-user">Admin · <?= e(
    user()['full_name'],
) ?></span></header><main class="ops-content"><?php flashes(); ?><div class="d-flex justify-content-between align-items-end mb-4"><div><div class="section-kicker">Access control</div><h1 class="display-serif mt-2">People.</h1><p class="muted">Create staff accounts and control roles/status.</p></div><button class="btn btn-primary rounded-pill" data-bs-toggle="collapse" data-bs-target="#newUser">+ New account</button></div>
<div class="collapse mb-4" id="newUser"><div class="dashboard-card"><h5 class="fw-bold">Create account</h5><form method="post" class="row g-3 mt-1"><?= csrf_field() ?><input type="hidden" name="action" value="create"><div class="col-md-6"><input class="form-control" name="full_name" placeholder="Full name" required></div><div class="col-md-6"><input class="form-control" type="email" name="email" placeholder="Email" required></div><div class="col-md-4"><input class="form-control" name="phone" placeholder="Phone"></div><div class="col-md-3"><select class="form-select" name="role"><?php foreach (
    ['staff', 'admin']
    as $v
): ?><option><?= $v ?></option><?php endforeach; ?></select></div><div class="col-md-5"><input class="form-control" type="password" name="password" placeholder="Temporary password (8+ chars)" minlength="8" required></div><div class="col-12"><button class="btn btn-primary rounded-pill">Create account</button></div></form></div></div>
<div class="table-card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Person</th><th>Role</th><th>Status</th><th>Last login</th><th>Save</th></tr></thead><tbody><?php foreach (
    $rows
    as $r
): ?><tr><td><strong><?= e($r['full_name']) ?></strong><div class="small muted"><?= e($r['email']) ?> · <?= e(
     $r['phone'],
 ) ?></div></td><td colspan="3"><form method="post" class="row g-2 align-items-center"><?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= $r[
    'id'
] ?>"><div class="col-md-4"><select name="role" class="form-select form-select-sm"><?php foreach (
    ['staff', 'admin']
    as $v
): ?><option <?= $r['role'] === $v
    ? 'selected'
    : '' ?>><?= $v ?></option><?php endforeach; ?></select></div><div class="col-md-4"><select name="status" class="form-select form-select-sm"><?php foreach (
    ['active', 'inactive']
    as $v
): ?><option <?= $r['status'] === $v
    ? 'selected'
    : '' ?>><?= $v ?></option><?php endforeach; ?></select></div><div class="col-md-4"><span class="small muted"><?= $r[
    'last_login_at'
]
    ? e($r['last_login_at'])
    : 'Never' ?></span></div></form></td><td><button form="" class="btn btn-sm btn-primary rounded-pill" onclick="this.closest('tr').querySelector('form').submit()">Save</button></td></tr><?php endforeach; ?></tbody></table></div></div></main></div></body></html>
