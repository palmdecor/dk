<?php
require __DIR__ . '/includes/bootstrap.php';

$meta = seo_meta_tags($translations, 'meta.products.title', 'meta.products.description');
$products = published_products($pdo);

include __DIR__ . '/partials/header.php';
?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="fw-bold mb-3"><?= __t('products.title', $translations) ?></h1>
            <p class="text-muted lead mb-0"><?= __t('meta.products.description', $translations) ?></p>
        </div>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <h2 class="h5 fw-bold mb-2"><?= htmlspecialchars($product['name']) ?></h2>
                            <div class="text-primary fw-semibold mb-3"><?= format_price((float) $product['price']) ?></div>
                            <p class="text-muted flex-grow-1"><?= nl2br(htmlspecialchars(mb_substr($product['description'], 0, 180))) ?><?= mb_strlen($product['description']) > 180 ? '...' : '' ?></p>
                            <?php if ($product['stock'] > 0): ?>
                                <p class="small text-success mb-3"><?= sprintf(__t('products.stock', $translations), (int) $product['stock']) ?></p>
                                <form method="post" action="purchase.php" class="mt-auto">
                                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                    <button type="submit" class="btn btn-primary w-100"><?= __t('products.buy', $translations) ?></button>
                                </form>
                            <?php else: ?>
                                <p class="small text-danger mt-auto mb-0"><?= __t('products.out_of_stock', $translations) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center mb-0">Henüz ürün eklenmedi.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
