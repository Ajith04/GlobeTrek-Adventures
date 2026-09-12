<?php
require_once __DIR__ . '/config.php';
$state = gt_reset_state();
if (!$state || ($state['purpose'] ?? '') !== 'management' || empty($state['verified'])) {
    flash('warning', 'Verify your email code first.');
    go('forgot_password.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $r = gt_reset_password($_POST['password'] ?? '', $_POST['confirm_password'] ?? '');
    if ($r['ok']) {
        flash('success', 'Password updated successfully. You can now sign in with your new password.');
        go('login.php');
    }
    $error = $r['message'];
}
?><!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>GlobeTrek Management · New password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>" rel="stylesheet">
</head>

<body class="auth-page">
    <div class="auth-card">
        <div class="brand-mark">◈ GlobeTrek</div>
        <div class="small text-secondary mb-4">Management Portal</div>
        <h1 class="fw-bold">Choose a new password.</h1>
        <p class="text-secondary">Use at least 8 characters with at least one letter and one number.</p><?php if (
            $error
        ): ?>
            <div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post"><?= csrf_field() ?><label class="form-label">New password</label><input
                class="form-control mb-3" type="password" name="password" minlength="8" required
                autocomplete="new-password"><label class="form-label">Confirm new password</label><input
                class="form-control mb-4" type="password" name="confirm_password" minlength="8" required
                autocomplete="new-password"><button class="btn btn-primary w-100">Update password</button></form>
    </div>
</body>

</html>