<?php
use App\Core\Helpers;

ob_start();
?>
<h1>Giriş</h1>
<form method="post" action="/login">
  <?php echo $csrf->field(); ?>
  <label>E-posta</label>
  <input name="email" required>
  <label>Şifre</label>
  <input type="password" name="password" required>
  <button type="submit">Giriş Yap</button>
</form>
<?php if (!empty($error)): ?>
  <p class="small" style="color:#dc2626"><?php echo Helpers::e($error); ?></p>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Giriş';
include __DIR__ . '/../layout.php';
?>
