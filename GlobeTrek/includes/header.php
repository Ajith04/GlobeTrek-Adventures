<?php require_once __DIR__ . '/../config.php';
$title = $title ?? 'GlobeTrek Adventures';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e(
        $title,
    ) ?> · GlobeTrek Adventures</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= e(
        app_url('assets/style.css'),
    ) ?>" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark site-nav">
        <div class="container"><a class="navbar-brand" href="<?= e(
            app_url('index.php'),
        ) ?>"><span class="brand-mark"><i class="bi bi-compass"></i></span>Globe<span>Trek</span></a><button
                class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span
                    class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav mx-auto gap-lg-2">
                    <li><a class="nav-link" href="<?= e(
                        app_url('index.php'),
                    ) ?>">Home</a></li>
                    <li><a class="nav-link" href="<?= e(
                        app_url('packages.php'),
                    ) ?>">Tours</a></li>
                    <li><a class="nav-link" href="<?= e(
                        app_url('destinations.php'),
                    ) ?>">Destinations</a></li>
                    <li><a class="nav-link" href="<?= e(app_url('why-us.php')) ?>">Why us</a></li><?php if (
                          user() &&
                          user()['role'] === 'customer'
                      ): ?>
                        <li><a class="nav-link" href="<?= e(
                            app_url('bookings.php'),
                        ) ?>">My journeys</a></li><?php endif; ?>
                </ul>
                <div class="d-flex align-items-center gap-2"><?php if (
                    user() &&
                    user()['role'] === 'customer'
                ): ?><span class="text-white-50 small d-none d-xl-inline"><?= e(
                     user()['full_name'],
                 ) ?></span><a class="btn btn-light btn-sm rounded-pill" href="<?= e(
                      app_url('bookings.php'),
                  ) ?>">My trips</a><a class="btn btn-outline-light btn-sm rounded-pill" href="<?= e(
                       app_url('logout.php'),
                   ) ?>">Sign out</a><?php else: ?><a class="nav-link" href="<?= e(
                          app_url('login.php'),
                      ) ?>">Sign in</a><a class="btn btn-sand btn-sm rounded-pill px-3" href="<?= e(
                           app_url('register.php'),
                       ) ?>">Plan a trip</a><?php endif; ?></div>
            </div>
        </div>
    </nav>
    <main>
        <div class="container content-shell"><?php flashes(); ?>