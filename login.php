<?php
require __DIR__ . '/includes/bootstrap.php';
$meta = seo_meta_tags($translations, 'meta.login.title', 'meta.login.description');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ? strtolower($_POST['email']) : '';
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = __t('form.validation_error', $translations);
    } else {
        $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
            redirect_with_message('dashboard.php', 'success', __t('form.success', $translations));
        } else {
            $errors[] = 'Geçersiz e-posta veya şifre.';
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
            <div class="col-md-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="fw-bold mb-4 text-center"><?= __t('auth.login.title', $translations) ?></h2>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                            </div>
                        <?php endif; ?>
                        <form method="post" novalidate>
                            <div class="mb-3">
                                <label class="form-label" for="email"><?= __t('auth.email', $translations) ?></label>
                                <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password"><?= __t('auth.password', $translations) ?></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><?= __t('auth.login.submit', $translations) ?></button>
                        </form>
                        <p class="mt-3 text-center text-muted">
                            <?= __t('auth.no_account', $translations) ?> <a href="register.php"><?= __t('nav.register', $translations) ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
