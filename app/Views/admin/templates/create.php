<?php
ob_start();
?>
<h1>Create Template</h1>
<form method="post" action="/admin/templates">
  <?php echo $csrf->field(); ?>
  <label>Name</label>
  <input name="name" required>
  <label>Width</label>
  <input type="number" name="width" required>
  <label>Height</label>
  <input type="number" name="height" required>
  <label>Media fit mode</label>
  <select name="media_fit_mode">
    <option value="cover">cover</option>
    <option value="contain">contain</option>
  </select>
  <label>Background color (contain)</label>
  <input name="background_color" value="#000000">
  <label>Export format</label>
  <select name="export_format">
    <option value="jpg">jpg</option>
    <option value="png">png</option>
  </select>
  <button type="submit">Create</button>
</form>
<?php
$content = ob_get_clean();
$title = 'Create Template';
include __DIR__ . '/../../layout.php';
?>
