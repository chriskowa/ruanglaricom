/**
 * Running Calendar - Main App Controller
 * Configures UI panels, slide navigation, keyboard shortcuts, asset controls,
 * custom text editing, and bootstraps the application.
 */

function addChoice(id, container, value, label, extra = '') {
  const card = document.createElement('label'); card.className = 'choice';
  const input = document.createElement('input'); input.type = 'radio'; input.name = id + 'Choice'; input.value = value; input.checked = byId(id).value === value;
  const title = document.createElement('span'); title.textContent = label;
  card.append(input, title); if (extra) card.insertAdjacentHTML('beforeend', extra);
  input.addEventListener('change', () => { byId(id).value = value; byId(id).dispatchEvent(new Event('change', { bubbles: true })); syncChoices(); });
  container.append(card);
  return card;
}

function syncChoices() {
  for (const id of ['layout', 'monthsPerPage', 'template', 'font', 'page'])
    document.querySelectorAll('input[name="' + id + 'Choice"]').forEach(r => r.checked = r.value === byId(id).value);
}

function setupChoices() {
  for (const [v, title, detail] of [['wall', 'Kalender dinding', 'A3+ · 320 × 480 mm'], ['landscape', 'Kalender lari', 'A4 · lanskap']]) {
    const c = addChoice('layout', byId('layoutChoices'), v, title);
    c.querySelector('span').innerHTML = '<b>' + title + '</b><small>' + detail + '</small>';
    const icon = document.createElement('i'); icon.className = 'swatch-paper ' + (v === 'landscape' ? 'wide' : ''); c.prepend(icon);
  }
  for (const [v, title, detail] of [
    ['1', '1 bulan / lembar', '13 halaman + sampul*'],
    ['2', '2 bulan / lembar', '7 halaman termasuk sampul'],
    ['4', '4 bulan / lembar', '4 halaman termasuk sampul'],
    ['12', '12 bulan / lembar', '1 lembar poster A3+']
  ]) {
    const c = addChoice('monthsPerPage', byId('monthsPerPageChoices'), v, title);
    c.querySelector('span').innerHTML = '<b>' + title + '</b><small>' + (v === '1' ? '13 halaman termasuk sampul' : detail) + '</small>';
  }
  for (const [v, title, desc] of templateDefinitions) {
    const c = addChoice('template', byId('templateChoices'), v, title);
    c.querySelector('span').innerHTML = '<b>' + title + '</b><small>' + (desc || '') + '</small>';
    const isWall = byId('layout') ? byId('layout').value === 'wall' : true;
    const mini = document.createElement('canvas');
    mini.width = isWall ? 100 : 120;
    mini.height = isWall ? 150 : 80;
    mini.className = 'template-mini';
    mini.dataset.template = v;
    mini.setAttribute('aria-label', 'Preview ' + title);
    c.prepend(mini);
  }
  const collageContainer = byId('collageChoices');
  if (collageContainer) {
    collageContainer.innerHTML = '';
    for (const [v, title, detail] of collageLayoutDefinitions) {
      const card = document.createElement('label'); card.className = 'choice';
      const input = document.createElement('input'); input.type = 'radio'; input.name = 'collageLayoutChoice'; input.value = v;
      const titleSpan = document.createElement('span');
      titleSpan.innerHTML = '<b>' + title + '</b><small>' + detail + '</small>';
      card.append(input, titleSpan);
      input.addEventListener('change', () => {
        recordHistory();
        const conf = getPageCollage(selected());
        conf.layout = v;
        conf.activeSlot = 0;
        syncCollageControls();
        render();
      });
      collageContainer.append(card);
    }
  }
  const observer = 'IntersectionObserver' in window ? new IntersectionObserver(entries => {
    for (const e of entries) if (e.isIntersecting) { ensureFont(e.target.dataset.font); observer.unobserve(e.target); }
  }, { root: byId('fontChoices'), rootMargin: '160px' }) : null;

  for (const family of fontFamilies) {
    const c = addChoice('font', byId('fontChoices'), family, family); c.dataset.font = family;
    const sample = document.createElement('div'); sample.className = 'font-sample'; sample.textContent = 'Aa 2027'; sample.style.fontFamily = '"' + family + '",sans-serif'; c.prepend(sample);
    if (observer) observer.observe(c);
  }
  byId('fontCount').textContent = '/ ' + fontFamilies.length + ' pilihan';
  byId('fontSearch').oninput = () => {
    const q = byId('fontSearch').value.toLowerCase().trim();
    let count = 0;
    for (const c of byId('fontChoices').children) {
      const i = fontFamilies.indexOf(c.dataset.font);
      const category = i >= 26 && i <= 29 ? 'tulisan tangan script' : i >= 30 ? 'mono' : i >= 20 ? 'serif' : 'sans';
      c.hidden = !(c.dataset.font + ' ' + category).toLowerCase().includes(q);
      c.style.display = c.hidden ? 'none' : '';
      if (!c.hidden) count++;
    }
    byId('fontCount').textContent = '/ ' + count + ' pilihan';
    byId('fontChoices').scrollLeft = 0;
  };
  setupChoiceStripsSlideTouch();
}

function buildSlides() {
  const isPoster = typeof isPoster12M === 'function' && isPoster12M();
  const is4M = typeof isFourMonthMode === 'function' ? isFourMonthMode() : false;
  const is2M = isTwoMonthMode();
  const maxP = getMaxPage();
  const curSelected = selected();
  byId('pageChoices').replaceChildren();
  byId('slides').innerHTML = '';

  if (isPoster) {
    const btn = document.createElement('button');
    btn.textContent = 'Poster 12 Bulan (A3+)';
    btn.dataset.page = '0';
    btn.onclick = () => {
      page.value = 0;
      page.onchange();
      btn.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
    };
    byId('slides').append(btn);
    addChoice('page', byId('pageChoices'), '0', btn.textContent);
    page.value = 0;
    return;
  }

  for (let p = -1; p <= maxP; p++) {
    const btn = document.createElement('button');
    btn.textContent = p === -1 ? 'Sampul' : (is4M ? fourMonthLabels[p] : (is2M ? twoMonthLabels[p] : months[p]));
    btn.dataset.page = p;
    btn.onclick = () => {
      page.value = p;
      page.onchange();
      btn.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
    };
    byId('slides').append(btn);
    addChoice('page', byId('pageChoices'), String(p), btn.textContent);
  }
  page.value = Math.max(-1, Math.min(maxP, isNaN(curSelected) ? -1 : curSelected));
}

function navigate(delta) {
  const maxP = getMaxPage();
  page.value = Math.max(-1, Math.min(maxP, selected() + delta));
  page.onchange();
}

function selectCustomText(id) {
  activeTextId = id;
  if (id) activeElement = 'custom:' + id;
  else if (activeElement?.startsWith('custom:')) activeElement = null;
  const list = customTexts.get(selected()) || [];
  const item = list.find(t => t.id === id);
  if (item) {
    byId('textControls').style.display = 'flex';
    byId('noTextHint').textContent = '💡 Geser teks langsung di kalender preview.';
    byId('customTextContent').value = item.text;
    byId('customTextSize').value = item.size;
    byId('customTextColor').value = item.color;
    byId('customTextBold').checked = item.bold;
    byId('customTextItalic').checked = item.italic;
  } else {
    byId('textControls').style.display = 'none';
    byId('noTextHint').textContent = '💡 Klik elemen atau pilih di panel Posisi elemen, lalu geser.';
  }
}

let panelSignature = '';
function syncElementPanel() {
  const keys = [...elementBounds.keys()];
  for (const key of [...selectedElements]) if (!keys.includes(key)) selectedElements.delete(key);
  if (!selectedElements.has(activeElement)) { activeElement = [...selectedElements].at(-1) || null; }
  const signature = selected() + ':' + keys.map(k => k + '=' + elementLabel(k) + '/' + isLocked(k)).join('|');
  if (signature !== panelSignature) {
    panelSignature = signature;
    byId('elementChoices').replaceChildren();
    for (const key of keys) {
      const c = document.createElement('label');
      c.className = 'choice' + (isLocked(key) ? ' is-locked' : '');
      const r = document.createElement('input');
      r.type = 'checkbox'; r.name = 'elementChoice'; r.value = key;
      r.onchange = () => chooseElement(key, true);
      const text = document.createElement('span'); text.textContent = elementLabel(key);
      c.append(r, text);
      if (isLocked(key)) {
        const lock = document.createElement('span'); lock.className = 'lock-symbol'; lock.textContent = '🔒'; c.append(lock);
      }
      byId('elementChoices').append(c);
    }
  }
  for (const r of document.querySelectorAll('input[name="elementChoice"]')) r.checked = selectedElements.has(r.value);
  const count = selectedElements.size, multi = count > 1, pos = multi ? (selectionBox() || { x: 0, y: 0 }) : getPosition();
  byId('selectionInfo').textContent = count ? count + ' elemen terpilih' + (count > selectionRoots().length ? ' · termasuk elemen anak / terkunci' : '') : 'Belum ada elemen terpilih';
  byId('positionXLabel').textContent = multi ? 'Posisi grup X' : 'Geser X';
  byId('positionYLabel').textContent = multi ? 'Posisi grup Y' : 'Geser Y';
  const canMove = selectionRoots().length > 0;
  for (const [id, val] of [['elementX', pos.x], ['elementY', pos.y]]) {
    if (document.activeElement !== byId(id)) byId(id).value = Math.round(val);
    byId(id).disabled = !canMove;
  }
  const resetBtn = byId('resetSelection') || byId('resetElement');
  if (resetBtn) resetBtn.disabled = !canMove;
  const lockBtn = byId('lockElement') || byId('toggleLock');
  if (lockBtn) {
    lockBtn.disabled = !count;
    lockBtn.textContent = count && [...selectedElements].every(isLocked) ? '🔓 Buka kunci' : '🔒 Kunci';
  }
  for (const b of document.querySelectorAll('[data-align]')) b.disabled = !canMove;
}

function syncAssetControls() {
  for (const b of document.querySelectorAll('[data-preset]')) b.setAttribute('aria-pressed', b.dataset.preset === activePreset);
  const g = selectedElements.size === 1 ? graphicForKey(activeElement) : null;
  byId('assetControls').hidden = !g;
  if (!g) return;
  byId('assetSize').min = Math.min(12, g.w); byId('assetSize').max = Math.min(640, 3000 * g.w / g.h);
  if (byId('assetTitle')) byId('assetTitle').textContent = elementLabel(activeElement);
  const disabled = isLocked(activeElement);
  for (const id of ['assetSize', 'assetColor', 'assetRotation', 'assetOpacity']) byId(id).disabled = disabled || (id === 'assetColor' && g.type !== 'icon');
  if (document.activeElement !== byId('assetSize')) byId('assetSize').value = Math.round(g.w);
  if (document.activeElement !== byId('assetRotation')) byId('assetRotation').value = g.rotation || 0;
  byId('assetColor').value = g.color || accent.value;
  if (document.activeElement !== byId('assetOpacity')) byId('assetOpacity').value = Math.round((g.opacity ?? 1) * 100);
  byId('assetSizeValue').textContent = Math.round(g.w) + ' × ' + Math.round(g.h);
  byId('assetOpacityValue').textContent = Math.round((g.opacity ?? 1) * 100) + '%';
  byId('deleteAssets').disabled = disabled;
  byId('duplicateAsset').disabled = disabled;
}

function updateGraphicFromControls(id) {
  const g = selectedElements.size === 1 ? graphicForKey(activeElement) : null;
  if (!g || isLocked(activeElement)) return;
  if (id === 'assetSize') { const size = Number(byId(id).value); g.h = Math.max(.01, g.h * size / g.w); g.w = size; }
  if (id === 'assetRotation') { const value = Number(byId(id).value); if (Number.isFinite(value)) g.rotation = Math.max(-180, Math.min(180, value)); }
  if (id === 'assetColor') g.color = byId(id).value;
  if (id === 'assetOpacity') g.opacity = Number(byId(id).value) / 100;
  graphicRevision++;
  render();
}

function deleteSelection() {
  const removable = [...selectedElements].filter(k => !isLocked(k) && (k.startsWith('graphic:') || k.startsWith('custom:')));
  if (!removable.length) return;
  recordHistory();
  const ids = new Set(removable);
  graphics.set(selected(), (graphics.get(selected()) || []).filter(g => !ids.has('graphic:' + g.id)));
  customTexts.set(selected(), (customTexts.get(selected()) || []).filter(t => !ids.has('custom:' + t.id)));
  for (const k of removable) selectedElements.delete(k);
  graphicRevision++;
  updateActiveSelection();
  render();
}

function duplicateSelection() {
  const keys = [...selectedElements].filter(k => !isLocked(k) && (k.startsWith('graphic:') || k.startsWith('custom:')));
  if (!keys.length) { status.textContent = 'Pilih PNG, emoji, ikon, atau teks tambahan untuk diduplikat.'; return; }
  recordHistory();
  selectedElements.clear();
  for (const key of keys) {
    const g = graphicForKey(key), t = textForKey(key), copy = { ...(g || t), id: makeId(), x: (g || t).x + 16, y: (g || t).y + 16, preset: false };
    const map = g ? graphics : customTexts, list = map.get(selected()) || [];
    list.push(copy);
    map.set(selected(), list);
    selectedElements.add((g ? 'graphic:' : 'custom:') + copy.id);
  }
  graphicRevision++;
  updateActiveSelection();
  render();
}

function copyDecorations() {
  const source = graphics.get(selected()) || [];
  if (!source.length) { status.textContent = 'Tambahkan PNG, emoji, atau ikon terlebih dahulu.'; return; }
  recordHistory();
  for (let p = 0; p <= getMaxPage(); p++) {
    if (p === selected()) continue;
    graphics.set(p, source.map(g => ({ ...g, id: makeId() })));
    const locks = lockedElements.get(p);
    if (locks) for (const key of [...locks]) if (key.startsWith('graphic:')) locks.delete(key);
  }
  graphicRevision++;
  render();
  status.textContent = 'Dekorasi disalin ke seluruh halaman bulan. Sampul tetap. Gunakan Urungkan bila perlu.';
}

function applyStylePreset(key) {
  const preset = stylePresets[key];
  if (!preset) return;
  recordHistory();
  byId('template').value = preset.template;
  byId('font').value = preset.font;
  byId('paper').value = preset.paper;
  accent.value = preset.accent;
  activePreset = key;
  ensureFont(preset.font);
  graphics.set(selected(), (graphics.get(selected()) || []).filter(g => !g.preset));
  const b = elementBounds.get('photo'), box = b ? worldBox(b) : { x: 0, y: 0, w: 200, h: 200 };
  const size = Math.max(26, Math.min(56, box.w * .2));
  addGraphic({ type: 'icon', icon: preset.icon, label: 'Badge ' + preset.label, x: Math.round(box.x + box.w - size - 14), y: Math.round(box.y + 14), w: size, h: size, color: preset.accent, background: preset.template === 'bold' ? '#152237' : preset.paper, preset: true }, selected(), false);
  status.textContent = 'Gaya ' + preset.label + ' diterapkan. Isi dan posisi manual tetap tersimpan.';
}

function syncCollageControls() {
  const p = selected();
  const conf = getPageCollage(p);
  const totalSlots = calculateCollageRects(0, 0, 100, 100, conf.layout, conf.gap).length;
  if (conf.activeSlot >= totalSlots) conf.activeSlot = 0;

  document.querySelectorAll('input[name="collageLayoutChoice"]').forEach(r => {
    r.checked = r.value === conf.layout;
  });

  const collageDef = collageLayoutDefinitions.find(d => d[0] === conf.layout);
  if (byId('collageModeLabel')) byId('collageModeLabel').textContent = '/ ' + (collageDef ? collageDef[1] : '1 Foto');

  const isMulti = conf.layout !== '1';
  if (byId('collageGapRow')) byId('collageGapRow').style.display = isMulti ? 'flex' : 'none';
  if (byId('slotSelectorRow')) byId('slotSelectorRow').style.display = isMulti ? 'flex' : 'none';

  if (byId('collageGap')) byId('collageGap').value = conf.gap;
  if (byId('collageGapValue')) byId('collageGapValue').textContent = conf.gap + 'px';

  const slotTabs = byId('slotTabs');
  if (slotTabs) {
    slotTabs.innerHTML = '';
    for (let i = 0; i < totalSlots; i++) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'slot-btn' + (conf.activeSlot === i ? ' active' : '');
      const hasImg = (conf.slots[i]?.img != null) || (i === 0 && getDefaultBg(p) != null);
      btn.textContent = (hasImg ? '● ' : '○ ') + 'Foto ' + (i + 1);
      btn.onclick = () => {
        conf.activeSlot = i;
        syncCollageControls();
        render();
      };
      slotTabs.append(btn);
    }
  }

  const curSlot = conf.slots[conf.activeSlot] || { crop: { x: 50, y: 50, z: 1 } };
  const crop = curSlot.crop || { x: 50, y: 50, z: 1 };
  byId('px').value = crop.x;
  byId('py').value = crop.y;
  byId('zoom').value = crop.z;
  if (byId('photoLabel') && byId('photoLabel').firstChild) {
    byId('photoLabel').firstChild.textContent = isMulti ? ('Unggah Foto ' + (conf.activeSlot + 1)) : 'Unggah foto';
  }
}

function changePhoto(reset) {
  recordHistory();
  const p = selected();
  const conf = getPageCollage(p);
  pending.delete(p);
  if (reset) {
    if (conf.activeSlot === 0) {
      photos.delete(p);
      setSlotPhoto(p, 0, null);
    } else {
      setSlotPhoto(p, conf.activeSlot, null);
    }
  } else {
    setSlotPhoto(p, conf.activeSlot, null);
    if (conf.activeSlot === 0) photos.set(p, null);
  }
  byId('photo').value = ''; status.textContent = '';
  syncCollageControls();
  render();
}

function setupEnhancements() {
  const emojiPicker = byId('emojiPicker');
  if (emojiPicker) {
    for (const emoji of ['🏃', '🏃‍♀️', '🏅', '🏆', '🔥', '⚡', '💪', '❤️', '⭐', '🎯', '🏔️', '🌿', '🌞', '🌙', '📍', '📷', '✈️', '🎉', '🎂', '☕']) {
      const b = document.createElement('button'); b.type = 'button'; b.textContent = emoji; b.title = 'Tambahkan ' + emoji;
      b.onclick = () => addGraphic({ type: 'emoji', emoji, label: 'Emoji ' + emoji });
      emojiPicker.append(b);
    }
  }
  const iconPicker = byId('iconPicker');
  if (iconPicker) {
    for (const [key, icon] of Object.entries(faIcons)) {
      const b = document.createElement('button'); b.type = 'button'; b.title = iconLabels[key] || key;
      b.setAttribute('aria-label', 'Tambah ikon ' + (iconLabels[key] || key));
      b.dataset.search = key + ' ' + (iconLabels[key] || '');
      b.innerHTML = '<svg aria-hidden="true" viewBox="0 0 ' + icon.w + ' ' + icon.h + '">' + icon.paths.map(d => '<path fill="currentColor" d="' + d + '"/>').join('') + '</svg>';
      b.onclick = () => addGraphic({ type: 'icon', icon: key, label: 'Ikon ' + (iconLabels[key] || key) });
      iconPicker.append(b);
    }
  }
  if (byId('iconSearch') && iconPicker) {
    byId('iconSearch').oninput = () => {
      const q = byId('iconSearch').value.toLowerCase().trim();
      for (const b of iconPicker.children) b.hidden = !b.dataset.search.toLowerCase().includes(q);
    };
  }
  if (byId('addEmoji')) {
    byId('addEmoji').onclick = () => {
      const emoji = byId('emojiInput')?.value.trim();
      if (!emoji) { if (status) status.textContent = 'Pilih emoji atau tempel emoji sendiri terlebih dahulu.'; return; }
      addGraphic({ type: 'emoji', emoji, label: 'Emoji ' + emoji });
      if (byId('emojiInput')) byId('emojiInput').value = '';
    };
  }
  if (byId('addPng')) byId('addPng').onchange = e => uploadPngFiles(e.target.files);
  for (const b of document.querySelectorAll('[data-preset]')) b.onclick = () => applyStylePreset(b.dataset.preset);
  if (byId('deleteAssets')) byId('deleteAssets').onclick = deleteSelection;
  if (byId('deleteSelection')) byId('deleteSelection').onclick = deleteSelection;
  if (byId('duplicateAsset')) byId('duplicateAsset').onclick = duplicateSelection;
  if (byId('duplicateSelection')) byId('duplicateSelection').onclick = duplicateSelection;
  if (byId('copyDecorations')) byId('copyDecorations').onclick = copyDecorations;
  if (byId('undo')) byId('undo').onclick = undo;
  if (byId('redo')) byId('redo').onclick = redo;
  if (byId('runningMode')) {
    byId('runningMode').onchange = () => {
      recordHistory();
      render();
    };
  }
  for (const id of ['assetSize', 'assetRotation', 'assetColor', 'assetOpacity']) {
    if (byId(id)) byId(id).oninput = () => updateGraphicFromControls(id);
  }
  for (const id of ['template', 'font', 'paper', 'accent']) {
    if (byId(id)) {
      byId(id).addEventListener(id === 'paper' || id === 'accent' ? 'input' : 'change', () => {
        activePreset = null;
        for (const b of document.querySelectorAll('[data-preset]')) b.setAttribute('aria-pressed', 'false');
      });
    }
  }

  // Keyboard Shortcuts
  document.addEventListener('keydown', e => {
    if (['INPUT', 'SELECT', 'TEXTAREA'].includes(e.target.tagName) || e.target.isContentEditable) return;
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') { e.preventDefault(); e.shiftKey ? redo() : undo(); return; }
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y') { e.preventDefault(); redo(); return; }
    if (e.target.tagName === 'BUTTON') return;
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'd') { e.preventDefault(); duplicateSelection(); return; }
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'a' && e.target === canvas) { e.preventDefault(); byId('selectAllElements').click(); return; }
    if (e.key === ' ' && e.target === canvas) { spaceHeld = true; canvas.style.cursor = 'grab'; e.preventDefault(); return; }
    if (e.key === 'Escape') { chooseElement(null); return; }
    if ((e.key === 'Delete' || e.key === 'Backspace') && selectedElements.size) { e.preventDefault(); deleteSelection(); return; }
    if (selectedElements.size && ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key)) {
      e.preventDefault();
      const step = e.shiftKey ? 10 : 1;
      moveSelection(e.key === 'ArrowRight' ? step : e.key === 'ArrowLeft' ? -step : 0, e.key === 'ArrowDown' ? step : e.key === 'ArrowUp' ? -step : 0);
      return;
    }
    if (e.key === 'ArrowLeft') { e.preventDefault(); navigate(-1); }
    if (e.key === 'ArrowRight') { e.preventDefault(); navigate(1); }
    if (e.target === canvas && (e.key === '+' || e.key === '=')) { e.preventDefault(); setViewZoom(viewZoom + .25); }
    if (e.target === canvas && e.key === '-') { e.preventDefault(); setViewZoom(viewZoom - .25); }
  });

  document.addEventListener('keyup', e => { if (e.key === ' ') { spaceHeld = false; canvas.style.cursor = panMode ? 'grab' : 'default'; } });
  window.addEventListener('blur', () => { spaceHeld = false; dragState = null; });
}

// Initial Wire-Up
for (const element of [nameInput, yearInput, accent]) {
  if (element) element.addEventListener('input', render);
}
if (byId('font')) byId('font').onchange = () => { ensureFont(byId('font').value); render(); };
if (showHolidays) showHolidays.onchange = render;
if (monthsPerPage) {
  monthsPerPage.onchange = () => {
    buildSlides();
    selectCustomText(null);
    if (page && page.onchange) page.onchange();
  };
}
if (page) {
  page.onchange = () => {
    dragState = null;
    syncCollageControls();
    if (byId('photo')) byId('photo').value = '';
    if (status) status.textContent = '';
    selectCustomText(null); activeElement = null; selectedElements.clear();
    if (typeof updateRuangLariUrl === 'function') updateRuangLariUrl();
    render();
  };
}
if (byId('photo')) {
  byId('photo').onchange = async event => {
    const file = event.target.files[0], p = selected();
    if (!file) return;
    const token = {}; pending.set(p, token);
    const url = URL.createObjectURL(file), image = new Image();
    try {
      image.src = url;
      await image.decode();
      if (pending.get(p) === token) {
        recordHistory();
        const conf = getPageCollage(p);
        setSlotPhoto(p, conf.activeSlot, image);
        if (selected() === p) {
          if (status) status.textContent = 'Foto ' + (conf.activeSlot + 1) + ' diperbarui.';
          syncCollageControls();
          render();
        }
      }
    } catch {
      if (selected() === p && pending.get(p) === token) {
        if (status) status.textContent = 'Foto tidak dapat dibuka. Coba JPG, PNG, atau WebP.';
      }
    } finally { URL.revokeObjectURL(url); }
  };
}
if (byId('remove')) byId('remove').onclick = () => changePhoto(false);
if (byId('reset')) byId('reset').onclick = () => changePhoto(true);

if (byId('collageGap')) {
  byId('collageGap').oninput = () => {
    const p = selected();
    const conf = getPageCollage(p);
    conf.gap = Number(byId('collageGap').value);
    if (byId('collageGapValue')) byId('collageGapValue').textContent = conf.gap + 'px';
    render();
  };
}

if (byId('template')) {
  byId('template').onchange = () => {
    const colors = palettes[byId('template').value];
    if (colors) {
      if (accent) accent.value = colors[0];
      if (byId('paper')) byId('paper').value = colors[1];
    }
    buildSlides();
    if (typeof syncThemeButtons === 'function') syncThemeButtons();
    render();
  };
}
for (const id of ['paper']) {
  if (byId(id)) byId(id).oninput = render;
}
for (const id of ['px', 'py', 'zoom']) {
  if (byId(id)) {
    byId(id).oninput = () => {
      const p = selected();
      const conf = getPageCollage(p);
      setSlotCrop(p, conf.activeSlot, {
        x: Number(byId('px')?.value || 50),
        y: Number(byId('py')?.value || 50),
        z: Number(byId('zoom')?.value || 1)
      });
      render();
    };
  }
}

if (byId('prev')) byId('prev').onclick = () => navigate(-1);
if (byId('next')) byId('next').onclick = () => navigate(1);
if (byId('layout')) byId('layout').onchange = () => { activeElement = null; selectedElements.clear(); selectCustomText(null); applyPreviewZoom(); render(); };

if (byId('addText')) {
  byId('addText').onclick = () => {
    const p = selected();
    const list = customTexts.get(p) || [];
    const wall = byId('layout')?.value === 'wall';
    const newText = {
      id: Date.now() + Math.random().toString(36).substr(2, 4),
      text: 'Teks Baru ' + (list.length + 1),
      x: wall ? 60 : 260,
      y: wall ? (p === -1 ? 520 : 455) : (p === -1 ? 120 : 160),
      size: 24,
      color: accent?.value || '#f45123',
      bold: true,
      italic: false
    };
    list.push(newText);
    customTexts.set(p, list);
    selectCustomText(newText.id);
    render();
  };
}
if (byId('customTextContent')) {
  byId('customTextContent').oninput = e => {
    const item = (customTexts.get(selected()) || []).find(t => t.id === activeTextId);
    if (item) { item.text = e.target.value; render(); }
  };
}
if (byId('customTextSize')) {
  byId('customTextSize').oninput = e => {
    const item = (customTexts.get(selected()) || []).find(t => t.id === activeTextId);
    if (item) { item.size = Number(e.target.value); render(); }
  };
}
if (byId('customTextColor')) {
  byId('customTextColor').oninput = e => {
    const item = (customTexts.get(selected()) || []).find(t => t.id === activeTextId);
    if (item) { item.color = e.target.value; render(); }
  };
}
if (byId('customTextBold')) {
  byId('customTextBold').onchange = e => {
    const item = (customTexts.get(selected()) || []).find(t => t.id === activeTextId);
    if (item) { item.bold = e.target.checked; render(); }
  };
}
if (byId('customTextItalic')) {
  byId('customTextItalic').onchange = e => {
    const item = (customTexts.get(selected()) || []).find(t => t.id === activeTextId);
    if (item) { item.italic = e.target.checked; render(); }
  };
}
const delText = byId('deleteCustomText') || byId('deleteText');
if (delText) delText.onclick = () => {
  const p = selected();
  let list = customTexts.get(p) || [];
  list = list.filter(t => t.id !== activeTextId);
  customTexts.set(p, list);
  selectCustomText(null);
  render();
};

const resetOff = byId('resetPositions') || byId('resetOffsets');
if (resetOff) resetOff.onclick = () => {
  elemOffsets.delete(selected());
  render();
  if (status) status.textContent = 'Posisi elemen bawaan pada halaman ini dikembalikan ke default.';
};

for (const axis of ['X', 'Y']) byId('element' + axis).oninput = () => {
  if (byId('element' + axis).value === '') return;
  const x = Number(byId('elementX').value), y = Number(byId('elementY').value);
  if (selectedElements.size > 1) { const b = selectionBox(); if (b) applyMovement(movementSnapshot(), x - b.x, y - b.y); }
  else setPosition(x, y);
};

const moveMap = {
  moveUp: [0, -4],
  moveDown: [0, 4],
  moveLeft: [-4, 0],
  moveRight: [4, 0]
};
for (const [btnId, [dx, dy]] of Object.entries(moveMap)) {
  if (byId(btnId)) byId(btnId).onclick = () => moveSelection(dx, dy);
}

const alignMap = {
  alignLeft: 'left',
  alignTop: 'top',
  alignCenterX: 'centerX',
  alignCenterY: 'centerY',
  alignSafe: 'safe'
};
for (const [btnId, kind] of Object.entries(alignMap)) {
  if (byId(btnId)) byId(btnId).onclick = () => alignSelection(kind);
}

const resetEl = byId('resetSelection') || byId('resetElement');
if (resetEl) resetEl.onclick = () => {
  recordHistory();
  for (const key of selectionRoots()) {
    const g = graphicForKey(key), t = textForKey(key);
    if (g) { const p = defaultGraphicPosition(g.w, g.h); setPosition(p.x, p.y, key, false); }
    else if (t) { const wall = byId('layout').value === 'wall'; setPosition(wall ? 60 : 260, wall ? 455 : 160, key, false); }
    else setPosition(0, 0, key, false);
  }
  render();
};

const deselect = byId('clearSelection') || byId('deselectElement');
if (deselect) deselect.onclick = () => chooseElement(null);

if (byId('selectAllElements')) byId('selectAllElements').onclick = () => {
  selectedElements.clear();
  for (const key of elementBounds.keys()) if (!isLocked(key)) selectedElements.add(key);
  updateActiveSelection();
  render();
};

const togLock = byId('lockElement') || byId('toggleLock');
if (togLock) togLock.onclick = () => {
  recordHistory();
  const set = lockedElements.get(selected()) || new Set(), unlock = [...selectedElements].every(isLocked);
  for (const key of selectedElements) {
    if (unlock) set.delete(key); else set.add(key);
  }
  lockedElements.set(selected(), set);
  dragState = null;
  render();
};

if (byId('unlockAll')) byId('unlockAll').onclick = () => {
  recordHistory();
  lockedElements.delete(selected());
  dragState = null;
  render();
};

// --- Ruang Lari & Strava Integration ---
function updateRuangLariUrl() {
  const u = (byId('ruangLariUser')?.value || 'runner').trim() || 'runner';
  const t = (byId('ruangLariToken')?.value || 'RL2026').trim();
  const m = selected();
  const mStr = String(m >= 0 ? m + 1 : 1).padStart(2, '0');
  const url = `https://ruanglari.com/runner/${encodeURIComponent(u)}/${year()}-${mStr}?token=${encodeURIComponent(t)}`;
  const previewEl = byId('ruangLariUrlPreview');
  if (previewEl) previewEl.textContent = url;
}

function initRuangLariHandlers() {
  const qrCb = byId('ruangLariQr');
  if (qrCb) {
    qrCb.onchange = () => {
      recordHistory();
      render();
    };
  }
  const userIn = byId('ruangLariUser');
  if (userIn) {
    userIn.oninput = () => {
      updateRuangLariUrl();
      render();
      if (typeof triggerAutosave === 'function') triggerAutosave();
    };
  }
  const tokenIn = byId('ruangLariToken');
  if (tokenIn) {
    tokenIn.oninput = () => {
      updateRuangLariUrl();
      render();
      if (typeof triggerAutosave === 'function') triggerAutosave();
    };
  }

  // Real Ruang Lari events importer
  const btnSyncEvents = byId('btnSyncEvents') || byId('btnSyncStravaDemo');
  if (btnSyncEvents) {
    btnSyncEvents.onclick = async () => {
      recordHistory();
      const y = year();
      const searchUrl = (window.KP_CONFIG?.eventsSearchUrl || '/kalender-pelari/api/events/search') + `?year=${y}`;
      status.textContent = 'Memuat agenda lomba lari dari database Ruang Lari...';
      try {
        const res = await fetch(searchUrl);
        const data = await res.json();
        if (data && data.events && data.events.length) {
          let count = 0;
          data.events.forEach(ev => {
            if (ev.year && ev.month && ev.day) {
              const key = `${ev.year}-${ev.month - 1}-${ev.day}`;
              const dist = (ev.distances && ev.distances.length) ? ` (${ev.distances.join(', ')})` : '';
              stravaLogs.set(key, {
                km: 21.1,
                type: 'race',
                name: `${ev.name}${dist}`
              });
              count++;
            }
          });
          render();
          status.textContent = `${count} agenda lomba lari nasional Ruang Lari berhasil ditandai di kalender.`;
        } else {
          // Fallback to major annual Indonesian events if none found
          stravaLogs.set(`${y}-4-17`, { km: 21.1, type: 'race', name: 'Borobudur Marathon' });
          stravaLogs.set(`${y}-7-23`, { km: 10.0, type: 'race', name: 'Pocari Sweat Run' });
          stravaLogs.set(`${y}-9-18`, { km: 42.2, type: 'race', name: 'Maybank Marathon Bali' });
          stravaLogs.set(`${y}-10-22`, { km: 21.1, type: 'race', name: 'Jakarta Marathon' });
          render();
          status.textContent = `Agenda 4 lomba lari utama ${y} berhasil dimuat ke kalender.`;
        }
      } catch {
        stravaLogs.set(`${y}-4-17`, { km: 21.1, type: 'race', name: 'Borobudur Marathon' });
        stravaLogs.set(`${y}-7-23`, { km: 10.0, type: 'race', name: 'Pocari Sweat Run' });
        stravaLogs.set(`${y}-9-18`, { km: 42.2, type: 'race', name: 'Maybank Marathon Bali' });
        stravaLogs.set(`${y}-10-22`, { km: 21.1, type: 'race', name: 'Jakarta Marathon' });
        render();
        status.textContent = `Agenda lomba lari utama ${y} berhasil dimuat ke kalender.`;
      }
      if (typeof triggerAutosave === 'function') triggerAutosave();
    };
  }

  const btnClear = byId('btnClearStravaLog');
  if (btnClear) {
    btnClear.onclick = () => {
      recordHistory();
      stravaLogs.clear();
      render();
      status.textContent = 'Log agenda lomba dan catatan lari dibersihkan.';
      if (typeof triggerAutosave === 'function') triggerAutosave();
    };
  }

  // Attach autosave triggers to input fields
  [nameInput, yearInput, accent, byId('paper'), showHolidays, byId('runningMode')].forEach(el => {
    if (el) {
      el.addEventListener('change', () => {
        if (typeof triggerAutosave === 'function') triggerAutosave();
      });
    }
  });
}

function handleUrlParameters() {
  if (typeof window === 'undefined' || !window.location || !window.location.search) return;
  const p = new URLSearchParams(window.location.search);

  if (p.has('runner') && byId('ruangLariUser')) {
    byId('ruangLariUser').value = p.get('runner');
  }
  if (p.has('token') && byId('ruangLariToken')) {
    byId('ruangLariToken').value = p.get('token');
  }
  if (p.has('name') && nameInput) {
    nameInput.value = p.get('name');
  } else if (p.has('runner') && nameInput) {
    const formatted = p.get('runner').replace(/[-_]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    nameInput.value = formatted;
  }
  if (p.has('year') && yearInput) {
    const yr = parseInt(p.get('year'), 10);
    if (!isNaN(yr) && yr >= 2020 && yr <= 2040) {
      yearInput.value = yr;
    }
  }
  if (p.has('template') && byId('template')) {
    const t = p.get('template');
    if (palettes[t]) {
      byId('template').value = t;
      if (accent) accent.value = palettes[t][0];
      if (byId('paper')) byId('paper').value = palettes[t][1];
    }
  }
  if (p.has('layout') && byId('layout')) {
    const lay = p.get('layout');
    if (['wall', 'landscape'].includes(lay)) {
      byId('layout').value = lay;
    }
  }
  if (p.has('monthsPerPage') && byId('monthsPerPage')) {
    const mpp = p.get('monthsPerPage');
    if (['1', '2', '4', '12'].includes(mpp)) {
      byId('monthsPerPage').value = mpp;
    }
  }
  if (p.has('qr') && byId('ruangLariQr')) {
    const val = p.get('qr');
    byId('ruangLariQr').checked = !(val === '0' || val === 'false' || val === 'no');
  }

  updateRuangLariUrl();
}

// --- Athlete Idol Template Management ---
let activeAthleteTemplateId = null;

function applyAthleteTemplate(template) {
  if (!template || !Array.isArray(template.photo_urls)) return;
  if (typeof recordHistory === 'function') recordHistory();

  // 1. Update defaults background images in data.js
  if (typeof updateAthleteDefaults === 'function') {
    updateAthleteDefaults(template.photo_urls);
  }

  // 2. Map 13 photos (index 0 = cover, 1..12 = Jan..Dec)
  template.photo_urls.forEach((url, i) => {
    if (!url) return;
    const pageIndex = (i === 0) ? -1 : (i - 1);
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
      if (typeof setSlotPhoto === 'function') {
        setSlotPhoto(pageIndex, 0, img);
      }
      if (typeof render === 'function') render();
    };
    img.src = url;
  });

  // 3. Apply accent color if provided
  if (template.accent_color && byId('accent')) {
    byId('accent').value = template.accent_color;
    byId('accent').dispatchEvent(new Event('change', { bubbles: true }));
  }

  // 4. Status message
  if (status) {
    status.textContent = `Template foto ${template.name} berhasil diterapkan ke 13 halaman kalender.`;
  }

  if (typeof render === 'function') render();
}

function resetToDefaultPhotos() {
  if (typeof recordHistory === 'function') recordHistory();

  if (typeof updateAthleteDefaults === 'function' && Array.isArray(defaultBgSources)) {
    updateAthleteDefaults(defaultBgSources);
  }

  if (Array.isArray(defaultBgSources)) {
    defaultBgSources.forEach((url, i) => {
      const pageIndex = (i === 0) ? -1 : (i - 1);
      const img = new Image();
      img.onload = () => {
        if (typeof setSlotPhoto === 'function') {
          setSlotPhoto(pageIndex, 0, img);
        }
        if (typeof render === 'function') render();
      };
      img.src = url;
    });
  }

  if (status) {
    status.textContent = 'Foto bawaan Ruang Lari berhasil diterapkan kembali.';
  }

  if (typeof render === 'function') render();
}

async function setupAthleteTemplates() {
  const container = byId('athleteTemplateChoices');
  if (!container) return;

  try {
    const res = await fetch('/kalender-pelari/api/templates/athletes');
    if (!res.ok) throw new Error('Gagal memuat template');
    const json = await res.json();
    const athletes = json.data || [];

    const countElem = byId('athleteTemplateCount');
    if (countElem) {
      countElem.textContent = `/ ${athletes.length} Idola Atlet`;
    }

    container.innerHTML = '';

    // 1. Option 0: Default Photos
    const defaultCard = document.createElement('label');
    defaultCard.className = 'choice active';
    defaultCard.dataset.athleteId = 'default';
    defaultCard.innerHTML = `
      <input type="radio" name="athleteChoice" value="default" checked class="sr-only">
      <img src="${(typeof defaultBgSources !== 'undefined' && defaultBgSources[0]) ? defaultBgSources[0] : '/images/kalender-pelari/bg/815920233_18619567672013478_7707120302547332417_n.jpg'}" alt="Bawaan" class="athlete-thumb">
      <div class="athlete-name">Foto Bawaan</div>
      <div class="athlete-cat">Standar</div>
    `;
    defaultCard.addEventListener('click', () => {
      container.querySelectorAll('.choice').forEach(c => c.classList.remove('active'));
      defaultCard.classList.add('active');
      activeAthleteTemplateId = 'default';
      resetToDefaultPhotos();
    });
    container.appendChild(defaultCard);

    // 2. Athlete options from DB
    athletes.forEach(athlete => {
      const card = document.createElement('label');
      card.className = 'choice';
      card.dataset.athleteId = athlete.id;
      card.title = athlete.bio || athlete.name;
      card.innerHTML = `
        <input type="radio" name="athleteChoice" value="${athlete.id}" class="sr-only">
        <img src="${athlete.cover_url}" alt="${athlete.name}" class="athlete-thumb">
        <div class="athlete-name">${athlete.name}</div>
        <div class="athlete-cat">${athlete.category || 'Atlet'}</div>
      `;
      card.addEventListener('click', () => {
        container.querySelectorAll('.choice').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        activeAthleteTemplateId = athlete.id;
        applyAthleteTemplate(athlete);
      });
      container.appendChild(card);
    });
    setupChoiceStripsSlideTouch();
  } catch (err) {
    container.innerHTML = '<div class="text-xs text-slate-400 p-2">Template atlet tidak tersedia saat ini.</div>';
  }
}

function applyThemeMode(isDark) {
  if (isDark) {
    if (byId('paper')) byId('paper').value = '#0b0f17';
    if (byId('accent')) {
      const acc = (byId('accent').value || '').toLowerCase();
      if (acc === '#454b45' || acc === '#202020' || acc === '#1c1917' || acc === '#0f172a') {
        byId('accent').value = '#ccff00';
      }
    }
  } else {
    if (byId('paper')) byId('paper').value = '#ffffff';
    if (byId('accent')) {
      const acc = (byId('accent').value || '').toLowerCase();
      if (acc === '#ccff00' || acc === '#d8ff00' || acc === '#00f0ff' || acc === '#b8ff00') {
        byId('accent').value = '#e63946';
      }
    }
  }
  syncThemeButtons();
  render();
}

function syncThemeButtons() {
  const paper = byId('paper') ? byId('paper').value : '#ffffff';
  let isDark = false;
  if (paper) {
    const clean = paper.replace('#', '');
    if (clean.length >= 6) {
      const r = parseInt(clean.substring(0, 2), 16) / 255;
      const g = parseInt(clean.substring(2, 4), 16) / 255;
      const b = parseInt(clean.substring(4, 6), 16) / 255;
      isDark = (0.2126 * r + 0.7152 * g + 0.0722 * b) < 0.45;
    }
  }
  document.querySelectorAll('.btn-theme-dark').forEach(b => {
    if (isDark) {
      b.classList.add('active', 'bg-slate-800', 'text-white', 'shadow-sm');
      b.classList.remove('text-slate-400');
    } else {
      b.classList.remove('active', 'bg-slate-800', 'text-white', 'shadow-sm');
      b.classList.add('text-slate-400');
    }
  });
  document.querySelectorAll('.btn-theme-light').forEach(b => {
    if (isDark) {
      b.classList.remove('active', 'bg-slate-800', 'text-white', 'shadow-sm');
      b.classList.add('text-slate-400');
    } else {
      b.classList.add('active', 'bg-slate-800', 'text-white', 'shadow-sm');
      b.classList.remove('text-slate-400');
    }
  });
}

function setupThemeMode() {
  document.querySelectorAll('.btn-theme-light').forEach(btn => {
    btn.onclick = () => applyThemeMode(false);
  });
  document.querySelectorAll('.btn-theme-dark').forEach(btn => {
    btn.onclick = () => applyThemeMode(true);
  });
  if (byId('paper')) {
    byId('paper').addEventListener('input', syncThemeButtons);
  }
  syncThemeButtons();
}

function setStudioTheme(mode) {
  const isLight = mode === 'light';

  if (document.body) {
    if (isLight) {
      document.body.classList.add('kp-light-theme');
      document.body.classList.remove('kp-dark-theme');
    } else {
      document.body.classList.add('kp-dark-theme');
      document.body.classList.remove('kp-light-theme');
    }
  }

  const wrapper = document.querySelector('.kp-studio-wrapper');
  if (wrapper) {
    if (isLight) {
      wrapper.classList.add('kp-light-theme');
      wrapper.classList.remove('kp-dark-theme');
    } else {
      wrapper.classList.add('kp-dark-theme');
      wrapper.classList.remove('kp-light-theme');
    }
  }

  document.querySelectorAll('.btn-studio-light').forEach(b => {
    if (isLight) {
      b.classList.add('active');
    } else {
      b.classList.remove('active');
    }
  });

  document.querySelectorAll('.btn-studio-dark').forEach(b => {
    if (!isLight) {
      b.classList.add('active');
    } else {
      b.classList.remove('active');
    }
  });

  try {
    localStorage.setItem('kp_studio_theme', isLight ? 'light' : 'dark');
  } catch (e) {
    // Restricted or private context
  }
}

function setupStudioTheme() {
  document.querySelectorAll('.btn-studio-light').forEach(btn => {
    btn.onclick = () => setStudioTheme('light');
  });
  document.querySelectorAll('.btn-studio-dark').forEach(btn => {
    btn.onclick = () => setStudioTheme('dark');
  });

  let saved = 'dark';
  try {
    saved = localStorage.getItem('kp_studio_theme') || 'dark';
  } catch (e) {
    saved = 'dark';
  }
  setStudioTheme(saved);
}

function initSlideTouch(element) {
  if (!element || element.dataset.slideTouchActive) return;
  element.dataset.slideTouchActive = 'true';

  let isDown = false;
  let startX = 0;
  let scrollLeftStart = 0;
  let hasMoved = false;
  let velX = 0;
  let lastX = 0;
  let lastTime = 0;
  let momentumID = null;

  function stopMomentum() {
    if (momentumID) {
      cancelAnimationFrame(momentumID);
      momentumID = null;
    }
  }

  element.addEventListener('dragstart', (e) => e.preventDefault());

  element.addEventListener('pointerdown', (e) => {
    if (e.pointerType === 'mouse' && e.button !== 0) return;
    stopMomentum();
    isDown = true;
    hasMoved = false;
    startX = e.clientX;
    lastX = e.clientX;
    scrollLeftStart = element.scrollLeft;
    velX = 0;
    lastTime = performance.now();
  });

  window.addEventListener('pointermove', (e) => {
    if (!isDown) return;
    const currentX = e.clientX;
    const deltaX = currentX - startX;

    if (Math.abs(deltaX) > 4) {
      hasMoved = true;
      element.classList.add('is-dragging');
    }

    if (hasMoved) {
      element.scrollLeft = scrollLeftStart - deltaX;

      const now = performance.now();
      const dt = now - lastTime;
      if (dt > 8) {
        velX = (currentX - lastX) / dt;
        lastX = currentX;
        lastTime = now;
      }
    }
  });

  function onPointerEnd() {
    if (!isDown) return;
    isDown = false;
    element.classList.remove('is-dragging');

    if (hasMoved && Math.abs(velX) > 0.1) {
      let momentumVel = velX * 16;
      function step() {
        if (Math.abs(momentumVel) < 0.3) {
          stopMomentum();
          return;
        }
        element.scrollLeft -= momentumVel;
        momentumVel *= 0.92;
        momentumID = requestAnimationFrame(step);
      }
      stopMomentum();
      momentumID = requestAnimationFrame(step);
    }

    if (hasMoved) {
      setTimeout(() => {
        hasMoved = false;
      }, 60);
    }
  }

  window.addEventListener('pointerup', onPointerEnd);
  window.addEventListener('pointercancel', onPointerEnd);

  element.addEventListener('click', (e) => {
    if (hasMoved) {
      e.preventDefault();
      e.stopPropagation();
    }
  }, true);

  element.addEventListener('wheel', (e) => {
    if (e.deltaY !== 0 && e.deltaX === 0) {
      element.scrollLeft += e.deltaY;
    }
  }, { passive: true });
}

function setupChoiceStripsSlideTouch() {
  document.querySelectorAll('.choice-strip').forEach(initSlideTouch);
}

// --- Bootstrap Application ---
initPointerInteractions();
initExportHandlers();
initRuangLariHandlers();
setupChoices();
setupAthleteTemplates();
setupEnhancements();
setupThemeMode();
setupStudioTheme();
setupChoiceStripsSlideTouch();

if (window.KP_INITIAL_DRAFT && typeof window.KP_STUDIO?.loadProjectData === 'function') {
  window.KP_STUDIO.loadProjectData(window.KP_INITIAL_DRAFT).then(() => {
    handleUrlParameters();
    syncChoices();
    buildSlides();
    applyPreviewZoom();
    render();
  });
} else {
  handleUrlParameters();
  syncChoices();
  buildSlides();
  applyPreviewZoom();
  render();
}
if (document.fonts) document.fonts.ready.then(() => { applyPreviewZoom(); render(); });
window.addEventListener('load', () => { applyPreviewZoom(); render(); });
