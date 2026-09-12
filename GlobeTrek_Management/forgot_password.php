<?php
require_once __DIR__ . '/config.php';
if (current_user() && in_array(current_user()['role'] ?? '', ['staff', 'admin'], true)) {
    go('index.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid work email address.';
    } else {
        try {
            $u = gt_allowed_user_for_reset($email, 'management');
            if ($u) {
                $r = gt_create_reset_otp($u, 'management');
                if ($r['ok']) {
                    go('verify_otp.php');
                }
                $error = $r['message'];
            } else {
                flash(
                    'success',
                    'If an active staff or administrator account exists for that email, a reset code has been sent.',
                );
                go('login.php');
            }
        } catch (Throwable $e) {
            $error = 'Password reset is temporarily unavailable. Please try again.';
        }
    }
}
?><!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>GlobeTrek Management · Forgot password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>" rel="stylesheet">
</head>

<body class="auth-page">
    <div class="auth-card">
        <div class="brand-mark">◈ GlobeTrek</div>
        <div class="small text-secondary mb-4">Management Portal</div>
        <h1 class="fw-bold">Reset password.</h1>
        <p class="text-secondary">Enter your staff or administrator work email. We’ll send a 6-digit code valid for 10
            minutes.</p>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post"><?= csrf_field() ?><label class="form-label">Work email</label><input
                class="form-control mb-4" type="email" name="email" value="<?= e(
                    $_POST['email'] ?? '',
                ) ?>" required autocomplete="email"><button class="btn btn-primary w-100">Send verification code</button></form>
        <div class="text-center mt-4"><a href="login.php">Back to sign in</a></div>
    </div>
</body>

</html>