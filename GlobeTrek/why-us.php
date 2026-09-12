<?php
require_once 'config.php';
$portal = 'customer';
$title = 'Why Us';
include 'includes/header.php';
?>
<section class="page-hero page-hero-why mb-5">
    <div class="page-hero-copy">
        <div class="eyebrow">WHY GLOBETREK</div>
        <h1 class="display-serif">Travel designed around you.</h1>
        <p>We combine local knowledge, thoughtful planning and flexible journeys to make exploring Sri Lanka feel
            effortless from the first idea to the final day.</p>
        <a href="packages.php" class="btn btn-sand rounded-pill px-4">Find your journey</a>
    </div>
</section>

<section class="section pt-4">
    <div class="row align-items-end g-4 mb-5">
        <div class="col-lg-7">
            <div class="section-kicker">THE GLOBETREK DIFFERENCE</div>
            <h2 class="mt-2">Local insight. Personal journeys. Clear support.</h2>
        </div>
        <div class="col-lg-5">
            <p class="section-lead mb-0">A great trip is more than a list of places. It is the right pace, the right
                experiences and people who understand the destination.</p>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-6 col-xl-3">
            <div class="feature-card">
                <div class="icon-box mb-4"><i class="bi bi-geo-alt"></i></div>
                <h4 class="fw-bold">Local expertise</h4>
                <p class="muted mb-0">Journeys shaped by people who know Sri Lanka's regions, routes and experiences.
                </p>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="feature-card">
                <div class="icon-box mb-4"><i class="bi bi-sliders"></i></div>
                <h4 class="fw-bold">Flexible planning</h4>
                <p class="muted mb-0">Packages can be adapted around your dates, group size and travel preferences.</p>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="feature-card">
                <div class="icon-box mb-4"><i class="bi bi-shield-check"></i></div>
                <h4 class="fw-bold">Trusted service</h4>
                <p class="muted mb-0">Clear booking details and dependable coordination throughout your journey.</p>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="feature-card">
                <div class="icon-box mb-4"><i class="bi bi-headset"></i></div>
                <h4 class="fw-bold">Human support</h4>
                <p class="muted mb-0">Real assistance when you need help before, during or after your trip.</p>
            </div>
        </div>
    </div>
</section>

<section class="why-story mb-5">
    <div class="row g-0 align-items-stretch">
        <div class="col-lg-6 why-story-image"></div>
        <div class="col-lg-6 p-4 p-md-5 d-flex align-items-center">
            <div>
                <div class="section-kicker">OUR APPROACH</div>
                <h2 class="display-serif mt-2">Less rushing. More remembering.</h2>
                <p class="muted">We believe travel should leave room for discovery. GlobeTrek focuses on well-paced
                    itineraries that balance Sri Lanka's famous highlights with the character that makes each place
                    memorable.</p>
                <p class="muted mb-0">Whether you are planning a first visit, a family holiday or an adventure with
                    friends, the experience starts with what matters to you.</p>
            </div>
        </div>
    </div>
</section>

<section class="quote-section p-4 p-md-5 mb-4 text-center">
    <div class="eyebrow">READY WHEN YOU ARE</div>
    <h2 class="mt-2">Make Sri Lanka your next story.</h2>
    <p class="quote mx-auto" style="max-width:720px">Explore curated journeys and choose the experience that feels right
        for you.</p><a href="packages.php" class="btn btn-sand rounded-pill px-4 py-2">Explore tours</a>
</section>
<?php include 'includes/footer.php'; ?>