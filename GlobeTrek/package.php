<?php
require_once 'config.php';
$portal = 'customer';
$id = (int) ($_GET['id'] ?? 0);
$s = db()->prepare("SELECT * FROM tour_packages WHERE id=? AND status='active'");
$s->execute([$id]);
$p = $s->fetch();
if (!$p) {
    flash('danger', 'That journey is no longer available.');
    go(app_url('packages.php'));
}
$title = $p['title'];
include 'includes/header.php';
?>
<div class="row g-5 align-items-start">
    <div class="col-lg-7"><img src="<?= e(
        $p['image_url'],
    ) ?>" class="w-100 rounded-5" style="height:510px;object-fit:cover">
        <div class="d-flex gap-2 mt-3"><?php foreach (
            array_map('trim', explode(',', $p['activities']))
            as $a
        ): ?><span class="pill"><?= e(
             $a,
         ) ?></span><?php endforeach; ?></div>
    </div>
    <div class="col-lg-5">
        <div class="section-kicker"><?= e(
            $p['destination'],
        ) ?></div>
        <h1 class="display-serif mt-2"><?= e($p['title']) ?></h1>
        <p class="muted fs-5"><?= e(
            $p['short_description'],
        ) ?></p>
        <div class="mt-4">
            <p><?= nl2br(e($p['description'] ?? ($p['short_description'] ?? ''))) ?></p><?php
                  if (!empty($p['inclusions'])): ?>
                <div class="mt-3"><strong>Includes</strong>
                    <p class="muted small"><?= nl2br(
                        e($p['inclusions']),
                    ) ?></p>
                </div><?php endif;
                  if (!empty($p['exclusions'])): ?>
                <div><strong>Excludes</strong>
                    <p class="muted small"><?= nl2br(
                        e($p['exclusions']),
                    ) ?></p>
                </div>
            <?php endif;
                  ?>
        </div>
        <div class="border-top border-bottom py-4 my-4">
            <div class="muted small">Package from</div>
            <div class="display-6 fw-bold"><?= money(
                $p['price'],
            ) ?></div>
            <div class="muted small"><?= $p[
                'duration_days'
            ] ?> days · per traveler</div>
        </div><a href="book.php?id=<?= $p[
            'id'
        ] ?>" class="btn btn-primary btn-lg rounded-pill w-100">Customize & book <i class="bi bi-arrow-right ms-2"></i></a>
        <p class="muted small text-center mt-3"><i class="bi bi-shield-check me-1"></i>Booking confirmed by our travel
            team</p>
    </div>
</div>
<?php include 'includes/footer.php'; ?>