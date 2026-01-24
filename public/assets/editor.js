(() => {
  const stage = document.getElementById('editorStage');
  if (!stage) return;

  const templateData = JSON.parse(stage.dataset.template || '{}');
  const fieldsData = JSON.parse(stage.dataset.fields || '{}');
  const overlayUrl = stage.dataset.overlay;
  const scale = Math.min(1, 900 / (templateData.width || 1));

  stage.style.width = `${templateData.width * scale}px`;
  stage.style.height = `${templateData.height * scale}px`;
  stage.style.backgroundImage = overlayUrl ? `url(${overlayUrl})` : 'none';
  stage.style.backgroundSize = '100% 100%';

  const boxes = {
    headline: { ...fieldsData.headline },
    subhead: { ...fieldsData.subhead }
  };

  const form = {
    key: document.getElementById('fieldKey'),
    x: document.getElementById('fieldX'),
    y: document.getElementById('fieldY'),
    w: document.getElementById('fieldW'),
    h: document.getElementById('fieldH'),
    padding: document.getElementById('fieldPadding'),
    font: document.getElementById('fieldFont'),
    base: document.getElementById('fieldBase'),
    min: document.getElementById('fieldMin'),
    lines: document.getElementById('fieldLines'),
    lineHeight: document.getElementById('fieldLineHeight'),
    color: document.getElementById('fieldColor'),
    align: document.getElementById('fieldAlign'),
    valign: document.getElementById('fieldValign'),
    stroke: document.getElementById('fieldStroke'),
    strokeWidth: document.getElementById('fieldStrokeWidth'),
    strokeColor: document.getElementById('fieldStrokeColor'),
    shadow: document.getElementById('fieldShadow'),
    shadowX: document.getElementById('fieldShadowX'),
    shadowY: document.getElementById('fieldShadowY'),
    shadowBlur: document.getElementById('fieldShadowBlur'),
    shadowColor: document.getElementById('fieldShadowColor'),
    drawOrder: document.getElementById('fieldDrawOrder')
  };

  const boxEls = {
    headline: stage.querySelector('[data-key="headline"]'),
    subhead: stage.querySelector('[data-key="subhead"]')
  };

  let activeKey = form.key.value || 'headline';

  function applyBoxStyle(key) {
    const box = boxes[key];
    const el = boxEls[key];
    if (!box || !el) return;
    el.style.left = `${box.x * scale}px`;
    el.style.top = `${box.y * scale}px`;
    el.style.width = `${box.w * scale}px`;
    el.style.height = `${box.h * scale}px`;
  }

  function loadForm() {
    const box = boxes[activeKey];
    if (!box) return;
    form.key.value = activeKey;
    form.x.value = Math.round(box.x);
    form.y.value = Math.round(box.y);
    form.w.value = Math.round(box.w);
    form.h.value = Math.round(box.h);
    form.padding.value = box.padding || 0;
    form.font.value = box.font_id || '';
    form.base.value = box.base_font_size || 48;
    form.min.value = box.min_font_size || 18;
    form.lines.value = box.max_lines || 3;
    form.lineHeight.value = box.line_height || 1.2;
    form.color.value = box.color || '#ffffff';
    form.align.value = box.align || 'left';
    form.valign.value = box.valign || 'top';
    form.stroke.value = box.stroke_enabled || 0;
    form.strokeWidth.value = box.stroke_width || 2;
    form.strokeColor.value = box.stroke_color || '#000000';
    form.shadow.value = box.shadow_enabled || 0;
    form.shadowX.value = box.shadow_x || 2;
    form.shadowY.value = box.shadow_y || 2;
    form.shadowBlur.value = box.shadow_blur || 4;
    form.shadowColor.value = box.shadow_color || '#000000';
    form.drawOrder.value = box.draw_order || 1;
  }

  function syncBox() {
    const box = boxes[activeKey];
    if (!box) return;
    box.x = parseInt(form.x.value || 0, 10);
    box.y = parseInt(form.y.value || 0, 10);
    box.w = parseInt(form.w.value || 0, 10);
    box.h = parseInt(form.h.value || 0, 10);
    box.padding = parseInt(form.padding.value || 0, 10);
    box.font_id = parseInt(form.font.value || 0, 10) || null;
    box.base_font_size = parseInt(form.base.value || 0, 10);
    box.min_font_size = parseInt(form.min.value || 0, 10);
    box.max_lines = parseInt(form.lines.value || 0, 10);
    box.line_height = parseFloat(form.lineHeight.value || 1.2);
    box.color = form.color.value;
    box.align = form.align.value;
    box.valign = form.valign.value;
    box.stroke_enabled = parseInt(form.stroke.value || 0, 10);
    box.stroke_width = parseInt(form.strokeWidth.value || 0, 10);
    box.stroke_color = form.strokeColor.value;
    box.shadow_enabled = parseInt(form.shadow.value || 0, 10);
    box.shadow_x = parseInt(form.shadowX.value || 0, 10);
    box.shadow_y = parseInt(form.shadowY.value || 0, 10);
    box.shadow_blur = parseInt(form.shadowBlur.value || 0, 10);
    box.shadow_color = form.shadowColor.value;
    box.draw_order = parseInt(form.drawOrder.value || 1, 10);
    applyBoxStyle(activeKey);
  }

  function bindDrag(key) {
    const el = boxEls[key];
    if (!el) return;
    const handle = el.querySelector('.editor-handle');
    let mode = null;
    let start = null;

    const onMove = (event) => {
      if (!start) return;
      const dx = (event.clientX - start.x) / scale;
      const dy = (event.clientY - start.y) / scale;
      const box = boxes[key];
      if (!box) return;
      if (mode === 'move') {
        box.x = Math.max(0, start.box.x + dx);
        box.y = Math.max(0, start.box.y + dy);
      } else if (mode === 'resize') {
        box.w = Math.max(40, start.box.w + dx);
        box.h = Math.max(40, start.box.h + dy);
      }
      applyBoxStyle(key);
      if (key === activeKey) {
        loadForm();
      }
    };

    const stop = () => {
      mode = null;
      start = null;
      document.removeEventListener('mousemove', onMove);
      document.removeEventListener('mouseup', stop);
    };

    el.addEventListener('mousedown', (event) => {
      if (event.target === handle) return;
      activeKey = key;
      form.key.value = key;
      loadForm();
      mode = 'move';
      start = {
        x: event.clientX,
        y: event.clientY,
        box: { ...boxes[key] }
      };
      document.addEventListener('mousemove', onMove);
      document.addEventListener('mouseup', stop);
    });

    handle.addEventListener('mousedown', (event) => {
      event.stopPropagation();
      activeKey = key;
      form.key.value = key;
      loadForm();
      mode = 'resize';
      start = {
        x: event.clientX,
        y: event.clientY,
        box: { ...boxes[key] }
      };
      document.addEventListener('mousemove', onMove);
      document.addEventListener('mouseup', stop);
    });
  }

  form.key.addEventListener('change', (e) => {
    activeKey = e.target.value;
    loadForm();
  });

  Object.values(form).forEach((input) => {
    if (!input) return;
    input.addEventListener('input', () => {
      syncBox();
    });
  });

  Object.keys(boxes).forEach((key) => {
    applyBoxStyle(key);
    bindDrag(key);
  });

  loadForm();

  document.getElementById('saveFields').addEventListener('click', async (e) => {
    e.preventDefault();
    syncBox();
    const res = await fetch(`/admin/templates/${templateData.id}/fields`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ fields: boxes })
    });
    if (res.ok) {
      alert('Fields saved');
    } else {
      alert('Save failed');
    }
  });

  document.getElementById('testRender').addEventListener('click', async (e) => {
    e.preventDefault();
    const res = await fetch(`/admin/templates/${templateData.id}/test-render`, { method: 'POST' });
    if (res.ok) {
      const payload = await res.json();
      document.getElementById('testPreview').innerHTML = `<img src="${payload.preview_url}" alt="Preview">`;
    } else {
      alert('Test render failed');
    }
  });
})();
