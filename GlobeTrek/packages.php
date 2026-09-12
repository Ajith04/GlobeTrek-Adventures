<?php
require_once 'config.php';
$portal = 'customer';
$title = 'Explore Sri Lanka';
$q = trim($_GET['q'] ?? '');
$rows = db()->query("SELECT * FROM tour_packages WHERE status='active' ORDER BY featured DESC,price ASC")->fetchAll();
include 'includes/header.php';
?>
<div class="row align-items-end g-3 mb-5">
    <div class="col-lg-7">
        <div class="section-kicker">Curated collection</div>
        <h1 class="display-serif mt-2">Choose your kind of Sri Lanka.</h1>
        <p class="muted mb-0">Every package can be adapted to your dates, group and preferences.</p>
    </div>
    <div class="col-lg-5">
        <form id="package-search" class="input-group" role="search"><input id="package-search-input"
                class="form-control" name="q" value="<?= e(
                    $q,
                ) ?>" placeholder="Search destination, activity..." autocomplete="off"><button
                class="btn btn-primary">Search</button>
        </form>
        <div id="package-result-count" class="muted small mt-2" aria-live="polite"></div>
    </div>
</div>
<div id="package-grid" class="row g-4"><?php foreach ($rows as $p): ?>
        <div class="col-md-6 col-xl-4 package-result" data-search="<?= e(
            $p['title'] . ' ' . $p['destination'] . ' ' . $p['activities'] . ' ' . $p['short_description'],
        ) ?>">
            <article class="package-card position-relative"><img src="<?= e($p['image_url']) ?>" class="package-img">
                <div class="package-body"><span class="pill"><?= e($p['destination']) ?></span>
                    <h4 class="fw-bold mt-3"><?= e($p['title']) ?></h4>
                    <p class="muted small"><?= e($p['short_description']) ?></p>
                    <div class="d-flex flex-wrap gap-2 mb-4"><?php foreach (
                        array_slice(array_map('trim', explode(',', $p['activities'])), 0, 3)
                        as $a
                    ): ?><span class="badge text-bg-light"><?= e($a) ?></span><?php endforeach; ?></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="muted small">from</div>
                            <div class="price"><?= money($p['price']) ?></div>
                        </div><a href="package.php?id=<?= $p[
                            'id'
                        ] ?>" class="btn btn-primary rounded-pill stretched-link" aria-label="View <?= e(
                             $p['title'],
                         ) ?>">View trip</a>
                    </div>
                </div>
            </article>
        </div><?php endforeach; ?>
    <div id="no-package-results" class="col-12 d-none">
        <div class="dashboard-card text-center py-5">
            <h4>No matching journeys</h4>
            <p class="muted">Try a different destination or activity.</p>
        </div>
    </div>
</div>
<script>
    (() => {
        const form = document.getElementById('package-search');
        const input = document.getElementById('package-search-input');
        const cards = [...document.querySelectorAll('.package-result')];
        const empty = document.getElementById('no-package-results');
        const count = document.getElementById('package-result-count');
        const normalize = value => value.toLocaleLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();

        function filterPackages() {
            const query = normalize(input.value);
            let visible = 0;
            cards.forEach(card => {
                const matches = !query || normalize(card.dataset.search).includes(query);
                card.classList.toggle('d-none', !matches);
                if (matches) visible++;
            });
            empty.classList.toggle('d-none', visible !== 0);
            count.textContent = `${visible} ${visible === 1 ? 'journey' : 'journeys'} found`;

            const url = new URL(window.location.href);
            query ? url.searchParams.set('q', input.value.trim()) : url.searchParams.delete('q');
            history.replaceState(null, '', url);
        }

        input.addEventListener('input', filterPackages);
        form.addEventListener('submit', event => {
            event.preventDefault();
            filterPackages();
        });
        filterPackages();
    })();
</script>
<?php include 'includes/footer.php'; ?>