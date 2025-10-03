# Finans Portal

Finans Portal, interaktifkredi.com.tr benzeri sade PHP (Laravel olmadan) ile geliştirilmiş, kredi hesaplama ve başvuru süreçlerini yöneten bir web uygulamasıdır. Uygulama, Bootstrap tabanlı modern bir arayüz, çoklu dil desteği, üyelik sistemi ve mini CRM özellikli bir admin paneli sunar.

## Özellikler
- 💳 **Kredi Hesaplama** – Basit faiz yöntemine göre aylık ödeme tahmini.
- 👤 **Üyelik Sistemi** – Kullanıcı kayıt & giriş, session tabanlı oturum yönetimi.
- 📝 **Kredi Başvurusu** – Kimlik, gelir ve iletişim bilgilerinin toplanması ve veritabanına kaydedilmesi.
- 🛠️ **Admin Paneli** – Başvurular, sayfalar, blog yazıları, yorumlar, ürünler ve siparişleri yönetme.
- 🌐 **Çoklu Dil** – Türkçe ve İngilizce dil dosyaları ile hızlı geçiş.
- ✉️ **İletişim Formu** – Mail gönderimi, başarısızlık halinde log kaydı.
- 🔐 **Güvenlik** – PDO ile hazırlıklı sorgular, temel doğrulamalar, SSL uyumlu yapı.
- 📄 **KVKK & Kullanıcı Sözleşmesi** – Sabit bilgilendirme sayfaları.
- 📰 **Blog & Yorumlar** – Dinamik blog listesi, yazı detayı ve onaya tabi yorum sistemi.
- 🛒 **Ürün Yönetimi & Ödeme Akışı** – Findeks raporu gibi dijital ürünler için stok/fiyat takibi, PayTR/Shopier entegrasyon senaryosu ve callback simülasyonu.
- 📊 **Raporlama Dashboard'u** – Toplam başvuru, aylık başvurular, satılan ürünler ve ciro istatistikleri + Chart.js grafiği.

## Kurulum
1. Depoyu sunucunuza kopyalayın ve PHP 8.x + MySQL ortamı hazırlayın.
2. `config.php` dosyasında veritabanı ve e-posta ayarlarını güncelleyin.
3. MySQL üzerinde veritabanı oluşturup `database/schema.sql` dosyasını çalıştırın.
4. Web sunucunuzu (Nginx/Apache) proje kök dizinine yönlendirin.

## Varsayılan Dizayn
- Frontend: Bootstrap 5, Bootstrap Icons, jQuery.
- Backend: PDO ile saf PHP.
- Stil: `/assets/css/style.css`
- JS: `/assets/js/app.js`

## Notlar
- Admin yetkisi için `users` tablosundaki `role` alanını `admin` olarak güncelleyin.
- İletişim formu mail() fonksiyonu başarısız olursa içerik `storage/logs/contact.log` dosyasına kaydedilir.
- API üzerinden kredi hesaplamak için `GET /api/calculate.php?amount=50000&term=24` endpointini kullanabilirsiniz.
- Ürün satın alma akışı iki aşamalıdır: `products.php` sayfasından ürün seçilir → `purchase.php` siparişi oluşturur ve ödeme sağlayıcısına yönlendirir. Test için `payment_callback.php?order_id=ID&status=success` (veya `failed`) uç noktasına istek atarak sipariş statüsünü güncelleyebilirsiniz.
- Ödeme callback'i başarılı olduğunda stok güncellenir ve müşteri mail() ile bilgilendirilir; e-posta gönderimi başarısız olursa `storage/logs/orders.log` dosyasına log düşer.

## SEO
Tüm sayfalarda meta başlık/açıklama, canonical link ve çoklu dil desteği mevcuttur.
