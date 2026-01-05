# Bulk Watermark Studio

Laravel 10 + PHP 8.2 tabanlı, Hostinger paylaşımlı hosting uyumlu toplu watermark uygulaması.

## Kurulum (local)
1. `composer install` (internet erişimi yoksa paketleri önceden indirilmiş vendor klasörüyle yükleyin).
2. `.env` dosyasını `.env.example` üzerinden oluşturun, `APP_KEY` üretin.
3. MySQL bağlantısını ayarlayın, `php artisan migrate` çalıştırın.
4. İlk admin kullanıcısını `php artisan tinker` ile oluşturun veya seeder ekleyin.
5. `php artisan storage:link` ile storage symlink oluşturun.

## Deploy (Hostinger shared hosting)
1. Kaynak kodu Hostinger hesabınıza yükleyin.
2. `public` klasör içeriğini `public_html` içine taşıyın veya `index.php` yolunu `../bulk-watermark-studio/public` gibi ayarlayın.
3. `storage` klasörüne yazma izni verin (`chmod -R 775 storage bootstrap/cache`).
4. `.env` dosyasını üretim değerleriyle güncelleyin, `APP_ENV=production`, `APP_DEBUG=false`.
5. `QUEUE_CONNECTION=database` ayarlayın ve veritabanında migrasyonları çalıştırın.
6. Cron ekleyin:
   - `php /home/USERNAME/domains/DOMAIN/public_html/artisan queue:work --stop-when-empty`
   - `php /home/USERNAME/domains/DOMAIN/public_html/artisan app:cleanup-old-jobs`

## public klasör ayarı
Hostinger'da `public_html` web root olduğundan Laravel `public` klasörünü buraya yönlendirin veya içeriğini buraya kopyalayın.

## storage izinleri
`storage` ve `bootstrap/cache` klasörlerinin web sunucusu tarafından yazılabilir olduğundan emin olun.

## .env ayarları
```
APP_NAME=BulkWatermarkStudio
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain.com
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```
Veritabanı ve mail ayarlarını gerektiği gibi doldurun.

## Database migrate
`php artisan migrate --force`

## Queue kurulumu
- `.env` -> `QUEUE_CONNECTION=database`
- Cron: `php /home/USERNAME/domains/DOMAIN/public_html/artisan queue:work --stop-when-empty`

## Cleanup cron
`php /home/USERNAME/domains/DOMAIN/public_html/artisan app:cleanup-old-jobs`

## Imagick / GD
Sunucuda Imagick varsa otomatik kullanılır, yoksa GD fallback devreye girer. Ayarlar ekranında kullanılan sürücü gösterilir.

## Hostinger PHP limitleri artırma
`php.ini` veya `.htaccess` içine:
```
upload_max_filesize = 64M
post_max_size = 64M
memory_limit = 256M
```
Ayrıca Hostinger panelinden PHP seçeneklerini güncelleyebilirsiniz.
