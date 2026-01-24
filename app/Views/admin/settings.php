<?php
use App\Core\Helpers;

$renderMin = $settings['render_min_seconds'] ?? '0';
$telegramUrl = $settings['telegram_url'] ?? '';

ob_start();
?>
<h1>Ayarlar</h1>
<form method="post" action="/admin/settings/save">
  <?php echo $csrf->field(); ?>
  <label>Render Süresi (sn)</label>
  <input type="number" name="render_min_seconds" value="<?php echo Helpers::e((string)$renderMin); ?>">
  <label>Telegram Bildirim URL</label>
  <input name="telegram_url" value="<?php echo Helpers::e($telegramUrl); ?>">
  <button type="submit">Kaydet</button>
</form>
<p class="small">Render bekletme davranışı config üzerinden açılır (RENDER_WAIT_ENABLED).</p>
<?php
$content = ob_get_clean();
$title = 'Ayarlar';
include __DIR__ . '/../layout.php';
?>
