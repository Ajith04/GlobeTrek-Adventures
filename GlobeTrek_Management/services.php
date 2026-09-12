<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/management_auth.php';
require_management();
$portal = 'staff';
$title = 'Travel Services';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $type = $_POST['type'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    if ($type === 'stay') {
        $q = db()->prepare(
            'UPDATE accommodations SET name=?,location=?,price_per_night=?,description=?,status=? WHERE id=?',
        );
        $q->execute([
            trim($_POST['name']),
            trim($_POST['location']),
            floatval($_POST['price']),
            trim($_POST['description']),
            $status,
            $id,
        ]);
    }
    if ($type === 'ride') {
        $q = db()->prepare(
            'UPDATE transportation_services SET provider_name=?,service_type=?,price=?,description=?,status=? WHERE id=?',
        );
        $q->execute([
            trim($_POST['provider']),
            trim($_POST['service_type']),
            floatval($_POST['price']),
            trim($_POST['description']),
            $status,
            $id,
        ]);
    }
    flash('success', 'Travel service updated.');
    go(app_url('services.php'));
}
$stays = db()->query('SELECT * FROM accommodations ORDER BY created_at DESC')->fetchAll();
$rides = db()->query('SELECT * FROM transportation_services ORDER BY created_at DESC')->fetchAll();
?><!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e(
        $title ?? 'GlobeTrek Management',
    ) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/style.css?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>" rel="stylesheet">
</head>

<body>
    <?php
    $navActive = 'services';
    $navBase = '';
    include __DIR__ . '/includes/sidebar.php';
    ?>
    <div class="ops-main">
        <header class="ops-top"><button class="btn btn-light mobile-menu"
                onclick="document.querySelector('.ops-nav').classList.toggle('open')"><i
                    class="bi bi-list"></i></button><strong>Travel Operations</strong><span class="ops-user"><?= e(
                        ucfirst(current_user()['role']),
                    ) ?> · <?= e(current_user()['full_name']) ?></span></header>
        <main class="ops-content"><?php flashes(); ?>
            <div class="mb-4">
                <div class="section-kicker">Supplier catalogue</div>
                <h1 class="display-serif mt-2">Stays & transport.</h1>
                <p class="muted">Keep the service catalogue aligned with hotel and transport partners.</p>
            </div>
            <div class="row g-4">
                <div class="col-xl-6">
                    <div class="service-category">
                        <h5 class="fw-bold mb-4">Accommodation partners</h5><?php foreach (
                            $stays
                            as $a
                        ): ?>
                            <details class="service-item">
                                <summary class="service-item-summary">
                                    <div><strong><?= e(
                                        $a['name'],
                                    ) ?></strong>
                                        <div class="muted small"><?= e(
                                            $a['location'],
                                        ) ?></div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="text-end"><span class="status <?= e(
                                            $a['status'],
                                        ) ?>"><?= e($a['status']) ?></span>
                                            <div class="muted small mt-1"><?= money(
                                                $a['price_per_night'],
                                            ) ?> / night</div>
                                        </div><i class="service-item-chevron bi bi-chevron-down" aria-hidden="true"></i>
                                    </div>
                                </summary>
                                <div class="service-item-details">
                                    <form method="post" class="service-editor mb-0"><?= csrf_field() ?><input type="hidden"
                                            name="type" value="stay"><input type="hidden" name="id" value="<?= $a[
                                                'id'
                                            ] ?>">
                                        <div class="row g-2">
                                            <div class="col-md-7"><input class="form-control" name="name" value="<?= e(
                                                $a['name'],
                                            ) ?>"></div>
                                            <div class="col-md-5"><input class="form-control" name="location" value="<?= e(
                                                $a['location'],
                                            ) ?>"></div>
                                            <div class="col-md-5"><input class="form-control" type="number" step=".01"
                                                    name="price" value="<?= $a[
                                                        'price_per_night'
                                                    ] ?>"></div>
                                            <div class="col-md-7"><select class="form-select" name="status"><?php foreach (
                                                ['active', 'inactive']
                                                as $v
                                            ): ?>
                                                        <option <?= $a['status'] === $v
                                                            ? 'selected'
                                                            : '' ?>><?= $v ?></option>
                                                    <?php endforeach; ?>
                                                </select></div>
                                            <div class="col-12"><textarea class="form-control" name="description" rows="2"><?= e(
                                                $a['description'],
                                            ) ?></textarea></div>
                                        </div><button class="btn btn-sm btn-primary rounded-pill mt-2">Save stay</button>
                                    </form>
                                </div>
                            </details><?php endforeach; ?>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="service-category">
                        <h5 class="fw-bold mb-4">Transportation partners</h5><?php foreach (
                            $rides
                            as $t
                        ): ?>
                            <details class="service-item">
                                <summary class="service-item-summary">
                                    <div><strong><?= e(
                                        $t['provider_name'],
                                    ) ?></strong>
                                        <div class="muted small"><?= e(
                                            $t['service_type'],
                                        ) ?></div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="text-end"><span class="status <?= e(
                                            $t['status'],
                                        ) ?>"><?= e($t['status']) ?></span>
                                            <div class="muted small mt-1"><?= money(
                                                $t['price'],
                                            ) ?></div>
                                        </div><i class="service-item-chevron bi bi-chevron-down" aria-hidden="true"></i>
                                    </div>
                                </summary>
                                <div class="service-item-details">
                                    <form method="post" class="service-editor mb-0"><?= csrf_field() ?><input type="hidden"
                                            name="type" value="ride"><input type="hidden" name="id" value="<?= $t[
                                                'id'
                                            ] ?>">
                                        <div class="row g-2">
                                            <div class="col-md-6"><input class="form-control" name="provider" value="<?= e(
                                                $t['provider_name'],
                                            ) ?>"></div>
                                            <div class="col-md-6"><input class="form-control" name="service_type" value="<?= e(
                                                $t['service_type'],
                                            ) ?>"></div>
                                            <div class="col-md-5"><input class="form-control" type="number" step=".01"
                                                    name="price" value="<?= $t[
                                                        'price'
                                                    ] ?>"></div>
                                            <div class="col-md-7"><select class="form-select" name="status"><?php foreach (
                                                ['active', 'inactive']
                                                as $v
                                            ): ?>
                                                        <option <?= $t['status'] === $v
                                                            ? 'selected'
                                                            : '' ?>><?= $v ?></option>
                                                    <?php endforeach; ?>
                                                </select></div>
                                            <div class="col-12"><textarea class="form-control" name="description" rows="2"><?= e(
                                                $t['description'],
                                            ) ?></textarea></div>
                                        </div><button class="btn btn-sm btn-primary rounded-pill mt-2">Save
                                            transport</button>
                                    </form>
                                </div>
                            </details><?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php ?>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/bootstrap.bundle.min.js"></script>
</body>

</html>
