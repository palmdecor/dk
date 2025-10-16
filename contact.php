<?php
if (!defined('APP_ENTRY')) {
    require __DIR__ . '/includes/bootstrap.php';
}
$meta = seo_meta_tags($translations, 'meta.contact.title', 'meta.contact.description');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ? $_POST['email'] : '';
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$email || strlen($message) < 10) {
        $errors[] = __t('form.validation_error', $translations);
    } else {
        $subject = 'İletişim Formu: ' . $name;
        $body = "İsim: {$name}\nE-posta: {$email}\nMesaj:\n{$message}";
        $headers = 'From: ' . $config['mail']['from'];
        $sent = @mail($config['mail']['to'], $subject, $body, $headers);

        if (!$sent) {
            $logMessage = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $body);
            file_put_contents(__DIR__ . '/storage/logs/contact.log', $logMessage, FILE_APPEND);
        }

        redirect_with_message(site_url('iletisim'), 'success', __t('form.success', $translations));
    }
}

$flash = get_flash_messages();
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/flash.php';
?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="fw-bold mb-4"><?= __t('contact.title', $translations) ?></h2>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                            </div>
                        <?php endif; ?>
                        <form method="post" novalidate>
                            <div class="mb-3">
                                <label class="form-label" for="name"><?= __t('contact.name', $translations) ?></label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($name ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="email"><?= __t('contact.email', $translations) ?></label>
                                <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="message"><?= __t('contact.message', $translations) ?></label>
                                <textarea class="form-control" id="message" name="message" rows="4" required><?= htmlspecialchars($message ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><?= __t('contact.submit', $translations) ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
