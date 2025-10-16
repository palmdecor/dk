<?php
if (!defined('APP_ENTRY')) {
    require __DIR__ . '/includes/bootstrap.php';
}
$meta = seo_meta_tags($translations, 'meta.register.title', 'meta.register.description');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ? strtolower($_POST['email']) : '';
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (!$name || !$email || strlen($password) < 6 || $password !== $passwordConfirm) {
        $errors[] = __t('form.validation_error', $translations);
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Bu e-posta adresi zaten kayıtlı.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, created_at) VALUES (:name, :email, :password, :role, NOW())');
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hash,
                'role' => 'user',
            ]);
            redirect_with_message(site_url('giris'), 'success', __t('form.success', $translations));
        }
    }
}

$flash = get_flash_messages();
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/flash.php';
?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="fw-bold mb-4 text-center"><?= __t('auth.register.title', $translations) ?></h2>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                            </div>
                        <?php endif; ?>
                        <form method="post" novalidate>
                            <div class="mb-3">
                                <label class="form-label" for="name"><?= __t('auth.name', $translations) ?></label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($name ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="email"><?= __t('auth.email', $translations) ?></label>
                                <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password"><?= __t('auth.password', $translations) ?></label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password_confirm"><?= __t('auth.password_confirm', $translations) ?></label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="6">
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><?= __t('auth.register.submit', $translations) ?></button>
                        </form>
                        <p class="mt-3 text-center text-muted">
                            <?= __t('auth.have_account', $translations) ?> <a href="<?= site_url('giris') ?>"><?= __t('nav.login', $translations) ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
