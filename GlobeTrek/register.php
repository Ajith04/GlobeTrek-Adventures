<?php
require_once 'config.php';
verify_csrf();
if (user()) {
    go(portal_url());
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pass = $_POST['password'] ?? '';
    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
        $error = 'Please complete all fields. Password must be at least 8 characters.';
    } else {
        try {
            $s = db()->prepare(
                "INSERT INTO users(full_name,email,phone,password_hash,role) VALUES(?,?,?,?,'customer')",
            );
            $s->execute([$name, $email, $phone, password_hash($pass, PASSWORD_DEFAULT)]);
            audit('create', 'user', null, ['role' => 'customer']);
            flash('success', 'Account created. Welcome to GlobeTrek.');
            go(app_url('login.php'));
        } catch (PDOException $e) {
            $error =
                $e->getCode() === '23000'
                ? 'A customer account already exists for that email.'
                : 'Registration could not be completed.';
        }
    }
}
$title = 'Create account';
$portal = 'public';
include 'includes/header.php';
?>
<div class="auth-card">
    <div class="section-kicker">Start exploring</div>
    <h1 class="display-serif fs-1 mt-2">Your next chapter.</h1>
    <p class="muted">Create your free GlobeTrek account and keep every trip in one place.</p><?php if (
        $error
    ): ?>
        <div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="mt-4"><?= csrf_field() ?><label class="form-label fw-semibold">Full name</label><input
            class="form-control mb-3" name="full_name" required><label
            class="form-label fw-semibold">Email</label><input class="form-control mb-3" type="email" name="email"
            required><label class="form-label fw-semibold">Phone</label><input class="form-control mb-3"
            name="phone"><label class="form-label fw-semibold">Password</label><input class="form-control mb-4"
            type="password" name="password" minlength="6" required><button
            class="btn btn-primary btn-lg rounded-pill w-100">Create my account</button></form>
</div>
<?php include 'includes/footer.php'; ?>