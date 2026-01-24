(() => {
  const stage = document.getElementById('editorStage');
  if (stage) {
    const templateData = JSON.parse(stage.dataset.template || '{}');
    const fieldsData = JSON.parse(stage.dataset.fields || '{}');
    const overlayUrl = stage.dataset.overlay || '';
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

    let activeKey = form.key ? form.key.value : 'headline';

    const applyBoxStyle = (key) => {
      const box = boxes[key];
      const el = boxEls[key];
      if (!box || !el) return;
      el.style.left = `${box.x * scale}px`;
      el.style.top = `${box.y * scale}px`;
      el.style.width = `${box.w * scale}px`;
      el.style.height = `${box.h * scale}px`;
    };

    const loadForm = () => {
      const box = boxes[activeKey];
      if (!box || !form.key) return;
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
    };

    const syncBox = () => {
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
    };

    const bindDrag = (key) => {
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
        document.removeEventListener('touchmove', onMove);
        document.removeEventListener('touchend', stop);
      };

      const startDrag = (event, dragMode) => {
        const point = event.touches ? event.touches[0] : event;
        activeKey = key;
        if (form.key) {
          form.key.value = key;
          loadForm();
        }
        mode = dragMode;
        start = {
          x: point.clientX,
          y: point.clientY,
          box: { ...boxes[key] }
        };
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', stop);
        document.addEventListener('touchmove', onMove, { passive: false });
        document.addEventListener('touchend', stop);
      };

      el.addEventListener('mousedown', (event) => {
        if (event.target === handle) return;
        startDrag(event, 'move');
      });

      el.addEventListener('touchstart', (event) => {
        if (event.target === handle) return;
        startDrag(event, 'move');
      });

      if (handle) {
        handle.addEventListener('mousedown', (event) => {
          event.stopPropagation();
          startDrag(event, 'resize');
        });
        handle.addEventListener('touchstart', (event) => {
          event.stopPropagation();
          startDrag(event, 'resize');
        });
      }
    };

    if (form.key) {
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
    }

    Object.keys(boxes).forEach((key) => {
      applyBoxStyle(key);
      bindDrag(key);
    });

    loadForm();

    const saveBtn = document.getElementById('saveFields');
    if (saveBtn) {
      saveBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        syncBox();
        const res = await fetch(`/admin/templates/${templateData.id}/fields`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ fields: boxes })
        });
        alert(res.ok ? 'Alanlar kaydedildi' : 'Kaydetme başarısız');
      });
    }

    const testBtn = document.getElementById('testRender');
    if (testBtn) {
      testBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        const res = await fetch(`/admin/templates/${templateData.id}/test-render`, { method: 'POST' });
        if (res.ok) {
          const payload = await res.json();
          const preview = document.getElementById('testPreview');
          if (preview) {
            preview.innerHTML = `<img src="${payload.preview_url}" alt="Önizleme">`;
          }
        } else {
          alert('Test render başarısız');
        }
      });
    }
  }

  const dropzone = document.getElementById('photoDropzone');
  const input = document.getElementById('photoInput');
  const filename = document.getElementById('photoFilename');
  if (dropzone && input) {
    const updateName = (file) => {
      if (filename) {
        filename.textContent = file ? file.name : 'Dosya seçilmedi';
      }
    };

    input.addEventListener('change', () => {
      updateName(input.files && input.files[0] ? input.files[0] : null);
    });

    dropzone.addEventListener('dragover', (event) => {
      event.preventDefault();
      dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', () => {
      dropzone.classList.remove('dragover');
    });

    dropzone.addEventListener('drop', (event) => {
      event.preventDefault();
      dropzone.classList.remove('dragover');
      const file = event.dataTransfer.files[0];
      if (!file) return;
      const dt = new DataTransfer();
      dt.items.add(file);
      input.files = dt.files;
      updateName(file);
    });
  }

  const canvas = document.getElementById('photoCanvas');
  if (canvas) {
    const ctx = canvas.getContext('2d');
    const zoomInput = document.getElementById('photoZoom');
    const zoomLabel = document.getElementById('photoZoomLabel');
    const offsetXInput = document.getElementById('photoOffsetX');
    const offsetYInput = document.getElementById('photoOffsetY');
    const templateSelect = document.getElementById('templateSelect');

    let img = null;
    let dragging = false;
    let last = { x: 0, y: 0 };
    let zoom = 1;
    let offset = { x: 0, y: 0 };
    let templateSize = { w: canvas.width, h: canvas.height };

    const draw = () => {
      if (!ctx) return;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.fillStyle = '#111';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      if (!img) return;

      const baseScale = Math.max(canvas.width / img.width, canvas.height / img.height);
      const scale = baseScale * zoom;
      const drawW = img.width * scale;
      const drawH = img.height * scale;
      const x = (canvas.width - drawW) / 2 + offset.x;
      const y = (canvas.height - drawH) / 2 + offset.y;
      ctx.drawImage(img, x, y, drawW, drawH);
    };

    const updateHidden = () => {
      if (offsetXInput && offsetYInput) {
        const scaleRatio = templateSize.w / canvas.width;
        offsetXInput.value = Math.round(offset.x * scaleRatio);
        offsetYInput.value = Math.round(offset.y * scaleRatio);
      }
    };

    const updateZoom = (value) => {
      zoom = value;
      if (zoomLabel) {
        zoomLabel.textContent = `${Math.round(zoom * 100)}%`;
      }
      if (document.getElementById('photoZoomValue')) {
        document.getElementById('photoZoomValue').value = zoom.toFixed(2);
      }
      draw();
      updateHidden();
    };

    const loadTemplate = async () => {
      if (!templateSelect) return;
      const id = templateSelect.value;
      const res = await fetch(`/templates/${id}`);
      if (!res.ok) return;
      const data = await res.json();
      templateSize = { w: data.template.width, h: data.template.height };
      canvas.width = Math.min(600, templateSize.w);
      canvas.height = Math.round(canvas.width * (templateSize.h / templateSize.w));
      offset = { x: 0, y: 0 };
      updateHidden();
      draw();
    };

    if (templateSelect) {
      templateSelect.addEventListener('change', loadTemplate);
      loadTemplate();
    }

    if (input) {
      input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
          img = new Image();
          img.onload = () => {
            draw();
          };
          img.src = e.target.result;
        };
        reader.readAsDataURL(file);
      });
    }

    if (zoomInput) {
      zoomInput.addEventListener('input', (e) => {
        updateZoom(parseFloat(e.target.value));
      });
      updateZoom(parseFloat(zoomInput.value || '1'));
    }

    const startDrag = (event) => {
      dragging = true;
      const point = event.touches ? event.touches[0] : event;
      last = { x: point.clientX, y: point.clientY };
    };

    const moveDrag = (event) => {
      if (!dragging) return;
      event.preventDefault();
      const point = event.touches ? event.touches[0] : event;
      offset.x += point.clientX - last.x;
      offset.y += point.clientY - last.y;
      last = { x: point.clientX, y: point.clientY };
      draw();
      updateHidden();
    };

    const stopDrag = () => { dragging = false; };

    canvas.addEventListener('mousedown', startDrag);
    canvas.addEventListener('mousemove', moveDrag);
    canvas.addEventListener('mouseup', stopDrag);
    canvas.addEventListener('mouseleave', stopDrag);
    canvas.addEventListener('touchstart', startDrag);
    canvas.addEventListener('touchmove', moveDrag, { passive: false });
    canvas.addEventListener('touchend', stopDrag);
  }
})();
