<?php
use App\Core\Helpers;

ob_start();
?>
<h1>Render Image</h1>
<form method="post" action="/render/image" enctype="multipart/form-data">
  <?php echo $csrf->field(); ?>
  <label>Template</label>
  <select name="template_id" required>
    <?php foreach ($templates as $template): ?>
      <option value="<?php echo (int)$template['id']; ?>"><?php echo Helpers::e($template['name']); ?></option>
    <?php endforeach; ?>
  </select>
  <label>Photo</label>
  <input type="file" name="photo" required>
  <label>Headline</label>
  <textarea name="headline" rows="2"></textarea>
  <label>Subhead</label>
  <textarea name="subhead" rows="3"></textarea>
  <button type="submit">Create Render</button>
</form>

<h2>Your Recent Renders</h2>
<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Status</th>
      <th>Preview</th>
      <th>Download</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($renders)): ?>
      <tr><td colspan="4">No renders yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($renders as $render): ?>
      <tr>
        <td><?php echo (int)$render['id']; ?></td>
        <td><?php echo Helpers::e($render['status']); ?></td>
        <td>
          <?php if (!empty($render['preview_path'])): ?>
            <img src="/preview/<?php echo Helpers::e($render['preview_path']); ?>" width="120" alt="Preview">
          <?php endif; ?>
        </td>
        <td>
          <?php if (!empty($render['output_path'])): ?>
            <a href="/download/<?php echo (int)$render['id']; ?>">Download</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php
$content = ob_get_clean();
$title = 'Render';
include __DIR__ . '/../../layout.php';
?>
