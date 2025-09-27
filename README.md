# Kurumsal CMS

Bu proje, Twig tabanlı tema motoru ve Symfony bileşenleri kullanılarak hazırlanmış çok yönlü bir yönetim paneli ve vitrini içerir. PHP 8.1+ sürümü ile uyumludur.

## Özellikler
- Hizmetler, blog, sayfalar, slider, SSS, yorumlar ve mesajlar için CRUD modülleri
- PayTR, Shopier, havale/EFT, SMS sağlayıcıları gibi ayar alanları
- Rol ve yetki yönetimi (Admin, Editör, Çalışan)
- CSRF korumalı formlar ve PDO üzerinden prepared statement kullanımı
- Twig şablonlarıyla kolay tema entegrasyonu
- İletişim formlarının veritabanına kaydedilmesi ve SMTP üzerinden mail gönderilmesi

## Kurulum
1. Depoyu klonladıktan sonra bağımlılıkları yükleyin:
   ```bash
   composer install
   ```
2. `.env` dosyasını oluşturun:
   ```bash
   cp .env.example .env
   ```
   Ardından veritabanı, SMTP ve API bilgilerini doldurun.
3. Veritabanını oluşturup `database/schema.sql` dosyasındaki tabloları çalıştırın.
4. `public/` dizinini web sunucusuna root olarak tanımlayın.

## Çalıştırma
Yerel geliştirme için PHP yerleşik sunucusunu kullanabilirsiniz:
```bash
php -S localhost:8000 -t public/
```

## Twig Parçaları
- `{% include 'partials/header.twig' %}` ve `{% include 'partials/footer.twig' %}`: Tema header/footer alanları.
- `{% include 'admin/partials/header.twig' %}` ve `{% include 'admin/partials/footer.twig' %}`: Yönetim paneli genel çerçevesi.
- `{% include 'admin/partials/sidebar.twig' %}`: Sol menü.

## Yönetim Paneli Sayfa Kodları
| Sayfa | Kullanılacak Şablon | Açıklama |
|-------|--------------------|----------|
| `/admin/services.php` | `admin/services/list.twig` | Hizmet listeleme. |
| `/admin/service_add.php` | `admin/services/form.twig` | Hizmet ekleme. |
| `/admin/service_edit.php?id=` | `admin/services/form.twig` | Hizmet düzenleme. |
| `/admin/service_delete.php?id=` | — | Silme işlemi (POST). |
| `/admin/blogs.php` | `admin/blog/list.twig` | Blog listeleme. |
| `/admin/blog_add.php` | `admin/blog/form.twig` | Blog ekleme. |
| `/admin/pages.php` | `admin/pages/list.twig` | Sayfa yönetimi. |
| `/admin/slider.php` | `admin/slider/list.twig` | Slider kartları. |
| `/admin/faq.php` | `admin/faq/list.twig` | SSS yönetimi. |
| `/admin/comments.php` | `admin/comments/list.twig` | Yorum moderasyonu. |
| `/admin/messages.php` | `admin/messages/list.twig` | Mesaj kutusu. |
| `/admin/settings_general.php` | `admin/settings/general.twig` | Genel ayarlar. |
| `/admin/settings_seo.php` | `admin/settings/seo.twig` | SEO ayarları. |
| `/admin/settings_payment.php` | `admin/settings/payment.twig` | Ödeme entegrasyonları. |
| `/admin/settings_notifications.php` | `admin/settings/notifications.twig` | Bildirim ayarları. |
| `/admin/users.php` | `admin/users/list.twig` | Kullanıcı yönetimi. |

CRUD işlemlerinde `{% for item in items %}` döngüsü ile veriler listelenir; formlar `{% if item %}` koşulu ile düzenleme/ekleme modunu ayırt eder. Silme işlemleri için Twig tarafında `csrf.generateToken('modul_form')` ile CSRF token üretilir.

## Veritabanı Şeması
`database/schema.sql` içerisinde tabloların tamamı ve ilişkili alanlar yer alır. PDO kullanıldığı için MySQL veya MariaDB ile uyumludur.

## Güvenlik
- Parolalar `password_hash` ile saklanır.
- Tüm formlarda CSRF token zorunludur.
- Yetki kontrolü `Auth::checkPermission` ile yapılır.

## Tema Entegrasyonu
HTML tema dosyalarınızda Twig blokları ve include ifadeleri ile aşağıdaki alanları güncelleyebilirsiniz:
- `{% block title %}`: Sayfa başlıkları
- `{% block content %}`: Sayfa ana içeriği
- `{% include 'admin/partials/sidebar.twig' %}`: Yönetim menüsü
- `{% for slider in sliders %}`: Slider rotasyonu
- `{% for service in services %}` ve `{% for post in blog %}`: Kart listeleri

Ayrıca `templates/public/*.twig` dosyalarını temel alarak tema şablonlarınızı kolayca uyarlayabilirsiniz.

## Test Kullanıcısı
Veritabanına örnek bir kullanıcı ekleyerek giriş yapabilirsiniz:
```sql
INSERT INTO users (name, email, password, role, permissions, status, created_at, updated_at)
VALUES ('Admin', 'admin@example.com', PASSWORD_HASH('admin123', PASSWORD_BCRYPT), 'admin', '[]', 'active', NOW(), NOW());
```

> Not: `PASSWORD_HASH` fonksiyonu MySQL 8+ sürümlerinde mevcut değildir; PHP üzerinden hash üretip kaydedebilirsiniz.
