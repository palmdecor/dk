<?php
require __DIR__ . '/includes/bootstrap.php';
$meta = seo_meta_tags($translations, 'meta.kvkk.title', 'meta.kvkk.description');
$flash = get_flash_messages();
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/flash.php';
?>
<section class="py-5">
    <div class="container">
        <h1 class="fw-bold mb-4"><?= __t('footer.kvkk', $translations) ?></h1>
        <p class="text-muted">Kişisel verilerinizin korunması bizim için önceliklidir. Bu metin, 6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında bilgilendirme amacı taşımaktadır.</p>
        <h4 class="mt-4">1. Veri Sorumlusu</h4>
        <p>Finans Portal, veri sorumlusu sıfatıyla kullanıcı verilerini işler ve korur.</p>
        <h4 class="mt-4">2. İşlenen Kişisel Veriler</h4>
        <p>Kimlik, iletişim, finansal ve başvuruya ilişkin veriler işlenebilir.</p>
        <h4 class="mt-4">3. İşleme Amaçları</h4>
        <p>Başvuruların değerlendirilmesi, kullanıcı desteği sağlanması ve yasal yükümlülüklerin yerine getirilmesi.</p>
        <h4 class="mt-4">4. Haklarınız</h4>
        <p>Verilerinize erişme, düzeltme, silme ve itiraz etme gibi haklara sahipsiniz. Talepleriniz için iletişim kanallarımızdan bize ulaşabilirsiniz.</p>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
