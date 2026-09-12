<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/management_auth.php';
require_management();
verify_csrf();
$portal = 'staff';
$title = 'Guest Inquiries';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = db()->prepare('UPDATE inquiries SET response=?,status=? WHERE id=?');
    $s->execute([trim($_POST['response']), $_POST['status'], (int) $_POST['id']]);
    flash('success', 'Response saved.');
    go(app_url('queries.php'));
}
$rows = db()
    ->query('SELECT i.*,u.full_name,u.email FROM inquiries i JOIN users u ON u.id=i.user_id ORDER BY i.created_at DESC')
    ->fetchAll();
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
    $navActive = 'inquiries';
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
                <div class="section-kicker">Guest care</div>
                <h1 class="display-serif mt-2">Inquiries.</h1>
                <p class="muted">Keep every customer conversation clear and personal.</p>
            </div>
            <div class="row g-4"><?php foreach (
                $rows
                as $r
            ): ?>
                    <div class="col-12">
                        <details class="dashboard-card inquiry-item p-0 overflow-hidden">
                            <summary class="inquiry-summary p-4">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <div>
                                        <h5 class="fw-bold mb-1"><?= e(
                                            $r['subject'],
                                        ) ?></h5>
                                        <div class="small muted"><?= e($r['full_name']) ?> · <?= e(
                                               $r['email'],
                                           ) ?></div>
                                    </div><span class="status <?= e($r['status']) ?>"><?= e(
                                          $r['status'],
                                      ) ?></span>
                                    <div class="d-flex align-items-center gap-3"><span class="muted small"><?= e(
                                        date('M j, Y', strtotime($r['created_at'])),
                                    ) ?></span><i class="inquiry-chevron bi bi-chevron-down" aria-hidden="true"></i></div>
                                </div>
                            </summary>
                            <div class="inquiry-details border-top p-4">
                                <p><?= e(
                                    $r['message'],
                                ) ?></p>
                                <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r[
                                      'id'
                                  ] ?>"><textarea class="form-control mb-2" name="response" rows="3" placeholder="Write a helpful response..."><?= e(
                                       $r['response'],
                                   ) ?></textarea>
                                    <div class="d-flex gap-2"><select class="form-select" name="status"><?php foreach (
                                        ['open', 'answered', 'closed']
                                        as $v
                                    ): ?>
                                                <option <?= $r['status'] === $v
                                                    ? 'selected'
                                                    : '' ?>><?= $v ?></option>
                                            <?php endforeach; ?>
                                        </select><button class="btn btn-primary rounded-pill px-4">Save</button></div>
                                </form>
                            </div>
                        </details>
                    </div><?php endforeach; ?>
            </div>
            <?php ?>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/bootstrap.bundle.min.js"></script>
</body>

</html>