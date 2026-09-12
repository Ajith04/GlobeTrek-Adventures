<?php
require_once __DIR__ . '/config.php';
$state = gt_reset_state();
if (!$state || ($state['purpose'] ?? '') !== 'management') {
    flash('warning', 'Request a password reset code first.');
    go('forgot_password.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (($_POST['action'] ?? 'verify') === 'resend') {
        $u = gt_allowed_user_for_reset((string) $state['email'], 'management');
        if (!$u) {
            gt_clear_reset_state();
            flash('warning', 'Your account is unavailable.');
            go('forgot_password.php');
        }
        $r = gt_create_reset_otp($u, 'management');
        if ($r['ok']) {
            flash('success', 'A new verification code was sent.');
            go('verify_otp.php');
        }
        $error = $r['message'];
    } else {
        $r = gt_verify_reset_otp(trim($_POST['otp'] ?? ''));
        if ($r['ok']) {
            go('reset_password.php');
        }
        $error = $r['message'];
    }
}
?><!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>GlobeTrek Management · Verify code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>" rel="stylesheet">
</head>

<body class="auth-page">
    <div class="auth-card">
        <div class="brand-mark">◈ GlobeTrek</div>
        <div class="small text-secondary mb-4">Management Portal</div>
        <h1 class="fw-bold">Check your inbox.</h1>
        <p class="text-secondary">Enter the 6-digit code sent to <strong><?= e(
            $state['email'],
        ) ?></strong>.</p><?php
         flashes();
         if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif;
         ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="verify"><label
                class="form-label">Verification code</label><input class="form-control otp-input mb-4" type="text"
                inputmode="numeric" pattern="[0-9]{6}" maxlength="6" name="otp" required autocomplete="one-time-code"
                autofocus><button class="btn btn-primary w-100">Verify code</button></form>
        <form method="post" class="text-center mt-3"><?= csrf_field() ?><input type="hidden" name="action"
                value="resend"><button class="btn btn-link">Resend code</button></form>
        <div class="text-center"><a href="forgot_password.php">Use a different email</a></div>
    </div>
</body>

</html>