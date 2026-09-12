<?php
$navActive = $navActive ?? '';
$navBase = $navBase ?? '';
$navUser = user();
$navItems = [
    'dashboard' => ['index.php', 'Dashboard'],
    'bookings' => ['bookings.php', 'Bookings'],
    'packages' => ['packages.php', 'Packages'],
    'services' => ['services.php', 'Services'],
    'inquiries' => ['queries.php', 'Inquiries'],
];
$adminItems = [
    'users' => ['admin/users.php', 'Users & Staff'],
    'reports' => ['admin/reports.php', 'Reports'],
];
?>
<aside class="ops-nav">
    <div class="ops-brand">◈ GlobeTrek</div>
    <div class="ops-label"><?= e(strtoupper($navUser['role'])) ?> PORTAL</div>
    <?php foreach ($navItems as $key => [$path, $label]): ?>
        <a<?= $navActive === $key ? ' class="active"' : '' ?> href="<?= e($navBase . $path) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
        <?php if ($navUser['role'] === 'admin'): ?>
            <div class="ops-label">ADMINISTRATION</div>
            <?php foreach ($adminItems as $key => [$path, $label]): ?>
                <a<?= $navActive === $key ? ' class="active"' : '' ?> href="<?= e($navBase . $path) ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
            <?php endif; ?>
            <div class="ops-label">ACCOUNT</div>
            <a href="<?= e($navBase . 'logout.php') ?>">Sign out</a>
</aside>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const snapshot = form => JSON.stringify(
            [...new FormData(form).entries()].map(([name, value]) => [name, String(value)])
        );

        document.querySelectorAll('form').forEach(form => {
            let saveButtons = [...form.querySelectorAll('button')].filter(button =>
                /^save\b/i.test(button.textContent.trim())
            );

            // The Users & Staff table keeps its Save button in the row beside the form.
            if (!saveButtons.length) {
                const row = form.closest('tr');
                if (row) {
                    saveButtons = [...row.querySelectorAll('button')].filter(button =>
                        /^save\b/i.test(button.textContent.trim())
                    );
                }
            }

            if (!saveButtons.length) return;

            const initialState = snapshot(form);
            const updateSaveState = () => {
                const changed = snapshot(form) !== initialState;
                saveButtons.forEach(button => {
                    button.disabled = !changed;
                    button.setAttribute('aria-disabled', changed ? 'false' : 'true');
                });
            };

            form.addEventListener('input', updateSaveState);
            form.addEventListener('change', updateSaveState);
            form.addEventListener('submit', event => {
                if (snapshot(form) === initialState) event.preventDefault();
            });
            updateSaveState();
        });
    });
</script>