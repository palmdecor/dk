</main>
<footer class="bg-dark text-white py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>Finans Portal</h5>
                <p><?= __t('hero.subtitle', $translations) ?></p>
            </div>
            <div class="col-md-3">
                <h6 class="text-uppercase">Menü</h6>
                <ul class="list-unstyled">
                    <li><a class="text-white-50 text-decoration-none" href="<?= site_url('kullanici-sozlesmesi') ?>"><?= __t('footer.terms', $translations) ?></a></li>
                    <li><a class="text-white-50 text-decoration-none" href="<?= site_url('kvkk') ?>"><?= __t('footer.kvkk', $translations) ?></a></li>
                    <li><a class="text-white-50 text-decoration-none" href="<?= site_url('iletisim') ?>"><?= __t('nav.contact', $translations) ?></a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 class="text-uppercase">İletişim</h6>
                <p class="text-white-50">info@finansportal.com<br>+90 212 000 00 00</p>
            </div>
        </div>
        <div class="text-center text-white-50 mt-4">
            <?= __t('footer.rights', $translations) ?>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>
