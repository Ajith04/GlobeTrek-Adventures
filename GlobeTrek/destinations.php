<?php
require_once 'config.php';
$portal = 'customer';
$title = 'Destinations';

$s = db()->query(
    "SELECT destination, COUNT(*) AS tour_count, MIN(price) AS from_price, MAX(image_url) AS image_url, MAX(short_description) AS short_description FROM tour_packages WHERE status='active' GROUP BY destination ORDER BY destination",
);
$destinations = $s->fetchAll();
include 'includes/header.php';
?>
<section class="page-hero page-hero-destinations mb-5">
    <div class="page-hero-copy">
        <div class="eyebrow">DISCOVER SRI LANKA</div>
        <h1 class="display-serif">Places worth the journey.</h1>
        <p>From misty hill country and ancient kingdoms to golden beaches and wild national parks, find the part of Sri
            Lanka that calls to you.</p>
        <a href="#destination-grid" class="btn btn-sand rounded-pill px-4">Explore destinations</a>
    </div>
</section>

<section id="destination-grid" class="section pt-4">
    <div class="row align-items-end g-3 mb-5">
        <div class="col-lg-8">
            <div class="section-kicker">Explore by place</div>
            <h2 class="mt-2 mb-3">Your Sri Lanka, your way.</h2>
            <p class="section-lead mb-0">Choose a destination to see the journeys currently available there.</p>
        </div>
        <div class="col-lg-4 text-lg-end"><span class="pill"><?= count($destinations) ?> destinations</span></div>
    </div>

    <div class="row g-4">
        <?php foreach ($destinations as $d):

            $image = trim((string) ($d['image_url'] ?? ''));
            $desc = trim((string) ($d['short_description'] ?? ''));
            ?>
            <div class="col-md-6 col-xl-4">
                <a class="destination-card destination-link d-block" href="packages.php?q=<?= urlencode(
                    $d['destination'],
                ) ?>" <?= $image ? 'style="background-image:url(\'' . e($image) . '\')"' : '' ?>>
                    <div class="caption">
                        <div class="destination-meta mb-2"><?= (int) $d['tour_count'] ?>     <?= (int) $d['tour_count'] === 1
                                    ? 'journey'
                                    : 'journeys' ?> · from <?= money($d['from_price']) ?></div>
                        <h3><?= e($d['destination']) ?></h3>
                        <p><?= e($desc ?: 'Discover curated experiences, local character and unforgettable places.') ?></p>
                        <span class="destination-cta mt-3">Explore <i class="bi bi-arrow-right"></i></span>
                    </div>
                </a>
            </div>
            <?php
        endforeach; ?>

        <?php if (!$destinations): ?>
            <div class="col-12">
                <div class="feature-card text-center py-5">
                    <div class="icon-box mx-auto mb-3"><i class="bi bi-map"></i></div>
                    <h4 class="fw-bold">Destinations are being curated.</h4>
                    <p class="muted mb-0">Active destinations will appear here as soon as tour packages are published.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="quote-section p-4 p-md-5 mb-4">
    <div class="row align-items-center g-4">
        <div class="col-lg-8">
            <div class="eyebrow">NOT SURE WHERE TO START?</div>
            <h2 class="mt-2 mb-2">See the island through experiences.</h2>
            <p class="quote mb-0">Browse every curated tour and find the combination of coast, culture, nature and
                adventure that suits you.</p>
        </div>
        <div class="col-lg-4 text-lg-end"><a href="packages.php" class="btn btn-sand rounded-pill px-4 py-2">Browse all
                tours</a></div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>