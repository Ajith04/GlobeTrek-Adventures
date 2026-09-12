<?php
declare(strict_types=1);

session_name('globetrek_customer');
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
session_start();
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

require_once dirname(__DIR__) . '/shared/password_reset.php';
gt_load_env(dirname(__DIR__) . '/.env');

function envv(string $key, string $default = ''): string
{
    $v = getenv($key);
    return $v === false ? $default : $v;
}
const DB_HOST = 'localhost';
const DB_NAME = 'globetrek_adventures';
const DB_USER = 'root';
const DB_PASS = '';
function app_base(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $root = realpath(__DIR__);
    $doc = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $base = $root && $doc && str_starts_with($root, $doc) ? str_replace('\\', '/', substr($root, strlen($doc))) : '';
    return rtrim($base, '/');
}
function app_url(string $path = ''): string
{
    return app_base() . ($path !== '' ? '/' . ltrim($path, '/') : '/');
}
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $host = envv('DB_HOST', DB_HOST);
        $name = envv('DB_NAME', DB_NAME);
        $user = envv('DB_USER', DB_USER);
        $pass = envv('DB_PASS', DB_PASS);
        $pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}
function e($v): string
{
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
}
function money($v): string
{
    return 'LKR ' . number_format((float) $v, 2);
}
function user(): ?array
{
    return $_SESSION['user'] ?? null;
}
function login_user(array $u): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = $u;
}
function logout_user(): void
{
    unset($_SESSION['user']);
}
function go(string $url): never
{
    header('Location: ' . $url);
    exit();
}
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
function flashes(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    echo '<div class="alert alert-' .
        e($f['type']) .
        ' alert-dismissible fade show shadow-sm">' .
        e($f['message']) .
        '<button class="btn-close" data-bs-dismiss="alert"></button></div>';
}
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Invalid form token.');
    }
}
function require_role(string $role, string $login = 'login.php'): void
{
    if (user() && user()['role'] === $role) {
        return;
    }
    if (!user() && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $base = app_base();
        if ($uri !== '' && ($base === '' || str_starts_with(parse_url($uri, PHP_URL_PATH) ?? '', $base . '/'))) {
            $_SESSION['login_redirect'] = $uri;
        }
    }
    go(app_url($login));
}
function portal_url(): string
{
    return app_url('index.php');
}
function status_class(string $s): string
{
    return preg_replace('/[^a-z0-9_-]/i', '', strtolower($s));
}
function booking_ref(int $id): string
{
    return 'GT-' . date('ym') . '-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
}
function audit(string $action, string $type, ?string $id = null, array $details = []): void
{
    try {
        $q = db()->prepare(
            'INSERT INTO audit_logs(user_id,action,entity_type,entity_id,details,ip_address) VALUES(?,?,?,?,?,?)',
        );
        $q->execute([
            user()['id'] ?? null,
            $action,
            $type,
            $id,
            $details ? json_encode($details) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable $e) {
    }
}
function split_name(string $name): array
{
    $parts = preg_split('/\s+/', $name, 2);
    return [$parts[0] ?? 'Guest', $parts[1] ?? ''];
}
function payhere_enabled(): bool
{
    return filter_var(envv('PAYHERE_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN);
}
function payhere_config(): array
{
    return [
        'merchant_id' => envv('PAYHERE_MERCHANT_ID'),
        'merchant_secret' => envv('PAYHERE_MERCHANT_SECRET'),
        'sandbox' => filter_var(envv('PAYHERE_SANDBOX', 'true'), FILTER_VALIDATE_BOOLEAN),
        'currency' => envv('PAYHERE_CURRENCY', 'LKR'),
    ];
}
function payhere_url(): string
{
    return payhere_config()['sandbox']
        ? 'https://sandbox.payhere.lk/pay/checkout'
        : 'https://www.payhere.lk/pay/checkout';
}
function payhere_hash(string $merchant, string $order, float $amount, string $currency, string $secret): string
{
    return strtoupper(
        md5($merchant . $order . number_format($amount, 2, '.', '') . $currency . strtoupper(md5($secret))),
    );
}
