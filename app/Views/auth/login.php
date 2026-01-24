<?php
use App\Core\Helpers;

ob_start();
?>
<h1>Login</h1>
<form method="post" action="/login">
  <?php echo $csrf->field(); ?>
  <label>Email</label>
  <input name="email" required>
  <label>Password</label>
  <input type="password" name="password" required>
  <button type="submit">Login</button>
</form>
<?php if (!empty($error)): ?>
  <p style="color:red"><?php echo Helpers::e($error); ?></p>
<?php endif; ?>
<p class="small">Create the first admin user by inserting into the users table (see README).</p>
<?php
$content = ob_get_clean();
$title = 'Login';
include __DIR__ . '/../layout.php';
?>
