<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();
$meta = seo_meta_tags($translations, 'meta.dashboard.title', 'meta.dashboard.description');

$stmt = $pdo->prepare('SELECT id, loan_amount, loan_term, status, created_at FROM applications WHERE user_id = :user_id ORDER BY created_at DESC');
$stmt->execute(['user_id' => current_user()['id']]);
$applications = $stmt->fetchAll();

$flash = get_flash_messages();
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/flash.php';
?>
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold"><?= __t('dashboard.title', $translations) ?></h2>
            <a href="apply.php" class="btn btn-primary"><?= __t('nav.apply', $translations) ?></a>
        </div>
        <?php if (empty($applications)): ?>
            <div class="alert alert-info" role="alert">
                <?= __t('dashboard.empty', $translations) ?>
            </div>
        <?php else: ?>
            <div class="table-responsive shadow-sm">
                <table class="table table-hover bg-white mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?= __t('apply.loan_amount', $translations) ?></th>
                            <th><?= __t('apply.loan_term', $translations) ?></th>
                            <th><?= __t('dashboard.status', $translations) ?></th>
                            <th><?= __t('dashboard.created_at', $translations) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $application): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($application['id']) ?></td>
                                <td><?= number_format($application['loan_amount'], 2, ',', '.') ?> ₺</td>
                                <td><?= htmlspecialchars($application['loan_term']) ?></td>
                                <td><span class="badge badge-status <?= htmlspecialchars($application['status']) ?>"><?= __t('status.' . $application['status'], $translations) ?></span></td>
                                <td><?= date('d.m.Y H:i', strtotime($application['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
