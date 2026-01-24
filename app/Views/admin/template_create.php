<?php
ob_start();
?>
<h1>Şablon Oluştur</h1>
<form method="post" action="/admin/templates">
  <?php echo $csrf->field(); ?>
  <label>Ad</label>
  <input name="name" required>
  <label>Genişlik</label>
  <input type="number" name="width" required>
  <label>Yükseklik</label>
  <input type="number" name="height" required>
  <label>Medya Yerleşimi</label>
  <select name="media_fit_mode">
    <option value="cover">cover</option>
    <option value="contain">contain</option>
  </select>
  <label>Arka Plan Rengi</label>
  <input name="background_color" value="#000000">
  <label>Çıktı Formatı</label>
  <select name="export_format">
    <option value="jpg">jpg</option>
    <option value="png">png</option>
  </select>
  <button type="submit">Kaydet</button>
</form>
<?php
$content = ob_get_clean();
$title = 'Şablon Oluştur';
include __DIR__ . '/../layout.php';
?>
