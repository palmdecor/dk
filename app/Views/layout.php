<?php
use App\Core\Helpers;
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
      <a href="/templates">Templates</a>
      <?php if ($auth->isAdmin()): ?>
        <a href="/admin/templates">Admin Templates</a>
        <a href="/admin/templates/create">New Template</a>
        <a href="/admin/fonts">Fonts</a>
      <?php endif; ?>
      <form method="post" action="/logout" style="display:inline">
        <?php echo $csrf->field(); ?>
        <button type="submit">Logout</button>
      </form>
    <?php else: ?>
      <a href="/login">Login</a>
    <?php endif; ?>
  </nav>
  <?php echo $content; ?>
</body>
</html>
