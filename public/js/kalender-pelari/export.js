/**
 * Running Calendar - Export & Project Persistence
 * Handles PNG rendering & download, multi-page PDF generation via print window,
 * and JSON project serialization/deserialization with schema validation.
 */

function validPage(p) { return Number.isInteger(p) && p >= -1 && p <= 11; }

function validateTexts(entries) {
  if (!Array.isArray(entries)) return new Map();
  const valid = new Map();
  for (const item of entries) {
    if (!Array.isArray(item) || item.length !== 2 || !validPage(item[0]) || !Array.isArray(item[1])) continue;
    const cleanList = [];
    for (const t of item[1]) {
      if (typeof t?.id !== 'string' || typeof t?.text !== 'string' || !Number.isFinite(t?.x) || !Number.isFinite(t?.y) || !Number.isFinite(t?.size)) continue;
      cleanList.push({ id: t.id.slice(0, 40), text: t.text.slice(0, 120), x: Math.max(-2000, Math.min(2000, t.x)), y: Math.max(-2000, Math.min(2000, t.y)), size: Math.max(6, Math.min(200, t.size)), color: typeof t.color === 'string' ? t.color : '#111', bold: Boolean(t.bold), italic: Boolean(t.italic) });
    }
    valid.set(item[0], cleanList);
  }
  return valid;
}

function validateOffsets(entries) {
  if (!Array.isArray(entries)) return new Map();
  const valid = new Map();
  for (const item of entries) {
    if (!Array.isArray(item) || item.length !== 2 || !validPage(item[0]) || typeof item[1] !== 'object' || !item[1]) continue;
    const cleanOff = {};
    for (const [k, v] of Object.entries(item[1])) {
      if (typeof k !== 'string' || !Number.isFinite(v?.x) || !Number.isFinite(v?.y)) continue;
      cleanOff[k.slice(0, 40)] = { x: Math.max(-2000, Math.min(2000, v.x)), y: Math.max(-2000, Math.min(2000, v.y)) };
    }
    valid.set(item[0], cleanOff);
  }
  return valid;
}

function validateLocks(entries) {
  if (!Array.isArray(entries)) return new Map();
  const valid = new Map();
  for (const item of entries) {
    if (!Array.isArray(item) || item.length !== 2 || !validPage(item[0]) || !Array.isArray(item[1])) continue;
    valid.set(item[0], new Set(item[1].filter(k => typeof k === 'string').map(k => k.slice(0, 40))));
  }
  return valid;
}

async function validateGraphics(entries) {
  if (!Array.isArray(entries)) return new Map();
  const valid = new Map();
  for (const item of entries) {
    if (!Array.isArray(item) || item.length !== 2 || !validPage(item[0]) || !Array.isArray(item[1])) continue;
    const cleanList = [];
    for (const g of item[1]) {
      if (typeof g?.id !== 'string' || !['emoji', 'icon', 'png'].includes(g?.type) || !Number.isFinite(g?.x) || !Number.isFinite(g?.y) || !Number.isFinite(g?.w) || !Number.isFinite(g?.h)) continue;
      const base = { id: g.id.slice(0, 40), type: g.type, x: Math.max(-2000, Math.min(2000, g.x)), y: Math.max(-2000, Math.min(2000, g.y)), w: Math.max(8, Math.min(1200, g.w)), h: Math.max(8, Math.min(1200, g.h)), rotation: Number.isFinite(g.rotation) ? g.rotation : 0, opacity: Number.isFinite(g.opacity) ? Math.max(0, Math.min(1, g.opacity)) : 1, preset: Boolean(g.preset), label: typeof g.label === 'string' ? g.label.slice(0, 60) : '' };
      if (g.type === 'emoji' && typeof g.emoji === 'string') { cleanList.push({ ...base, emoji: g.emoji.slice(0, 16), color: g.color || '#111' }); }
      else if (g.type === 'icon' && faIcons[g.icon]) { cleanList.push({ ...base, icon: g.icon, color: g.color || '#f45123', background: g.background || '' }); }
      else if (g.type === 'png' && typeof g.src === 'string' && g.src.startsWith('data:image/png')) {
        try {
          const img = new Image(); img.src = g.src; await img.decode(); graphicImages.set(g.src, img); cleanList.push({ ...base, src: g.src });
        } catch { }
      }
    }
    valid.set(item[0], cleanList);
  }
  return valid;
}

async function uploadPngFiles(files) {
  const p = selected(), accepted = Array.from(files || []);
  let added = 0;
  for (const file of accepted) {
    if (file.type !== 'image/png' && !/\.png$/i.test(file.name)) { status.textContent = 'Gunakan file PNG untuk elemen gambar.'; continue; }
    if (file.size > 15 * 1024 * 1024) { status.textContent = 'Ukuran PNG maksimal 15 MB per file.'; continue; }
    const url = URL.createObjectURL(file);
    try {
      const source = new Image(); source.src = url; await source.decode();
      const tmp = document.createElement('canvas'), scale = Math.min(1, 1600 / Math.max(source.naturalWidth, source.naturalHeight));
      tmp.width = Math.max(1, Math.round(source.naturalWidth * scale));
      tmp.height = Math.max(1, Math.round(source.naturalHeight * scale));
      tmp.getContext('2d').drawImage(source, 0, 0, tmp.width, tmp.height);
      const src = tmp.toDataURL('image/png'), image = new Image();
      image.src = src; await image.decode();
      graphicImages.set(src, image);
      const fit = Math.min(1, 150 / tmp.width, 150 / tmp.height), w = tmp.width * fit, h = tmp.height * fit;
      addGraphic({ type: 'png', src, w, h, label: file.name.slice(0, 60) }, p, added === 0);
      added++;
    } catch {
      status.textContent = 'PNG tidak dapat dibuka. Coba file PNG lain.';
    } finally {
      URL.revokeObjectURL(url);
    }
  }
  if (byId('addPng')) byId('addPng').value = '';
  if (added) status.textContent = added + ' PNG ditambahkan ke ' + (p === -1 ? 'sampul' : isTwoMonthMode() ? twoMonthLabels[p] : months[p]) + '.';
}

function getProjectSnapshot() {
  const settings = {};
  for (const id of ['layout', 'monthsPerPage', 'template', 'font', 'paper', 'name', 'year', 'accent']) {
    if (byId(id)) settings[id] = byId(id).value;
  }
  settings.showHolidays = Boolean(byId('showHolidays')?.checked);
  settings.runningMode = typeof isRunningMode === 'function' ? isRunningMode() : true;
  const encoded = [];
  for (const [p, img] of photos) {
    if (!img) { encoded.push([p, null]); continue; }
    try {
      const tmp = document.createElement('canvas');
      const scale = Math.min(1, 2400 / Math.max(img.naturalWidth, img.naturalHeight));
      tmp.width = Math.round(img.naturalWidth * scale);
      tmp.height = Math.round(img.naturalHeight * scale);
      tmp.getContext('2d').drawImage(img, 0, 0, tmp.width, tmp.height);
      encoded.push([p, tmp.toDataURL('image/jpeg', .92)]);
    } catch {
      encoded.push([p, null]);
    }
  }
  const customTextsArr = [];
  for (const [p, list] of customTexts) customTextsArr.push([p, list]);
  const elemOffsetsArr = [];
  for (const [p, off] of elemOffsets) elemOffsetsArr.push([p, off]);
  const collagesArr = [];
  for (const [p, c] of pageCollages) {
    const encodedSlots = [];
    for (const sl of c.slots) {
      if (!sl.img) { encodedSlots.push({ img: null, crop: sl.crop }); continue; }
      try {
        const tmp = document.createElement('canvas');
        const scale = Math.min(1, 2400 / Math.max(sl.img.naturalWidth, sl.img.naturalHeight));
        tmp.width = Math.round(sl.img.naturalWidth * scale);
        tmp.height = Math.round(sl.img.naturalHeight * scale);
        tmp.getContext('2d').drawImage(sl.img, 0, 0, tmp.width, tmp.height);
        encodedSlots.push({ img: tmp.toDataURL('image/jpeg', .92), crop: sl.crop });
      } catch {
        encodedSlots.push({ img: null, crop: sl.crop });
      }
    }
    collagesArr.push([p, { layout: c.layout, gap: c.gap, activeSlot: c.activeSlot, slots: encodedSlots }]);
  }
  return {
    version: 5,
    settings,
    photos: encoded,
    crops: [...crops],
    collages: collagesArr,
    customTexts: customTextsArr,
    elemOffsets: elemOffsetsArr,
    graphics: [...graphics],
    lockedElements: [...lockedElements].map(([p, set]) => [p, [...set]]),
    stravaLogs: typeof stravaLogs !== 'undefined' ? [...stravaLogs] : [],
    ruangLari: {
      qr: byId('ruangLariQr')?.checked ?? true,
      user: byId('ruangLariUser')?.value || 'runner',
      token: byId('ruangLariToken')?.value || 'RL2026'
    },
    activePreset
  };
}

async function loadProjectData(data) {
  if (!data) return false;
  if (typeof data === 'string') {
    try { data = JSON.parse(data); } catch { return false; }
  }
  if (data.settings && !data.settings.layout) data.settings.layout = 'landscape';
  if (!data.settings || !Array.isArray(data.photos) || !Array.isArray(data.crops)) return false;
  const incoming = new Map();
  for (const [p, src] of data.photos) {
    if (!Number.isInteger(p) || p < -1 || p > 11) continue;
    if (src === null) { incoming.set(p, null); continue; }
    if (typeof src === 'string' && src.startsWith('data:image/')) {
      try {
        const img = new Image(); img.src = src; await img.decode(); incoming.set(p, img);
      } catch {}
    }
  }
  for (const id of ['layout', 'template', 'font', 'paper', 'name', 'year', 'accent']) {
    if (typeof data.settings[id] !== 'string') continue;
  }
  const importedGraphics = await validateGraphics(data.graphics || []);
  const importedTexts = validateTexts(data.customTexts || []);
  const importedOffsets = validateOffsets(data.elemOffsets || []);
  const importedLocks = validateLocks(data.lockedElements || []);

  recordHistory();
  pending.clear(); photos.clear(); crops.clear(); customTexts.clear(); elemOffsets.clear(); graphics.clear(); lockedElements.clear();
  importedGraphics.forEach((v, k) => graphics.set(k, v));
  importedTexts.forEach((v, k) => customTexts.set(k, v));
  importedOffsets.forEach((v, k) => elemOffsets.set(k, v));
  importedLocks.forEach((v, k) => lockedElements.set(k, v));

  if (Array.isArray(data.collages)) {
    pageCollages.clear();
    for (const [p, c] of data.collages) {
      const loadedSlots = [];
      for (const sl of (c.slots || [])) {
        let imgObj = null;
        if (typeof sl.img === 'string' && sl.img.startsWith('data:image/')) {
          try {
            imgObj = new Image(); imgObj.src = sl.img; await imgObj.decode();
          } catch {}
        }
        loadedSlots.push({ img: imgObj, crop: sl.crop || { x: 50, y: 50, z: 1 } });
      }
      pageCollages.set(p, {
        layout: c.layout || '1',
        gap: c.gap || 4,
        activeSlot: c.activeSlot || 0,
        slots: loadedSlots
      });
    }
  }

  activePreset = typeof data.activePreset === 'string' && stylePresets[data.activePreset] ? data.activePreset : null;
  graphicRevision++;
  incoming.forEach((img, p) => photos.set(p, img));
  data.crops.forEach(([p, crop]) => crops.set(p, crop));
  for (const id of ['layout', 'monthsPerPage', 'template', 'font', 'paper', 'name', 'year', 'accent']) {
    if (byId(id) && data.settings[id]) byId(id).value = data.settings[id];
  }
  if (typeof data.settings.showHolidays === 'boolean') {
    showHolidays.checked = data.settings.showHolidays;
  }
  if (typeof data.settings.runningMode === 'boolean' && byId('runningMode')) {
    byId('runningMode').checked = data.settings.runningMode;
  }
  if (Array.isArray(data.stravaLogs) && typeof stravaLogs !== 'undefined') {
    stravaLogs.clear();
    for (const [k, v] of data.stravaLogs) {
      if (typeof k === 'string' && v && typeof v === 'object') {
        stravaLogs.set(k, { km: Number(v.km) || 0, type: v.type || 'easy', name: v.name || '' });
      }
    }
  }
  if (data.ruangLari) {
    if (byId('ruangLariQr') && typeof data.ruangLari.qr === 'boolean') byId('ruangLariQr').checked = data.ruangLari.qr;
    if (byId('ruangLariUser') && typeof data.ruangLari.user === 'string') byId('ruangLariUser').value = data.ruangLari.user;
    if (byId('ruangLariToken') && typeof data.ruangLari.token === 'string') byId('ruangLariToken').value = data.ruangLari.token;
    if (typeof updateRuangLariUrl === 'function') updateRuangLariUrl();
  }
  ensureFont(byId('font').value);
  buildSlides();
  page.onchange();
  return true;
}

let autosaveTimer = null;
function triggerAutosave() {
  if (!window.KP_CONFIG || !window.KP_CONFIG.autosaveUrl) return;
  const ind = document.getElementById('kpSaveIndicator');
  if (ind) {
    ind.textContent = 'Menyimpan perubahan...';
    ind.className = 'text-amber-400 text-xs font-medium';
  }
  clearTimeout(autosaveTimer);
  autosaveTimer = setTimeout(async () => {
    try {
      const snapshot = getProjectSnapshot();
      const payload = {
        name: byId('name')?.value || 'Kalender Lari',
        version_counter: (window.KP_CONFIG.versionCounter || 1) + 1,
        state_draft: snapshot
      };
      if (window.KP_CONFIG.projectId) {
        payload.project_id = window.KP_CONFIG.projectId;
      }
      if (window.KP_CONFIG.anonymousUuid) {
        payload.anonymous_uuid = window.KP_CONFIG.anonymousUuid;
      }
      const res = await fetch(window.KP_CONFIG.autosaveUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': window.KP_CONFIG.csrfToken || ''
        },
        body: JSON.stringify(payload)
      });
      const resJson = await res.json();
      if (res.ok && resJson.success) {
        window.KP_CONFIG.versionCounter = resJson.project?.version_counter || payload.version_counter;
        if (ind) {
          ind.textContent = 'Tersimpan otomatis';
          ind.className = 'text-emerald-400 text-xs font-medium';
        }
      } else {
        if (ind) {
          ind.textContent = 'Gagal menyimpan otomatis';
          ind.className = 'text-red-400 text-xs font-medium';
        }
      }
    } catch {
      if (ind) {
        ind.textContent = 'Gagal menyimpan (offline)';
        ind.className = 'text-red-400 text-xs font-medium';
      }
    }
  }, 2000);
}

window.KP_STUDIO = {
  getProjectSnapshot,
  loadProjectData,
  triggerAutosave
};

function initExportHandlers() {
  if (byId('download')) {
    byId('download').onclick = async () => {
      if (byId('font')) await ensureFont(byId('font').value);
      if (document.fonts) await document.fonts.ready;
      const isPoster = typeof isPoster12M === 'function' && isPoster12M();
      const is4M = typeof isFourMonthMode === 'function' ? isFourMonthMode() : false;
      const is2M = isTwoMonthMode();
      const label = isPoster ? 'Poster-12-Bulan' : (selected() === -1 ? 'Sampul' : (is4M ? fourMonthLabels[selected()].replace(/\s*—\s*/g, '-') : (is2M ? twoMonthLabels[selected()].replace(/\s*—\s*/g, '-') : months[selected()])));
      const link = document.createElement('a');
      link.download = 'Running-Calendar-' + year() + '-' + label + '.png';
      const output = document.createElement('canvas');
      dimensions(output, true);
      draw(output, selected(), true);
      try {
        link.href = output.toDataURL('image/png');
        link.click();
      } catch {
        if (status) status.textContent = 'Gagal membuat file PNG.';
      }
    };
  }

  if (byId('print')) {
    byId('print').onclick = async () => {
      const printWindow = window.open('', '_blank');
      if (!printWindow) { if (status) status.textContent = 'Izinkan pop-up untuk mencetak kalender.'; return; }
      if (byId('font')) await ensureFont(byId('font').value);
      if (document.fonts) await document.fonts.ready;
      const doc = printWindow.document;
      doc.title = 'Running Calendar ' + year();
      const style = doc.createElement('style');
      style.textContent = '@page{size:' + (byId('layout')?.value === 'wall' ? '320mm 480mm;margin:0' : 'A4 landscape;margin:8mm') + '}body{margin:0}img{width:100%;display:block;break-inside:avoid}img:not(:last-child){break-after:page}';
      doc.head.append(style);
      const output = document.createElement('canvas');
      dimensions(output, true);
      const maxP = getMaxPage();
      const isPoster = typeof isPoster12M === 'function' && isPoster12M();
      const is4M = typeof isFourMonthMode === 'function' ? isFourMonthMode() : false;
      const is2M = isTwoMonthMode();
      try {
        const startP = isPoster ? 0 : -1;
        for (let p = startP; p <= maxP; p++) {
          draw(output, p, true);
          const image = doc.createElement('img');
          image.alt = isPoster ? 'Poster 12 Bulan' : (p === -1 ? 'Sampul' : (is4M ? fourMonthLabels[p] : (is2M ? twoMonthLabels[p] : months[p])));
          image.src = output.toDataURL('image/jpeg', .94);
          doc.body.append(image);
        }
        await Promise.all(Array.from(doc.images, image => image.decode().catch(() => { })));
        printWindow.focus();
        printWindow.print();
      } catch {
        printWindow.close();
        if (status) status.textContent = 'Gagal menyiapkan halaman cetak.';
      }
    };
  }

  if (byId('saveProject')) {
    byId('saveProject').onclick = () => {
      const snapshot = getProjectSnapshot();
      const blob = new Blob([JSON.stringify(snapshot, null, 2)], { type: 'application/json' });
      const url = URL.createObjectURL(blob), link = document.createElement('a');
      link.href = url;
      link.download = 'Kalender-' + year() + '-proyek.json';
      link.click();
      setTimeout(() => URL.revokeObjectURL(url), 1000);
      if (status) status.textContent = 'Proyek siap diunduh.';
    };
  }

  if (byId('loadProject')) {
    byId('loadProject').onchange = async event => {
      try {
        const file = event.target.files[0];
        if (!file) return;
        const data = JSON.parse(await file.text());
        const ok = await loadProjectData(data);
        if (ok) {
          if (status) status.textContent = 'Proyek berhasil dibuka.';
        } else {
          throw new Error();
        }
      } catch {
        if (status) status.textContent = 'File proyek tidak valid. Gunakan JSON hasil Simpan proyek.';
      }
      event.target.value = '';
    };
  }
}
