<?php
use App\Core\Helpers;

$overlayUrl = $template['overlay_png_path'] ? '/download/overlay_' . (int)$template['id'] : '';

ob_start();
?>
<h1>Şablon Düzenle: <?php echo Helpers::e($template['name']); ?></h1>
<div class="container">
  <div class="panel">
    <div class="notice">
      <strong>Overlay PNG:</strong> <?php echo $template['overlay_png_path'] ? 'Yüklü' : 'Eksik'; ?>
    </div>
    <form method="post" action="/admin/templates/<?php echo (int)$template['id']; ?>/overlay" enctype="multipart/form-data">
      <?php echo $csrf->field(); ?>
      <label>Overlay PNG Yükle</label>
      <input type="file" name="overlay" required>
      <button type="submit">Yükle</button>
    </form>

    <h3>Drag & Drop Editör</h3>
    <div
      id="editorStage"
      data-template='<?php echo json_encode($template, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP); ?>'
      data-fields='<?php echo json_encode($fields, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP); ?>'
      data-overlay="<?php echo Helpers::e($overlayUrl); ?>"
      class="editor-stage"
    >
      <div class="editor-box editor-headline" data-key="headline">
        <span class="editor-label">HEADLINE</span>
        <div class="editor-handle"></div>
      </div>
      <div class="editor-box editor-subhead" data-key="subhead">
        <span class="editor-label">SUBHEAD</span>
        <div class="editor-handle"></div>
      </div>
    </div>
    <p class="small">Kutuları sürükle ve yeniden boyutlandır. Kaydet ile sunucuya gönder.</p>
    <button id="saveFields">Alanları Kaydet</button>
    <button id="testRender">Test Render</button>
    <div id="testPreview" class="preview"></div>
  </div>

  <div class="panel">
    <h3>Seçili Alan</h3>
    <div class="field-grid">
      <div>
        <label>Alan</label>
        <select id="fieldKey">
          <option value="headline">headline</option>
          <option value="subhead">subhead</option>
        </select>
      </div>
      <div>
        <label>X</label>
        <input id="fieldX" type="number">
      </div>
      <div>
        <label>Y</label>
        <input id="fieldY" type="number">
      </div>
      <div>
        <label>Genişlik</label>
        <input id="fieldW" type="number">
      </div>
      <div>
        <label>Yükseklik</label>
        <input id="fieldH" type="number">
      </div>
      <div>
        <label>Padding</label>
        <input id="fieldPadding" type="number" value="0">
      </div>
      <div>
        <label>Font</label>
        <select id="fieldFont">
          <option value="">Seçiniz</option>
          <?php foreach ($fonts as $font): ?>
            <option value="<?php echo (int)$font['id']; ?>"><?php echo Helpers::e($font['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Base Font</label>
        <input id="fieldBase" type="number" value="48">
      </div>
      <div>
        <label>Min Font</label>
        <input id="fieldMin" type="number" value="18">
      </div>
      <div>
        <label>Max Satır</label>
        <input id="fieldLines" type="number" value="3">
      </div>
      <div>
        <label>Line Height</label>
        <input id="fieldLineHeight" type="number" step="0.05" value="1.2">
      </div>
      <div>
        <label>Renk</label>
        <input id="fieldColor" value="#ffffff">
      </div>
      <div>
        <label>Yatay Hizalama</label>
        <select id="fieldAlign">
          <option value="left">left</option>
          <option value="center">center</option>
          <option value="right">right</option>
        </select>
      </div>
      <div>
        <label>Dikey Hizalama</label>
        <select id="fieldValign">
          <option value="top">top</option>
          <option value="middle">middle</option>
          <option value="bottom">bottom</option>
        </select>
      </div>
      <div>
        <label>Stroke</label>
        <select id="fieldStroke">
          <option value="0">Kapalı</option>
          <option value="1">Açık</option>
        </select>
      </div>
      <div>
        <label>Stroke Width</label>
        <input id="fieldStrokeWidth" type="number" value="2">
      </div>
      <div>
        <label>Stroke Renk</label>
        <input id="fieldStrokeColor" value="#000000">
      </div>
      <div>
        <label>Shadow</label>
        <select id="fieldShadow">
          <option value="0">Kapalı</option>
          <option value="1">Açık</option>
        </select>
      </div>
      <div>
        <label>Shadow X</label>
        <input id="fieldShadowX" type="number" value="2">
      </div>
      <div>
        <label>Shadow Y</label>
        <input id="fieldShadowY" type="number" value="2">
      </div>
      <div>
        <label>Shadow Blur</label>
        <input id="fieldShadowBlur" type="number" value="4">
      </div>
      <div>
        <label>Shadow Renk</label>
        <input id="fieldShadowColor" value="#000000">
      </div>
      <div>
        <label>Draw Order</label>
        <input id="fieldDrawOrder" type="number" value="1">
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Şablon Düzenle';
include __DIR__ . '/../layout.php';
?>
