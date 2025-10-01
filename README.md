# Interaktif Kredi Platformu

Laravel 11 ile geliştirilen bu proje, interaktifkredi.com.tr benzeri modern bir kredi başvuru deneyimi sunar. Sistem, kredi hesaplama modülü, kullanıcı üyeliği, başvuru yönetimi ve CRM benzeri bir yönetim paneli içerir.

## Özellikler

- 💳 **Kredi Hesaplama**: Gerçek zamanlı aylık ödeme hesaplama ve sonuçların saklanması.
- 👤 **Üyelik Sistemi**: Kayıt, giriş, çoklu dil desteği (Türkçe ve İngilizce).
- 📝 **Kredi Başvurusu**: KVKK uyumlu form, doğrulama ve bildirim e-postaları.
- 🛠️ **Yönetim Paneli**: Başvuruları listeleme, onay/red işlemleri ve rol tabanlı yetkilendirme.
- 📬 **İletişim Formu**: Form doğrulama ve e-posta gönderimi.
- 🔒 **Güvenlik**: CSRF koruması, form doğrulama, SSL uyumlu yapılandırma.
- 🌐 **SEO & Responsiveness**: Bootstrap tabanlı responsive arayüz, meta tag desteği.

## Kurulum

> Projeyi çalıştırmak için makinenizde PHP 8.2+, Composer, Node.js ve MySQL bulunmalıdır.

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install && npm run build # isteğe bağlı, özelleştirilmiş varlıklar için
php artisan serve
```

Admin paneline giriş için seeding sonrasında aşağıdaki kullanıcı oluşturulur:

- E-posta: `admin@interaktifkredi.com.tr`
- Şifre: `Password123!`

## Dizayn ve Teknoloji

- Laravel 11, Blade temaları, Bootstrap 5.3
- Spatie Laravel Permission ile rol yönetimi
- Laravel Sanctum hazır API koruması
- Çoklu dil için `resources/lang/tr` ve `resources/lang/en`

## Testler

Örnek testleri çalıştırmak için:

```bash
php artisan test
```

## Lisans

Bu proje ticari kullanımlar için hazırlanmıştır. Tüm hakları saklıdır.
