<?php
declare(strict_types=1);

function gt_load_env(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key === '' || getenv($key) !== false) {
            continue;
        }
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

function gt_password_reset_ensure_schema(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    db()->exec("CREATE TABLE IF NOT EXISTS password_reset_otps (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        purpose ENUM('customer','management') NOT NULL,
        otp_hash VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        verified_at DATETIME NULL,
        used_at DATETIME NULL,
        attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
        request_ip VARCHAR(45) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_password_reset_user_purpose (user_id, purpose, created_at),
        INDEX idx_password_reset_expiry (expires_at),
        CONSTRAINT fk_password_reset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $done = true;
}

function gt_reset_session_key(): string
{
    return 'gt_password_reset';
}

function gt_reset_state(): ?array
{
    $v = $_SESSION[gt_reset_session_key()] ?? null;
    return is_array($v) ? $v : null;
}

function gt_set_reset_state(array $state): void
{
    $_SESSION[gt_reset_session_key()] = $state;
}

function gt_clear_reset_state(): void
{
    unset($_SESSION[gt_reset_session_key()]);
}

function gt_allowed_user_for_reset(string $email, string $purpose): ?array
{
    $email = mb_strtolower(trim($email));
    if ($purpose === 'customer') {
        $sql =
            "SELECT id, full_name, email, role FROM users WHERE LOWER(email)=? AND role='customer' AND status='active' LIMIT 1";
    } else {
        $sql =
            "SELECT id, full_name, email, role FROM users WHERE LOWER(email)=? AND role IN ('staff','admin') AND status='active' LIMIT 1";
    }
    $q = db()->prepare($sql);
    $q->execute([$email]);
    $row = $q->fetch();
    return $row ?: null;
}

function gt_recent_reset_wait_seconds(int $userId, string $purpose): int
{
    gt_password_reset_ensure_schema();
    $q = db()->prepare(
        'SELECT created_at FROM password_reset_otps WHERE user_id=? AND purpose=? ORDER BY id DESC LIMIT 1',
    );
    $q->execute([$userId, $purpose]);
    $created = $q->fetchColumn();
    if (!$created) {
        return 0;
    }
    $elapsed = time() - strtotime((string) $created);
    return max(0, 60 - $elapsed);
}

function gt_create_reset_otp(array $user, string $purpose): array
{
    gt_password_reset_ensure_schema();
    $wait = gt_recent_reset_wait_seconds((int) $user['id'], $purpose);
    if ($wait > 0) {
        return ['ok' => false, 'wait' => $wait, 'message' => 'Please wait before requesting another code.'];
    }

    $otp = (string) random_int(100000, 999999);
    $hash = password_hash($otp, PASSWORD_DEFAULT);
    $q = db()->prepare(
        'INSERT INTO password_reset_otps(user_id,purpose,otp_hash,expires_at,request_ip) VALUES(?,?,?,DATE_ADD(NOW(), INTERVAL 10 MINUTE),?)',
    );
    $q->execute([(int) $user['id'], $purpose, $hash, $_SERVER['REMOTE_ADDR'] ?? null]);
    $otpId = (int) db()->lastInsertId();

    $subject = 'Your GlobeTrek password reset code';
    $name = trim((string) ($user['full_name'] ?? 'GlobeTrek user'));
    $portalName = $purpose === 'management' ? 'Management Portal' : 'Customer Portal';
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeOtp = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');
    $html =
        '<!doctype html><html><body style="margin:0;background:#f4f7f5;font-family:Arial,sans-serif;color:#17312f">' .
        '<div style="max-width:560px;margin:36px auto;background:#fff;border:1px solid #e1e8e5;border-radius:18px;padding:34px">' .
        '<div style="font-size:22px;font-weight:700;color:#0e665f">GlobeTrek Adventures</div>' .
        '<h2 style="margin:26px 0 10px">Password reset code</h2>' .
        '<p>Hello ' .
        $safeName .
        ',</p><p>Use this one-time code to reset your ' .
        htmlspecialchars($portalName, ENT_QUOTES, 'UTF-8') .
        ' password:</p>' .
        '<div style="font-size:34px;letter-spacing:8px;font-weight:800;background:#edf6f3;border-radius:14px;padding:18px;text-align:center;margin:24px 0">' .
        $safeOtp .
        '</div>' .
        '<p>This code expires in <strong>10 minutes</strong>. If you did not request a reset, you can ignore this email.</p>' .
        '<p style="color:#6b7d78;font-size:13px;margin-top:28px">For your security, never share this code with anyone.</p>' .
        '</div></body></html>';

    $sent = gt_send_email((string) $user['email'], $name, $subject, $html);
    if (!$sent['ok']) {
        db()
            ->prepare('DELETE FROM password_reset_otps WHERE id=?')
            ->execute([$otpId]);
        return ['ok' => false, 'wait' => 0, 'message' => $sent['error'] ?: 'Unable to send the reset email.'];
    }

    gt_set_reset_state([
        'user_id' => (int) $user['id'],
        'email' => (string) $user['email'],
        'purpose' => $purpose,
        'otp_id' => $otpId,
        'verified' => false,
        'started_at' => time(),
    ]);
    return ['ok' => true, 'wait' => 0, 'message' => ''];
}

function gt_verify_reset_otp(string $otp): array
{
    gt_password_reset_ensure_schema();
    $state = gt_reset_state();
    if (!$state || empty($state['otp_id']) || empty($state['user_id']) || empty($state['purpose'])) {
        return ['ok' => false, 'message' => 'Your reset session has expired. Please request a new code.'];
    }
    if (!preg_match('/^\d{6}$/', $otp)) {
        return ['ok' => false, 'message' => 'Enter the 6-digit code from your email.'];
    }

    $q = db()->prepare(
        'SELECT * FROM password_reset_otps WHERE id=? AND user_id=? AND purpose=? AND used_at IS NULL LIMIT 1',
    );
    $q->execute([(int) $state['otp_id'], (int) $state['user_id'], (string) $state['purpose']]);
    $row = $q->fetch();
    if (!$row) {
        return ['ok' => false, 'message' => 'This reset request is no longer valid. Please request a new code.'];
    }
    if (strtotime((string) $row['expires_at']) < time()) {
        return ['ok' => false, 'message' => 'This code has expired. Please request a new code.'];
    }
    if ((int) $row['attempts'] >= 5) {
        return ['ok' => false, 'message' => 'Too many incorrect attempts. Please request a new code.'];
    }

    if (!password_verify($otp, (string) $row['otp_hash'])) {
        db()
            ->prepare('UPDATE password_reset_otps SET attempts=attempts+1 WHERE id=?')
            ->execute([(int) $row['id']]);
        $remaining = max(0, 4 - (int) $row['attempts']);
        return [
            'ok' => false,
            'message' =>
                $remaining > 0
                ? 'Incorrect code. ' . $remaining . ' attempt' . ($remaining === 1 ? '' : 's') . ' remaining.'
                : 'Too many incorrect attempts. Please request a new code.',
        ];
    }

    db()
        ->prepare('UPDATE password_reset_otps SET verified_at=NOW() WHERE id=?')
        ->execute([(int) $row['id']]);
    $state['verified'] = true;
    gt_set_reset_state($state);
    return ['ok' => true, 'message' => ''];
}

function gt_reset_password(string $password, string $confirm): array
{
    gt_password_reset_ensure_schema();
    $state = gt_reset_state();
    if (!$state || empty($state['verified']) || empty($state['otp_id']) || empty($state['user_id'])) {
        return ['ok' => false, 'message' => 'Verify your email code before setting a new password.'];
    }
    if ($password !== $confirm) {
        return ['ok' => false, 'message' => 'The passwords do not match.'];
    }
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        return ['ok' => false, 'message' => 'Use at least 8 characters with at least one letter and one number.'];
    }

    $q = db()->prepare(
        'SELECT id FROM password_reset_otps WHERE id=? AND user_id=? AND verified_at IS NOT NULL AND used_at IS NULL AND expires_at>=NOW() LIMIT 1',
    );
    $q->execute([(int) $state['otp_id'], (int) $state['user_id']]);
    if (!$q->fetchColumn()) {
        return ['ok' => false, 'message' => 'Your verified reset request has expired. Please start again.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    db()->beginTransaction();
    try {
        db()
            ->prepare('UPDATE users SET password_hash=? WHERE id=?')
            ->execute([$hash, (int) $state['user_id']]);
        db()
            ->prepare('UPDATE password_reset_otps SET used_at=NOW() WHERE id=?')
            ->execute([(int) $state['otp_id']]);
        db()
            ->prepare('DELETE FROM password_reset_otps WHERE user_id=? AND id<>?')
            ->execute([(int) $state['user_id'], (int) $state['otp_id']]);
        db()
            ->prepare(
                'INSERT INTO audit_logs(user_id,action,entity_type,entity_id,details,ip_address) VALUES(?,?,?,?,?,?)',
            )
            ->execute([
                (int) $state['user_id'],
                'password_reset',
                'user',
                (string) $state['user_id'],
                json_encode(['purpose' => $state['purpose'] ?? '']),
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        db()->commit();
    } catch (Throwable $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        return ['ok' => false, 'message' => 'Unable to update your password right now. Please try again.'];
    }
    gt_clear_reset_state();
    return ['ok' => true, 'message' => ''];
}

function gt_mail_config(): array
{
    return [
        'driver' => strtolower(envv('MAIL_DRIVER', 'smtp')),
        'host' => envv('MAIL_HOST', ''),
        'port' => (int) envv('MAIL_PORT', '587'),
        'encryption' => strtolower(envv('MAIL_ENCRYPTION', 'tls')),
        'username' => envv('MAIL_USERNAME', ''),
        'password' => envv('MAIL_PASSWORD', ''),
        'from_address' => envv('MAIL_FROM_ADDRESS', envv('MAIL_USERNAME', '')),
        'from_name' => envv('MAIL_FROM_NAME', 'GlobeTrek Adventures'),
    ];
}

function gt_send_email(string $toEmail, string $toName, string $subject, string $html): array
{
    $cfg = gt_mail_config();
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Invalid email address.'];
    }

    if ($cfg['driver'] === 'log') {
        $dir = dirname(__DIR__) . '/storage';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $line =
            "\n--- " .
            date('c') .
            " ---\nTO: {$toEmail}\nSUBJECT: {$subject}\n" .
            strip_tags(str_replace(['</p>', '<br>', '<br/>', '<br />'], "\n", $html)) .
            "\n";
        $ok = @file_put_contents($dir . '/mail.log', $line, FILE_APPEND | LOCK_EX) !== false;
        return ['ok' => $ok, 'error' => $ok ? '' : 'Unable to write the local mail log.'];
    }

    if ($cfg['driver'] === 'mail') {
        if ($cfg['from_address'] === '') {
            return ['ok' => false, 'error' => 'MAIL_FROM_ADDRESS is not configured.'];
        }
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $cfg['from_name'] . ' <' . $cfg['from_address'] . '>',
        ];
        $ok = @mail($toEmail, $subject, $html, implode("\r\n", $headers));
        return [
            'ok' => $ok,
            'error' => $ok ? '' : 'PHP mail() could not send the message. Configure SMTP in .env for local WAMP.',
        ];
    }

    if ($cfg['driver'] !== 'smtp') {
        return ['ok' => false, 'error' => 'Unsupported MAIL_DRIVER. Use smtp, mail, or log.'];
    }
    if ($cfg['host'] === '' || $cfg['username'] === '' || $cfg['password'] === '' || $cfg['from_address'] === '') {
        return [
            'ok' => false,
            'error' =>
                'Email SMTP is not configured yet. Add MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD and MAIL_FROM_ADDRESS to the project .env file.',
        ];
    }
    return gt_smtp_send($cfg, $toEmail, $toName, $subject, $html);
}

function gt_smtp_read($socket): string
{
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (strlen($line) < 4 || $line[3] === ' ') {
            break;
        }
    }
    return $response;
}

function gt_smtp_expect($socket, array $codes): array
{
    $response = gt_smtp_read($socket);
    $code = (int) substr($response, 0, 3);
    return ['ok' => in_array($code, $codes, true), 'response' => $response, 'code' => $code];
}

function gt_smtp_cmd($socket, string $command, array $codes): array
{
    fwrite($socket, $command . "\r\n");
    return gt_smtp_expect($socket, $codes);
}

function gt_smtp_send(array $cfg, string $toEmail, string $toName, string $subject, string $html): array
{
    $host = $cfg['host'];
    $port = $cfg['port'] ?: 587;
    $transport = $cfg['encryption'] === 'ssl' ? 'ssl://' : 'tcp://';
    $errno = 0;
    $errstr = '';
    $socket = @stream_socket_client($transport . $host . ':' . $port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
    if (!$socket) {
        return ['ok' => false, 'error' => 'Could not connect to the configured SMTP server.'];
    }
    stream_set_timeout($socket, 15);
    try {
        $r = gt_smtp_expect($socket, [220]);
        if (!$r['ok']) {
            throw new RuntimeException('SMTP greeting failed.');
        }
        $helo = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $r = gt_smtp_cmd($socket, 'EHLO ' . preg_replace('/[^A-Za-z0-9.\-]/', '', $helo), [250]);
        if (!$r['ok']) {
            throw new RuntimeException('SMTP EHLO failed.');
        }
        if ($cfg['encryption'] === 'tls') {
            $r = gt_smtp_cmd($socket, 'STARTTLS', [220]);
            if (!$r['ok']) {
                throw new RuntimeException('SMTP STARTTLS failed.');
            }
            $crypto = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if ($crypto !== true) {
                throw new RuntimeException('Unable to establish TLS with SMTP server.');
            }
            $r = gt_smtp_cmd($socket, 'EHLO ' . preg_replace('/[^A-Za-z0-9.\-]/', '', $helo), [250]);
            if (!$r['ok']) {
                throw new RuntimeException('SMTP EHLO after TLS failed.');
            }
        }
        $r = gt_smtp_cmd($socket, 'AUTH LOGIN', [334]);
        if (!$r['ok']) {
            throw new RuntimeException('SMTP authentication is not available.');
        }
        $r = gt_smtp_cmd($socket, base64_encode($cfg['username']), [334]);
        if (!$r['ok']) {
            throw new RuntimeException('SMTP username was rejected.');
        }
        $r = gt_smtp_cmd($socket, base64_encode($cfg['password']), [235]);
        if (!$r['ok']) {
            throw new RuntimeException('SMTP password was rejected. Check your app password.');
        }
        $r = gt_smtp_cmd($socket, 'MAIL FROM:<' . $cfg['from_address'] . '>', [250]);
        if (!$r['ok']) {
            throw new RuntimeException('SMTP sender address was rejected.');
        }
        $r = gt_smtp_cmd($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251]);
        if (!$r['ok']) {
            throw new RuntimeException('SMTP recipient address was rejected.');
        }
        $r = gt_smtp_cmd($socket, 'DATA', [354]);
        if (!$r['ok']) {
            throw new RuntimeException('SMTP DATA command failed.');
        }

        $encodeHeader = static fn(string $v): string => '=?UTF-8?B?' . base64_encode($v) . '?=';
        $safeToName = str_replace(["\r", "\n"], '', $toName);
        $safeSubject = str_replace(["\r", "\n"], '', $subject);
        $message =
            'Date: ' .
            date(DATE_RFC2822) .
            "\r\n" .
            'From: ' .
            $encodeHeader($cfg['from_name']) .
            ' <' .
            $cfg['from_address'] .
            ">\r\n" .
            'To: ' .
            ($safeToName !== '' ? $encodeHeader($safeToName) . ' ' : '') .
            '<' .
            $toEmail .
            ">\r\n" .
            'Subject: ' .
            $encodeHeader($safeSubject) .
            "\r\n" .
            "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n" .
            $html;
        $message = preg_replace('/(?m)^\./', '..', $message);
        fwrite($socket, $message . "\r\n.\r\n");
        $r = gt_smtp_expect($socket, [250]);
        if (!$r['ok']) {
            throw new RuntimeException('SMTP server did not accept the message.');
        }
        @gt_smtp_cmd($socket, 'QUIT', [221]);
        fclose($socket);
        return ['ok' => true, 'error' => ''];
    } catch (Throwable $e) {
        @fwrite($socket, "QUIT\r\n");
        @fclose($socket);
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}
