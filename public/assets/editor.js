(() => {
  const canvas = document.getElementById('templateCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const data = JSON.parse(canvas.dataset.template);
  const fields = JSON.parse(canvas.dataset.fields);
  const overlayUrl = canvas.dataset.overlay;
  const scale = Math.min(1, 900 / data.width);
  canvas.style.width = `${data.width * scale}px`;
  canvas.style.height = `${data.height * scale}px`;

  const fieldDefaults = {
    headline: {
      field_key: 'headline',
      x: 50, y: 50, w: 500, h: 200,
      padding: 0,
      font_id: null,
      base_font_size: 48,
      min_font_size: 20,
      max_lines: 3,
      line_height: 1.2,
      color: '#ffffff',
      align: 'left',
      valign: 'top',
      stroke_enabled: 0,
      stroke_width: 2,
      stroke_color: '#000000',
      shadow_enabled: 0,
      shadow_x: 2,
      shadow_y: 2,
      shadow_blur: 4,
      shadow_color: '#000000',
      draw_order: 1
    },
    subhead: {
      field_key: 'subhead',
      x: 50, y: 300, w: 500, h: 160,
      padding: 0,
      font_id: null,
      base_font_size: 32,
      min_font_size: 16,
      max_lines: 3,
      line_height: 1.2,
      color: '#ffffff',
      align: 'left',
      valign: 'top',
      stroke_enabled: 0,
      stroke_width: 2,
      stroke_color: '#000000',
      shadow_enabled: 0,
      shadow_x: 2,
      shadow_y: 2,
      shadow_blur: 4,
      shadow_color: '#000000',
      draw_order: 2
    }
  };

  const boxes = {
    headline: fields.headline ? { ...fieldDefaults.headline, ...fields.headline } : { ...fieldDefaults.headline },
    subhead: fields.subhead ? { ...fieldDefaults.subhead, ...fields.subhead } : { ...fieldDefaults.subhead }
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

  let activeKey = form.key.value || 'headline';

  function loadForm() {
    const box = boxes[activeKey];
    form.key.value = activeKey;
    form.x.value = Math.round(box.x);
    form.y.value = Math.round(box.y);
    form.w.value = Math.round(box.w);
    form.h.value = Math.round(box.h);
    form.padding.value = box.padding;
    form.font.value = box.font_id || '';
    form.base.value = box.base_font_size;
    form.min.value = box.min_font_size;
    form.lines.value = box.max_lines;
    form.lineHeight.value = box.line_height;
    form.color.value = box.color;
    form.align.value = box.align;
    form.valign.value = box.valign;
    form.stroke.value = box.stroke_enabled;
    form.strokeWidth.value = box.stroke_width;
    form.strokeColor.value = box.stroke_color;
    form.shadow.value = box.shadow_enabled;
    form.shadowX.value = box.shadow_x;
    form.shadowY.value = box.shadow_y;
    form.shadowBlur.value = box.shadow_blur;
    form.shadowColor.value = box.shadow_color;
    form.drawOrder.value = box.draw_order;
  }

  function syncBox() {
    const box = boxes[activeKey];
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
  }

  form.key.addEventListener('change', (e) => {
    activeKey = e.target.value;
    loadForm();
    draw();
  });

  Object.values(form).forEach((input) => {
    if (!input) return;
    input.addEventListener('input', () => {
      syncBox();
      draw();
    });
  });

  let drag = null;
  canvas.addEventListener('mousedown', (e) => {
    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX - rect.left) / scale;
    const y = (e.clientY - rect.top) / scale;
    const box = boxes[activeKey];
    const cornerSize = 12;
    const isInCorner = x > box.x + box.w - cornerSize && y > box.y + box.h - cornerSize;
    if (x > box.x && x < box.x + box.w && y > box.y && y < box.y + box.h) {
      drag = { mode: isInCorner ? 'resize' : 'move', startX: x, startY: y, box: { ...box } };
    }
  });

  canvas.addEventListener('mousemove', (e) => {
    if (!drag) return;
    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX - rect.left) / scale;
    const y = (e.clientY - rect.top) / scale;
    if (drag.mode === 'move') {
      boxes[activeKey].x = Math.max(0, drag.box.x + (x - drag.startX));
      boxes[activeKey].y = Math.max(0, drag.box.y + (y - drag.startY));
    } else {
      boxes[activeKey].w = Math.max(10, drag.box.w + (x - drag.startX));
      boxes[activeKey].h = Math.max(10, drag.box.h + (y - drag.startY));
    }
    loadForm();
    draw();
  });

  canvas.addEventListener('mouseup', () => { drag = null; });
  canvas.addEventListener('mouseleave', () => { drag = null; });

  function drawOverlay(img) {
    ctx.drawImage(img, 0, 0, data.width, data.height);
  }

  function drawBoxes() {
    ['headline', 'subhead'].forEach((key) => {
      const box = boxes[key];
      ctx.strokeStyle = key === 'headline' ? '#4da3ff' : '#5fd28d';
      ctx.lineWidth = 4;
      ctx.strokeRect(box.x, box.y, box.w, box.h);
    });
  }

  function draw() {
    ctx.clearRect(0, 0, data.width, data.height);
    if (overlayUrl) {
      const img = new Image();
      img.onload = () => {
        drawOverlay(img);
        drawBoxes();
      };
      img.src = overlayUrl;
    } else {
      drawBoxes();
    }
  }

  loadForm();
  draw();

  document.getElementById('saveFields').addEventListener('click', async (e) => {
    e.preventDefault();
    syncBox();
    const res = await fetch(`/admin/templates/${data.id}/fields`, {
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
    const res = await fetch(`/admin/templates/${data.id}/test-render`, { method: 'POST' });
    if (res.ok) {
      const payload = await res.json();
      document.getElementById('testPreview').innerHTML = `<img src="${payload.preview_url}" alt="Preview">`;
    } else {
      alert('Test render failed');
    }
  });
})();
