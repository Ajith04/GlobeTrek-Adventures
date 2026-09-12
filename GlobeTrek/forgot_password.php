<?php
require_once 'config.php';
if (user()) {
    go(portal_url());
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } else {
        try {
            $u = gt_allowed_user_for_reset($email, 'customer');
            if ($u) {
                $result = gt_create_reset_otp($u, 'customer');
                if ($result['ok']) {
                    go('verify_otp.php');
                }
                $error = $result['message'];
            } else {
                // Do not reveal whether a customer account exists.
                flash('success', 'If an active customer account exists for that email, a reset code has been sent.');
                go('login.php');
            }
        } catch (Throwable $e) {
            $error = 'Password reset is temporarily unavailable. Please try again.';
        }
    }
}
$title = 'Forgot password';
$portal = 'public';
include 'includes/header.php';
?>
<div class="auth-card">
    <div class="section-kicker">Account recovery</div>
    <h1 class="display-serif mt-2">Reset your password.</h1>
    <p class="muted">Enter the email used for your GlobeTrek customer account. We’ll send a 6-digit code valid for 10
        minutes.</p>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="mt-4"><?= csrf_field() ?>
        <label class="form-label fw-semibold">Email address</label>
        <input class="form-control mb-4" type="email" name="email" value="<?= e(
            $_POST['email'] ?? '',
        ) ?>" required autocomplete="email">
        <button class="btn btn-primary btn-lg rounded-pill w-100">Send verification code</button>
    </form>
    <p class="text-center muted mt-4 mb-0"><a href="login.php">Back to sign in</a></p>
</div>
<?php include 'includes/footer.php'; ?>