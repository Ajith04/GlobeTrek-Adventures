<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../management_auth.php';
require_management_role('admin');
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Settings · GlobeTrek</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="../assets/style.css?v=<?= filemtime(__DIR__ . '/../assets/style.css') ?>" rel="stylesheet"></head><body>
<?php
$navActive = '';
$navBase = '../';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="ops-main"><header class="ops-top"><strong>System settings</strong><span class="ops-user">Admin · <?= e(
    user()['full_name'],
) ?></span></header><main class="ops-content"><div class="panel" style="max-width:800px"><div class="section-kicker">Administration</div><h1 class="display-serif mt-2">System Settings.</h1><p class="text-secondary mb-0">GlobeTrek Adventures · Database: globetrek_adventures</p></div></main></div>
</body></html>
