<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/management_auth.php';
require_management();
verify_csrf();
$title = 'Booking Operations';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'pending';
    $payment = $_POST['payment_status'] ?? 'unpaid';
    $staff = (int) ($_POST['assigned_staff_id'] ?? 0);
    $notes = trim($_POST['internal_notes'] ?? '');
    $q = db()->prepare(
        "UPDATE bookings SET status=?,payment_status=?,assigned_staff_id=?,internal_notes=?,confirmed_at=IF(?='confirmed' AND confirmed_at IS NULL,NOW(),confirmed_at) WHERE id=?",
    );
    $q->execute([$status, $payment, $staff ?: null, $notes, $status, $id]);
    $q = db()->prepare(
        'UPDATE booking_services SET accommodation_status=?,transportation_status=?,supplier_notes=? WHERE booking_id=?',
    );
    $q->execute([
        $_POST['accommodation_status'] ?? 'pending',
        $_POST['transportation_status'] ?? 'pending',
        trim($_POST['supplier_notes'] ?? ''),
        $id,
    ]);
    audit('update', 'booking', (string) $id, ['status' => $status, 'payment' => $payment]);
    flash('success', 'Booking workflow updated.');
    go(app_url('bookings.php'));
}
$staff = db()
    ->query("SELECT id,full_name FROM users WHERE role='staff' AND status='active' ORDER BY full_name")
    ->fetchAll();
$rows = db()
    ->query(
        'SELECT b.*,b.booking_reference,u.full_name,u.email,p.title,p.destination,s.accommodation_status,s.transportation_status,s.supplier_notes,a.name accommodation_name,t.provider_name,t.service_type,st.full_name assigned_name FROM bookings b JOIN users u ON u.id=b.user_id JOIN tour_packages p ON p.id=b.package_id LEFT JOIN booking_services s ON s.booking_id=b.id LEFT JOIN accommodations a ON a.id=s.accommodation_id LEFT JOIN transportation_services t ON t.id=s.transportation_id LEFT JOIN users st ON st.id=b.assigned_staff_id ORDER BY b.created_at DESC',
    )
    ->fetchAll();
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Bookings · GlobeTrek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>" rel="stylesheet">
</head>

<body>
    <?php
    $navActive = 'bookings';
    $navBase = '';
    include __DIR__ . '/includes/sidebar.php';
    ?>
    <div class="ops-main">
        <header class="ops-top"><strong>Booking operations</strong><span class="ops-user"><?= e(
            ucfirst(user()['role']),
        ) ?> · <?= e(
              user()['full_name'],
          ) ?></span></header>
        <main class="ops-content"><?php flashes(); ?>
            <div class="mb-4">
                <div class="section-kicker">Guest operations</div>
                <h1 class="display-serif mt-2">Bookings.</h1>
                <p class="muted">Confirm guests, assign ownership and coordinate suppliers.</p>
            </div>
            <div class="row g-4"><?php
            foreach (
                $rows
                as $r
            ): ?>
                    <div class="col-12">
                        <details class="dashboard-card booking-item p-0 overflow-hidden">
                            <summary class="booking-summary p-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                    <div><span class="pill"><?= e(
                                        $r['booking_reference'],
                                    ) ?></span>
                                        <h4 class="fw-bold mt-2 mb-1"><?= e($r['title']) ?></h4>
                                        <div class="muted"><?= e($r['full_name']) ?> · <?= e(
                                               $r['email'],
                                           ) ?> · <?= e($r['travel_date']) ?> · <?= $r[
                                                    'travelers'
                                                ] ?> traveler(s)</div>
                                    </div>
                                    <div class="text-end"><span class="status <?= e($r['status']) ?>"><?= e(
                                          $r['status'],
                                      ) ?></span>
                                        <div class="price mt-2"><?= money(
                                            $r['total_amount'],
                                        ) ?></div><i class="booking-chevron bi bi-chevron-down ms-3" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </summary>
                            <div class="booking-details border-top p-4">
                                <form method="post" class="row g-3"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r[
                                      'id'
                                  ] ?>">
                                    <div class="col-md-3"><label class="small muted">Booking status</label><select
                                            name="status" class="form-select"><?php foreach (
                                                ['pending', 'confirmed', 'completed', 'cancelled']
                                                as $v
                                            ): ?>
                                                <option <?= $r['status'] === $v
                                                    ? 'selected'
                                                    : '' ?>><?= $v ?></option>
                                            <?php endforeach; ?>
                                        </select></div>
                                    <div class="col-md-3"><label class="small muted">Payment</label><select
                                            name="payment_status" class="form-select"><?php foreach (
                                                ['unpaid', 'pending', 'paid', 'refunded', 'failed']
                                                as $v
                                            ): ?>
                                                <option <?= $r['payment_status'] === $v
                                                    ? 'selected'
                                                    : '' ?>><?= $v ?></option>
                                            <?php endforeach; ?>
                                        </select></div>
                                    <div class="col-md-3"><label class="small muted">Assigned staff</label><select
                                            name="assigned_staff_id" class="form-select">
                                            <option value="0">Unassigned</option><?php foreach (
                                                $staff
                                                as $st
                                            ): ?>
                                                <option value="<?= $st['id'] ?>" <?= $r['assigned_staff_id'] == $st['id'] ? 'selected' : '' ?>><?= e(
                                                              $st['full_name'],
                                                          ) ?></option><?php endforeach; ?>
                                        </select></div>
                                    <div class="col-md-3"><label class="small muted">Accommodation</label><select
                                            name="accommodation_status" class="form-select"><?php foreach (
                                                ['pending', 'requested', 'confirmed', 'cancelled']
                                                as $v
                                            ): ?>
                                                <option <?= $r['accommodation_status'] === $v
                                                    ? 'selected'
                                                    : '' ?>><?= $v ?>
                                                </option><?php endforeach; ?>
                                        </select></div>
                                    <div class="col-md-3"><label class="small muted">Transport</label><select
                                            name="transportation_status" class="form-select"><?php foreach (
                                                ['pending', 'requested', 'confirmed', 'cancelled']
                                                as $v
                                            ): ?>
                                                <option <?= $r['transportation_status'] === $v
                                                    ? 'selected'
                                                    : '' ?>><?= $v ?>
                                                </option><?php endforeach; ?>
                                        </select></div>
                                    <div class="col-md-9">
                                        <div class="small muted mb-1">Requested services</div>
                                        <div><?= e(
                                            $r['accommodation_name'] ?: 'No accommodation',
                                        ) ?> · <?= e(
                                              $r['provider_name'] ? $r['provider_name'] . ' / ' . $r['service_type'] : 'No transport',
                                          ) ?></div>
                                    </div>
                                    <div class="col-md-6"><label class="small muted">Supplier coordination
                                            notes</label><textarea class="form-control" name="supplier_notes" rows="3"><?= e(
                                                $r['supplier_notes'],
                                            ) ?></textarea></div>
                                    <div class="col-md-6"><label class="small muted">Internal notes</label><textarea
                                            class="form-control" name="internal_notes" rows="3"><?= e(
                                                $r['internal_notes'],
                                            ) ?></textarea></div>
                                    <div class="col-12"><button class="btn btn-primary rounded-pill px-4">Save
                                            workflow</button></div>
                                </form>
                            </div>
                        </details>
                    </div><?php endforeach;
            if (!$rows): ?>
                    <div class="dashboard-card text-center">No bookings yet.</div>
                <?php endif;
            ?>
            </div>
        </main>
    </div>
</body>

</html>