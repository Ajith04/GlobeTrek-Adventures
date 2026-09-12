<?php
require_once __DIR__ . '/config.php';
if (current_user() && in_array(current_user()['role'] ?? '', ['staff', 'admin'], true)) {
    go('index.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $q = db()->prepare("SELECT * FROM users WHERE email=? AND role IN ('staff','admin') AND status='active' LIMIT 1");
    $q->execute([trim($_POST['email'] ?? '')]);
    $u = $q->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
        login_user($u);
        db()
            ->prepare('UPDATE users SET last_login_at=NOW() WHERE id=?')
            ->execute([$u['id']]);
        audit('login', 'user', (string) $u['id']);
        go('index.php');
    }
    $error = 'Invalid management email or password.';
}
?><!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>GlobeTrek Management · Sign in</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>" rel="stylesheet">
</head>

<body class="auth-page">
    <div class="auth-card">
        <div class="brand-mark">◈ GlobeTrek</div>
        <div class="small text-secondary mb-4">Management Portal</div>
        <h1 class="fw-bold">Welcome back.</h1>
        <p class="text-secondary">One secure login for staff and administrators.</p>
        <?php
        flashes();
        if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif;
        ?>
        <form method="post"><?= csrf_field() ?><label class="form-label">Work email</label><input
                class="form-control mb-3" type="email" name="email" required>
            <div class="d-flex justify-content-between align-items-center"><label class="form-label">Password</label><a
                    class="small" href="forgot_password.php">Forgot password?</a></div><input class="form-control mb-4"
                type="password" name="password" required autocomplete="current-password">
            <button class="btn btn-primary w-100">Sign in</button>
        </form>
    </div>
</body>

</html>