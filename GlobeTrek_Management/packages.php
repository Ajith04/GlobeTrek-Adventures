<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/management_auth.php';
require_management();
verify_csrf();
$title = 'Package Management';
$portal = 'staff';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    if ($action === 'delete') {
        $q = db()->prepare('SELECT COUNT(*) FROM bookings WHERE package_id=? AND status IN ("pending","confirmed")');
        $q->execute([$id]);
        if ((int) $q->fetchColumn() > 0) {
            flash('danger', 'A package with active bookings cannot be deleted. Set it inactive instead.');
        } else {
            db()
                ->prepare('DELETE FROM tour_packages WHERE id=?')
                ->execute([$id]);
            audit('delete', 'package', (string) $id);
            flash('success', 'Package deleted.');
        }
    } else {
        $data = [
            trim($_POST['title'] ?? ''),
            trim($_POST['destination'] ?? ''),
            max(1, (int) ($_POST['duration_days'] ?? 1)),
            max(0, (float) ($_POST['price'] ?? 0)),
            trim($_POST['image_url'] ?? ''),
            trim($_POST['short_description'] ?? ''),
            trim($_POST['description'] ?? ''),
            trim($_POST['activities'] ?? ''),
            trim($_POST['inclusions'] ?? ''),
            trim($_POST['exclusions'] ?? ''),
            $_POST['status'] ?? 'draft',
        ];
        if ($data[0] === '' || $data[1] === '') {
            flash('danger', 'Title and destination are required.');
        } elseif ($action === 'create') {
            $q = db()->prepare(
                'INSERT INTO tour_packages(title,destination,duration_days,price,image_url,short_description,description,activities,inclusions,exclusions,status,created_by) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)',
            );
            $q->execute([...$data, user()['id']]);
            audit('create', 'package', (string) db()->lastInsertId());
            flash('success', 'Package created.');
        } elseif ($action === 'update') {
            $q = db()->prepare(
                'UPDATE tour_packages SET title=?,destination=?,duration_days=?,price=?,image_url=?,short_description=?,description=?,activities=?,inclusions=?,exclusions=?,status=? WHERE id=?',
            );
            $q->execute([...$data, $id]);
            audit('update', 'package', (string) $id);
            flash('success', 'Package updated.');
        }
    }
    go(app_url('packages.php'));
}
$rows = db()->query('SELECT * FROM tour_packages ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Packages · GlobeTrek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>" rel="stylesheet">
</head>

<body>
    <?php
    $navActive = 'packages';
    $navBase = '';
    include __DIR__ . '/includes/sidebar.php';
    ?>
    <div class="ops-main">
        <header class="ops-top"><strong>Package catalogue</strong><span class="ops-user"><?= e(
            ucfirst(user()['role']),
        ) ?> · <?= e(
              user()['full_name'],
          ) ?></span></header>
        <main class="ops-content"><?php flashes(); ?>
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <div class="section-kicker">Product catalogue</div>
                    <h1 class="display-serif mt-2">Tour packages.</h1>
                    <p class="muted">Create, edit, publish and retire experiences.</p>
                </div><button class="btn btn-primary rounded-pill" data-bs-toggle="collapse"
                    data-bs-target="#newPackage">+ New package</button>
            </div>
            <div class="collapse mb-4" id="newPackage">
                <div class="dashboard-card">
                    <h5 class="fw-bold">Create package</h5>
                    <form method="post" class="row g-3 mt-1"><?= csrf_field() ?><input type="hidden" name="action"
                            value="create">
                        <?php
                        $pkg = [];
                        include __DIR__ . '/package_form_fields.php';
                        ?>
                        <div class="col-12"><button class="btn btn-primary rounded-pill">Create package</button></div>
                    </form>
                </div>
            </div>
            <div class="row g-4"><?php foreach (
                $rows
                as $r
            ): ?>
                    <div class="col-12">
                        <details class="dashboard-card package-editor-item p-0 overflow-hidden">
                            <summary class="package-editor-summary p-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                    <div><span class="pill"><?= e(
                                        $r['destination'],
                                    ) ?></span>
                                        <h5 class="fw-bold mt-2"><?= e(
                                            $r['title'],
                                        ) ?></h5>
                                    </div>
                                    <div class="d-flex align-items-center gap-4">
                                        <div class="text-end"><span class="status <?= e(
                                            $r['status'],
                                        ) ?>"><?= e($r['status']) ?></span>
                                            <div class="muted small mt-2"><?= $r['duration_days'] ?> days · <?= money(
                                                   $r['price'],
                                               ) ?></div>
                                        </div><i class="package-editor-chevron bi bi-chevron-down" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </summary>
                            <div class="package-editor-details border-top p-4">
                                <form method="post" class="row g-2"><?= csrf_field() ?><input type="hidden" name="action"
                                        value="update"><input type="hidden" name="id" value="<?= $r[
                                            'id'
                                        ] ?>">
                                    <?php
                                    $pkg = $r;
                                    include __DIR__ . '/package_form_fields.php';
                                    ?>
                                    <div class="col-12 d-flex gap-2"><button class="btn btn-primary rounded-pill">Save
                                            changes</button></div>
                                </form>
                                <form method="post" class="mt-2" onsubmit="return confirm('Delete this package?')">
                                    <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input
                                        type="hidden" name="id" value="<?= $r[
                                            'id'
                                        ] ?>"><button class="btn btn-sm btn-outline-danger rounded-pill">Delete</button></form>
                            </div>
                        </details>
                    </div><?php endforeach; ?>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>