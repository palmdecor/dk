<?php

declare(strict_types=1);

require_once __DIR__ . '/core/functions.php';

$pdo = Database::getConnection();
$settings = get_settings($pdo);

$categories = $pdo->query('SELECT id, name FROM categories WHERE status = 1 ORDER BY name')->fetchAll();
$products = $pdo->query('SELECT id, category_id, name, price, description, image FROM products WHERE status = 1 ORDER BY id DESC')->fetchAll();

$productsByCategory = [];
foreach ($products as $product) {
    $productsByCategory[$product['category_id']][] = $product;
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($settings['restaurant_name']) ?> Menü</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900">
<header class="bg-white shadow-sm">
    <div class="mx-auto flex max-w-6xl items-center gap-4 px-6 py-4">
        <?php if (!empty($settings['logo'])) : ?>
            <img src="<?= e($settings['logo']) ?>" alt="<?= e($settings['restaurant_name']) ?>" class="h-12 w-12 rounded-full object-cover">
        <?php endif; ?>
        <div>
            <h1 class="text-2xl font-bold"><?= e($settings['restaurant_name']) ?></h1>
            <p class="text-sm text-slate-500">QR Menü</p>
        </div>
    </div>
</header>

<main class="mx-auto max-w-6xl px-6 py-8">
    <div class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-xl font-semibold">Menümüz</h2>
        <p class="mt-1 text-sm text-slate-500">Lezzetli seçeneklerimizi keşfedin.</p>
    </div>

    <?php if (empty($categories)) : ?>
        <div class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-slate-500">
            Henüz aktif kategori bulunmuyor.
        </div>
    <?php endif; ?>

    <?php foreach ($categories as $category) : ?>
        <section class="mt-8">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-800"><?= e($category['name']) ?></h3>
                <span class="text-xs uppercase tracking-wide text-slate-400">Kategori</span>
            </div>

            <?php if (empty($productsByCategory[$category['id']])) : ?>
                <div class="mt-4 rounded-xl border border-dashed border-slate-200 bg-white p-6 text-sm text-slate-500">
                    Bu kategori için ürün bulunmuyor.
                </div>
            <?php else : ?>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($productsByCategory[$category['id']] as $product) : ?>
                        <article class="flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-sm">
                            <?php if (!empty($product['image'])) : ?>
                                <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" class="h-40 w-full object-cover">
                            <?php else : ?>
                                <div class="flex h-40 items-center justify-center bg-slate-100 text-sm text-slate-400">Görsel yok</div>
                            <?php endif; ?>
                            <div class="flex flex-1 flex-col gap-3 p-4">
                                <div>
                                    <h4 class="text-base font-semibold"><?= e($product['name']) ?></h4>
                                    <p class="mt-1 text-sm text-slate-500"><?= e($product['description']) ?></p>
                                </div>
                                <div class="mt-auto text-right text-lg font-bold text-slate-900">
                                    <?= e(number_format((float) $product['price'], 2)) ?> ₺
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
</main>

<footer class="border-t border-slate-200 bg-white">
    <div class="mx-auto flex max-w-6xl flex-col items-center gap-2 px-6 py-6 text-sm text-slate-500 sm:flex-row sm:justify-between">
        <span><?= e($settings['restaurant_name']) ?> © <?= e((string) date('Y')) ?></span>
        <span>QR Menü ile hızlı sipariş</span>
    </div>
</footer>
</body>
</html>
