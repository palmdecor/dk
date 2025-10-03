<?php
require __DIR__ . '/includes/bootstrap.php';
$meta = seo_meta_tags($translations, 'meta.terms.title', 'meta.terms.description');
$flash = get_flash_messages();
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/flash.php';
?>
<section class="py-5">
    <div class="container">
        <h1 class="fw-bold mb-4"><?= __t('footer.terms', $translations) ?></h1>
        <p class="text-muted">Finans Portal hizmetlerini kullanarak aşağıdaki şartları kabul etmiş sayılırsınız.</p>
        <h4 class="mt-4">1. Hizmetin Kapsamı</h4>
        <p>Finans Portal, kullanıcıların kredi ürünleri hakkında bilgi edinmesini ve başvuru taleplerini iletmesini sağlayan dijital bir platformdur.</p>
        <h4 class="mt-4">2. Üyelik ve Güvenlik</h4>
        <p>Kullanıcılar kayıt olurken sundukları bilgilerin doğruluğundan sorumludur. Hesap güvenliği için güçlü bir şifre oluşturulmalı ve üçüncü kişilerle paylaşılmamalıdır.</p>
        <h4 class="mt-4">3. Veri Kullanımı</h4>
        <p>Paylaşılan bilgiler, başvuru değerlendirmesi ve kullanıcı deneyimini geliştirmek amacıyla işlenir. Detaylı bilgi için KVKK aydınlatma metnimizi inceleyiniz.</p>
        <h4 class="mt-4">4. Değişiklikler</h4>
        <p>Şartlar herhangi bir zamanda güncellenebilir. Güncel metin her zaman bu sayfa üzerinden yayınlanır.</p>
    </div>
</section>
<?php include __DIR__ . '/partials/footer.php'; ?>
