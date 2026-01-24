<?php
use App\Core\Helpers;

$error = $session->flash('error');
$success = $session->flash('success');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo Helpers::e($title ?? 'Invenio'); ?></title>
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
  <nav>
    <?php if ($auth->check()): ?>
      <a href="/render">Render</a>
      <?php if ($auth->isAdmin()): ?>
        <a href="/admin/dashboard">Dashboard</a>
        <a href="/admin/users">Üyeler</a>
        <a href="/admin/templates">Şablonlar</a>
        <a href="/admin/fonts">Fontlar</a>
        <a href="/admin/settings">Ayarlar</a>
      <?php endif; ?>
      <form method="post" action="/logout" style="display:inline">
        <?php echo $csrf->field(); ?>
        <button type="submit">Çıkış</button>
      </form>
    <?php else: ?>
      <a href="/login">Giriş</a>
    <?php endif; ?>
  </nav>
  <?php if ($error): ?>
    <div class="notice notice-error"><?php echo Helpers::e($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="notice notice-success"><?php echo Helpers::e($success); ?></div>
  <?php endif; ?>
  <?php echo $content; ?>
  <script src="/assets/app.js"></script>
</body>
</html>
