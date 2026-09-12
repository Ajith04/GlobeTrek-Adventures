<?php
require_once 'config.php';
$portal = 'customer';
$title = user() ? 'My Travel Hub' : 'Explore Sri Lanka';
$trip = null;
$tripCount = 0;
if (user() && user()['role'] === 'customer') {
    $uid = user()['id'];
    $b = db()->prepare(
        'SELECT b.*,p.title,p.destination,p.image_url FROM bookings b JOIN tour_packages p ON p.id=b.package_id WHERE b.user_id=? ORDER BY b.created_at DESC LIMIT 1',
    );
    $b->execute([$uid]);
    $trip = $b->fetch();
    $count = db()->prepare('SELECT COUNT(*) c FROM bookings WHERE user_id=?');
    $count->execute([$uid]);
    $tripCount = $count->fetch()['c'];
}
include 'includes/header.php';
?>
<?php if (!user()): ?>
    <section class="page-hero page-hero-destinations mb-5">
        <div class="page-hero-copy">
            <div class="eyebrow">DISCOVER SRI LANKA</div>
            <h1 class="display-serif">Your next story starts here.</h1>
            <p>Explore curated packages, destinations, stays and transport freely. Sign in only when you are ready to book.
            </p><a href="packages.php" class="btn btn-sand rounded-pill px-4">Explore tour packages</a>
        </div>
    </section>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="feature-card h-100">
                <div class="icon-box mb-3"><i class="bi bi-map"></i></div>
                <h4 class="fw-bold">Explore destinations</h4>
                <p class="muted">Find the coast, culture, hills and wildlife that suit you.</p><a
                    href="destinations.php">View destinations</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card h-100">
                <div class="icon-box mb-3"><i class="bi bi-suitcase"></i></div>
                <h4 class="fw-bold">Compare packages</h4>
                <p class="muted">Review itineraries, inclusions, activities and prices.</p><a href="packages.php">Browse
                    packages</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card h-100">
                <div class="icon-box mb-3"><i class="bi bi-stars"></i></div>
                <h4 class="fw-bold">Travel your way</h4>
                <p class="muted">Customize your selected trip when you are ready to book.</p><a href="why-us.php">Why
                    GlobeTrek</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="dashboard-hero mb-4">
        <div class="eyebrow">TRAVEL HUB</div>
        <h1 class="display-serif mt-2">Welcome, <?= e(
            explode(' ', user()['full_name'])[0],
        ) ?>.</h1>
        <p class="mb-0 text-white-50">Everything for your next Sri Lankan escape, in one place.</p>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat">
                <div class="muted small">Journeys</div>
                <div class="number"><?= $tripCount ?></div>
                <div class="muted small">total bookings</div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="dashboard-card h-100 d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="section-kicker">Need inspiration?</div>
                    <h5 class="fw-bold mt-2">Find your next experience.</h5>
                    <p class="muted mb-0">Explore curated tours across the island.</p>
                </div><a href="packages.php" class="btn btn-primary rounded-pill px-4">Explore</a>
            </div>
        </div>
    </div>
    <?php if (
        $trip
    ): ?>
        <div class="dashboard-card">
            <div class="row align-items-center g-4">
                <div class="col-md-4"><img src="<?= e(
                    $trip['image_url'],
                ) ?>" class="rounded-4 w-100" style="height:190px;object-fit:cover"></div>
                <div class="col-md-8">
                    <div class="section-kicker">Latest journey</div>
                    <h3 class="display-serif mt-2"><?= e(
                        $trip['title'],
                    ) ?></h3>
                    <p class="muted"><?= e($trip['destination']) ?> · <?= e($trip['travel_date']) ?> · <?= $trip[
                              'travelers'
                          ] ?> traveler(s)</p><span class="status <?= e($trip['status']) ?>"><?= e(
                                  ucfirst($trip['status']),
                              ) ?></span><a href="bookings.php" class="btn btn-outline-dark rounded-pill ms-2">View details</a>
                </div>
            </div>
        </div><?php else: ?>
        <div class="dashboard-card text-center py-5">
            <div class="icon-box mx-auto mb-3"><i class="bi bi-compass"></i></div>
            <h4 class="fw-bold">Your journey starts here.</h4>
            <p class="muted">You have no bookings yet.</p><a href="packages.php" class="btn btn-primary rounded-pill">Explore
                tours</a>
        </div><?php endif; ?>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>