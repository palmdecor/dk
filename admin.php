<?php
require __DIR__ . '/includes/bootstrap.php';
require_admin();
$meta = seo_meta_tags($translations, 'meta.admin.title', 'meta.admin.description');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicationId = filter_var($_POST['application_id'] ?? '', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    $allowed = ['approve' => 'approved', 'reject' => 'rejected'];

    if ($applicationId && isset($allowed[$action])) {
        $stmt = $pdo->prepare('UPDATE applications SET status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['status' => $allowed[$action], 'id' => $applicationId]);
        redirect_with_message('admin.php', 'success', __t('form.success', $translations));
    } else {
        redirect_with_message('admin.php', 'danger', __t('form.error', $translations));
    }
}

$stmt = $pdo->query('SELECT applications.*, users.name, users.email FROM applications JOIN users ON users.id = applications.user_id ORDER BY applications.created_at DESC');
$applications = $stmt->fetchAll();

$flash = get_flash_messages();
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/flash.php';
?>
<section class="py-5">
    <div class="container">
        <h2 class="fw-bold mb-4"><?= __t('admin.title', $translations) ?></h2>
        <div class="table-responsive shadow-sm">
            <table class="table table-hover bg-white mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?= __t('admin.applicant', $translations) ?></th>
                        <th><?= __t('apply.loan_amount', $translations) ?></th>
                        <th><?= __t('apply.loan_term', $translations) ?></th>
                        <th><?= __t('dashboard.status', $translations) ?></th>
                        <th><?= __t('dashboard.created_at', $translations) ?></th>
                        <th><?= __t('admin.actions', $translations) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $application): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($application['id']) ?></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($application['name']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($application['email']) ?></div>
                            </td>
                            <td><?= number_format($application['loan_amount'], 2, ',', '.') ?> ₺</td>
                            <td><?= htmlspecialchars($application['loan_term']) ?></td>
                            <td><span class="badge badge-status <?= htmlspecialchars($application['status']) ?>"><?= __t('status.' . $application['status'], $translations) ?></span></td>
                            <td><?= date('d.m.Y H:i', strtotime($application['created_at'])) ?></td>
                            <td>
                                <form method="post" class="d-flex gap-2">
                                    <input type="hidden" name="application_id" value="<?= (int) $application['id'] ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" <?= $application['status'] === 'approved' ? 'disabled' : '' ?>><?= __t('admin.approve', $translations) ?></button>
                                    <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" <?= $application['status'] === 'rejected' ? 'disabled' : '' ?>><?= __t('admin.reject', $translations) ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
