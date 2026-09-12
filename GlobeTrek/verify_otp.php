<?php
require_once 'config.php';
if (user()) {
    go(portal_url());
}
$state = gt_reset_state();
if (!$state || ($state['purpose'] ?? '') !== 'customer') {
    flash('warning', 'Request a password reset code first.');
    go('forgot_password.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (($_POST['action'] ?? 'verify') === 'resend') {
        $u = gt_allowed_user_for_reset((string) $state['email'], 'customer');
        if (!$u) {
            gt_clear_reset_state();
            flash('warning', 'Your account is unavailable.');
            go('forgot_password.php');
        }
        $r = gt_create_reset_otp($u, 'customer');
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
$title = 'Verify reset code';
$portal = 'public';
include 'includes/header.php';
?>
<div class="auth-card">
    <div class="section-kicker">Verify email</div>
    <h1 class="display-serif mt-2">Check your inbox.</h1>
    <p class="muted">Enter the 6-digit code sent to <strong><?= e(
        $state['email'],
    ) ?></strong>. The code expires in 10 minutes.</p>
    <?php
    flashes();
    if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div><?php endif;
    ?>
    <form method="post" class="mt-4"><?= csrf_field() ?>
        <input type="hidden" name="action" value="verify">
        <label class="form-label fw-semibold">Verification code</label>
        <input class="form-control otp-input mb-4" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
            name="otp" required autocomplete="one-time-code" autofocus>
        <button class="btn btn-primary btn-lg rounded-pill w-100">Verify code</button>
    </form>
    <form method="post" class="text-center mt-3"><?= csrf_field() ?><input type="hidden" name="action"
            value="resend"><button class="btn btn-link">Resend code</button></form>
    <p class="text-center muted mb-0"><a href="forgot_password.php">Use a different email</a></p>
</div>
<?php include 'includes/footer.php'; ?>