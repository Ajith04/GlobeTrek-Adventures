<?php
require_once 'config.php';
require_role('customer', 'login.php');
verify_csrf();
$id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));
$q = db()->prepare(
    'SELECT b.*,p.title,u.full_name,u.email,u.phone FROM bookings b JOIN tour_packages p ON p.id=b.package_id JOIN users u ON u.id=b.user_id WHERE b.id=? AND b.user_id=?',
);
$q->execute([$id, user()['id']]);
$b = $q->fetch();
if (!$b || $b['status'] === 'cancelled') {
    flash('danger', 'Payment is unavailable for this booking.');
    go(app_url('bookings.php'));
}
if ($b['payment_status'] === 'paid') {
    flash('success', 'This booking is already paid.');
    go(app_url('bookings.php'));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!payhere_enabled()) {
        flash('warning', 'Online payments are not enabled yet. Configure PayHere in the server environment.');
        go(app_url('bookings.php'));
    }
    $cfg = payhere_config();
    $order = $b['booking_reference'] . '-' . bin2hex(random_bytes(3));
    $amount = (float) $b['total_amount'];
    db()
        ->prepare("INSERT INTO payments(booking_id,order_id,amount,currency,status) VALUES(?,?,?,?, 'pending')")
        ->execute([$b['id'], $order, $amount, $cfg['currency']]);
    [$first, $last] = split_name($b['full_name']);
    $hash = payhere_hash($cfg['merchant_id'], $order, $amount, $cfg['currency'], $cfg['merchant_secret']);
    $return = app_url('payment_return.php?id=' . $b['id']);
    $cancel = app_url('payment_return.php?id=' . $b['id'] . '&cancel=1');
    $notify = app_url('payment_notify.php');
    ?><!doctype html>
    <html>

    <body onload="document.getElementById('payhere').submit()">
        <form id="payhere" method="post" action="<?= e(
            payhere_url(),
        ) ?>"><input type="hidden" name="merchant_id" value="<?= e(
             $cfg['merchant_id'],
         ) ?>"><input type="hidden" name="return_url" value="<?= e(
              $return,
          ) ?>"><input type="hidden" name="cancel_url" value="<?= e(
               $cancel,
           ) ?>"><input type="hidden" name="notify_url" value="<?= e(
                $notify,
            ) ?>"><input type="hidden" name="first_name" value="<?= e(
                 $first,
             ) ?>"><input type="hidden" name="last_name" value="<?= e($last) ?>"><input type="hidden" name="email" value="<?= e(
                    $b['email'],
                ) ?>"><input type="hidden" name="phone" value="<?= e(
                     $b['phone'] ?: '0000000000',
                 ) ?>"><input type="hidden" name="address" value="Negombo"><input type="hidden" name="city" value="Negombo"><input
                type="hidden" name="country" value="Sri Lanka"><input type="hidden" name="order_id" value="<?= e(
                    $order,
                ) ?>"><input type="hidden" name="items" value="<?= e(
                     $b['title'] . ' - ' . $b['booking_reference'],
                 ) ?>"><input type="hidden" name="currency" value="<?= e(
                      $cfg['currency'],
                  ) ?>"><input type="hidden" name="amount" value="<?= number_format(
                       $amount,
                       2,
                       '.',
                       '',
                   ) ?>"><input type="hidden" name="hash" value="<?= e(
                        $hash,
                    ) ?>"></form>
        <p style="font-family:sans-serif;text-align:center;margin-top:20vh">Redirecting to secure payment…</p>
    </body>

    </html>
    <?php exit();
}
$portal = 'customer';
$title = 'Pay for booking';
include 'includes/header.php';
?>
<div class="dashboard-card mx-auto" style="max-width:700px">
    <div class="section-kicker">Secure checkout</div>
    <h1 class="display-serif mt-2">Complete your payment.</h1>
    <p class="muted"><?= e(
        $b['booking_reference'],
    ) ?> · <?= e(
          $b['title'],
      ) ?></p>
    <div class="quote-box my-4">
        <div class="muted small">Amount due</div>
        <div class="display-6 fw-bold"><?= money(
            $b['total_amount'],
        ) ?></div>
        <div class="muted small">Payments are processed securely by PayHere.</div>
    </div><?php if (
        !payhere_enabled()
    ): ?>
        <div class="alert alert-warning">PayHere is currently disabled. Set <code>PAYHERE_ENABLED=true</code> and your
            merchant credentials on the server.</div><?php endif; ?>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $b[
          'id'
      ] ?>"><button class="btn btn-primary btn-lg rounded-pill w-100" <?= payhere_enabled()
           ? ''
           : 'disabled' ?>>Continue to
            secure payment</button></form>
</div><?php include 'includes/footer.php'; ?>