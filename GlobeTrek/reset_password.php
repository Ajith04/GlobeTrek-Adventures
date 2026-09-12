<?php
require_once 'config.php';
if (user()) {
    go(portal_url());
}
$state = gt_reset_state();
if (!$state || ($state['purpose'] ?? '') !== 'customer' || empty($state['verified'])) {
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
$title = 'Choose new password';
$portal = 'public';
include 'includes/header.php';
?>
<div class="auth-card">
    <div class="section-kicker">New password</div>
    <h1 class="display-serif mt-2">Secure your account.</h1>
    <p class="muted">Use at least 8 characters with at least one letter and one number.</p>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="mt-4"><?= csrf_field() ?>
        <label class="form-label fw-semibold">New password</label>
        <input class="form-control mb-3" type="password" name="password" minlength="8" required
            autocomplete="new-password">
        <label class="form-label fw-semibold">Confirm new password</label>
        <input class="form-control mb-4" type="password" name="confirm_password" minlength="8" required
            autocomplete="new-password">
        <button class="btn btn-primary btn-lg rounded-pill w-100">Update password</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>