<?php
require_once 'config.php';
$portal = 'customer';
$title = 'Stays & Transport';
$stays = db()->query("SELECT * FROM accommodations WHERE status='active' ORDER BY price_per_night")->fetchAll();
$rides = db()->query("SELECT * FROM transportation_services WHERE status='active' ORDER BY price")->fetchAll();
include 'includes/header.php';
?>
<div class="mb-5">
    <div class="section-kicker">Travel essentials</div>
    <h1 class="display-serif mt-2">Stays & transport.</h1>
    <p class="muted">Choose practical, comfortable add-ons when you customize a tour.</p>
</div>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="dashboard-card h-100">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="icon-box"><i class="bi bi-building"></i></div>
                <div>
                    <h4 class="fw-bold mb-0">Accommodation</h4>
                    <div class="muted small">Selected per trip</div>
                </div>
            </div><?php foreach (
                $stays
                as $a
            ): ?>
                <div class="service-row">
                    <div><strong><?= e($a['name']) ?></strong>
                        <div class="muted small"><?= e(
                            $a['location'],
                        ) ?></div>
                        <div class="muted small mt-1"><?= e($a['description']) ?></div>
                    </div><strong><?= money(
                        $a['price_per_night'],
                    ) ?><span class="muted small">/night</span></strong>
                </div><?php endforeach; ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="dashboard-card h-100">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="icon-box"><i class="bi bi-car-front"></i></div>
                <div>
                    <h4 class="fw-bold mb-0">Transportation</h4>
                    <div class="muted small">Private & coordinated services</div>
                </div>
            </div><?php foreach (
                $rides
                as $t
            ): ?>
                <div class="service-row">
                    <div><strong><?= e($t['provider_name']) ?></strong>
                        <div class="muted small"><?= e(
                            $t['service_type'],
                        ) ?></div>
                        <div class="muted small mt-1"><?= e($t['description']) ?></div>
                    </div><strong><?= money(
                        $t['price'],
                    ) ?></strong>
                </div><?php endforeach; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>