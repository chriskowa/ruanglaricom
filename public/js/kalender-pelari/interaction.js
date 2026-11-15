/**
 * Running Calendar - Interaction System
 * Handles hit detection, selection box, multi-selection, mouse & touch dragging,
 * 8-handle mouse resizing, canvas zoom, pan navigation, and coordinate transforms.
 */

function getVirtualCoords(e) {
  const rect = canvas.getBoundingClientRect();
  const clientX = e.touches ? e.touches[0].clientX : e.clientX;
  const clientY = e.touches ? e.touches[0].clientY : e.clientY;
  const scaleX = canvas.width / rect.width;
  const scaleY = canvas.height / rect.height;
  const screenX = (clientX - rect.left) * scaleX;
  const screenY = (clientY - rect.top) * scaleY;
  const wall = byId('layout').value === 'wall';
  const vW = wall ? 640 : 794;
  const vH = wall ? 960 : 530;
  return {
    x: screenX * (vW / canvas.width),
    y: screenY * (vH / canvas.height)
  };
}

function worldBox(b) {
  const points = [[b.x, b.y], [b.x + b.w, b.y], [b.x + b.w, b.y + b.h], [b.x, b.y + b.h]].map(([x, y]) => new DOMPoint(x, y).matrixTransform(b.matrix));
  const x = Math.min(...points.map(p => p.x)), y = Math.min(...points.map(p => p.y));
  return { x, y, w: Math.max(...points.map(p => p.x)) - x, h: Math.max(...points.map(p => p.y)) - y };
}

function selectionBox(keys = [...selectedElements]) {
  const boxes = keys.filter(k => elementBounds.has(k)).map(k => worldBox(elementBounds.get(k)));
  if (!boxes.length) return null;
  const x = Math.min(...boxes.map(b => b.x)), y = Math.min(...boxes.map(b => b.y));
  return { x, y, w: Math.max(...boxes.map(b => b.x + b.w)) - x, h: Math.max(...boxes.map(b => b.y + b.h)) - y };
}

function paintSelection(ctx, wall, target) {
  if (!selectedElements.size) return;
  const sx = target.width / (wall ? 640 : 794), sy = target.height / (wall ? 960 : 530);

  for (const key of selectedElements) {
    const b = elementBounds.get(key);
    if (!b) continue;
    const m = b.matrix;
    ctx.save();
    ctx.setTransform(sx, 0, 0, sy, 0, 0);
    ctx.transform(m.a, m.b, m.c, m.d, m.e, m.f);
    ctx.strokeStyle = isLocked(key) ? '#9a7a23' : '#2459d3';
    ctx.lineWidth = 1.3;
    ctx.setLineDash([5, 4]);
    ctx.strokeRect(b.x - 3, b.y - 3, b.w + 6, b.h + 6);
    ctx.setLineDash([]);
    ctx.restore();
  }

  // Draw 8 resize handles for single selected element
  if (selectedElements.size === 1) {
    const key = [...selectedElements][0];
    if (isResizable(key) && !isLocked(key)) {
      const b = elementBounds.get(key);
      if (!b) return;
      const m = b.matrix;
      const hs = 5;
      const corners = [
        { cx: b.x - 3, cy: b.y - 3, cursor: 'nw' },
        { cx: b.x + b.w + 3, cy: b.y - 3, cursor: 'ne' },
        { cx: b.x + b.w + 3, cy: b.y + b.h + 3, cursor: 'se' },
        { cx: b.x - 3, cy: b.y + b.h + 3, cursor: 'sw' },
        { cx: b.x + b.w / 2, cy: b.y - 3, cursor: 'n' },
        { cx: b.x + b.w + 3, cy: b.y + b.h / 2, cursor: 'e' },
        { cx: b.x + b.w / 2, cy: b.y + b.h + 3, cursor: 's' },
        { cx: b.x - 3, cy: b.y + b.h / 2, cursor: 'w' }
      ];
      ctx.save();
      ctx.setTransform(sx, 0, 0, sy, 0, 0);
      ctx.transform(m.a, m.b, m.c, m.d, m.e, m.f);
      for (const c of corners) {
        ctx.fillStyle = '#fff';
        ctx.fillRect(c.cx - hs, c.cy - hs, hs * 2, hs * 2);
        ctx.strokeStyle = '#2459d3';
        ctx.lineWidth = 1.5;
        ctx.strokeRect(c.cx - hs, c.cy - hs, hs * 2, hs * 2);
      }
      ctx.restore();
    }
  }

  if (selectedElements.size > 1) {
    const b = selectionBox();
    if (b) {
      ctx.save();
      ctx.setTransform(sx, 0, 0, sy, 0, 0);
      ctx.strokeStyle = '#2459d3';
      ctx.lineWidth = 1.2;
      ctx.setLineDash([8, 5]);
      ctx.strokeRect(b.x - 6, b.y - 6, b.w + 12, b.h + 12);
      ctx.restore();
    }
  }
}

function isResizable(key) {
  if (!key) return false;
  return key.startsWith('graphic:') || key.startsWith('custom:') || key === 'photo' || key.startsWith('photo:');
}

function getResizeHandles(key) {
  const b = elementBounds.get(key);
  if (!b || !isResizable(key) || isLocked(key)) return [];
  const hs = 6;
  return [
    { cursor: 'nwse-resize', handle: 'nw', cx: b.x - 3, cy: b.y - 3 },
    { cursor: 'nesw-resize', handle: 'ne', cx: b.x + b.w + 3, cy: b.y - 3 },
    { cursor: 'nwse-resize', handle: 'se', cx: b.x + b.w + 3, cy: b.y + b.h + 3 },
    { cursor: 'nesw-resize', handle: 'sw', cx: b.x - 3, cy: b.y + b.h + 3 },
    { cursor: 'ns-resize', handle: 'n', cx: b.x + b.w / 2, cy: b.y - 3 },
    { cursor: 'ew-resize', handle: 'e', cx: b.x + b.w + 3, cy: b.y + b.h / 2 },
    { cursor: 'ns-resize', handle: 's', cx: b.x + b.w / 2, cy: b.y + b.h + 3 },
    { cursor: 'ew-resize', handle: 'w', cx: b.x - 3, cy: b.y + b.h / 2 }
  ].map(h => ({ ...h, matrix: b.matrix, bounds: b, hs }));
}

function findResizeHandle(x, y) {
  if (selectedElements.size !== 1) return null;
  const key = [...selectedElements][0];
  const handles = getResizeHandles(key);
  for (const h of handles) {
    const p = new DOMPoint(x, y).matrixTransform(h.matrix.inverse());
    if (Math.abs(p.x - h.cx) <= h.hs && Math.abs(p.y - h.cy) <= h.hs) return h;
  }
  return null;
}

function applyResize(key, handle, dx, dy, startBounds, startObj) {
  const g = graphicForKey(key);
  const t = textForKey(key);
  const isPhoto = key === 'photo';
  if (!g && !t && !isPhoto) return;

  const b = startBounds;
  const m = b.matrix;
  const det = m.a * m.d - m.b * m.c;
  const ldx = (m.d * dx - m.c * dy) / det;
  const ldy = (-m.b * dx + m.a * dy) / det;

  let newX = startObj.x, newY = startObj.y, newW = startObj.w, newH = startObj.h;
  const minSize = 8;

  if (handle === 'se') { newW = Math.max(minSize, startObj.w + ldx); newH = Math.max(minSize, startObj.h + ldy); }
  else if (handle === 'nw') { const dw = Math.min(ldx, startObj.w - minSize); const dh = Math.min(ldy, startObj.h - minSize); newX = startObj.x + dw; newY = startObj.y + dh; newW = startObj.w - dw; newH = startObj.h - dh; }
  else if (handle === 'ne') { newW = Math.max(minSize, startObj.w + ldx); const dh = Math.min(ldy, startObj.h - minSize); newY = startObj.y + dh; newH = startObj.h - dh; }
  else if (handle === 'sw') { const dw = Math.min(ldx, startObj.w - minSize); newX = startObj.x + dw; newW = startObj.w - dw; newH = Math.max(minSize, startObj.h + ldy); }
  else if (handle === 'n') { const dh = Math.min(ldy, startObj.h - minSize); newY = startObj.y + dh; newH = startObj.h - dh; }
  else if (handle === 's') { newH = Math.max(minSize, startObj.h + ldy); }
  else if (handle === 'e') { newW = Math.max(minSize, startObj.w + ldx); }
  else if (handle === 'w') { const dw = Math.min(ldx, startObj.w - minSize); newX = startObj.x + dw; newW = startObj.w - dw; }

  if (g) {
    if (g.type === 'emoji' || g.type === 'icon') {
      const sz = Math.max(minSize, Math.max(newW, newH));
      newW = sz; newH = sz;
    } else if (g.type === 'png' && ['nw', 'ne', 'se', 'sw'].includes(handle)) {
      const asp = (startObj.w || 1) / (startObj.h || 1);
      newH = Math.max(minSize, Math.round(newW / asp));
    }
    g.x = Math.round(newX); g.y = Math.round(newY); g.w = Math.round(newW); g.h = Math.round(newH);
    graphicRevision++;
    if (byId('assetSize')) byId('assetSize').value = Math.round(g.w);
    if (byId('assetSizeValue')) byId('assetSizeValue').textContent = Math.round(g.w) + ' × ' + Math.round(g.h);
  } else if (t) {
    const scale = newW / (startObj.w || 1);
    t.size = Math.max(6, Math.min(200, Math.round(startObj.fontSize * scale)));
    if (handle.includes('w') || handle === 'nw' || handle === 'sw') t.x = Math.round(newX);
    if (handle.includes('n') || handle === 'nw' || handle === 'ne') t.y = Math.round(startObj.y + (newY - startObj.y));
    if (byId('customTextSize')) byId('customTextSize').value = t.size;
  } else if (isPhoto) {
    const scaleW = newW / startObj.w;
    const conf = getPageCollage(selected());
    const slot = conf.slots[conf.activeSlot] || conf.slots[0];
    slot.crop.z = Math.max(1, Math.min(3, (startObj.cropZ || 1) * scaleW));
    if (conf.activeSlot === 0) crops.set(selected(), slot.crop);
    byId('zoom').value = slot.crop.z;
  }
  render();
}

function findHit(x, y) {
  const list = [...elementBounds.values()].reverse().filter(b => !isLocked(b.key));
  list.sort((a, b) => {
    const aa = a.key.startsWith('graphic:') || a.key.startsWith('custom:'), bb = b.key.startsWith('graphic:') || b.key.startsWith('custom:');
    return Number(bb) - Number(aa) || (aa && bb ? 0 : a.w * a.h - b.w * b.h);
  });
  for (const b of list) {
    const p = new DOMPoint(x, y).matrixTransform(b.matrix.inverse());
    if (p.x >= b.x - 4 && p.x <= b.x + b.w + 4 && p.y >= b.y - 4 && p.y <= b.y + b.h + 4) return b;
  }
  return null;
}

function getPosition(key = activeElement) {
  const g = graphicForKey(key), t = textForKey(key);
  if (g || t) return { x: (g || t).x, y: (g || t).y };
  return elemOffsets.get(selected())?.[key] || { x: 0, y: 0 };
}

function setPosition(x, y, key = activeElement, redraw = true) {
  if (!key || isLocked(key) || !Number.isFinite(x) || !Number.isFinite(y)) return;
  const limit = 2000;
  x = Math.max(-limit, Math.min(limit, Math.round(x)));
  y = Math.max(-limit, Math.min(limit, Math.round(y)));
  const item = graphicForKey(key) || textForKey(key);
  if (item) { item.x = x; item.y = y; }
  else {
    const off = elemOffsets.get(selected()) || {};
    off[key] = { x, y };
    elemOffsets.set(selected(), off);
  }
  if (redraw) render();
}

function updateActiveSelection() {
  activeElement = [...selectedElements].at(-1) || null;
  if (typeof selectCustomText === 'function') {
    selectCustomText(selectedElements.size === 1 && activeElement?.startsWith('custom:') ? activeElement.slice(7) : null);
  }
}

function chooseElement(key, additive = false) {
  if (!additive) selectedElements.clear();
  if (key) {
    if (additive && selectedElements.has(key)) selectedElements.delete(key);
    else selectedElements.add(key);
  }
  updateActiveSelection();
  render();
}

function selectionRoots(keys = [...selectedElements]) {
  return keys.filter(key => {
    if (isLocked(key) || !elementBounds.has(key)) return false;
    let parent = elementBounds.get(key).parent;
    while (parent) {
      if (keys.includes(parent)) return false;
      parent = elementBounds.get(parent)?.parent;
    }
    return true;
  });
}

function movementSnapshot(keys = selectionRoots()) {
  return keys.map(key => ({
    key,
    pos: { ...getPosition(key) },
    matrix: key.startsWith('graphic:') || key.startsWith('custom:') ? new DOMMatrix() : elementBounds.get(key).matrix
  }));
}

function applyMovement(snapshot, dx, dy) {
  for (const item of snapshot) {
    const m = item.matrix, det = m.a * m.d - m.b * m.c;
    setPosition(item.pos.x + (m.d * dx - m.c * dy) / det, item.pos.y + (-m.b * dx + m.a * dy) / det, item.key, false);
  }
  render();
}

function moveSelection(dx, dy) {
  if (!selectedElements.size) return;
  recordHistory();
  applyMovement(movementSnapshot(), dx, dy);
}

function alignSelection(kind) {
  const keys = selectionRoots(), union = selectionBox(keys);
  if (!union) return;
  recordHistory();
  const wall = byId('layout').value === 'wall', vw = wall ? 640 : 794, vh = wall ? 960 : 530;
  const snapshot = movementSnapshot(keys);
  for (const item of snapshot) {
    const b = worldBox(elementBounds.get(item.key));
    let dx = 0, dy = 0;
    if (kind === 'left') dx = (keys.length > 1 ? union.x : 24) - b.x;
    if (kind === 'top') dy = (keys.length > 1 ? union.y : 24) - b.y;
    if (kind === 'centerX') dx = (keys.length > 1 ? union.x + union.w / 2 : vw / 2) - (b.x + b.w / 2);
    if (kind === 'centerY') dy = (keys.length > 1 ? union.y + union.h / 2 : vh / 2) - (b.y + b.h / 2);
    if (kind === 'safe') {
      const margin = item.key === 'photo' ? 0 : 16;
      dx = b.w <= vw - margin * 2 ? Math.min(Math.max(b.x, margin), vw - margin - b.w) - b.x : vw / 2 - (b.x + b.w / 2);
      dy = b.h <= vh - margin * 2 ? Math.min(Math.max(b.y, margin), vh - margin - b.h) - b.y : vh / 2 - (b.y + b.h / 2);
    }
    const m = item.matrix, det = m.a * m.d - m.b * m.c;
    setPosition(item.pos.x + (m.d * dx - m.c * dy) / det, item.pos.y + (-m.b * dx + m.a * dy) / det, item.key, false);
  }
  render();
}

// --- Preview Zoom & Pan ---
function applyPreviewZoom() {
  const stage = byId('stage');
  if (!stage) return;
  const vw = byId('layout').value === 'wall' ? 640 : 794, vh = byId('layout').value === 'wall' ? 960 : 530;
  const availableW = Math.max(100, (stage.clientWidth || 600) - 28);
  const availableH = Math.max(100, (stage.clientHeight || 480) - 28);
  const fit = Math.min(availableW / vw, availableH / vh);
  const w = Math.round(vw * fit * viewZoom);
  const h = Math.round(vh * fit * viewZoom);
  if (canvas.style.width !== w + 'px') canvas.style.width = w + 'px';
  if (canvas.style.height !== h + 'px') canvas.style.height = h + 'px';
  stage.style.overflow = viewZoom > 1.05 ? 'auto' : 'hidden';
  byId('previewZoom').value = Math.round(viewZoom * 100);
  byId('previewZoomValue').textContent = Math.round(viewZoom * 100) + '%';
  byId('zoomOut').disabled = viewZoom <= .25;
  byId('zoomIn').disabled = viewZoom >= 3;
}

function setViewZoom(value, fit = false) {
  const previous = viewZoom, stage = byId('stage');
  viewZoom = Math.max(.25, Math.min(3, Number(value) || 1));
  const cx = (stage.scrollLeft || 0) + (stage.clientWidth || 0) / 2;
  const cy = (stage.scrollTop || 0) + (stage.clientHeight || 0) / 2;
  applyPreviewZoom();
  if (fit) {
    stage.scrollLeft = 0; stage.scrollTop = 0;
  } else {
    stage.scrollLeft = Math.max(0, cx * viewZoom / previous - (stage.clientWidth || 0) / 2);
    stage.scrollTop = Math.max(0, cy * viewZoom / previous - (stage.clientHeight || 0) / 2);
  }
}

// Attach Pointer Event Listeners
function initPointerInteractions() {
  const stage = byId('stage');

  if (byId('zoomOut')) byId('zoomOut').onclick = () => setViewZoom(viewZoom - .25);
  if (byId('zoomIn')) byId('zoomIn').onclick = () => setViewZoom(viewZoom + .25);
  if (byId('zoomFit')) byId('zoomFit').onclick = () => setViewZoom(1, true);
  if (byId('previewZoom')) byId('previewZoom').oninput = e => setViewZoom(Number(e.target.value) / 100);
  if (byId('panMode')) {
    byId('panMode').onclick = () => {
      panMode = !panMode;
      byId('panMode').setAttribute('aria-pressed', panMode);
      if (canvas) canvas.style.cursor = panMode ? 'grab' : 'default';
    };
  }
  if (stage) {
    stage.addEventListener('wheel', e => {
      if (e.ctrlKey || e.metaKey) {
        e.preventDefault();
        setViewZoom(viewZoom * (e.deltaY > 0 ? .9 : 1.1));
      }
    }, { passive: false });
  }
  window.addEventListener('resize', applyPreviewZoom);

  canvas.addEventListener('pointerdown', e => {
    if (e.button !== 0 && e.button !== 1) return;
    canvas.focus({ preventScroll: true });
    if (panMode || spaceHeld || e.button === 1) {
      dragState = { pan: true, x: e.clientX, y: e.clientY, left: stage.scrollLeft, top: stage.scrollTop };
      canvas.setPointerCapture(e.pointerId);
      e.preventDefault();
      return;
    }
    const { x, y } = getVirtualCoords(e);
    // Check resize handle first
    const resizeHit = findResizeHandle(x, y);
    if (resizeHit) {
      const key = [...selectedElements][0];
      const g = graphicForKey(key); const t = textForKey(key);
      const startObj = g ? { x: g.x, y: g.y, w: g.w, h: g.h }
        : t ? { x: t.x, y: t.y, w: resizeHit.bounds.w, h: resizeHit.bounds.h, fontSize: t.size }
        : key === 'photo' ? { x: resizeHit.bounds.x, y: resizeHit.bounds.y, w: resizeHit.bounds.w, h: resizeHit.bounds.h, cropZ: (crops.get(selected()) || { z: 1 }).z }
        : null;
      if (startObj) {
        recordHistory();
        dragState = { resize: true, key, handle: resizeHit.handle, startX: x, startY: y, startBounds: resizeHit.bounds, startObj };
        canvas.setPointerCapture(e.pointerId);
        e.preventDefault();
        return;
      }
    }
    const hit = findHit(x, y), additive = e.shiftKey || e.ctrlKey || e.metaKey || byId('multiSelectMode')?.checked;
    if (hit && hit.key.startsWith('photo:slot')) {
      const slotIdx = Number(hit.key.split('slot')[1]) || 0;
      const conf = getPageCollage(selected());
      conf.activeSlot = slotIdx;
      if (typeof syncCollageControls === 'function') syncCollageControls();
    }
    if (!hit) {
      if (!additive) chooseElement(null);
      return;
    }
    if (additive) {
      chooseElement(hit.key, true);
      if (!selectedElements.has(hit.key)) return;
    } else if (!selectedElements.has(hit.key)) {
      chooseElement(hit.key);
    }
    const snapshot = movementSnapshot();
    if (!snapshot.length) return;
    recordHistory();
    dragState = { startX: x, startY: y, snapshot };
    canvas.setPointerCapture(e.pointerId);
    e.preventDefault();
  });

  canvas.addEventListener('pointermove', e => {
    if (dragState?.pan) {
      stage.scrollLeft = dragState.left - (e.clientX - dragState.x);
      stage.scrollTop = dragState.top - (e.clientY - dragState.y);
      canvas.style.cursor = 'grabbing';
      e.preventDefault();
      return;
    }
    const { x, y } = getVirtualCoords(e);
    if (dragState?.resize) {
      let dx = x - dragState.startX, dy = y - dragState.startY;
      if (byId('snapGrid')?.checked && !e.altKey) {
        dx = Math.round(dx / 8) * 8;
        dy = Math.round(dy / 8) * 8;
      }
      applyResize(dragState.key, dragState.handle, dx, dy, dragState.startBounds, dragState.startObj);
      e.preventDefault();
    } else if (dragState) {
      let dx = x - dragState.startX, dy = y - dragState.startY;
      if (byId('snapGrid')?.checked && !e.altKey) {
        dx = Math.round(dx / 8) * 8;
        dy = Math.round(dy / 8) * 8;
      }
      applyMovement(dragState.snapshot, dx, dy);
      e.preventDefault();
    } else {
      const rh = findResizeHandle(x, y);
      canvas.style.cursor = panMode || spaceHeld ? 'grab' : rh ? rh.cursor : findHit(x, y) ? 'move' : 'default';
    }
  });

  for (const event of ['pointerup', 'pointercancel', 'lostpointercapture']) {
    canvas.addEventListener(event, () => {
      if (dragState) {
        dragState = null;
        if (typeof syncElementPanel === 'function') syncElementPanel();
        if (typeof syncAssetControls === 'function') syncAssetControls();
        if (typeof syncHistoryButtons === 'function') syncHistoryButtons();
        if (typeof scheduleTemplatePreviews === 'function') scheduleTemplatePreviews();
      }
      canvas.style.cursor = panMode ? 'grab' : 'default';
    });
  }

  canvas.addEventListener('dblclick', e => {
    const { x, y } = getVirtualCoords(e), hit = findHit(x, y);
    if (hit?.key.startsWith('custom:')) {
      chooseElement(hit.key);
      byId('customTextContent').focus();
      byId('customTextContent').select();
    }
  });

  // Drag-and-drop image files directly onto canvas & collage slots
  canvas.addEventListener('dragover', e => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
  });

  canvas.addEventListener('drop', async e => {
    e.preventDefault();
    const files = Array.from(e.dataTransfer.files || []).filter(f => f.type.startsWith('image/'));
    if (!files.length) return;
    const { x, y } = getVirtualCoords(e);
    const hit = findHit(x, y);
    const p = selected();
    const conf = getPageCollage(p);

    recordHistory();
    try {
      if (hit && hit.key.startsWith('photo:slot')) {
        const targetSlot = Number(hit.key.split('slot')[1]) || 0;
        const img = new Image();
        const url = URL.createObjectURL(files[0]);
        img.src = url;
        await img.decode();
        setSlotPhoto(p, targetSlot, img);
        conf.activeSlot = targetSlot;
        URL.revokeObjectURL(url);
      } else {
        for (let i = 0; i < files.length && i < 5; i++) {
          const img = new Image();
          const url = URL.createObjectURL(files[i]);
          img.src = url;
          await img.decode();
          const targetSlot = (files.length === 1) ? conf.activeSlot : i;
          setSlotPhoto(p, targetSlot, img);
          URL.revokeObjectURL(url);
        }
      }
      if (typeof syncCollageControls === 'function') syncCollageControls();
      render();
      status.textContent = files.length + ' foto berhasil dimasukkan langsung ke kanvas.';
    } catch {
      status.textContent = 'Gagal memuat gambar yang di-drop. Coba format JPG, PNG, atau WebP.';
    }
  });
}
