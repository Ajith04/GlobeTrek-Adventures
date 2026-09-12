<?php
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}
$merchant = $_POST['merchant_id'] ?? '';
$order = $_POST['order_id'] ?? '';
$amount = $_POST['payhere_amount'] ?? '';
$currency = $_POST['payhere_currency'] ?? '';
$status = (string) ($_POST['status_code'] ?? '');
$sig = $_POST['md5sig'] ?? '';
$cfg = payhere_config();
$local = strtoupper(md5($merchant . $order . $amount . $currency . $status . strtoupper(md5($cfg['merchant_secret']))));
if (!$cfg['merchant_secret'] || !hash_equals($local, $sig)) {
    http_response_code(400);
    exit('Invalid checksum');
}
$q = db()->prepare('SELECT * FROM payments WHERE order_id=? LIMIT 1');
$q->execute([$order]);
$p = $q->fetch();
if (!$p) {
    http_response_code(404);
    exit('Unknown order');
}
$map = ['2' => 'success', '0' => 'pending', '-1' => 'cancelled', '-2' => 'failed', '-3' => 'refunded'];
$ps = $map[$status] ?? 'failed';
db()->beginTransaction();
try {
    $q = db()->prepare(
        'UPDATE payments SET gateway_payment_id=?,status=?,method=?,raw_status=?,gateway_payload=? WHERE id=?',
    );
    $q->execute([
        $_POST['payment_id'] ?? null,
        $ps,
        $_POST['method'] ?? null,
        $_POST['status_message'] ?? null,
        json_encode($_POST),
        $p['id'],
    ]);
    if ($ps === 'success') {
        db()
            ->prepare("UPDATE bookings SET payment_status='paid' WHERE id=?")
            ->execute([$p['booking_id']]);
    } elseif (in_array($ps, ['failed', 'cancelled'], true)) {
        db()
            ->prepare("UPDATE bookings SET payment_status='failed' WHERE id=? AND payment_status<>'paid'")
            ->execute([$p['booking_id']]);
    }
    db()->commit();
} catch (Throwable $e) {
    db()->rollBack();
    http_response_code(500);
    exit('Update failed');
}
http_response_code(200);
echo 'OK';
