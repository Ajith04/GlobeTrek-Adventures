</div>
<footer class="footer">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5">
                <div class="footer-brand">Globe<span>Trek</span></div>
                <p class="text-white-50 mt-3 mb-0">Curated Sri Lankan journeys, thoughtful service and local expertise —
                    from Negombo to every corner of the island.</p>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Explore</h6><a href="<?= e(
                    app_url('destinations.php'),
                ) ?>">Destinations</a><a href="<?= e(app_url('packages.php')) ?>">Tour packages</a>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Company</h6><a href="<?= e(app_url('why-us.php')) ?>">About us</a><a href="<?= e(
                      app_url('#contact'),
                  ) ?>">Contact</a>
            </div>
            <div class="col-lg-3">
                <h6>Negombo office</h6>
                <p class="text-white-50 mb-1">Western Province, Sri Lanka</p>
                <p class="text-white-50">+94 77 123 4567<br>hello@globetrek.lk</p>
            </div>
        </div>
        <div class="footer-bottom">© <?= date('Y') ?> GlobeTrek Adventures. Built for memorable journeys.</div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('[data-auto-dismiss]').forEach(el => setTimeout(() => el.remove(), 4500));
</script>
</body>

</html>