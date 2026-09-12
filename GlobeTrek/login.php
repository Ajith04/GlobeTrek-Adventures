<?php
require_once 'config.php';
verify_csrf();
if (user()) {
    go(portal_url());
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $q = db()->prepare("SELECT * FROM users WHERE email=? AND role='customer' AND status='active' LIMIT 1");
    $q->execute([$email]);
    $u = $q->fetch();
    if ($u && password_verify($pass, $u['password_hash'])) {
        db()
            ->prepare('UPDATE users SET last_login_at=NOW() WHERE id=?')
            ->execute([$u['id']]);
        login_user($u);
        audit('login', 'user', (string) $u['id']);
        $redirect = $_SESSION['login_redirect'] ?? portal_url();
        unset($_SESSION['login_redirect']);
        go($redirect);
    }
    $error = 'Invalid email or password.';
}
$title = 'Customer sign in';
$portal = 'public';
include 'includes/header.php';
?>
<div class="auth-card">
    <div class="section-kicker">GlobeTrek Adventures</div>
    <h1 class="display-serif mt-2">Welcome back.</h1>
    <p class="muted">Sign in to manage your journeys and payments.</p><?php
    flashes();
    if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif;
    ?>
    <form method="post" class="mt-4"><?= csrf_field() ?><label class="form-label fw-semibold">Email</label><input
            class="form-control mb-3" type="email" name="email" required autocomplete="email">
        <div class="d-flex justify-content-between align-items-center"><label
                class="form-label fw-semibold">Password</label><a class="small" href="forgot_password.php">Forgot
                password?</a></div><input class="form-control mb-4" type="password" name="password" required
            autocomplete="current-password"><button class="btn btn-primary btn-lg rounded-pill w-100">Sign in</button>
    </form>
    <p class="text-center muted mt-4 mb-0">New here? <a href="register.php">Create an account</a></p>
</div>
<?php include 'includes/footer.php'; ?>