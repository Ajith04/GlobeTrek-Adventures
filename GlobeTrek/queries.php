<?php
require_once 'config.php';
require_role('customer', 'login.php');
verify_csrf();
$portal = 'customer';
$title = 'Travel Support';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = db()->prepare("INSERT INTO inquiries(user_id,subject,message,status) VALUES(?,?,?,'open')");
    $s->execute([user()['id'], trim($_POST['subject']), trim($_POST['message'])]);
    flash('success', 'Your message has been sent to our travel team.');
    go(app_url('queries.php'));
}
$s = db()->prepare('SELECT * FROM inquiries WHERE user_id=? ORDER BY created_at DESC');
$s->execute([user()['id']]);
$rows = $s->fetchAll();
include 'includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="section-kicker">Concierge desk</div>
        <h1 class="display-serif mt-2">How can we help?</h1>
        <p class="muted">Ask about hotels, transfers, itineraries, dates or anything else.</p>
        <div class="dashboard-card mt-4">
            <form method="post"><?= csrf_field() ?><input name="subject" class="form-control mb-3"
                    placeholder="What can we help with?" required><textarea name="message" class="form-control mb-3"
                    rows="6" placeholder="Tell us what you need..." required></textarea><button
                    class="btn btn-primary rounded-pill w-100">Send message</button></form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="dashboard-card">
            <h5 class="fw-bold">Conversation history</h5><?php
            foreach (
                $rows
                as $r
            ): ?>
                <div class="py-4 border-bottom">
                    <div class="d-flex justify-content-between gap-3"><strong><?= e(
                        $r['subject'],
                    ) ?></strong><span class="status <?= e($r['status']) ?>"><?= e(
                           $r['status'],
                       ) ?></span></div>
                    <p class="muted mt-2 mb-2"><?= e($r['message']) ?></p><?php if (
                          $r['response']
                      ): ?>
                        <div class="p-3 rounded-3 bg-light small"><strong>GlobeTrek:</strong> <?= e(
                            $r['response'],
                        ) ?></div><?php endif; ?>
                </div><?php endforeach;
            if (!$rows): ?>
                <p class="muted mt-4">No conversations yet.</p>
            <?php endif;
            ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>