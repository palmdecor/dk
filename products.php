<?php

declare(strict_types=1);

require_once __DIR__ . '/core/functions.php';

require_login();

$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $name = trim($_POST['name'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $image = trim($_POST['image'] ?? '');
    $status = (int) ($_POST['status'] ?? 0);
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare(
            'UPDATE products SET category_id = :category_id, name = :name, price = :price, description = :description, image = :image, status = :status WHERE id = :id'
        );
        $stmt->execute([
            'category_id' => $categoryId,
            'name' => $name,
            'price' => $price,
            'description' => $description,
            'image' => $image,
            'status' => $status,
            'id' => $id,
        ]);
        flash_message('Ürün güncellendi.');
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO products (category_id, name, price, description, image, status) VALUES (:category_id, :name, :price, :description, :image, :status)'
        );
        $stmt->execute([
            'category_id' => $categoryId,
            'name' => $name,
            'price' => $price,
            'description' => $description,
            'image' => $image,
            'status' => $status,
        ]);
        flash_message('Ürün eklendi.');
    }

    redirect('products.php');
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute(['id' => (int) $_GET['delete']]);
    flash_message('Ürün silindi.', 'error');
    redirect('products.php');
}

$editProduct = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT id, category_id, name, price, description, image, status FROM products WHERE id = :id');
    $stmt->execute(['id' => (int) $_GET['edit']]);
    $editProduct = $stmt->fetch();
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$products = $pdo->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC')->fetchAll();

require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/sidebar.php';
?>
<main class="content">
    <div class="page-title">Ürünler</div>

    <?php if (!empty($flash)) : ?>
        <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="section">
        <h3><?= $editProduct ? 'Ürün Güncelle' : 'Yeni Ürün' ?></h3>
        <form method="post" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e((string) ($editProduct['id'] ?? '')) ?>">
            <label>Kategori
                <select name="category_id" required>
                    <?php foreach ($categories as $category) : ?>
                        <option value="<?= e((string) $category['id']) ?>" <?= ($editProduct['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Ürün Adı
                <input type="text" name="name" required value="<?= e($editProduct['name'] ?? '') ?>">
            </label>
            <label>Fiyat
                <input type="number" step="0.01" name="price" required value="<?= e((string) ($editProduct['price'] ?? '')) ?>">
            </label>
            <label>Görsel URL
                <input type="text" name="image" value="<?= e($editProduct['image'] ?? '') ?>">
            </label>
            <label>Açıklama
                <textarea name="description" rows="3"><?= e($editProduct['description'] ?? '') ?></textarea>
            </label>
            <label>Durum
                <select name="status">
                    <?php foreach (Status::cases() as $case) : ?>
                        <option value="<?= e((string) $case->value) ?>" <?= ($editProduct['status'] ?? 1) === $case->value ? 'selected' : '' ?>>
                            <?= e($case->label()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div>
                <label>&nbsp;</label>
                <button type="submit">Kaydet</button>
            </div>
        </form>
    </div>

    <div class="section">
        <h3>Ürün Listesi</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ürün</th>
                    <th>Kategori</th>
                    <th>Fiyat</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product) : ?>
                    <tr>
                        <td><?= e((string) $product['id']) ?></td>
                        <td><?= e($product['name']) ?></td>
                        <td><?= e($product['category_name'] ?? '-') ?></td>
                        <td><?= e(number_format((float) $product['price'], 2)) ?> ₺</td>
                        <td>
                            <?php $status = Status::from((int) $product['status']); ?>
                            <span class="badge <?= $status === Status::Active ? 'badge--active' : 'badge--passive' ?>">
                                <?= e($status->label()) ?>
                            </span>
                        </td>
                        <td>
                            <a href="products.php?edit=<?= e((string) $product['id']) ?>">Düzenle</a>
                            <a href="products.php?delete=<?= e((string) $product['id']) ?>" onclick="return confirm('Silmek istiyor musunuz?')">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php require_once __DIR__ . '/views/footer.php'; ?>
