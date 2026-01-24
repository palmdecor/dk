<?php
use App\Core\Helpers;

ob_start();
?>
<h1>Üyeler</h1>
<div class="panel">
  <h3>Yeni Üye</h3>
  <form method="post" action="/admin/users/create">
    <?php echo $csrf->field(); ?>
    <label>Ad Soyad</label>
    <input name="name" required>
    <label>E-posta</label>
    <input name="email" required>
    <label>Şifre</label>
    <input type="password" name="password" required>
    <label>Rol</label>
    <select name="role">
      <option value="user">Üye</option>
      <option value="admin">Admin</option>
    </select>
    <button type="submit">Oluştur</button>
  </form>
</div>
<div class="panel">
  <h3>Üye Listesi</h3>
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Ad</th>
        <th>E-posta</th>
        <th>Rol</th>
        <th>Durum</th>
        <th>İşlem</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user): ?>
        <tr>
          <td><?php echo (int)$user['id']; ?></td>
          <td><?php echo Helpers::e($user['name']); ?></td>
          <td><?php echo Helpers::e($user['email']); ?></td>
          <td><?php echo Helpers::e($user['role']); ?></td>
          <td><?php echo Helpers::e($user['status']); ?></td>
          <td>
            <form method="post" action="/admin/users/toggle/<?php echo (int)$user['id']; ?>">
              <?php echo $csrf->field(); ?>
              <button class="secondary" type="submit">Aktif/Pasif</button>
            </form>
            <form method="post" action="/admin/users/delete/<?php echo (int)$user['id']; ?>">
              <?php echo $csrf->field(); ?>
              <button class="danger" type="submit">Sil</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
$title = 'Üyeler';
include __DIR__ . '/../layout.php';
?>
