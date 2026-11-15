/**
 * Running Calendar - State & History
 * Holds DOM references, active page, maps for custom text, offsets, graphics,
 * locks, and undo/redo history management.
 */

const byId = id => document.getElementById(id);

// DOM input element shortcuts
const page = byId('page'), nameInput = byId('name'), yearInput = byId('year'), accent = byId('accent');
const monthsPerPage = byId('monthsPerPage'), showHolidays = byId('showHolidays');
const canvas = byId('canvas'), status = byId('status');

// Reactive state collections
const photos = new Map(), pending = new Map(), crops = new Map();
const pageCollages = new Map();
const customTexts = new Map(), elemOffsets = new Map();
const selectedElements = new Set(), graphics = new Map(), graphicImages = new Map(), lockedElements = new Map(), stravaLogs = new Map();
const elementBounds = new Map();
const loadedFonts = new Map();

function getPageCollage(p = selected()) {
  if (!pageCollages.has(p)) {
    pageCollages.set(p, {
      layout: '1',
      gap: 4,
      activeSlot: 0,
      slots: [
        { img: photos.get(p) || null, crop: crops.get(p) || { x: 50, y: 50, z: 1 } },
        { img: null, crop: { x: 50, y: 50, z: 1 } },
        { img: null, crop: { x: 50, y: 50, z: 1 } },
        { img: null, crop: { x: 50, y: 50, z: 1 } },
        { img: null, crop: { x: 50, y: 50, z: 1 } }
      ]
    });
  }
  const conf = pageCollages.get(p);
  if (photos.has(p) && conf.slots[0].img !== photos.get(p)) {
    conf.slots[0].img = photos.get(p);
  }
  if (crops.has(p) && conf.slots[0].crop !== crops.get(p)) {
    conf.slots[0].crop = crops.get(p);
  }
  return conf;
}

function setSlotPhoto(p, slotIdx, img) {
  const conf = getPageCollage(p);
  if (!conf.slots[slotIdx]) conf.slots[slotIdx] = { img: null, crop: { x: 50, y: 50, z: 1 } };
  conf.slots[slotIdx].img = img;
  if (slotIdx === 0) {
    if (img) photos.set(p, img);
    else photos.delete(p);
  }
}

function setSlotCrop(p, slotIdx, crop) {
  const conf = getPageCollage(p);
  if (!conf.slots[slotIdx]) conf.slots[slotIdx] = { img: null, crop: { x: 50, y: 50, z: 1 } };
  conf.slots[slotIdx].crop = { ...conf.slots[slotIdx].crop, ...crop };
  if (slotIdx === 0) {
    crops.set(p, conf.slots[0].crop);
  }
}

// Selection & interaction state
let activeTextId = null, dragState = null, activeElement = null;
let viewZoom = 1, panMode = false, spaceHeld = false, graphicRevision = 0, activePreset = null;

// Helpers & Accessors
const selected = () => Number(page.value);
const isTwelveMonthMode = () => (byId('monthsPerPage') && byId('monthsPerPage').value === '12') || (byId('template') && byId('template').value === 'poster12m');
const isPoster12M = isTwelveMonthMode;
const isFourMonthMode = () => !isTwelveMonthMode() && byId('monthsPerPage') && byId('monthsPerPage').value === '4';
const isTwoMonthMode = () => !isTwelveMonthMode() && !isFourMonthMode() && byId('monthsPerPage') && byId('monthsPerPage').value === '2';
const getMaxPage = () => {
  if (isTwelveMonthMode()) return 0;
  if (isFourMonthMode()) return 2;
  if (isTwoMonthMode()) return 5;
  return 11;
};
const isRunningMode = () => byId('runningMode') ? byId('runningMode').checked : true;
const year = () => Number(yearInput.value) >= 1900 && Number(yearInput.value) <= 2199 && Number.isInteger(Number(yearInput.value)) ? Number(yearInput.value) : 2027;

function makeId() { return Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 9); }
function graphicForKey(key) { return key?.startsWith('graphic:') ? (graphics.get(selected()) || []).find(g => 'graphic:' + g.id === key) : null; }
function textForKey(key) { return key?.startsWith('custom:') ? (customTexts.get(selected()) || []).find(t => 'custom:' + t.id === key) : null; }
function elementLabel(key) {
  const g = graphicForKey(key);
  if (g) return g.label || g.emoji || iconLabels[g.icon] || 'PNG';
  const t = textForKey(key);
  return t ? t.text || 'Teks kosong' : elementLabels[key] || key;
}
function isLocked(key) { return lockedElements.get(selected())?.has(key) || false; }

function getGraphicImage(src) {
  if (!src) return null;
  if (!graphicImages.has(src)) {
    const image = new Image();
    graphicImages.set(src, image);
    image.onload = () => { graphicRevision++; if (typeof render === 'function') render(); };
    image.onerror = () => { status.textContent = 'Salah satu PNG tidak dapat dimuat.'; };
    image.src = src;
  }
  return graphicImages.get(src);
}

function defaultGraphicPosition(w = 64, h = 64) {
  const b = elementBounds.get('photo');
  if (b && typeof worldBox === 'function') {
    const p = worldBox(b);
    return { x: Math.round(p.x + Math.max(12, (p.w - w) / 2)), y: Math.round(p.y + Math.max(12, (p.h - h) / 2)) };
  }
  return { x: 36, y: 36 };
}

function addGraphic(spec, pageIndex = selected(), remember = true) {
  if (remember) recordHistory();
  const list = graphics.get(pageIndex) || [];
  const pos = defaultGraphicPosition(spec.w || 64, spec.h || 64);
  const g = { id: makeId(), type: 'emoji', x: pos.x, y: pos.y, w: 64, h: 64, rotation: 0, opacity: 1, color: accent.value, ...spec };
  list.push(g);
  graphics.set(pageIndex, list);
  graphicRevision++;
  if (pageIndex === selected()) chooseElement('graphic:' + g.id);
  else if (typeof render === 'function') render();
  return g;
}

function getFontFamily() {
  const val = byId('font').value;
  return val.includes(' ') ? ('"' + val + '", sans-serif') : val + ', sans-serif';
}

function ensureFont(family) {
  if (['Arial', 'Georgia', 'Verdana', 'Courier New'].includes(family)) return Promise.resolve();
  if (!loadedFonts.has(family)) {
    const link = document.createElement('link'); link.rel = 'stylesheet';
    link.href = 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent(family).replace(/%20/g, '+') + '&display=swap';
    const promise = new Promise(resolve => {
      link.onload = async () => {
        try { await document.fonts.load('24px "' + family + '"'); } catch { }
        resolve();
        if (typeof render === 'function') render();
      };
      link.onerror = () => resolve();
    });
    loadedFonts.set(family, promise);
    document.head.append(link);
  }
  return loadedFonts.get(family);
}

// History stores state snapshots without re-encoding data URLs
const historyPast = [], historyFuture = [];
let historyApplying = false, lastHistorySignature = null;
const settingIds = ['layout', 'monthsPerPage', 'template', 'font', 'paper', 'name', 'year', 'accent'];

function captureState() {
  return {
    settings: Object.fromEntries(settingIds.map(id => [id, byId(id).value])),
    showHolidays: showHolidays.checked,
    runningMode: isRunningMode(),
    page: selected(),
    preset: activePreset,
    texts: [...customTexts].map(([p, list]) => [p, list.map(t => ({ ...t }))]),
    offsets: [...elemOffsets].map(([p, off]) => [p, Object.fromEntries(Object.entries(off).map(([k, v]) => [k, { ...v }]))]),
    graphics: [...graphics].map(([p, list]) => [p, list.map(g => ({ ...g }))]),
    locks: [...lockedElements].map(([p, set]) => [p, [...set]]),
    stravaLogs: [...stravaLogs].map(([k, v]) => [k, { ...v }]),
    photos: [...photos],
    crops: [...crops].map(([p, c]) => [p, { ...c }]),
    collages: [...pageCollages].map(([p, c]) => [p, {
      layout: c.layout,
      gap: c.gap,
      activeSlot: c.activeSlot,
      slots: c.slots.map(s => ({ img: s.img, crop: { ...s.crop } }))
    }])
  };
}

function stateSignature(s) {
  return JSON.stringify({
    ...s,
    photos: s.photos.map(([p, img]) => [p, img?.src || null]),
    collages: (s.collages || []).map(([p, c]) => [p, c.layout, c.gap, c.slots.map(sl => sl.img?.src || null)])
  }, (key, value) => key === 'src' && typeof value === 'string' && value.length > 150 ? value.length + ':' + value.slice(0, 80) : value);
}

function recordHistory() {
  if (historyApplying) return;
  const state = captureState(), signature = stateSignature(state);
  if (signature === lastHistorySignature) return;
  lastHistorySignature = signature;
  historyPast.push(state);
  if (historyPast.length > 30) historyPast.shift();
  historyFuture.length = 0;
  syncHistoryButtons();
  if (typeof triggerAutosave === 'function') triggerAutosave();
}

function restoreState(s) {
  historyApplying = true;
  try {
    for (const id of settingIds) byId(id).value = s.settings[id];
    showHolidays.checked = s.showHolidays;
    if (byId('runningMode') && s.runningMode !== undefined) byId('runningMode').checked = s.runningMode;
    for (const [map, list] of [[customTexts, s.texts], [elemOffsets, s.offsets], [graphics, s.graphics], [photos, s.photos], [crops, s.crops]]) {
      map.clear();
      for (const [p, item] of list) {
        if (Array.isArray(item)) map.set(p, item.map(v => ({ ...v })));
        else if (map === elemOffsets) map.set(p, Object.fromEntries(Object.entries(item).map(([k, v]) => [k, { ...v }])));
        else if (map === crops) map.set(p, { ...item });
        else map.set(p, item);
      }
    }
    lockedElements.clear();
    for (const [p, keys] of s.locks) lockedElements.set(p, new Set(keys));
    if (s.stravaLogs) { stravaLogs.clear(); for (const [k, v] of s.stravaLogs) stravaLogs.set(k, { ...v }); }
    if (s.collages) {
      pageCollages.clear();
      for (const [p, c] of s.collages) {
        pageCollages.set(p, {
          layout: c.layout || '1',
          gap: c.gap || 4,
          activeSlot: c.activeSlot || 0,
          slots: c.slots.map(sl => ({ img: sl.img, crop: { ...sl.crop } }))
        });
      }
    }
    activePreset = s.preset;
    pending.clear();
    selectedElements.clear();
    activeElement = null;
    page.value = s.page;
    graphicRevision++;
    ensureFont(byId('font').value);
    buildSlides();
    page.onchange();
  } finally {
    historyApplying = false;
    lastHistorySignature = null;
    syncHistoryButtons();
  }
}

function undo() {
  if (!historyPast.length) return;
  historyFuture.push(captureState());
  restoreState(historyPast.pop());
}

function redo() {
  if (!historyFuture.length) return;
  historyPast.push(captureState());
  restoreState(historyFuture.pop());
}

function syncHistoryButtons() {
  byId('undo').disabled = !historyPast.length;
  byId('redo').disabled = !historyFuture.length;
}
