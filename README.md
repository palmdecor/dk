# Invenio (Shared Hosting MVP)

Bu proje Apache + PHP 8.x + MySQL üzerinde çalışan, shared hosting uyumlu bir görsel render uygulamasıdır.

## Gereksinimler

- PHP 8.x
- MySQL / MariaDB
- Apache (public/.htaccess rewrite aktif)
- Imagick (varsa kullanılacak) veya GD + FreeType

## Kurulum

1) Composer autoload dosyalarını üretin:

```bash
composer install
```

2) Veritabanı şemasını içeri aktarın:

```sql
SOURCE schema.sql;
```

3) `config/config.php` üzerinden DB bilgilerini düzenleyin veya ortam değişkenlerini kullanın:

- `DB_DSN`
- `DB_USER`
- `DB_PASS`
- `APP_DEBUG` (true/false)
- `TELEGRAM_NOTIFY_URL`
- `RENDER_WAIT_ENABLED`

4) `storage/` klasörleri yazılabilir olmalıdır:

```
storage/
storage/uploads/
storage/overlays/
storage/fonts/
storage/renders/
storage/previews/
```

5) İlk admin kullanıcıyı oluşturun:

```php
<?php echo password_hash('sifreniz', PASSWORD_DEFAULT); ?>
```

```sql
INSERT INTO users (email, password_hash, name, role, status) VALUES (
  'admin@example.com',
  '$2y$10$uretilenHash',
  'Yonetici',
  'admin',
  'active'
);
```

## Notlar

- Overlay PNG zorunludur. SVG alanı opsiyonel olarak saklanır.
- Render tamamlanınca Telegram URL tanımlıysa HTTP POST yapılır.
- Üye render silemez, sadece admin silebilir.
- Admin panelde render süresi ayarı vardır; opsiyonel bekletme config üzerinden açılır.
