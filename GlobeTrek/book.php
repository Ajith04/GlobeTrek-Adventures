<?php
require_once 'config.php';
require_role('customer', 'login.php');
verify_csrf();
$portal = 'customer';
$id = (int) ($_GET['id'] ?? 0);
$s = db()->prepare("SELECT * FROM tour_packages WHERE id=? AND status='active'");
$s->execute([$id]);
$p = $s->fetch();
if (!$p) {
    flash('danger', 'That journey is no longer available.');
    go(app_url('packages.php'));
}
$accommodations = db()
    ->query("SELECT * FROM accommodations WHERE status='active' ORDER BY price_per_night")
    ->fetchAll();
$transport = db()->query("SELECT * FROM transportation_services WHERE status='active' ORDER BY price")->fetchAll();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['travel_date'] ?? '';
    $trav = max(1, (int) ($_POST['travelers'] ?? 1));
    $custom = trim($_POST['custom_plan'] ?? '');
    $aid = (int) ($_POST['accommodation_id'] ?? 0);
    $tid = (int) ($_POST['transportation_id'] ?? 0);
    if (!$date || $date < date('Y-m-d')) {
        $error = 'Please choose a valid future travel date.';
    } else {
        $stay = null;
        $ride = null;
        if ($aid) {
            $q = db()->prepare("SELECT * FROM accommodations WHERE id=? AND status='active'");
            $q->execute([$aid]);
            $stay = $q->fetch();
            if (!$stay) {
                $error = 'Selected accommodation is unavailable.';
            }
        }
        if ($tid && !$error) {
            $q = db()->prepare("SELECT * FROM transportation_services WHERE id=? AND status='active'");
            $q->execute([$tid]);
            $ride = $q->fetch();
            if (!$ride) {
                $error = 'Selected transportation is unavailable.';
            }
        }
        if (!$error) {
            $total =
                (float) $p['price'] * $trav +
                ($stay ? (float) $stay['price_per_night'] : 0) * (int) $p['duration_days'] +
                ($ride ? (float) $ride['price'] : 0);
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $q = $pdo->prepare(
                    "INSERT INTO bookings(booking_reference,user_id,package_id,travel_date,travelers,custom_plan,total_amount,status,payment_status) VALUES('',?,?,?,?,?,?, 'pending','unpaid')",
                );
                $q->execute(['', user()['id'], $id, $date, $trav, $custom, $total]);
                $bookingId = (int) $pdo->lastInsertId();
                $ref = booking_ref($bookingId);
                $pdo->prepare('UPDATE bookings SET booking_reference=? WHERE id=?')->execute([$ref, $bookingId]);
                $q = $pdo->prepare(
                    'INSERT INTO booking_services(booking_id,accommodation_id,transportation_id,notes) VALUES(?,?,?,?)',
                );
                $q->execute([$bookingId, $aid ?: null, $tid ?: null, $custom]);
                $pdo->commit();
                audit('create', 'booking', (string) $bookingId, ['reference' => $ref]);
                flash('success', 'Booking request submitted. Our travel team will confirm availability.');
                go(app_url('bookings.php'));
            } catch (Throwable $e) {
                $pdo->rollBack();
                $error = 'We could not submit the booking. Please try again.';
            }
        }
    }
}
$title = 'Customize your trip';
include 'includes/header.php';
?>
<div class="row g-5">
    <div class="col-lg-5">
        <div class="sticky-lg-top" style="top:125px"><img src="<?= e(
            $p['image_url'],
        ) ?>" class="w-100 rounded-5 object-cover" style="height:430px" alt="<?= e(
             $p['title'],
         ) ?>" onerror="this.classList.add('image-fallback');this.removeAttribute('src')">
            <div class="mt-4">
                <div class="section-kicker"><?= e(
                    $p['destination'],
                ) ?></div>
                <h2 class="display-serif mt-2"><?= e($p['title']) ?></h2>
                <p class="muted"><?= $p[
                    'duration_days'
                ] ?> days · <?= money($p['price']) ?> per traveler</p>
            </div>
        </div>
    </div>
    <div class="col-lg-6 offset-lg-1">
        <div class="dashboard-card">
            <div class="section-kicker">Your preferences</div>
            <h2 class="fw-bold mt-2">Make it yours.</h2>
            <p class="muted">Add a stay and transport, then tell our team anything else they should know.</p><?php if (
                $error
            ): ?>
                <div class="alert alert-danger"><?= e(
                    $error,
                ) ?></div><?php endif; ?>
            <form method="post" class="mt-4"><?= csrf_field() ?>
                <label class="form-label fw-semibold">Preferred travel date</label><input type="date" name="travel_date"
                    min="<?= date(
                        'Y-m-d',
                    ) ?>" class="form-control mb-4" required>
                <label class="form-label fw-semibold">Travelers</label><input id="travellers" type="number"
                    name="travelers" min="1" value="2" class="form-control mb-4" required>
                <label class="form-label fw-semibold">Accommodation <span
                        class="muted fw-normal">optional</span></label><select id="stay" name="accommodation_id"
                    class="form-select mb-4">
                    <option value="0" data-price="0">No accommodation needed</option><?php foreach (
                        $accommodations
                        as $a
                    ): ?>
                        <option value="<?= $a['id'] ?>" data-price="<?= $a['price_per_night'] ?>"><?= e($a['name']) ?> · <?= money(
                                   $a['price_per_night'],
                               ) ?>/night</option><?php endforeach; ?>
                </select>
                <label class="form-label fw-semibold">Transportation <span
                        class="muted fw-normal">optional</span></label><select id="ride" name="transportation_id"
                    class="form-select mb-4">
                    <option value="0" data-price="0">No transport needed</option><?php foreach (
                        $transport
                        as $t
                    ): ?>
                        <option value="<?= $t['id'] ?>" data-price="<?= $t['price'] ?>"><?= e($t['provider_name']) ?> — <?= e(
                                   $t['service_type'],
                               ) ?> · <?= money($t['price']) ?></option><?php endforeach; ?>
                </select>
                <label class="form-label fw-semibold">Special requests</label><textarea name="custom_plan"
                    class="form-control mb-4" rows="5"
                    placeholder="Airport pickup, dietary needs, room preferences, activities..."></textarea>
                <div class="quote-box mb-4">
                    <div class="muted small">Estimated trip value</div>
                    <div id="estimate" class="fs-3 fw-bold"></div>
                    <div class="muted small">Final amount is confirmed after service availability.</div>
                </div>
                <button class="btn btn-primary btn-lg rounded-pill w-100">Send booking request <i
                        class="bi bi-arrow-right ms-2"></i></button>
            </form>
        </div>
    </div>
</div>
<script>
    const base = <?= json_encode((float) $p['price']) ?>, days = <?= json_encode((int) $p['duration_days']) ?>;
    function estimate() { const n = Math.max(1, parseInt(document.getElementById('travellers').value || 1)); const stay = Number(document.querySelector('#stay option:checked').dataset.price || 0); const ride = Number(document.querySelector('#ride option:checked').dataset.price || 0); document.getElementById('estimate').textContent = 'LKR ' + Math.round(base * n + stay * days + ride).toLocaleString(); } ['travellers', 'stay', 'ride'].forEach(id => document.getElementById(id).addEventListener('input', estimate)); estimate();
</script>
<?php include 'includes/footer.php'; ?>