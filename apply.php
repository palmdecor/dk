<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();
$meta = seo_meta_tags($translations, 'meta.apply.title', 'meta.apply.description');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identityNumber = sanitize($_POST['identity_number'] ?? '');
    $monthlyIncome = filter_var($_POST['monthly_income'] ?? '', FILTER_VALIDATE_FLOAT);
    $loanAmount = filter_var($_POST['loan_amount'] ?? '', FILTER_VALIDATE_FLOAT);
    $loanTerm = filter_var($_POST['loan_term'] ?? '', FILTER_VALIDATE_INT);
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    if (!preg_match('/^[1-9][0-9]{10}$/', $identityNumber)) {
        $errors[] = 'TC kimlik numarası 11 haneli olmalıdır.';
    }
    if ($monthlyIncome === false || $monthlyIncome <= 0) {
        $errors[] = 'Aylık gelir bilgisi geçersiz.';
    }
    if ($loanAmount === false || $loanAmount < 1000) {
        $errors[] = 'Kredi tutarı en az 1000 TL olmalıdır.';
    }
    if ($loanTerm === false || $loanTerm < 6 || $loanTerm > 120) {
        $errors[] = 'Vade 6 ile 120 ay arasında olmalıdır.';
    }
    if (!preg_match('/^[0-9 +()-]{10,20}$/', $phone)) {
        $errors[] = 'Telefon numarası geçersiz.';
    }
    if (strlen($address) < 10) {
        $errors[] = 'Adres bilgisi en az 10 karakter olmalıdır.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO applications (user_id, identity_number, monthly_income, loan_amount, loan_term, phone, address, status, created_at) VALUES (:user_id, :identity_number, :monthly_income, :loan_amount, :loan_term, :phone, :address, :status, NOW())');
        $stmt->execute([
            'user_id' => current_user()['id'],
            'identity_number' => $identityNumber,
            'monthly_income' => $monthlyIncome,
            'loan_amount' => $loanAmount,
            'loan_term' => $loanTerm,
            'phone' => $phone,
            'address' => $address,
            'status' => 'pending',
        ]);
        redirect_with_message('dashboard.php', 'success', __t('form.success', $translations));
    }
}

$flash = get_flash_messages();
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/flash.php';
?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="fw-bold mb-4"><?= __t('apply.title', $translations) ?></h2>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                            </div>
                        <?php endif; ?>
                        <form method="post" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="identity_number"><?= __t('apply.identity_number', $translations) ?></label>
                                    <input type="text" class="form-control" id="identity_number" name="identity_number" required pattern="^[0-9]{11}$" value="<?= htmlspecialchars($identityNumber ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="monthly_income"><?= __t('apply.monthly_income', $translations) ?></label>
                                    <input type="number" min="0" class="form-control" id="monthly_income" name="monthly_income" required value="<?= htmlspecialchars($monthlyIncome ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="loan_amount"><?= __t('apply.loan_amount', $translations) ?></label>
                                    <input type="number" min="1000" step="500" class="form-control" id="loan_amount" name="loan_amount" required value="<?= htmlspecialchars($loanAmount ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="loan_term"><?= __t('apply.loan_term', $translations) ?></label>
                                    <input type="number" min="6" max="120" class="form-control" id="loan_term" name="loan_term" required value="<?= htmlspecialchars($loanTerm ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="phone"><?= __t('apply.phone', $translations) ?></label>
                                    <input type="text" class="form-control" id="phone" name="phone" required value="<?= htmlspecialchars($phone ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="address"><?= __t('apply.address', $translations) ?></label>
                                    <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($address ?? '') ?></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-4">
                                <?= __t('apply.submit', $translations) ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
