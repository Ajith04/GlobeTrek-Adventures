<?php
require_once 'config.php';
require_role('customer', 'login.php');
$portal = 'customer';
$title = 'My Journeys';
$q = db()->prepare(
    'SELECT b.*,p.title,p.destination,p.image_url,p.duration_days,s.accommodation_status,s.transportation_status,a.name accommodation_name,t.provider_name,t.service_type FROM bookings b JOIN tour_packages p ON p.id=b.package_id LEFT JOIN booking_services s ON s.booking_id=b.id LEFT JOIN accommodations a ON a.id=s.accommodation_id LEFT JOIN transportation_services t ON t.id=s.transportation_id WHERE b.user_id=? ORDER BY b.created_at DESC',
);
$q->execute([user()['id']]);
$rows = $q->fetchAll();
include 'includes/header.php';
?>
<div class="mb-5">
    <div class="section-kicker">Your travel desk</div>
    <h1 class="display-serif mt-2">My journeys.</h1>
    <p class="muted">Track requests, service coordination and payments in one place.</p>
</div>
<?php foreach (
    $rows
    as $r
): ?>
    <div class="dashboard-card mb-3">
        <div class="row align-items-center g-4">
            <div class="col-md-3"><img src="<?= e(
                $r['image_url'],
            ) ?>" class="w-100 rounded-4" style="height:150px;object-fit:cover" alt=""></div>
            <div class="col-md-6"><span class="pill"><?= e(
                $r['booking_reference'],
            ) ?></span>
                <h4 class="fw-bold mt-2"><?= e($r['title']) ?></h4>
                <p class="muted mb-1"><?= e(
                    $r['travel_date'],
                ) ?> · <?= $r['travelers'] ?> traveler(s)</p>
                <p class="muted small mb-1"><?= e(
                    $r['custom_plan'] ?: 'Standard package preferences',
                ) ?></p>
                <div class="small muted"><?= e($r['accommodation_name'] ?: 'No accommodation') ?> · <?= e(
                         $r['provider_name'] ? $r['provider_name'] . ' / ' . $r['service_type'] : 'No transport',
                     ) ?></div>
            </div>
            <div class="col-md-3 text-md-end"><span class="status <?= e($r['status']) ?>"><?= e(
                  ucfirst($r['status']),
              ) ?></span>
                <div class="price mt-3"><?= money($r['total_amount']) ?></div>
                <div class="small muted mb-2">Payment: <?= e(
                    $r['payment_status'],
                ) ?></div><?php if (
                     in_array($r['payment_status'], ['unpaid', 'failed'], true) &&
                     $r['status'] !== 'cancelled'
                 ): ?><a class="btn btn-primary btn-sm rounded-pill" href="payment.php?id=<?= $r[
                      'id'
                  ] ?>">Pay now</a><?php endif; ?>
            </div>
        </div>
    </div><?php endforeach; ?>
<?php
if (
    !$rows
): ?>
    <div class="dashboard-card text-center py-5">
        <h4>No journeys yet.</h4><a href="packages.php" class="btn btn-primary rounded-pill mt-3">Explore tours</a>
    </div><?php endif;
include 'includes/footer.php';
?>