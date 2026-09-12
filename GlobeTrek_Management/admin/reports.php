<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../management_auth.php';
require_management_role('admin');
$portal = 'admin';
$title = 'Reports';
$monthly = db()
    ->query(
        "SELECT DATE_FORMAT(created_at,'%Y-%m') period,COUNT(*) bookings,COALESCE(SUM(total_amount),0) sales FROM bookings GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY period DESC",
    )
    ->fetchAll();
$top = db()
    ->query(
        'SELECT p.title,COUNT(b.id) bookings FROM bookings b JOIN tour_packages p ON p.id=b.package_id GROUP BY p.id ORDER BY bookings DESC LIMIT 5',
    )
    ->fetchAll();
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e(
    $title ?? 'GlobeTrek Administration',
) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="../assets/style.css?v=<?= filemtime(__DIR__ . '/../assets/style.css') ?>" rel="stylesheet"></head><body><?php
$navActive = 'reports';
$navBase = '../';
include __DIR__ . '/../includes/sidebar.php';
?><div class="ops-main"><header class="ops-top"><button class="btn btn-light mobile-menu" onclick="document.querySelector('.ops-nav').classList.toggle('open')"><i class="bi bi-list"></i></button><strong>Administration Console</strong><span class="ops-user">Admin · <?= e(
    user()['full_name'],
) ?></span></header><main class="ops-content"><?php flashes(); ?>
<div class="mb-4"><div class="section-kicker">Business intelligence</div><h1 class="display-serif mt-2">Reports.</h1><p class="muted">A simple operating view of sales and demand.</p></div><div class="row g-4"><div class="col-lg-7"><div class="dashboard-card"><h5 class="fw-bold">Monthly sales</h5><div class="table-responsive mt-3"><table class="table"><thead><tr><th>Month</th><th>Bookings</th><th>Sales</th></tr></thead><tbody><?php foreach (
    $monthly
    as $r
): ?><tr><td><?= e($r['period']) ?></td><td><?= $r['bookings'] ?></td><td><?= money(
    $r['sales'],
) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div><div class="col-lg-5"><div class="dashboard-card"><h5 class="fw-bold">Most booked</h5><?php foreach (
    $top
    as $r
): ?><div class="d-flex justify-content-between align-items-center py-3 border-bottom"><span><?= e(
    $r['title'],
) ?></span><span class="pill"><?= $r['bookings'] ?> bookings</span></div><?php endforeach; ?></div></div></div>
<?php  ?>
</main></div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/bootstrap.bundle.min.js"></script></body></html>
