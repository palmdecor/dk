<?php
require __DIR__ . '/includes/bootstrap.php';

$meta = seo_meta_tags($translations, 'products.checkout.title', 'products.checkout.subtitle');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('products.php', 'danger', __t('form.error', $translations));
}

$productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
if (!$productId) {
    redirect_with_message('products.php', 'danger', __t('form.error', $translations));
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id AND status = 'published'");
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    redirect_with_message('products.php', 'danger', __t('form.error', $translations));
}

if ((int) $product['stock'] <= 0) {
    redirect_with_message('products.php', 'danger', __t('products.out_of_stock', $translations));
}

if (isset($_POST['confirm'])) {
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
    if ($quantity > (int) $product['stock']) {
        redirect_with_message('products.php', 'danger', __t('products.out_of_stock', $translations));
    }
    $provider = in_array($_POST['payment_provider'] ?? 'paytr', ['paytr', 'shopier'], true) ? $_POST['payment_provider'] : 'paytr';
    $customerName = trim($_POST['customer_name'] ?? '');
    $customerEmail = trim($_POST['customer_email'] ?? '');
    if ($customerEmail !== '' && !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        redirect_with_message('products.php', 'danger', __t('form.validation_error', $translations));
    }
    $total = $product['price'] * $quantity;
    $user = current_user();

    $orderStmt = $pdo->prepare('INSERT INTO orders (user_id, product_id, quantity, total_amount, payment_provider, payment_status, notes, created_at) VALUES (:user_id, :product_id, :quantity, :total_amount, :provider, :status, :notes, NOW())');
    $orderStmt->execute([
        'user_id' => $user['id'] ?? null,
        'product_id' => $product['id'],
        'quantity' => $quantity,
        'total_amount' => $total,
        'provider' => $provider,
        'status' => 'pending',
        'notes' => $customerName || $customerEmail ? json_encode(['name' => $customerName, 'email' => $customerEmail]) : null,
    ]);
    $orderId = (int) $pdo->lastInsertId();

    $eventStmt = $pdo->prepare('INSERT INTO order_events (order_id, event, payload, created_at) VALUES (:order_id, :event, :payload, NOW())');
    $eventStmt->execute([
        'order_id' => $orderId,
        'event' => 'created',
        'payload' => json_encode(['provider' => $provider, 'quantity' => $quantity]),
    ]);

    $reference = strtoupper($provider) . '-' . $orderId . '-' . time();
    $logMessage = sprintf("[%s] Order %d created for product %s (%d qty) via %s\n", date('Y-m-d H:i:s'), $orderId, $product['name'], $quantity, $provider);
    file_put_contents(__DIR__ . '/storage/logs/orders.log', $logMessage, FILE_APPEND);

    include __DIR__ . '/partials/header.php';
    ?>
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="bg-white shadow-sm rounded p-5 text-center">
                        <div class="mb-4">
                            <span class="display-1 text-primary"><i class="bi bi-shield-check"></i></span>
                        </div>
                        <h1 class="fw-bold mb-3"><?= __t('products.checkout.title', $translations) ?></h1>
                        <p class="text-muted mb-4"><?= __t('products.checkout.subtitle', $translations) ?></p>
                        <ul class="list-group text-start mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?= htmlspecialchars($product['name']) ?> × <?= $quantity ?></span>
                                <span class="fw-semibold"><?= format_price((float) $total) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?= __t('products.checkout.provider', $translations) ?></span>
                                <span class="fw-semibold text-uppercase"><?= htmlspecialchars($provider) ?></span>
                            </li>
                        </ul>
                        <div class="alert alert-info text-start">
                            <p class="mb-1">Ödeme sağlayıcısına yönlendiriliyorsunuz. Ödeme başarıyla tamamlandığında sistemimize otomatik bildirim düşecektir.</p>
                            <p class="mb-0 small">Test için aşağıdaki bağlantıları kullanabilirsiniz:</p>
                            <ul class="small mb-0 mt-2">
                                <li><a href="payment_callback.php?order_id=<?= $orderId ?>&status=success&provider=<?= urlencode($provider) ?>&reference=<?= urlencode($reference) ?>" class="text-decoration-none">Ödemeyi Başarılı Bildir</a></li>
                                <li><a href="payment_callback.php?order_id=<?= $orderId ?>&status=failed&provider=<?= urlencode($provider) ?>&reference=<?= urlencode($reference) ?>" class="text-decoration-none">Ödemeyi Başarısız Bildir</a></li>
                            </ul>
                        </div>
                        <a href="products.php" class="btn btn-outline-primary mt-3">Ürünlere Geri Dön</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
    include __DIR__ . '/partials/footer.php';
    exit;
}

include __DIR__ . '/partials/header.php';
?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="bg-white shadow-sm rounded p-4">
                    <h1 class="fw-bold mb-4"><?= __t('products.checkout.title', $translations) ?></h1>
                    <p class="text-muted mb-4"><?= __t('products.checkout.subtitle', $translations) ?></p>
                    <div class="border rounded p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h2 class="h5 mb-1"><?= htmlspecialchars($product['name']) ?></h2>
                                <p class="text-muted small mb-0"><?= sprintf(__t('products.stock', $translations), (int) $product['stock']) ?></p>
                            </div>
                            <div class="fs-4 fw-semibold text-primary"><?= format_price((float) $product['price']) ?></div>
                        </div>
                        <form method="post" class="row g-3">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <div class="col-md-6">
                                <label class="form-label">Ad Soyad</label>
                                <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars(current_user()['name'] ?? '') ?>" placeholder="Adınız" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">E-posta</label>
                                <input type="email" name="customer_email" class="form-control" value="<?= htmlspecialchars(current_user()['email'] ?? '') ?>" placeholder="eposta@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Adet</label>
                                <input type="number" name="quantity" min="1" value="1" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __t('products.checkout.provider', $translations) ?></label>
                                <select name="payment_provider" class="form-select">
                                    <option value="paytr">PayTR</option>
                                    <option value="shopier">Shopier</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="confirm" value="1" class="btn btn-primary w-100"><?= __t('products.checkout.submit', $translations) ?></button>
                            </div>
                        </form>
                    </div>
                    <p class="small text-muted mb-0">Sipariş oluşturulduktan sonra ödeme sağlayıcısının ekranına yönlendirilirsiniz ve ödeme sonucunuz callback ile sistemimize iletilir.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
