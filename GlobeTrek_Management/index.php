<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/management_auth.php';
require_management();
$role = current_user()['role'];
$pending = db()->query("SELECT COUNT(*) c FROM bookings WHERE status='pending'")->fetch()['c'];
$bookings = db()->query('SELECT COUNT(*) c FROM bookings')->fetch()['c'];
$open = db()->query("SELECT COUNT(*) c FROM inquiries WHERE status='open'")->fetch()['c'];
$packages = db()->query("SELECT COUNT(*) c FROM tour_packages WHERE status='active'")->fetch()['c'];
$sales = db()->query("SELECT COALESCE(SUM(total_amount),0) s FROM bookings WHERE payment_status='paid'")->fetch()['s'];
?><!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>GlobeTrek Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/style.css?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>" rel="stylesheet">
</head>

<body>
    <?php
    $navActive = 'dashboard';
    $navBase = '';
    include __DIR__ . '/includes/sidebar.php';
    ?>
    <div class="ops-main">
        <header class="ops-top"><strong>GlobeTrek Management</strong><span class="ops-user"><?= e(
            ucfirst($role),
        ) ?> · <?= e(current_user()['full_name']) ?></span></header>
        <main class="ops-content"><?php flashes(); ?>
            <div class="mb-4">
                <div class="section-kicker">GlobeTrek Adventures</div>
                <h1 class="display-serif mt-2"><?= $role ===
                    'admin'
                    ? 'Command centre.'
                    : 'Operations dashboard.' ?></h1>
                <p class="muted"><?= $role === 'admin'
                    ? 'Business-wide visibility and administration.'
                    : 'Keep every guest journey moving smoothly.' ?></p>
            </div>
            <div class="row g-4"><?php
            $cards =
                $role === 'admin'
                ? [
                    ['Users', db()->query('SELECT COUNT(*) c FROM users')->fetch()['c']],
                    ['Bookings', $bookings],
                    ['Paid sales', money($sales)],
                    ['Open inquiries', $open],
                ]
                : [
                    ['Pending bookings', $pending],
                    ['Open inquiries', $open],
                    ['Active packages', $packages],
                    ['Total bookings', $bookings],
                ];
            foreach (
                $cards
                as $c
            ): ?>
                    <div class="col-sm-6 col-xl-3">
                        <div class="stat">
                            <div class="muted small"><?= $c[0] ?></div>
                            <div class="number"><?= $c[1] ?></div>
                        </div>
                    </div>
                <?php endforeach;
            ?>
            </div>
            <div class="row g-4 mt-1">
                <div class="col-md-4">
                    <div class="dashboard-card h-100">
                        <h5>Booking operations</h5>
                        <p class="muted">Review and update the customer booking pipeline.</p><a href="bookings.php"
                            class="btn btn-primary rounded-pill">Open bookings</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dashboard-card h-100">
                        <h5>Package catalogue</h5>
                        <p class="muted">Maintain destinations, pricing and activities.</p><a href="packages.php"
                            class="btn btn-outline-dark rounded-pill">Manage packages</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dashboard-card h-100">
                        <h5><?= $role ===
                            'admin'
                            ? 'Reports'
                            : 'Travel services' ?></h5>
                        <p class="muted"><?= $role === 'admin'
                            ? 'Review sales and demand.'
                            : 'Coordinate accommodation and transport.' ?></p><a href="<?= $role === 'admin'
                              ? 'admin/reports.php'
                              : 'services.php' ?>" class="btn btn-outline-dark rounded-pill">Open</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>