<?php
use App\Core\Helpers;

ob_start();
?>
<h1>Admin Dashboard</h1>
<div class="container">
  <div class="panel">
    <h3>Özet</h3>
    <p>Son 7 gün: <strong><?php echo (int)$last7; ?></strong></p>
    <p>Son 30 gün: <strong><?php echo (int)$last30; ?></strong></p>
    <p>Ortalama render süresi: <strong><?php echo (int)$avgDuration; ?> ms</strong></p>
  </div>
  <div class="panel">
    <h3>Üye Bazlı Render</h3>
    <table class="table">
      <thead><tr><th>Üye</th><th>E-posta</th><th>Toplam</th></tr></thead>
      <tbody>
        <?php foreach ($userStats as $row): ?>
          <tr>
            <td><?php echo Helpers::e($row['name']); ?></td>
            <td><?php echo Helpers::e($row['email']); ?></td>
            <td><?php echo (int)$row['total']; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel">
  <h3>Son Renderlar</h3>
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Üye</th>
        <th>Şablon</th>
        <th>Durum</th>
        <th>Süre (ms)</th>
        <th>İşlem</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recentRenders as $render): ?>
        <tr>
          <td><?php echo (int)$render['id']; ?></td>
          <td><?php echo Helpers::e($render['user_name'] ?? ''); ?></td>
          <td><?php echo Helpers::e($render['template_name'] ?? ''); ?></td>
          <td><?php echo Helpers::e($render['status']); ?></td>
          <td><?php echo (int)($render['duration_ms'] ?? 0); ?></td>
          <td>
            <form method="post" action="/admin/renders/delete/<?php echo (int)$render['id']; ?>">
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
$title = 'Dashboard';
include __DIR__ . '/../layout.php';
?>
