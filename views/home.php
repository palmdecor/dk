<?php
if (!isset($homepageContent, $translations, $config, $featuredPosts, $featuredProducts, $interestRate, $flash, $tahsisRate)) {
    throw new RuntimeException('Home view requires prepared variables.');
}
include __DIR__ . '/../partials/header.php';
include __DIR__ . '/../partials/flash.php';
?>
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4"><?= htmlspecialchars($homepageContent['hero_title'] ?? '') ?></h1>
                <p class="lead mb-4"><?= htmlspecialchars($homepageContent['hero_subtitle'] ?? '') ?></p>
                <a href="<?= site_url('basvuru') ?>" class="btn btn-light btn-lg text-primary fw-semibold"><?= __t('nav.apply', $translations) ?></a>
            </div>
            <div class="col-lg-5 offset-lg-1 mt-5 mt-lg-0">
                <div class="card calculator-card border-0">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3"><?= __t('calculator.monthly_payment', $translations) ?></h5>
                        <form id="calculator-form" data-monthly-rate="<?= htmlspecialchars($interestRate) ?>" data-fee-rate="<?= htmlspecialchars($tahsisRate) ?>">
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
                            <div class="table-responsive d-none" id="calculator-table">
                                <table class="table table-sm mb-0 calculator-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row"><?= __t('calculator.interest_rate_label', $translations) ?></th>
                                            <td><span data-field="interest-rate">0%</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?= __t('calculator.total_interest', $translations) ?></th>
                                            <td><span data-field="total-interest">0</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?= __t('calculator.monthly_payment_label', $translations) ?></th>
                                            <td><span data-field="monthly-payment">0</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?= __t('calculator.total_payment', $translations) ?></th>
                                            <td><span data-field="total-payment">0</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?= __t('calculator.annual_cost', $translations) ?></th>
                                            <td><span data-field="annual-rate">0%</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?= __t('calculator.disbursement_fee', $translations) ?></th>
                                            <td><span data-field="fee-amount">0</span></td>
                                        </tr>
                                    </tbody>
                                </table>
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
            <?php foreach ($homepageContent['advantages'] as $index => $advantage): ?>
                <div class="col-md-4">
                    <div class="p-4 bg-white advantage-card h-100">
                        <h5 class="fw-semibold mb-2"><i class="bi <?= htmlspecialchars($advantageIcons[$index] ?? 'bi-star') ?> text-primary me-2"></i><?= htmlspecialchars($advantage['title'] ?? '') ?></h5>
                        <p class="text-muted mb-0"><?= htmlspecialchars($advantage['description'] ?? '') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold"><?= __t('testimonials.title', $translations) ?></h2>
        </div>
        <div class="row g-4">
            <?php foreach ($homepageContent['testimonials'] as $testimonial): ?>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <p class="mb-3 text-muted"><?= htmlspecialchars($testimonial['text'] ?? '') ?></p>
                        <div class="fw-semibold"><?= htmlspecialchars($testimonial['name'] ?? '') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php if (!empty($featuredPosts)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0"><?= __t('nav.blog', $translations) ?></h2>
            <a href="<?= site_url('blog') ?>" class="btn btn-outline-primary btn-sm"><?= __t('blog.read_more', $translations) ?></a>
        </div>
        <div class="row g-4">
            <?php foreach ($featuredPosts as $post): ?>
                <div class="col-md-4">
                    <article class="card h-100 shadow-sm border-0">
                        <?php if (!empty($post['image_path'])): ?>
                            <img src="<?= htmlspecialchars($post['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($post['title']) ?>">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h3 class="h5 fw-bold"><a href="<?= site_url('blog/' . $post['slug']) ?>" class="text-decoration-none stretched-link"><?= htmlspecialchars($post['title']) ?></a></h3>
                            <div class="text-muted small mb-2"><?= date('d.m.Y', strtotime($post['published_at'] ?? $post['created_at'])) ?></div>
                            <p class="text-muted flex-grow-1"><?= htmlspecialchars($post['excerpt'] ?: mb_substr(strip_tags($post['body']), 0, 100) . '...') ?></p>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php if (!empty($featuredProducts)): ?>
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0"><?= __t('nav.products', $translations) ?></h2>
            <a href="<?= site_url('urunler') ?>" class="btn btn-outline-primary btn-sm"><?= __t('products.buy', $translations) ?></a>
        </div>
        <div class="row g-4">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <h3 class="h5 fw-bold mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="text-primary fw-semibold mb-3"><?= format_price((float) $product['price']) ?></div>
                            <p class="text-muted flex-grow-1"><?= nl2br(htmlspecialchars(mb_substr($product['description'], 0, 120))) ?><?= mb_strlen($product['description']) > 120 ? '...' : '' ?></p>
                            <form method="post" action="<?= site_url('urun/' . $product['slug']) ?>" class="mt-auto">
                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                <button type="submit" class="btn btn-primary w-100"><?= __t('products.buy', $translations) ?></button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php include __DIR__ . '/../partials/footer.php'; ?>
