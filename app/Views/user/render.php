<?php
use App\Core\Helpers;

ob_start();
?>
<h1>Render Oluştur</h1>
<div class="container">
  <div class="panel">
    <form method="post" action="/render/image" enctype="multipart/form-data">
      <?php echo $csrf->field(); ?>
      <label>Şablon</label>
      <select id="templateSelect" name="template_id" required>
        <?php foreach ($templates as $template): ?>
          <option value="<?php echo (int)$template['id']; ?>"><?php echo Helpers::e($template['name']); ?></option>
        <?php endforeach; ?>
      </select>

      <label>Fotoğraf</label>
      <div id="photoDropzone" class="dropzone">
        <input id="photoInput" type="file" name="photo" accept="image/*" required>
        <p>Fotoğrafı sürükleyip bırakın veya tıklayın.</p>
        <span class="small" id="photoFilename">Dosya seçilmedi</span>
      </div>

      <label>Başlık</label>
      <textarea name="headline" rows="2"></textarea>
      <label>Alt Başlık</label>
      <textarea name="subhead" rows="3"></textarea>

      <input type="hidden" name="photo_offset_x" id="photoOffsetX" value="0">
      <input type="hidden" name="photo_offset_y" id="photoOffsetY" value="0">
      <input type="hidden" name="photo_zoom" id="photoZoomValue" value="1">

      <button type="submit">Render Al</button>
      <p class="small">Tahmini süre: <?php echo (int)$minSeconds; ?> sn</p>
    </form>
  </div>

  <div class="panel">
    <h3>Fotoğraf Kadrajı</h3>
    <div class="canvas-wrap">
      <canvas id="photoCanvas" width="600" height="400"></canvas>
    </div>
    <div class="range">
      <label>Zoom</label>
      <input id="photoZoom" type="range" min="1" max="2" step="0.01" value="1">
      <span id="photoZoomLabel">100%</span>
    </div>
    <p class="small">Fotoğrafı sürükleyerek konumlandırın. Zoom %100-200 arasıdır.</p>
  </div>
</div>

<div class="panel">
  <h3>Son Renderlarım</h3>
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Şablon</th>
        <th>Durum</th>
        <th>Önizleme</th>
        <th>İndir</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($renders)): ?>
        <tr><td colspan="5">Render yok</td></tr>
      <?php endif; ?>
      <?php foreach ($renders as $render): ?>
        <tr>
          <td><?php echo (int)$render['id']; ?></td>
          <td><?php echo Helpers::e($render['template_name'] ?? ''); ?></td>
          <td><?php echo Helpers::e($render['status']); ?></td>
          <td>
            <?php if (!empty($render['preview_path'])): ?>
              <img src="/download/<?php echo Helpers::e(pathinfo($render['preview_path'], PATHINFO_FILENAME)); ?>" width="120" alt="Önizleme">
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($render['output_path'])): ?>
              <a class="button-link" href="/download/<?php echo (int)$render['id']; ?>">İndir</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
$title = 'Render';
include __DIR__ . '/../layout.php';
?>
