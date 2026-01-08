<?php

declare(strict_types=1);

require_once __DIR__ . '/core/functions.php';

require_login();

$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $name = trim($_POST['name'] ?? '');
    $status = (int) ($_POST['status'] ?? 0);
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare('UPDATE categories SET name = :name, status = :status WHERE id = :id');
        $stmt->execute(['name' => $name, 'status' => $status, 'id' => $id]);
        flash_message('Kategori güncellendi.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO categories (name, status) VALUES (:name, :status)');
        $stmt->execute(['name' => $name, 'status' => $status]);
        flash_message('Kategori eklendi.');
    }

    redirect('categories.php');
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
    $stmt->execute(['id' => (int) $_GET['delete']]);
    flash_message('Kategori silindi.', 'error');
    redirect('categories.php');
}

$editCategory = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT id, name, status FROM categories WHERE id = :id');
    $stmt->execute(['id' => (int) $_GET['edit']]);
    $editCategory = $stmt->fetch();
}

$categories = $pdo->query('SELECT id, name, status FROM categories ORDER BY id DESC')->fetchAll();

require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/sidebar.php';
?>
<main class="content">
    <div class="page-title">Kategoriler</div>

    <?php if (!empty($flash)) : ?>
        <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="section">
        <h3><?= $editCategory ? 'Kategori Güncelle' : 'Yeni Kategori' ?></h3>
        <form method="post" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e((string) ($editCategory['id'] ?? '')) ?>">
            <label>Ad
                <input type="text" name="name" required value="<?= e($editCategory['name'] ?? '') ?>">
            </label>
            <label>Durum
                <select name="status">
                    <?php foreach (Status::cases() as $case) : ?>
                        <option value="<?= e((string) $case->value) ?>" <?= ($editCategory['status'] ?? 1) === $case->value ? 'selected' : '' ?>>
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
        <h3>Liste</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ad</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category) : ?>
                    <tr>
                        <td><?= e((string) $category['id']) ?></td>
                        <td><?= e($category['name']) ?></td>
                        <td>
                            <?php $status = Status::from((int) $category['status']); ?>
                            <span class="badge <?= $status === Status::Active ? 'badge--active' : 'badge--passive' ?>">
                                <?= e($status->label()) ?>
                            </span>
                        </td>
                        <td>
                            <a href="categories.php?edit=<?= e((string) $category['id']) ?>">Düzenle</a>
                            <a href="categories.php?delete=<?= e((string) $category['id']) ?>" onclick="return confirm('Silmek istiyor musunuz?')">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php require_once __DIR__ . '/views/footer.php'; ?>
