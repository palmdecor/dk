<?php
require __DIR__ . '/includes/bootstrap.php';
$meta = seo_meta_tags($translations, 'meta.home.title', 'meta.home.description');
$flash = get_flash_messages();
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/flash.php';
?>
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4"><?= __t('hero.title', $translations) ?></h1>
                <p class="lead mb-4"><?= __t('hero.subtitle', $translations) ?></p>
                <a href="apply.php" class="btn btn-light btn-lg text-primary fw-semibold"><?= __t('nav.apply', $translations) ?></a>
            </div>
            <div class="col-lg-5 offset-lg-1 mt-5 mt-lg-0">
                <div class="card calculator-card border-0">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3"><?= __t('calculator.monthly_payment', $translations) ?></h5>
                        <form id="calculator-form" data-monthly-rate="<?= $config['app']['interest_rate'] ?>">
                            <div class="mb-3">
                                <label for="loanAmount" class="form-label"><?= __t('calculator.amount', $translations) ?></label>
                                <input type="number" min="1000" step="500" class="form-control" id="loanAmount" placeholder="50000">
                            </div>
                            <div class="mb-3">
                                <label for="loanTerm" class="form-label"><?= __t('calculator.term', $translations) ?></label>
                                <input type="number" min="6" max="120" step="6" class="form-control" id="loanTerm" placeholder="24">
                            </div>
                            <div id="calculator-result" class="alert alert-primary d-none" role="alert">
                                <?= sprintf(__t('calculator.result', $translations), '<span class="result-value">0</span>') ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold"><?= __t('advantages.title', $translations) ?></h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="p-4 bg-white advantage-card h-100">
                    <h5 class="fw-semibold mb-2"><i class="bi bi-lightning-fill text-primary me-2"></i><?= __t('advantages.fast', $translations) ?></h5>
                    <p class="text-muted mb-0"><?= __t('advantages.fast.desc', $translations) ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white advantage-card h-100">
                    <h5 class="fw-semibold mb-2"><i class="bi bi-headset text-primary me-2"></i><?= __t('advantages.support', $translations) ?></h5>
                    <p class="text-muted mb-0"><?= __t('advantages.support.desc', $translations) ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white advantage-card h-100">
                    <h5 class="fw-semibold mb-2"><i class="bi bi-shield-lock text-primary me-2"></i><?= __t('advantages.secure', $translations) ?></h5>
                    <p class="text-muted mb-0"><?= __t('advantages.secure.desc', $translations) ?></p>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold"><?= __t('testimonials.title', $translations) ?></h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial-card">
                    <p class="mb-3 text-muted"><?= __t('testimonials.1', $translations) ?></p>
                    <div class="fw-semibold"><?= __t('testimonials.1.name', $translations) ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <p class="mb-3 text-muted"><?= __t('testimonials.2', $translations) ?></p>
                    <div class="fw-semibold"><?= __t('testimonials.2.name', $translations) ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <p class="mb-3 text-muted"><?= __t('testimonials.3', $translations) ?></p>
                    <div class="fw-semibold"><?= __t('testimonials.3.name', $translations) ?></div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
