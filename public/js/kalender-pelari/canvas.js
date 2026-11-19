/**
 * Running Calendar - Canvas Renderer
 * Handles high-resolution canvas dimensioning, Wall & Landscape calendar layouts
 * (1-month & 2-month modes), Cover pages, 16 distinct templates with unique layouts
 * and photo placements, optional Running Tracker Mode (runningMode), and selection overlays.
 */

function dimensions(target, exporting = false) {
  const wall = byId('layout').value === 'wall';
  const w = wall ? (exporting ? 3780 : 1280) : 2382;
  const h = wall ? (exporting ? 5669 : 1920) : 1590;
  if (target.width !== w) target.width = w;
  if (target.height !== h) target.height = h;
}

function calculateCollageRects(x, y, w, h, layout = '1', gap = 4) {
  gap = Math.max(0, Math.min(30, Number(gap) || 4));
  if (layout === '2-v') {
    const w1 = Math.round((w - gap) / 2);
    const w2 = w - gap - w1;
    return [{ x, y, w: w1, h }, { x: x + w1 + gap, y, w: w2, h }];
  }
  if (layout === '2-h') {
    const h1 = Math.round((h - gap) / 2);
    const h2 = h - gap - h1;
    return [{ x, y, w: w1, h: h1 }, { x, y: y + h1 + gap, w, h: h2 }];
  }
  if (layout === '3-left') {
    const w1 = Math.round((w - gap) * 0.6);
    const w2 = w - gap - w1;
    const h1 = Math.round((h - gap) / 2);
    const h2 = h - gap - h1;
    return [
      { x, y, w: w1, h },
      { x: x + w1 + gap, y, w: w2, h: h1 },
      { x: x + w1 + gap, y: y + h1 + gap, w: w2, h: h2 }
    ];
  }
  if (layout === '3-top') {
    const h1 = Math.round((h - gap) * 0.58);
    const h2 = h - gap - h1;
    const w1 = Math.round((w - gap) / 2);
    const w2 = w - gap - w1;
    return [
      { x, y, w: w1, h: h1 },
      { x, y: y + h1 + gap, w: w1, h: h2 },
      { x: x + w1 + gap, y: y + h1 + gap, w: w2, h: h2 }
    ];
  }
  if (layout === '3-cols') {
    const colW = Math.round((w - gap * 2) / 3);
    const colW3 = w - gap * 2 - colW * 2;
    return [
      { x, y, w: colW, h },
      { x: x + colW + gap, y, w: colW, h },
      { x: x + (colW + gap) * 2, y, w: colW3, h }
    ];
  }
  if (layout === '4-grid') {
    const w1 = Math.round((w - gap) / 2);
    const w2 = w - gap - w1;
    const h1 = Math.round((h - gap) / 2);
    const h2 = h - gap - h1;
    return [
      { x, y, w: w1, h: h1 },
      { x: x + w1 + gap, y, w: w2, h: h1 },
      { x, y: y + h1 + gap, w: w1, h: h2 },
      { x: x + w1 + gap, y: y + h1 + gap, w: w2, h: h2 }
    ];
  }
  if (layout === '4-hero') {
    const w1 = Math.round((w - gap) * 0.6);
    const w2 = w - gap - w1;
    const h1 = Math.round((h - gap * 2) / 3);
    const h3 = h - gap * 2 - h1 * 2;
    return [
      { x, y, w: w1, h },
      { x: x + w1 + gap, y, w: w2, h: h1 },
      { x: x + w1 + gap, y: y + h1 + gap, w: w2, h: h1 },
      { x: x + w1 + gap, y: y + (h1 + gap) * 2, w: w2, h: h3 }
    ];
  }
  if (layout === '5-mosaic') {
    const w1 = Math.round((w - gap) * 0.52);
    const w2 = w - gap - w1;
    const sw1 = Math.round((w2 - gap) / 2);
    const sw2 = w2 - gap - sw1;
    const sh1 = Math.round((h - gap) / 2);
    const sh2 = h - gap - sh1;
    return [
      { x, y, w: w1, h },
      { x: x + w1 + gap, y, w: sw1, h: sh1 },
      { x: x + w1 + gap + sw1 + gap, y, w: sw2, h: sh1 },
      { x: x + w1 + gap, y: y + sh1 + gap, w: sw1, h: sh2 },
      { x: x + w1 + gap + sw1 + gap, y: y + sh1 + gap, w: sw2, h: sh2 }
    ];
  }
  if (layout === '5-split') {
    const h1 = Math.round((h - gap) / 2);
    const h2 = h - gap - h1;
    const wt = Math.round((w - gap) / 2);
    const wb = Math.round((w - gap * 2) / 3);
    return [
      { x, y, w: wt, h: h1 },
      { x: x + wt + gap, y, w: w - gap - wt, h: h1 },
      { x, y: y + h1 + gap, w: wb, h: h2 },
      { x: x + wb + gap, y: y + h1 + gap, w: wb, h: h2 },
      { x: x + (wb + gap) * 2, y: y + h1 + gap, w: w - gap * 2 - wb * 2, h: h2 }
    ];
  }
  return [{ x, y, w, h }];
}

function draw(target, pageIndex, isExport = false) {
  const ctx = target.getContext('2d');
  const editable = target === canvas && !isExport;
  const elementStack = [];
  if (editable) elementBounds.clear();

  const color = (accent && accent.value) || (byId('accent') && byId('accent').value) || '#f45123';
  const theme = byId('template').value || 'classic';
  const paper = byId('paper').value || '#ffffff';

  function isColorDark(hex) {
    if (!hex || typeof hex !== 'string') return false;
    const clean = hex.replace('#', '');
    if (clean.length < 6) return false;
    const r = parseInt(clean.substring(0, 2), 16) / 255;
    const g = parseInt(clean.substring(2, 4), 16) / 255;
    const b = parseInt(clean.substring(4, 6), 16) / 255;
    const lum = 0.2126 * r + 0.7152 * g + 0.0722 * b;
    return lum < 0.45;
  }
  const paperIsDark = isColorDark(paper);
  const dark = paperIsDark || theme === 'night' || theme === 'neon' || theme === 'strava' || theme === 'club' || theme === 'acid' || theme === 'blueprint' || theme === 'sunset';
  const foreground = dark ? '#e9edf5' : '#0f172a';
  const surface = dark ? '#25334a' : theme === 'retro' ? '#e6dbc5' : theme === 'journal' ? '#f0ebd8' : theme === 'kraft' ? '#ded4c3' : theme === 'stamp' ? '#ece5d8' : theme === 'arch' ? '#eee6dc' : theme === 'metro' ? '#e2e8f0' : theme === 'topography' ? '#e8ebde' : theme === 'infographic' ? '#fef3c7' : '#f1f5f9';
  const isRun = isRunningMode();

  const rgb = (color.slice(1).match(/../g) || ['ff', '55', '22']).map(val => parseInt(val, 16) / 255).map(val => val <= .04045 ? val / 12.92 : ((val + .055) / 1.055) ** 2.4);
  const ink = .2126 * rgb[0] + .7152 * rgb[1] + .0722 * rgb[2] > .179 ? '#111' : '#fff';
  const name = nameInput.value.trim() || 'Nama Pelari';
  const wall = byId('layout').value === 'wall';
  const is2M = isTwoMonthMode();
  const is4M = typeof isFourMonthMode === 'function' ? isFourMonthMode() : false;
  const is12M = typeof isTwelveMonthMode === 'function' ? isTwelveMonthMode() : (typeof isPoster12M === 'function' && isPoster12M());
  const off = elemOffsets.get(pageIndex) || { name: { x: 0, y: 0 }, year: { x: 0, y: 0 } };
  const showHol = showHolidays.checked;
  const redHolidayColor = '#d92525';
  const currentFontFamily = getFontFamily();

  ctx.setTransform(target.width / (wall ? 640 : 794), 0, 0, target.height / (wall ? 960 : 530), 0, 0);

  function register(key, x, y, w, h) {
    if (!editable) return;
    const m = ctx.getTransform();
    const sx = target.width / (wall ? 640 : 794), sy = target.height / (wall ? 960 : 530);
    const matrix = new DOMMatrix([m.a / sx, m.b / sy, m.c / sx, m.d / sy, m.e / sx, m.f / sy]);
    elementBounds.set(key, { key, x, y, w, h, matrix, parent: elementStack.at(-1) || null });
  }

  function beginElement(key, x, y, w, h) {
    ctx.save();
    const d = off[key] || { x: 0, y: 0 };
    ctx.translate(d.x || 0, d.y || 0);
    register(key, x, y, w, h);
    elementStack.push(key);
  }

  function endElement() {
    elementStack.pop();
    ctx.restore();
  }

  function elementText(key, val, x, y, fnt = '11px Arial', fill = '#111', max = 794) {
    ctx.save();
    const d = off[key] || { x: 0, y: 0 };
    ctx.translate(d.x || 0, d.y || 0);
    ctx.font = fnt.replace(/(Arial|Georgia)/g, currentFontFamily);
    const match = fnt.match(/([\d.]+)px/);
    const size = match ? Number(match[1]) : 11;
    register(key, x, y - size, Math.min(ctx.measureText(String(val)).width, max), size + 5);
    text(val, x, y, fnt, fill, max);
    ctx.restore();
  }

  function box(x, y, w, h, fill) {
    ctx.fillStyle = fill === '#fff' ? paper : fill === '#ddd' || fill === '#dedede' ? surface : fill === '#f1f1f1' ? (dark ? '#182336' : paper) : fill;
    ctx.fillRect(x, y, w, h);
  }

  function line(x, y, x2, y2, fill = '#555') {
    ctx.strokeStyle = dark ? '#718096' : fill;
    ctx.lineWidth = .8;
    ctx.beginPath();
    ctx.moveTo(x, y);
    ctx.lineTo(x2, y2);
    ctx.stroke();
  }

  function text(val, x, y, fnt = '11px Arial', fill = '#111', max = 794) {
    ctx.fillStyle = fill === '#111' ? foreground : fill;
    ctx.font = fnt.replace(/(Arial|Georgia)/g, currentFontFamily);
    ctx.fillText(val, x, y, max);
  }

  function photo(x, y, w, h) {
    const collage = getPageCollage(pageIndex);
    const rects = calculateCollageRects(x, y, w, h, collage.layout, collage.gap);

    beginElement('photo', x, y, w, h);
    box(x, y, w, h, dark ? '#1c283c' : '#ecece8');

    rects.forEach((r, idx) => {
      const slot = collage.slots[idx] || { img: null, crop: { x: 50, y: 50, z: 1 } };
      const img = slot.img || (idx === 0 ? getDefaultBg(pageIndex) : null);
      const crop = slot.crop || { x: 50, y: 50, z: 1 };

      ctx.save();
      ctx.beginPath();
      if (theme === 'arch' && rects.length === 1) {
        const radius = r.w / 2;
        ctx.moveTo(r.x, r.y + radius);
        ctx.arc(r.x + radius, r.y + radius, radius, Math.PI, 0, false);
        ctx.lineTo(r.x + r.w, r.y + r.h);
        ctx.lineTo(r.x, r.y + r.h);
        ctx.closePath();
      } else {
        ctx.rect(r.x, r.y, r.w, r.h);
      }
      ctx.clip();

      if (img?.naturalWidth) {
        const scale = Math.max(r.w / img.naturalWidth, r.h / img.naturalHeight) * crop.z;
        const sw = r.w / scale, sh = r.h / scale;
        ctx.drawImage(img, (img.naturalWidth - sw) * crop.x / 100, (img.naturalHeight - sh) * crop.y / 100, sw, sh, r.x, r.y, r.w, r.h);
      } else {
        box(r.x, r.y, r.w, r.h, dark ? '#223046' : '#deded9');
        text('+ Foto ' + (idx + 1), r.x + 8, r.y + r.h / 2, 'bold 11px Arial', dark ? '#8ea3c4' : '#666');
      }

      if (editable && collage.layout !== '1' && collage.activeSlot === idx) {
        ctx.strokeStyle = '#2459d3';
        ctx.lineWidth = 2.5;
        ctx.strokeRect(r.x + 1, r.y + 1, r.w - 2, r.h - 2);
      }
      ctx.restore();

      register('photo:slot' + idx, r.x, r.y, r.w, r.h);
    });

    endElement();
  }

  function drawStampPerforations(sx, sy, sw, sh, dotR = 3.5, gap = 14) {
    ctx.save();
    ctx.fillStyle = paper;
    for (let px = sx + gap; px < sx + sw - gap / 2; px += gap) {
      ctx.beginPath(); ctx.arc(px, sy, dotR, 0, Math.PI * 2); ctx.fill();
      ctx.beginPath(); ctx.arc(px, sy + sh, dotR, 0, Math.PI * 2); ctx.fill();
    }
    for (let py = sy + gap; py < sy + sh - gap / 2; py += gap) {
      ctx.beginPath(); ctx.arc(sx, py, dotR, 0, Math.PI * 2); ctx.fill();
      ctx.beginPath(); ctx.arc(sx + sw, py, dotR, 0, Math.PI * 2); ctx.fill();
    }
    ctx.restore();
  }

  function drawPostmark(px, py, radius = 40, label = 'RUNNER POST') {
    ctx.save();
    ctx.translate(px, py);
    ctx.rotate(-0.12);
    ctx.strokeStyle = color;
    ctx.lineWidth = 1.8;
    ctx.beginPath(); ctx.arc(0, 0, radius, 0, Math.PI * 2); ctx.stroke();
    ctx.beginPath(); ctx.arc(0, 0, radius - 4, 0, Math.PI * 2); ctx.stroke();
    ctx.fillStyle = color;
    ctx.font = 'bold 8px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(label, 0, -radius / 2 + 4);
    ctx.fillText(year() + ' • FINISH', 0, 3);
    ctx.fillText('OFFICIAL', 0, radius / 2 - 2);
    for (let i = -1; i <= 1; i++) {
      const wy = i * 9;
      ctx.beginPath();
      ctx.moveTo(radius + 5, wy);
      ctx.bezierCurveTo(radius + 18, wy - 5, radius + 32, wy + 5, radius + 46, wy);
      ctx.stroke();
    }
    ctx.restore();
  }

  function drawFilmstripRails(fx, fy, fw, fh) {
    ctx.save();
    const railH = 20;
    box(fx, fy, fw, railH, '#111');
    box(fx, fy + fh - railH, fw, railH, '#111');
    ctx.fillStyle = '#ffffff';
    for (let x = fx + 8; x < fx + fw - 12; x += 22) {
      ctx.beginPath();
      ctx.roundRect(x, fy + 4, 10, 12, 2);
      ctx.roundRect(x, fy + fh - railH + 4, 10, 12, 2);
      ctx.fill();
    }
    ctx.fillStyle = '#f59e0b';
    ctx.font = 'bold 8px monospace';
    ctx.fillText('▶ 24A • ISO 400', fx + 16, fy + 14);
    ctx.fillText('KODAK TX 400', fx + fw - 85, fy + 14);
    ctx.restore();
  }

  function drawBibPins(bx, by, bw, bh) {
    ctx.save();
    const pinInset = 12;
    const pinR = 4.5;
    [[bx + pinInset, by + pinInset], [bx + bw - pinInset, by + pinInset],
     [bx + pinInset, by + bh - pinInset], [bx + bw - pinInset, by + bh - pinInset]].forEach(([px, py]) => {
      ctx.fillStyle = '#ffffff';
      ctx.beginPath(); ctx.arc(px, py, pinR, 0, Math.PI * 2); ctx.fill();
      ctx.strokeStyle = '#444'; ctx.lineWidth = 1.4; ctx.stroke();
    });
    ctx.restore();
  }

  function drawWashiTape(tx, ty, tw, th, rot = -0.05) {
    ctx.save();
    ctx.translate(tx, ty);
    ctx.rotate(rot);
    ctx.fillStyle = 'rgba(238, 226, 200, 0.85)';
    ctx.fillRect(-tw / 2, -th / 2, tw, th);
    ctx.strokeStyle = 'rgba(180, 160, 130, 0.45)';
    ctx.lineWidth = 1;
    ctx.strokeRect(-tw / 2, -th / 2, tw, th);
    ctx.restore();
  }

  function drawMetroRoute(mx, my, mw, stops = ['START', '10K', 'HALF', '30K', 'FINISH']) {
    ctx.save();
    ctx.strokeStyle = color;
    ctx.lineWidth = 3.5;
    ctx.beginPath(); ctx.moveTo(mx, my); ctx.lineTo(mx + mw, my); ctx.stroke();
    const step = mw / (stops.length - 1);
    stops.forEach((s, idx) => {
      const sx = mx + idx * step;
      ctx.fillStyle = '#ffffff';
      ctx.beginPath(); ctx.arc(sx, my, 5.5, 0, Math.PI * 2); ctx.fill();
      ctx.strokeStyle = color; ctx.lineWidth = 2.5; ctx.stroke();
      ctx.fillStyle = foreground;
      ctx.font = 'bold 8px Arial';
      ctx.textAlign = 'center';
      ctx.fillText(s, sx, my + 13);
    });
    ctx.restore();
  }

  const qrCache = new Map();
  function getQrCanvas(text, size = 120, darkColor = '#000000', lightColor = '#ffffff') {
    const key = text + '|' + size + '|' + darkColor + '|' + lightColor;
    if (qrCache.has(key)) return qrCache.get(key);
    if (typeof QRCode === 'undefined') return null;
    const div = document.createElement('div');
    div.style.display = 'none';
    document.body.appendChild(div);
    try {
      new QRCode(div, {
        text: text,
        width: size,
        height: size,
        colorDark: darkColor,
        colorLight: lightColor,
        correctLevel: QRCode.CorrectLevel.M
      });
      const qrCanvas = div.querySelector('canvas');
      if (qrCanvas) {
        const copy = document.createElement('canvas');
        copy.width = size;
        copy.height = size;
        copy.getContext('2d').drawImage(qrCanvas, 0, 0);
        qrCache.set(key, copy);
        return copy;
      }
    } catch (err) {
      console.warn('QR Code error:', err);
    } finally {
      div.remove();
    }
    return null;
  }

  function drawRuangLariQrBadge(bx, by, bw, bh, monthIdx) {
    if (!byId('ruangLariQr')?.checked) return;
    const pageOff = (typeof off !== 'undefined' && off) ? off : (elemOffsets.get(pageIndex) || {});
    const o = pageOff['qr_badge'] || { x: 0, y: 0 };
    const rx = bx + (o.x || 0);
    const ry = by + (o.y || 0);
    register('qr_badge', rx, ry, bw, bh);

    const u = (byId('ruangLariUser')?.value || 'runner').trim() || 'runner';
    const t = (byId('ruangLariToken')?.value || 'RL2026').trim();
    const mStr = String(monthIdx >= 0 ? monthIdx + 1 : 1).padStart(2, '0');
    const targetUrl = window.KP_QR_BASE_URL
      ? window.KP_QR_BASE_URL.replace('{user}', encodeURIComponent(u)).replace('{month}', `${year()}-${mStr}`).replace('{token}', encodeURIComponent(t))
      : `https://ruanglari.com/runner/${encodeURIComponent(u)}/${year()}-${mStr}?token=${encodeURIComponent(t)}`;

    const qrSize = Math.max(36, Math.min(bw - 10, bh - 20));
    const qrImg = getQrCanvas(targetUrl, 160, '#0f172a', '#ffffff');

    ctx.save();
    ctx.fillStyle = '#ffffff';
    ctx.shadowColor = 'rgba(0, 0, 0, 0.08)';
    ctx.shadowBlur = 6;
    ctx.shadowOffsetY = 2;
    ctx.beginPath();
    ctx.roundRect(rx, ry, bw, bh, 6);
    ctx.fill();
    ctx.shadowColor = 'transparent';

    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 1;
    ctx.stroke();

    if (qrImg) {
      const qx = rx + (bw - qrSize) / 2;
      const qy = ry + 5;
      ctx.drawImage(qrImg, qx, qy, qrSize, qrSize);
    }

    ctx.fillStyle = '#0f172a';
    ctx.font = 'bold 7px "Inter Tight", "Sora", sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'bottom';
    ctx.fillText('RUANG LARI', rx + bw / 2, ry + bh - 4);
    ctx.restore();
  }

  function drawGraphics() {
    for (const g of graphics.get(pageIndex) || []) {
      ctx.save();
      ctx.globalAlpha = g.opacity ?? 1;
      ctx.translate(g.x + g.w / 2, g.y + g.h / 2);
      ctx.rotate((g.rotation || 0) * Math.PI / 180);
      register('graphic:' + g.id, -g.w / 2, -g.h / 2, g.w, g.h);
      if (g.type === 'png') {
        const img = getGraphicImage(g.src);
        if (img?.naturalWidth) ctx.drawImage(img, -g.w / 2, -g.h / 2, g.w, g.h);
      } else if (g.type === 'emoji') {
        ctx.font = Math.min(g.w, g.h) * .84 + 'px "Apple Color Emoji","Segoe UI Emoji","Noto Color Emoji",sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = g.color || '#111';
        ctx.fillText(g.emoji, 0, 0, g.w);
      } else if (g.type === 'icon') {
        const icon = faIcons[g.icon];
        if (icon) {
          let inset = 0;
          if (g.background) {
            ctx.fillStyle = g.background;
            ctx.beginPath();
            ctx.roundRect(-g.w / 2, -g.h / 2, g.w, g.h, Math.min(g.w, g.h) * .22);
            ctx.fill();
            inset = Math.min(g.w, g.h) * .22;
          }
          const scale = Math.min((g.w - inset * 2) / icon.w, (g.h - inset * 2) / icon.h);
          ctx.translate(-icon.w * scale / 2, -icon.h * scale / 2);
          ctx.scale(scale, scale);
          ctx.fillStyle = g.color;
          for (const path of icon.paths) ctx.fill(new Path2D(path));
        }
      }
      ctx.restore();
    }
  }

  function drawCustomTexts() {
    drawGraphics();
    const list = customTexts.get(pageIndex) || [];
    for (const t of list) {
      const fontStr = (t.italic ? 'italic ' : '') + (t.bold ? 'bold ' : '') + t.size + 'px ' + currentFontFamily;
      ctx.fillStyle = t.color || (dark ? '#e9edf5' : '#111');
      ctx.font = fontStr;
      ctx.fillText(t.text, t.x, t.y);
      register('custom:' + t.id, t.x, t.y - t.size, ctx.measureText(t.text).width, t.size + 6);
    }
    if (editable && typeof paintSelection === 'function') paintSelection(ctx, wall, target);
  }

  // --- DRAW DAYS IN MONTH HELPER ---
  function drawDaysGrid(x0, y0, cellW, cellH, m, opts = {}) {
    const start = new Date(year(), m, 1).getDay();
    const days = new Date(year(), m + 1, 0).getDate();
    const showLines = opts.showLines !== false;
    const compact = opts.compact || false;

    for (let row = 0; row < 6; row++) {
      const cy = y0 + row * cellH;
      for (let col = 0; col < 7; col++) {
        const cx = x0 + col * cellW;
        const d = row * 7 + col - start + 1;
        const active = d > 0 && d <= days;

        if (opts.boxBg) {
          box(cx, cy, cellW, cellH, active ? (col === 0 || col === 6 ? '#dedede' : '#fff') : '#f1f1f1');
        } else if (theme === 'bold' && col === 0 && active) {
          box(cx, cy, cellW, cellH, surface);
        }

        if (active) {
          const hol = showHol && isHoliday(year(), m, d);
          const numColor = (col === 0 || hol) ? redHolidayColor : (opts.textColor || '#111');
          const fontSize = compact ? '12px' : '15px';
          text(d, cx + (compact ? 3 : 5), cy + (compact ? 14 : 18), 'bold ' + fontSize + ' Arial', numColor);

          // RUNNING TRACKER & STRAVA ACTIVITY LOG
          const logKey = year() + '-' + m + '-' + d;
          const log = typeof stravaLogs !== 'undefined' ? stravaLogs.get(logKey) : null;
          if (log) {
            const isRace = log.type === 'race';
            box(cx + 2, cy + cellH - (compact ? 13 : 16), cellW - 4, compact ? 11 : 13, isRace ? '#fc5200' : (dark ? '#fc520044' : '#ffedd5'));
            ctx.fillStyle = isRace ? '#ffffff' : '#c2410c';
            ctx.font = 'bold ' + (compact ? '7.5px' : '8.5px') + ' Arial';
            ctx.textAlign = 'center';
            ctx.fillText(isRace ? 'RACE ' + log.km + 'K' : log.km + 'k', cx + cellW / 2, cy + cellH - (compact ? 4 : 6));
            ctx.textAlign = 'left';
          } else if (isRun && !compact) {
            text('km', cx + 4, cy + cellH - 4, '7.5px Arial', dark ? '#8ea3c4' : '#777');
            line(cx + 18, cy + cellH - 5, cx + cellW - 4, cy + cellH - 5, dark ? '#495d7a' : '#bbb');
            ctx.strokeStyle = dark ? '#778ca8' : '#777';
            ctx.strokeRect(cx + cellW - 10, cy + 4, 6, 6);
          } else if (isRun && compact) {
            text('km _', cx + 3, cy + cellH - 3, '6.5px Arial', '#888');
          }
        }
        if (showLines && theme !== 'minimal') {
          line(cx, cy + cellH, cx + cellW, cy + cellH, dark ? '#2a3b54' : '#e0e0e0');
          if (opts.verticalLines) line(cx, cy, cx, cy + cellH, dark ? '#2a3b54' : '#e0e0e0');
        }
      }
    }
  }

  // =========================================================================
  // --- 0. POSTER 12 BULAN (1 LEMBAR A3+) ---
  // =========================================================================
  function drawPoster12MonthGrid() {
    if (wall) {
      box(0, 0, 640, 960, paper);

      let brandTag = isRun ? 'RUANG LARI // ATHLETIC CALENDAR & ANNUAL TRAINING LOG' : 'PERSONAL CALENDAR // ANNUAL PLANNER';
      if (theme === 'strava') brandTag = 'STRAVA ATHLETE LOG // YEAR IN SPORT ' + year();
      else if (theme === 'club') brandTag = 'RUN CLUB // ATHLETIC SEASON ' + year();
      else if (theme === 'majors') brandTag = 'WORLD MARATHON MAJORS // 7-STAR LOG';
      else if (theme === 'blueprint') brandTag = 'ATHLETIC BLUEPRINT // CAD SPEC SHEET ' + year();
      else if (theme === 'taper') brandTag = 'MARATHON RACE & TAPER // ' + year();
      else if (theme === 'badge') brandTag = 'OFFICIAL MARATHON BIB // ATHLETE DIVISION';
      else if (theme === 'minimal') brandTag = 'EDITORIAL // ANNUAL LOG ' + year();
      else if (theme === 'retro' || theme === 'kraft') brandTag = "RUNNER'S LOGBOOK // " + year();
      else if (theme === 'infographic') brandTag = 'YEAR IN SPORT // ATHLETIC SUMMARY ' + year();

      text(brandTag, 26, 30, 'bold 8.5px "Inter Tight", "Sora", sans-serif', color);

      const seasonText = 'SEASON ' + year();
      ctx.textAlign = 'right';
      text(seasonText, 614, 30, 'bold 9px "JetBrains Mono", monospace', dark ? '#94a3b8' : '#64748b');
      ctx.textAlign = 'left';

      // Hero Photo slot (Left)
      photo(26, 42, 330, 268);

      // Hero Runner Info (Right)
      const infoX = 372;
      const infoW = 242;

      elementText('heading', isRun ? 'ATHLETIC ANNUAL LOG' : 'ANNUAL OVERVIEW', infoX, 58, 'bold 10px "Inter Tight", "Sora", sans-serif', color, infoW);
      elementText('name', name, infoX, 98, 'bold 24px "Inter Tight", "Sora", sans-serif', foreground, infoW);
      elementText('year', year(), infoX, 150, 'bold 46px "Inter Tight", "Sora", sans-serif', color, infoW);
      elementText('subtitle', isRun ? 'ROAD TO MARATHON ' + year() : 'CALENDAR ' + year(), infoX, 174, '11px Arial', dark ? '#94a3b8' : '#64748b', infoW);

      // Metric Chips
      const chipBg = dark ? '#131924' : '#f8fafc';
      const chipBorder = dark ? '#222f42' : '#e2e8f0';

      ctx.fillStyle = chipBg;
      ctx.strokeStyle = chipBorder;
      ctx.lineWidth = 1;
      ctx.beginPath();
      ctx.roundRect(infoX, 190, infoW, 27, 4);
      ctx.fill();
      ctx.stroke();

      text('TARGET TAHUNAN', infoX + 8, 201, 'bold 7.5px "Inter Tight", "Sora", sans-serif', dark ? '#94a3b8' : '#64748b');
      text('1,500 KM / TAHUN', infoX + 8, 212, 'bold 10px "JetBrains Mono", monospace', foreground);

      ctx.fillStyle = chipBg;
      ctx.beginPath();
      ctx.roundRect(infoX, 223, infoW - 68, 27, 4);
      ctx.fill();
      ctx.stroke();

      text('MARATHON PB', infoX + 8, 234, 'bold 7.5px "Inter Tight", "Sora", sans-serif', dark ? '#94a3b8' : '#64748b');
      text('03:45:12', infoX + 8, 245, 'bold 10px "JetBrains Mono", monospace', foreground);

      // Ruang Lari QR Verification
      if (typeof drawRuangLariQrBadge === 'function') {
        drawRuangLariQrBadge(infoX + infoW - 62, 220, 62, 70, -1);
      }

      // Horizontal Divider
      box(26, 322, 588, 2, color);

      // 12 Months Grid: 4 Rows x 3 Columns
      const dayNames = ['S', 'S', 'R', 'K', 'J', 'S', 'M'];

      for (let m = 0; m < 12; m++) {
        const row = Math.floor(m / 3);
        const col = m % 3;
        const bx = 26 + col * (188 + 12);
        const by = 336 + row * (140 + 9);
        const bw = 188;
        const bh = 140;

        const cardBg = theme === 'minimal' ? paper : (dark ? '#131924' : '#ffffff');
        const cardBorder = dark ? '#222f42' : '#e2e8f0';
        ctx.fillStyle = cardBg;
        ctx.strokeStyle = cardBorder;
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.roundRect(bx, by, bw, bh, 6);
        ctx.fill();
        ctx.stroke();

        const monthNumStr = String(m + 1).padStart(2, '0');
        const monthNameStr = months[m].toUpperCase();

        if (theme === 'bold') {
          box(bx + 8, by + 6, 20, 14, color);
          text(monthNumStr, bx + 10, by + 16, 'bold 9.5px "JetBrains Mono", monospace', ink);
          text(monthNameStr, bx + 34, by + 16, 'bold 10.5px "Inter Tight", "Sora", sans-serif', foreground);
        } else {
          text(monthNumStr, bx + 10, by + 16, 'bold 10.5px "JetBrains Mono", monospace', color);
          text(monthNameStr, bx + 32, by + 16, 'bold 10.5px "Inter Tight", "Sora", sans-serif', foreground);
        }

        line(bx + 8, by + 22, bx + bw - 8, by + 22, dark ? '#222f42' : '#f1f5f9');

        const colW = (bw - 12) / 7;
        const xOffset = bx + 6;
        for (let dIdx = 0; dIdx < 7; dIdx++) {
          const dx = xOffset + dIdx * colW;
          const isSun = dIdx === 6;
          ctx.textAlign = 'center';
          text(dayNames[dIdx], dx + colW / 2, by + 33, 'bold 7.5px "Inter Tight", "Sora", sans-serif', isSun ? redHolidayColor : (dark ? '#64748b' : '#94a3b8'));
        }
        ctx.textAlign = 'left';

        const rawStart = new Date(year(), m, 1).getDay();
        const startDay = (rawStart + 6) % 7;
        const totalDays = new Date(year(), m + 1, 0).getDate();
        const cellH = 16.5;
        const startY = by + 39;

        for (let d = 1; d <= totalDays; d++) {
          const pos = startDay + d - 1;
          const dCol = pos % 7;
          const dRow = Math.floor(pos / 7);
          const cx = xOffset + dCol * colW;
          const cy = startY + dRow * cellH;

          const isSun = dCol === 6;
          const hol = showHol && isHoliday(year(), m, d);
          const numColor = (isSun || hol) ? redHolidayColor : foreground;

          ctx.textAlign = 'center';
          text(d, cx + colW / 2, cy + 11, 'bold 9.5px "JetBrains Mono", monospace', numColor);

          if (hol) {
            ctx.fillStyle = redHolidayColor;
            ctx.beginPath();
            ctx.arc(cx + colW / 2, cy + 13.5, 1.2, 0, Math.PI * 2);
            ctx.fill();
          }
        }
        ctx.textAlign = 'left';
      }
    } else {
      // Landscape fallback
      box(0, 0, 794, 530, paper);
      photo(20, 20, 230, 490);
      const dayNames = ['S', 'S', 'R', 'K', 'J', 'S', 'M'];
      for (let m = 0; m < 12; m++) {
        const row = Math.floor(m / 4);
        const col = m % 4;
        const bx = 265 + col * 128;
        const by = 20 + row * 162;
        const bw = 120;
        const bh = 154;

        const cardBg = dark ? '#131924' : '#ffffff';
        const cardBorder = dark ? '#222f42' : '#e2e8f0';
        ctx.fillStyle = cardBg; ctx.strokeStyle = cardBorder; ctx.lineWidth = 1;
        ctx.beginPath(); ctx.roundRect(bx, by, bw, bh, 5); ctx.fill(); ctx.stroke();

        const monthNumStr = String(m + 1).padStart(2, '0');
        text(monthNumStr + ' ' + months[m].toUpperCase(), bx + 6, by + 16, 'bold 9px "Inter Tight", "Sora", sans-serif', color);

        const colW = (bw - 8) / 7;
        const xOffset = bx + 4;
        for (let dIdx = 0; dIdx < 7; dIdx++) {
          const dx = xOffset + dIdx * colW;
          const isSun = dIdx === 6;
          ctx.textAlign = 'center';
          text(dayNames[dIdx], dx + colW / 2, by + 30, 'bold 7px "Inter Tight", "Sora", sans-serif', isSun ? redHolidayColor : (dark ? '#64748b' : '#94a3b8'));
        }
        ctx.textAlign = 'left';

        const rawStart = new Date(year(), m, 1).getDay();
        const startDay = (rawStart + 6) % 7;
        const totalDays = new Date(year(), m + 1, 0).getDate();
        const cellH = 18;
        const startY = by + 36;
        for (let d = 1; d <= totalDays; d++) {
          const pos = startDay + d - 1;
          const dCol = pos % 7;
          const dRow = Math.floor(pos / 7);
          const cx = xOffset + dCol * colW;
          const cy = startY + dRow * cellH;
          const isSun = dCol === 6;
          const hol = showHol && isHoliday(year(), m, d);
          ctx.textAlign = 'center';
          text(d, cx + colW / 2, cy + 12, 'bold 8.5px "JetBrains Mono", monospace', (isSun || hol) ? redHolidayColor : foreground);
        }
        ctx.textAlign = 'left';
      }
    }
  }

  if (is12M) {
    drawPoster12MonthGrid();
    drawCustomTexts();
    return;
  }

  // =========================================================================
  // --- 1. COVER PAGE (pageIndex === -1) ---
  // =========================================================================
  if (pageIndex === -1) {
    const headingText = isRun ? 'RUNNING CALENDAR' : 'PERSONAL CALENDAR';
    const subText = isRun ? 'TRAINING LOG · RACING GOALS · 2027' : 'JANUARI — DESEMBER · 2027';

    if (wall) {
      box(0, 0, 640, 960, paper);

      if (theme === 'split') {
        photo(0, 0, 310, 960);
        box(310, 0, 330, 960, paper);
        elementText('heading', headingText, 335, 260, 'bold 16px Arial', color, 280);
        elementText('year', year(), 330, 420, 'bold 130px Arial', color, 290);
        elementText('name', name, 335, 480, 'italic bold 28px Georgia', foreground, 280);
        line(335, 520, 610, 520, color);
        elementText('subtitle', subText, 335, 550, '12px Arial', foreground, 280);
      } else if (theme === 'bottom') {
        elementText('heading', headingText, 36, 120, 'bold 18px Arial', color);
        elementText('year', year(), 28, 280, 'bold 150px Arial', color, 575);
        elementText('name', name, 36, 350, 'italic 34px Georgia', foreground, 565);
        line(36, 385, 604, 385, color);
        elementText('subtitle', subText, 36, 420, '14px Arial');
        photo(0, 480, 640, 480);
        box(0, 475, 640, 5, color);
      } else if (theme === 'hero') {
        photo(0, 0, 640, 680);
        box(0, 680, 640, 8, color);
        elementText('heading', headingText, 36, 735, 'bold 16px Arial', color);
        elementText('year', year(), 36, 840, 'bold 85px Arial', foreground, 400);
        elementText('name', name, 36, 885, 'italic bold 26px Georgia', color, 560);
        elementText('subtitle', subText, 36, 920, '12px Arial');
      } else if (theme === 'magazine') {
        photo(0, 0, 640, 960);
        ctx.fillStyle = '#00000088'; ctx.fillRect(0, 0, 640, 960);
        elementText('heading', 'RUNNER’S SPECIAL ISSUE', 36, 160, 'bold 18px Arial', '#fff');
        elementText('year', year(), 28, 380, 'italic 900 170px Arial', color, 580);
        elementText('name', name.toUpperCase(), 36, 460, 'bold 44px Arial', '#fff', 560);
        line(36, 490, 604, 490, color);
        elementText('subtitle', 'DEDICATED TO EVERY STEP, MILE & FINISH LINE', 36, 530, 'bold 14px Arial', '#eee');
      } else if (theme === 'polaroid') {
        box(0, 0, 640, 960, '#faf8f5');
        ctx.save(); ctx.translate(320, 380); ctx.rotate(-.025);
        ctx.shadowColor = '#0004'; ctx.shadowBlur = 20; ctx.shadowOffsetY = 10;
        box(-230, -260, 460, 530, '#ffffff');
        ctx.shadowBlur = 0;
        photo(-210, -240, 420, 410);
        text(name + ' · ' + year(), -190, 230, 'italic bold 22px Georgia', '#222');
        ctx.restore();
        elementText('heading', headingText, 36, 780, 'bold 16px Arial', color);
        elementText('year', year(), 36, 860, 'bold 64px Arial', color);
        elementText('subtitle', subText, 36, 900, '13px Arial');
      } else if (theme === 'modernist') {
        box(0, 0, 640, 960, '#fff');
        box(0, 0, 24, 960, color);
        box(24, 0, 616, 28, color);
        photo(48, 50, 544, 420);
        box(48, 490, 16, 160, color);
        elementText('year', year(), 78, 620, 'bold 130px Arial', '#111', 500);
        elementText('heading', headingText, 78, 670, 'bold 22px Arial', color, 500);
        elementText('name', name.toUpperCase(), 78, 715, 'bold 30px Arial', '#111', 500);
        line(78, 745, 592, 745, '#111');
        elementText('subtitle', subText, 78, 780, 'bold 14px Arial', '#555');
      } else if (theme === 'neon') {
        box(0, 0, 640, 960, '#0d1117');
        for (let i = 0; i < 5; i++) {
          ctx.strokeStyle = i % 2 ? '#00f0ff33' : '#ff007a33'; ctx.lineWidth = 3; ctx.beginPath();
          ctx.arc(640, 480, 200 + i * 40, Math.PI * .5, Math.PI * 1.5); ctx.stroke();
        }
        photo(40, 60, 560, 420);
        elementText('heading', '⚡ ' + headingText, 40, 550, 'bold 18px Arial', '#00f0ff');
        elementText('year', year(), 36, 710, 'bold 140px Arial', '#ff007a', 560);
        elementText('name', name, 40, 780, 'bold 36px Arial', '#fff', 560);
        elementText('subtitle', subText, 40, 830, '14px Arial', '#00f0ff');
      } else if (theme === 'stamp') {
        box(0, 0, 640, 960, paper);
        ctx.strokeStyle = color; ctx.lineWidth = 1.5; ctx.strokeRect(18, 18, 604, 924);
        drawStampPerforations(18, 18, 604, 924, 4, 18);
        box(42, 36, 80, 26, color);
        text('RP 10.000', 50, 54, 'bold 12px Arial', ink);
        elementText('heading', 'OFFICIAL MARATHON POST', 136, 54, 'bold 13px Arial', color);
        photo(48, 76, 544, 480);
        drawStampPerforations(48, 76, 544, 480, 3.5, 15);
        drawPostmark(510, 135, 42, 'MARATHON POST');
        elementText('year', year(), 48, 680, 'bold 120px Georgia', color, 544);
        elementText('name', name.toUpperCase(), 50, 735, 'bold 28px Arial', foreground, 540);
        line(48, 755, 592, 755, color);
        elementText('subtitle', subText, 50, 790, '13px Georgia', foreground, 540);
        text('COMMEMORATIVE RUNNING CALENDAR ISSUE', 50, 850, 'bold 10px Arial', '#888');
      } else if (theme === 'filmstrip') {
        box(0, 0, 640, 960, dark ? '#111' : '#181818');
        drawFilmstripRails(0, 0, 640, 960);
        photo(30, 80, 580, 460);
        box(30, 76, 580, 4, color);
        box(30, 540, 580, 4, color);
        elementText('heading', 'A FILM BY ATHLETE STUDIO', 40, 595, 'bold 13px monospace', '#aaa');
        elementText('year', year(), 36, 730, 'bold 130px Arial', color, 560);
        elementText('name', name.toUpperCase(), 40, 785, 'bold 36px Arial', '#ffffff', 560);
        line(40, 810, 600, 810, '#444');
        elementText('subtitle', subText, 40, 845, '12px Arial', '#ccc');
        text('PRODUCED & DIRECTED BY THE RUNNER • 35MM CINEMATIC EDITION', 40, 880, 'bold 9px monospace', '#777');
      } else if (theme === 'badge') {
        box(0, 0, 640, 960, '#ffffff');
        box(0, 0, 640, 65, color);
        text('OFFICIAL MARATHON RACE BIB • ATHLETE DIVISION', 36, 40, 'bold 15px Arial', ink);
        photo(36, 85, 568, 420);
        drawBibPins(36, 85, 568, 420);
        box(36, 525, 568, 175, '#f4f6fa');
        text('BIB NUMBER', 56, 555, 'bold 13px Arial', '#666');
        elementText('year', year(), 52, 680, 'bold 125px Arial', color, 540);
        elementText('name', name.toUpperCase(), 36, 755, 'bold 38px Arial', '#111', 568);
        elementText('heading', headingText, 36, 795, 'bold 16px Arial', color);
        elementText('subtitle', subText, 36, 825, '13px Arial', '#444');
        line(36, 855, 604, 855, '#ccc');
        for (let bx = 36; bx < 604; bx += 8) {
          const bw = (bx % 16 === 0) ? 4 : 2;
          box(bx, 868, bw, 28, '#222');
        }
        text('*CHIP TIMED OFFICIAL RUNNER* • VERIFIED PACE', 36, 918, 'bold 9.5px monospace', '#666');
      } else if (theme === 'arch') {
        box(0, 0, 640, 960, paper);
        photo(80, 60, 480, 540);
        ctx.save();
        ctx.strokeStyle = color; ctx.lineWidth = 1.8;
        const ar = 240;
        ctx.beginPath();
        ctx.moveTo(80, 60 + ar);
        ctx.arc(80 + ar, 60 + ar, ar, Math.PI, 0, false);
        ctx.lineTo(80 + 480, 600);
        ctx.lineTo(80, 600);
        ctx.closePath();
        ctx.stroke();
        ctx.beginPath();
        ctx.arc(80 + ar, 60 + ar, ar + 10, Math.PI, 0, false);
        ctx.stroke();
        ctx.restore();
        elementText('heading', headingText, 80, 670, 'bold 16px Georgia', color, 480);
        elementText('year', year(), 75, 785, 'italic bold 110px Georgia', color, 490);
        elementText('name', name, 80, 835, 'italic 32px Georgia', foreground, 480);
        line(80, 860, 560, 860, color);
        elementText('subtitle', subText, 80, 895, '13px Georgia', foreground, 480);
      } else if (theme === 'diagonal') {
        box(0, 0, 640, 960, paper);
        photo(0, 0, 640, 480);
        ctx.save();
        ctx.fillStyle = color;
        ctx.beginPath();
        ctx.moveTo(0, 460); ctx.lineTo(640, 510); ctx.lineTo(640, 540); ctx.lineTo(0, 490);
        ctx.closePath();
        ctx.fill();
        ctx.restore();
        elementText('heading', '⚡ ' + headingText.toUpperCase(), 36, 580, 'bold 16px Arial', color);
        elementText('year', year(), 32, 730, 'italic 900 145px Arial', foreground, 570);
        elementText('name', name.toUpperCase(), 36, 800, 'italic bold 38px Arial', color, 565);
        line(36, 830, 604, 830, color);
        elementText('subtitle', subText, 36, 870, 'bold 14px Arial', foreground);
      } else if (theme === 'metro') {
        box(0, 0, 640, 960, paper);
        box(0, 0, 640, 36, color);
        text('M  METRO MARATHON LINE // RAPID TRANSIT', 32, 24, 'bold 13px Arial', ink);
        photo(36, 60, 568, 420);
        drawMetroRoute(40, 515, 560, ['START 0K', 'SPLIT 10K', 'HALF 21K', 'WALL 30K', 'FINISH 42K']);
        elementText('heading', 'STATION 2026 // LINE OVERVIEW', 40, 585, 'bold 15px Arial', color);
        elementText('year', year(), 36, 720, 'bold 135px Arial', foreground, 560);
        elementText('name', name.toUpperCase(), 40, 785, 'bold 36px Arial', color, 560);
        line(40, 815, 600, 815, '#cbd5e1');
        elementText('subtitle', subText, 40, 855, '13px Arial', foreground);
      } else if (theme === 'duotone') {
        box(0, 0, 640, 960, paper);
        box(0, 0, 70, 960, color);
        ctx.save();
        ctx.translate(45, 880);
        ctx.rotate(-Math.PI / 2);
        ctx.fillStyle = ink;
        ctx.font = 'bold 38px Arial';
        ctx.fillText('CALENDAR ' + year(), 0, 0);
        ctx.restore();
        box(120, 60, 480, 520, dark ? '#222' : '#e2e8f0');
        photo(105, 45, 480, 520);
        elementText('heading', headingText.toUpperCase(), 105, 625, 'bold 18px Arial', color);
        elementText('year', year(), 100, 750, 'bold 125px Arial', foreground, 490);
        elementText('name', name.toUpperCase(), 105, 815, 'bold 38px Arial', color, 490);
        line(105, 845, 595, 845, '#111');
        elementText('subtitle', subText, 105, 885, 'bold 13px Arial', foreground);
      } else if (theme === 'kraft') {
        box(0, 0, 640, 960, '#ede5d8');
        photo(50, 70, 540, 450);
        drawWashiTape(90, 70, 90, 26, -0.08);
        drawWashiTape(550, 70, 90, 26, 0.08);
        elementText('heading', 'RUNNER’S LOGBOOK — VOL. ' + year(), 50, 570, 'bold 16px "Courier New", monospace', color);
        elementText('year', year(), 45, 710, 'bold 125px "Courier New", monospace', '#3d2514', 540);
        elementText('name', name, 50, 775, 'bold 34px Georgia', color, 540);
        line(50, 805, 590, 805, '#b09e86');
        elementText('subtitle', subText, 50, 845, '14px "Courier New", monospace', '#5c432d');
        text('• RECORD YOUR MILES • EMBRACE THE JOURNEY •', 50, 900, 'bold 11px monospace', '#8b6f52');
        drawRuangLariQrBadge(510, 830, 85, 96, -1);
      } else if (theme === 'strava') {
        box(0, 0, 640, 960, '#0d1117');
        box(0, 0, 640, 40, '#fc5200');
        text('⚡ STRAVA ATHLETE FEED // VERIFIED RUNNER', 32, 26, 'bold 13px Arial', '#ffffff');
        photo(32, 60, 576, 440);
        // Polyline GPS trail glow
        ctx.strokeStyle = '#fc5200'; ctx.lineWidth = 3.5; ctx.beginPath();
        ctx.moveTo(60, 460); ctx.lineTo(160, 420); ctx.lineTo(240, 450); ctx.lineTo(340, 390); ctx.lineTo(440, 430); ctx.lineTo(540, 380); ctx.stroke();
        // Strava stats box
        box(32, 515, 576, 45, '#161b22');
        text('DISTANCE: 42.195 KM   |   TIME: 3:45:12   |   ELEVATION: +310M', 48, 542, 'bold 12px monospace', '#fc5200');
        elementText('year', year(), 32, 690, 'bold 135px Arial', '#ffffff', 576);
        elementText('name', name.toUpperCase(), 34, 755, 'bold 38px Arial', '#fc5200', 460);
        line(32, 785, 480, 785, '#30363d');
        elementText('subtitle', subText, 34, 825, '13px Arial', '#8b949e', 450);
        text('KUDOS TO EVERY MILE • STRAVA RUNNING EDITION', 34, 860, 'bold 10px Arial', '#fc5200');
        drawRuangLariQrBadge(510, 830, 85, 96, -1);
      } else if (theme === 'club') {
        box(0, 0, 640, 960, '#111111');
        box(0, 0, 640, 24, '#ccff00');
        photo(36, 60, 568, 440);
        box(36, 56, 568, 4, '#ccff00');
        elementText('heading', 'RUNNING CLUB // COMMUNITY EDITION', 36, 545, 'bold 15px Arial', '#ccff00');
        elementText('year', year(), 32, 690, 'italic 900 140px Arial', '#ffffff', 568);
        elementText('name', name.toUpperCase(), 36, 755, 'bold 36px Arial', '#ccff00', 460);
        line(36, 785, 480, 785, '#333');
        elementText('subtitle', subText, 36, 825, 'bold 13px Arial', '#888', 450);
        text('ALL PACES WELCOME • RUN TOGETHER • CRUSH GOALS', 36, 865, 'bold 10px Arial', '#ccff00');
        drawRuangLariQrBadge(510, 830, 85, 96, -1);
      } else if (theme === 'majors') {
        box(0, 0, 640, 960, '#ffffff');
        box(0, 0, 640, 65, '#002b49');
        text('WORLD MARATHON MAJORS // 7-STAR JOURNEY', 36, 40, 'bold 15px Arial', '#ffffff');
        photo(36, 85, 568, 430);
        box(36, 530, 568, 36, '#f0f4f8');
        text('TOKYO • BOSTON • LONDON • SYDNEY • BERLIN • CHICAGO • NYC', 48, 553, 'bold 10.5px Arial', '#002b49');
        elementText('year', year(), 36, 690, 'bold 125px Arial', '#002b49', 568);
        elementText('name', name.toUpperCase(), 36, 755, 'bold 36px Arial', color, 460);
        line(36, 785, 480, 785, '#002b49');
        elementText('subtitle', subText, 36, 825, '13px Arial', '#555', 450);
        text('ABBOTT FINISHER ASPIRATION • WORLD CLASS PACING', 36, 865, 'bold 10px Arial', '#002b49');
        drawRuangLariQrBadge(510, 830, 85, 96, -1);
      } else if (theme === 'topography') {
        box(0, 0, 640, 960, '#f3f4ed');
        photo(40, 70, 560, 440);
        // Topo contour lines
        for (let i = 0; i < 4; i++) {
          ctx.strokeStyle = '#1e392a22'; ctx.lineWidth = 1.5; ctx.beginPath();
          ctx.moveTo(40, 535 + i * 14); ctx.bezierCurveTo(180, 510 + i * 14, 340, 560 + i * 14, 600, 525 + i * 14); ctx.stroke();
        }
        elementText('heading', '▲ TRAIL & PEAK ELEVATION SERIES', 40, 600, 'bold 14px Arial', '#1e392a');
        elementText('year', year(), 36, 725, 'bold 125px Arial', '#1e392a', 560);
        elementText('name', name, 40, 785, 'italic bold 32px Georgia', color, 450);
        line(40, 810, 480, 810, '#1e392a');
        elementText('subtitle', 'VERT IS REAL • MOUNTAIN RUNNING & ULTRA MARATHON', 40, 845, '12px Arial', '#556b2f', 450);
        drawRuangLariQrBadge(510, 830, 85, 96, -1);
      } else if (theme === 'crown') {
        box(0, 0, 640, 960, paper);
        box(0, 0, 640, 40, '#ffb800');
        text('👑 KING OF THE MOUNTAIN // STRAVA SEGMENT RECORD', 32, 26, 'bold 13px Arial', '#111');
        photo(36, 60, 568, 440);
        box(36, 515, 568, 36, '#fffbeb');
        text('SEGMENT PR: 02:45   |   RANK #1 / 1,420   |   LOCAL LEGEND', 48, 538, 'bold 11px Arial', '#b45309');
        elementText('year', year(), 36, 680, 'bold 130px Arial', '#111', 568);
        elementText('name', name.toUpperCase(), 36, 750, 'bold 36px Arial', '#ffb800', 460);
        line(36, 780, 480, 780, '#ffb800');
        elementText('subtitle', subText, 36, 820, '13px Arial', foreground, 450);
        drawRuangLariQrBadge(510, 830, 85, 96, -1);
      } else if (theme === 'blueprint') {
        box(0, 0, 640, 960, '#0a3871');
        // CAD grid
        ctx.strokeStyle = '#ffffff15'; ctx.lineWidth = 1;
        for (let x = 0; x < 640; x += 32) { ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, 960); ctx.stroke(); }
        for (let y = 0; y < 960; y += 32) { ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(640, y); ctx.stroke(); }
        photo(40, 60, 560, 440);
        text('RUNNER BIOMECHANICS: CADENCE 180 SPM • OSCILLATION 6.2CM', 40, 525, 'bold 10px monospace', '#93c5fd');
        elementText('heading', 'TECHNICAL SPECIFICATION SHEET', 40, 565, 'bold 13px monospace', '#93c5fd');
        elementText('year', year(), 36, 700, 'bold 130px monospace', '#ffffff', 560);
        elementText('name', name.toUpperCase(), 40, 765, 'bold 34px monospace', '#93c5fd', 450);
        line(40, 795, 480, 795, '#ffffff');
        elementText('subtitle', subText, 40, 835, '12px monospace', '#bfdbfe', 450);
        drawRuangLariQrBadge(510, 830, 85, 96, -1);
      } else if (theme === 'acid') {
        box(0, 0, 640, 960, '#140026');
        photo(32, 60, 576, 440);
        ctx.save(); ctx.fillStyle = '#b8ff00';
        ctx.beginPath(); ctx.moveTo(0, 510); ctx.lineTo(640, 545); ctx.lineTo(640, 555); ctx.lineTo(0, 520); ctx.fill(); ctx.restore();
        elementText('heading', '⚡ CYBER RUNNER 3000 // SPEED MATRIX', 36, 590, 'bold 14px Arial', '#b8ff00');
        elementText('year', year(), 32, 730, 'italic 900 145px Arial', '#ffffff', 560);
        elementText('name', name.toUpperCase(), 36, 795, 'italic bold 36px Arial', '#b8ff00', 460);
        line(36, 825, 480, 825, '#b8ff00');
        elementText('subtitle', subText, 36, 865, '12px Arial', '#d8b4fe', 450);
        drawRuangLariQrBadge(510, 830, 85, 96, -1);
      } else if (theme === 'taper') {
        box(0, 0, 640, 960, '#ffffff');
        box(0, 0, 640, 36, '#dc2626');
        text('RACE WEEK TAPER & SPLIT MATRIX // MARATHON READY', 32, 23, 'bold 12px Arial', '#ffffff');
        photo(32, 55, 576, 430);
        box(32, 500, 576, 40, '#fef2f2');
        text('TARGET PACE: 04:30/KM   |   SUB-3:10 MARATHON   |   FUEL: 60G CHO/HR', 44, 525, 'bold 11px Arial', '#b91c1c');
        elementText('year', year(), 32, 675, 'bold 130px Arial', '#dc2626', 576);
        elementText('name', name.toUpperCase(), 34, 745, 'bold 36px Arial', '#111', 460);
        line(32, 775, 480, 775, '#dc2626');
        elementText('subtitle', subText, 34, 815, '13px Arial', '#555', 450);
        drawRuangLariQrBadge(510, 830, 85, 96, -1);
      } else if (theme === 'infographic') {
        box(0, 0, 640, 960, '#fafafa');
        box(0, 0, 640, 48, '#ea580c');
        text('YEAR IN SPORT ' + year() + ' // ATHLETE INFOGRAPHIC', 32, 30, 'bold 15px Arial', '#ffffff');
        photo(32, 68, 576, 380);
        // Giant metrics
        box(32, 465, 180, 75, '#ffedd5');
        text('TOTAL JARAK', 44, 488, 'bold 10px Arial', '#c2410c');
        text('2,026 KM', 44, 524, 'bold 26px Arial', '#ea580c');
        box(230, 465, 180, 75, '#ffedd5');
        text('TOTAL WAKTU', 242, 488, 'bold 10px Arial', '#c2410c');
        text('248 JAM', 242, 524, 'bold 26px Arial', '#ea580c');
        box(428, 465, 180, 75, '#ffedd5');
        text('TOTAL ELEVASI', 440, 488, 'bold 10px Arial', '#c2410c');
        text('+18,500 M', 440, 524, 'bold 26px Arial', '#ea580c');
        elementText('year', year(), 32, 680, 'bold 125px Arial', '#111', 568);
        elementText('name', name.toUpperCase(), 34, 745, 'bold 36px Arial', '#ea580c', 460);
        line(32, 775, 480, 775, '#ea580c');
        elementText('subtitle', subText, 34, 815, '13px Arial', '#555', 450);
        drawRuangLariQrBadge(510, 830, 85, 96, -1);
      } else if (theme === 'shoebox') {
        box(0, 0, 640, 960, '#ffffff');
        box(0, 0, 640, 36, '#e11d48');
        text('ATHLETE GEAR & SNEAKER ROTATION // BOX LABEL', 32, 23, 'bold 12px Arial', '#ffffff');
        photo(32, 55, 576, 430);
        box(32, 500, 576, 45, '#fff1f2');
        text('ROTATION: VAPORFLY 3 (210 KM) • PEGASUS 41 (420 KM) • ALPHAFLY 3 (RACE DAY)', 44, 528, 'bold 10px monospace', '#be123c');
        elementText('year', year(), 32, 675, 'bold 130px Arial', '#e11d48', 568);
        elementText('name', name.toUpperCase(), 34, 745, 'bold 36px Arial', '#111', 460);
        line(32, 775, 480, 775, '#e11d48');
        elementText('subtitle', subText, 34, 815, '13px Arial', '#555', 450);
        drawRuangLariQrBadge(510, 830, 85, 96, -1);
      } else if (theme === 'zine') {
        box(0, 0, 640, 960, '#f5f5f0');
        photo(40, 60, 560, 450);
        drawWashiTape(80, 60, 80, 24, -0.1);
        drawWashiTape(560, 60, 80, 24, 0.1);
        elementText('heading', 'RUN OR DIE // INDIE ATHLETE ZINE', 40, 565, 'bold 18px "Courier New", monospace', '#111');
        elementText('year', year(), 36, 715, 'bold 135px "Courier New", monospace', '#111', 560);
        elementText('name', name.toUpperCase(), 40, 775, 'bold 36px "Courier New", monospace', '#111', 450);
        line(40, 805, 480, 805, '#111');
        elementText('subtitle', subText, 40, 845, '13px "Courier New", monospace', '#555', 450);
        drawRuangLariQrBadge(510, 830, 85, 96, -1);
      } else if (theme === 'sunset') {
        box(0, 0, 640, 960, '#1f142e');
        photo(32, 60, 576, 450);
        ctx.save();
        const grad = ctx.createLinearGradient(0, 510, 0, 960);
        grad.addColorStop(0, '#ff6b35'); grad.addColorStop(1, '#1f142e');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 510, 640, 8);
        ctx.restore();
        elementText('heading', 'GOLDEN HOUR // DUSK & DAWN MILES', 36, 565, 'bold 15px Arial', '#ff6b35');
        elementText('year', year(), 32, 705, 'bold 135px Arial', '#ffffff', 568);
        elementText('name', name, 36, 770, 'italic bold 36px Georgia', '#ff6b35', 460);
        line(36, 800, 480, 800, '#ff6b35');
        elementText('subtitle', subText, 36, 840, '13px Arial', '#fed7aa', 450);
        drawRuangLariQrBadge(510, 830, 85, 96, -1);
      } else {
        // Classic, Bold, Minimal, Botanical, Retro, Nordic, Journal, Sidebar
        photo(0, 0, 640, 480);
        box(0, 480, 640, 6, color);
        elementText('heading', headingText, 36, 548, 'bold 18px Arial', color);
        elementText('year', year(), 28, 735, 'bold 155px Arial', color, 575);
        elementText('name', name, 36, 815, 'italic 36px Georgia', foreground, 565);
        line(36, 862, 604, 862, color);
        elementText('subtitle', subText, 36, 897, '14px Arial');
        drawRuangLariQrBadge(510, 830, 85, 96, -1);
      }
    } else {
      // Landscape Cover
      box(0, 0, 794, 530, paper);

      if (theme === 'split') {
        photo(0, 0, 395, 530);
        box(395, 0, 399, 530, paper);
        elementText('heading', headingText, 425, 140, 'bold 16px Arial', color, 340);
        elementText('year', year(), 420, 290, 'bold 130px Arial', color, 340);
        elementText('name', name, 425, 360, 'italic bold 28px Georgia', foreground, 340);
        line(425, 390, 760, 390, color);
        elementText('subtitle', subText, 425, 430, '12px Arial', foreground, 340);
      } else if (theme === 'bottom') {
        elementText('heading', headingText, 36, 120, 'bold 16px Arial', color, 340);
        elementText('year', year(), 32, 260, 'bold 120px Arial', color, 340);
        elementText('name', name, 36, 330, 'italic bold 26px Georgia', foreground, 340);
        line(36, 360, 370, 360, color);
        elementText('subtitle', subText, 36, 400, '12px Arial', foreground, 340);
        photo(394, 0, 400, 530);
      } else if (theme === 'stamp') {
        photo(25, 25, 370, 480);
        drawStampPerforations(25, 25, 370, 480, 3.5, 15);
        drawPostmark(350, 75, 38, 'POSTAL RUN');
        elementText('heading', 'OFFICIAL MARATHON POST', 420, 110, 'bold 15px Arial', color, 340);
        elementText('year', year(), 415, 250, 'bold 120px Georgia', color, 340);
        elementText('name', name.toUpperCase(), 420, 320, 'bold 28px Arial', foreground, 340);
        line(420, 350, 760, 350, color);
        elementText('subtitle', subText, 420, 390, '13px Georgia', foreground, 340);
      } else if (theme === 'filmstrip') {
        drawFilmstripRails(0, 0, 794, 530);
        photo(35, 35, 360, 460);
        elementText('heading', 'A FILM BY ATHLETE STUDIO', 420, 120, 'bold 13px monospace', '#aaa');
        elementText('year', year(), 415, 250, 'bold 110px monospace', color, 340);
        elementText('name', name.toUpperCase(), 420, 320, 'bold 30px Arial', foreground, 340);
        line(420, 350, 760, 350, '#444');
        elementText('subtitle', subText, 420, 390, '12px Arial', '#888');
      } else if (theme === 'badge') {
        photo(25, 25, 360, 480);
        drawBibPins(25, 25, 360, 480);
        box(405, 35, 360, 460, '#f4f6fa');
        box(405, 35, 360, 40, color);
        text('OFFICIAL RUNNER BIB • ' + year(), 420, 60, 'bold 13px Arial', ink);
        elementText('year', year(), 415, 210, 'bold 110px Arial', color, 340);
        elementText('name', name.toUpperCase(), 420, 275, 'bold 28px Arial', '#111', 330);
        line(420, 305, 745, 305, '#ccc');
        elementText('heading', headingText, 420, 340, 'bold 14px Arial', color);
        elementText('subtitle', subText, 420, 375, '12px Arial', '#555');
      } else if (theme === 'arch') {
        photo(35, 35, 340, 460);
        ctx.save();
        ctx.strokeStyle = color; ctx.lineWidth = 1.8;
        const ar = 170;
        ctx.beginPath();
        ctx.moveTo(35, 35 + ar);
        ctx.arc(35 + ar, 35 + ar, ar, Math.PI, 0, false);
        ctx.lineTo(35 + 340, 495); ctx.lineTo(35, 495);
        ctx.closePath();
        ctx.stroke();
        ctx.restore();
        elementText('heading', headingText, 410, 140, 'bold 16px Georgia', color, 340);
        elementText('year', year(), 405, 270, 'italic bold 110px Georgia', color, 340);
        elementText('name', name, 410, 330, 'italic 28px Georgia', foreground, 340);
        line(410, 360, 755, 360, color);
        elementText('subtitle', subText, 410, 400, '13px Georgia', foreground, 340);
      } else if (theme === 'diagonal') {
        photo(0, 0, 380, 530);
        ctx.save();
        ctx.fillStyle = color;
        ctx.beginPath();
        ctx.moveTo(365, 0); ctx.lineTo(415, 530); ctx.lineTo(430, 530); ctx.lineTo(380, 0);
        ctx.closePath();
        ctx.fill();
        ctx.restore();
        elementText('heading', '⚡ ' + headingText.toUpperCase(), 440, 130, 'bold 15px Arial', color);
        elementText('year', year(), 435, 270, 'italic 900 120px Arial', foreground, 330);
        elementText('name', name.toUpperCase(), 440, 335, 'italic bold 28px Arial', color, 330);
        line(440, 365, 765, 365, color);
        elementText('subtitle', subText, 440, 405, 'bold 13px Arial', foreground);
      } else if (theme === 'metro') {
        photo(30, 40, 350, 400);
        drawMetroRoute(30, 470, 350, ['0K', '10K', '21K', '30K', '42K']);
        box(410, 40, 360, 32, color);
        text('M  METRO LINE 2026 // OVERVIEW', 422, 61, 'bold 12px Arial', ink);
        elementText('year', year(), 415, 200, 'bold 115px Arial', foreground, 340);
        elementText('name', name.toUpperCase(), 420, 265, 'bold 30px Arial', color, 340);
        line(420, 295, 750, 295, '#cbd5e1');
        elementText('heading', headingText, 420, 330, 'bold 14px Arial', color);
        elementText('subtitle', subText, 420, 365, '12px Arial', foreground);
      } else if (theme === 'duotone') {
        box(0, 0, 50, 530, color);
        ctx.save();
        ctx.translate(32, 490);
        ctx.rotate(-Math.PI / 2);
        ctx.fillStyle = ink;
        ctx.font = 'bold 30px Arial';
        ctx.fillText('CALENDAR ' + year(), 0, 0);
        ctx.restore();
        photo(75, 35, 340, 460);
        elementText('heading', headingText.toUpperCase(), 440, 120, 'bold 16px Arial', color);
        elementText('year', year(), 435, 250, 'bold 115px Arial', foreground, 330);
        elementText('name', name.toUpperCase(), 440, 315, 'bold 32px Arial', color, 330);
        line(440, 345, 765, 345, '#111');
        elementText('subtitle', subText, 440, 385, 'bold 12px Arial', foreground);
      } else if (theme === 'kraft') {
        box(0, 0, 794, 530, '#ede5d8');
        photo(35, 35, 350, 460);
        drawWashiTape(70, 35, 70, 22, -0.07);
        drawWashiTape(350, 35, 70, 22, 0.07);
        elementText('heading', 'RUNNER’S LOGBOOK — ' + year(), 415, 120, 'bold 15px "Courier New", monospace', color);
        elementText('year', year(), 410, 250, 'bold 110px "Courier New", monospace', '#3d2514', 340);
        elementText('name', name, 415, 315, 'bold 28px Georgia', color, 340);
        line(415, 345, 755, 345, '#b09e86');
        elementText('subtitle', subText, 415, 385, '13px "Courier New", monospace', '#5c432d');
        drawRuangLariQrBadge(680, 410, 85, 96, -1);
      } else if (theme === 'strava') {
        box(0, 0, 794, 530, '#0d1117');
        box(0, 0, 794, 28, '#fc5200');
        text('⚡ STRAVA ATHLETE // LIVE ACTIVITY FEED', 25, 19, 'bold 12px Arial', '#ffffff');
        photo(30, 45, 360, 450);
        box(410, 45, 355, 40, '#161b22');
        text('42.195 KM • 3:45:12 • +310M', 425, 70, 'bold 12px monospace', '#fc5200');
        elementText('year', year(), 405, 200, 'bold 115px Arial', '#ffffff', 340);
        elementText('name', name.toUpperCase(), 410, 265, 'bold 30px Arial', '#fc5200', 340);
        line(410, 295, 745, 295, '#30363d');
        elementText('heading', headingText, 410, 330, 'bold 14px Arial', '#8b949e');
        elementText('subtitle', subText, 410, 365, '12px Arial', '#8b949e');
        drawRuangLariQrBadge(680, 410, 85, 96, -1);
      } else if (theme === 'club') {
        box(0, 0, 794, 530, '#111111');
        box(0, 0, 794, 20, '#ccff00');
        photo(30, 40, 360, 450);
        elementText('heading', 'RUN CLUB // 2026/2027', 415, 100, 'bold 14px Arial', '#ccff00');
        elementText('year', year(), 410, 230, 'italic 900 120px Arial', '#ffffff', 340);
        elementText('name', name.toUpperCase(), 415, 295, 'bold 30px Arial', '#ccff00', 340);
        line(415, 325, 745, 325, '#333');
        elementText('subtitle', subText, 415, 365, '12px Arial', '#888');
        drawRuangLariQrBadge(680, 410, 85, 96, -1);
      } else if (theme === 'majors') {
        photo(30, 40, 360, 450);
        box(410, 40, 355, 34, '#002b49');
        text('WORLD MARATHON MAJORS // 7 STARS', 422, 62, 'bold 12px Arial', '#ffffff');
        elementText('year', year(), 405, 200, 'bold 115px Arial', '#002b49', 340);
        elementText('name', name.toUpperCase(), 410, 265, 'bold 30px Arial', color, 340);
        line(410, 295, 745, 295, '#002b49');
        elementText('heading', headingText, 410, 330, 'bold 14px Arial', '#002b49');
        elementText('subtitle', subText, 410, 365, '12px Arial', '#555');
        drawRuangLariQrBadge(680, 410, 85, 96, -1);
      } else if (theme === 'topography') {
        box(0, 0, 794, 530, '#f3f4ed');
        photo(30, 40, 360, 450);
        elementText('heading', '▲ TRAIL ELEVATION PROFILE', 415, 100, 'bold 14px Arial', '#1e392a');
        elementText('year', year(), 410, 230, 'bold 115px Arial', '#1e392a', 340);
        elementText('name', name, 415, 295, 'italic bold 28px Georgia', color, 340);
        line(415, 325, 745, 325, '#1e392a');
        elementText('subtitle', subText, 415, 365, '12px Arial', '#556b2f');
        drawRuangLariQrBadge(680, 410, 85, 96, -1);
      } else if (theme === 'crown') {
        photo(30, 40, 360, 450);
        box(410, 40, 355, 32, '#ffb800');
        text('👑 KING OF THE MOUNTAIN // SEGMENT PR', 422, 61, 'bold 12px Arial', '#111');
        elementText('year', year(), 405, 200, 'bold 115px Arial', '#111', 340);
        elementText('name', name.toUpperCase(), 410, 265, 'bold 30px Arial', '#ffb800', 340);
        line(410, 295, 745, 295, '#ffb800');
        elementText('heading', headingText, 410, 330, 'bold 14px Arial', '#111');
        elementText('subtitle', subText, 410, 365, '12px Arial', '#555');
        drawRuangLariQrBadge(680, 410, 85, 96, -1);
      } else if (theme === 'blueprint') {
        box(0, 0, 794, 530, '#0a3871');
        photo(30, 40, 360, 450);
        elementText('heading', 'CAD ATHLETIC SPEC SHEET', 415, 100, 'bold 13px monospace', '#93c5fd');
        elementText('year', year(), 410, 230, 'bold 115px monospace', '#ffffff', 340);
        elementText('name', name.toUpperCase(), 415, 295, 'bold 28px monospace', '#93c5fd', 340);
        line(415, 325, 745, 325, '#ffffff');
        elementText('subtitle', subText, 415, 365, '12px monospace', '#bfdbfe');
        drawRuangLariQrBadge(680, 410, 85, 96, -1);
      } else if (theme === 'acid') {
        box(0, 0, 794, 530, '#140026');
        photo(30, 40, 360, 450);
        elementText('heading', '⚡ CYBER RUNNER 3000', 415, 100, 'bold 14px Arial', '#b8ff00');
        elementText('year', year(), 410, 230, 'italic 900 120px Arial', '#ffffff', 340);
        elementText('name', name.toUpperCase(), 415, 295, 'italic bold 30px Arial', '#b8ff00', 340);
        line(415, 325, 745, 325, '#b8ff00');
        elementText('subtitle', subText, 415, 365, '12px Arial', '#d8b4fe');
        drawRuangLariQrBadge(680, 410, 85, 96, -1);
      } else if (theme === 'taper') {
        photo(30, 40, 360, 450);
        box(410, 40, 355, 32, '#dc2626');
        text('RACE TAPER MATRIX // MARATHON', 422, 61, 'bold 12px Arial', '#ffffff');
        elementText('year', year(), 405, 200, 'bold 115px Arial', '#dc2626', 340);
        elementText('name', name.toUpperCase(), 410, 265, 'bold 30px Arial', '#111', 340);
        line(410, 295, 745, 295, '#dc2626');
        elementText('heading', headingText, 410, 330, 'bold 14px Arial', '#dc2626');
        elementText('subtitle', subText, 410, 365, '12px Arial', '#555');
        drawRuangLariQrBadge(680, 410, 85, 96, -1);
      } else if (theme === 'infographic') {
        photo(30, 40, 360, 450);
        box(410, 40, 355, 34, '#ea580c');
        text('YEAR IN SPORT ' + year() + ' // INFOGRAPHIC', 422, 62, 'bold 12px Arial', '#ffffff');
        elementText('year', year(), 405, 200, 'bold 115px Arial', '#111', 340);
        elementText('name', name.toUpperCase(), 410, 265, 'bold 30px Arial', '#ea580c', 340);
        line(410, 295, 745, 295, '#ea580c');
        elementText('heading', headingText, 410, 330, 'bold 14px Arial', '#ea580c');
        elementText('subtitle', subText, 410, 365, '12px Arial', '#555');
        drawRuangLariQrBadge(680, 410, 85, 96, -1);
      } else if (theme === 'shoebox') {
        photo(30, 40, 360, 450);
        box(410, 40, 355, 32, '#e11d48');
        text('GEAR ROTATION & SPEC // BOX LABEL', 422, 61, 'bold 12px Arial', '#ffffff');
        elementText('year', year(), 405, 200, 'bold 115px Arial', '#e11d48', 340);
        elementText('name', name.toUpperCase(), 410, 265, 'bold 30px Arial', '#111', 340);
        line(410, 295, 745, 295, '#e11d48');
        elementText('heading', headingText, 410, 330, 'bold 14px Arial', '#e11d48');
        elementText('subtitle', subText, 410, 365, '12px Arial', '#555');
        drawRuangLariQrBadge(680, 410, 85, 96, -1);
      } else if (theme === 'zine') {
        box(0, 0, 794, 530, '#f5f5f0');
        photo(30, 40, 360, 450);
        drawWashiTape(65, 40, 70, 22, -0.09);
        drawWashiTape(350, 40, 70, 22, 0.09);
        elementText('heading', 'INDIE RUNNER ZINE // ISSUE ' + year(), 415, 100, 'bold 14px "Courier New", monospace', '#111');
        elementText('year', year(), 410, 230, 'bold 115px "Courier New", monospace', '#111', 340);
        elementText('name', name.toUpperCase(), 415, 295, 'bold 28px "Courier New", monospace', '#111', 340);
        line(415, 325, 745, 325, '#111');
        elementText('subtitle', subText, 415, 365, '12px "Courier New", monospace', '#555');
        drawRuangLariQrBadge(680, 410, 85, 96, -1);
      } else if (theme === 'sunset') {
        box(0, 0, 794, 530, '#1f142e');
        photo(30, 40, 360, 450);
        elementText('heading', 'GOLDEN HOUR // DAWN & DUSK', 415, 100, 'bold 14px Arial', '#ff6b35');
        elementText('year', year(), 410, 230, 'bold 115px Arial', '#ffffff', 340);
        elementText('name', name, 415, 295, 'italic bold 28px Georgia', '#ff6b35', 340);
        line(415, 325, 745, 325, '#ff6b35');
        elementText('subtitle', subText, 415, 365, '12px Arial', '#fed7aa');
        drawRuangLariQrBadge(680, 410, 85, 96, -1);
      } else {
        // Classic landscape cover with curve lines
        photo(0, 0, 366, 530);
        for (let i = 0; i < 4; i++) {
          ctx.strokeStyle = dark ? '#2a3b54' : '#d9dbd6'; ctx.lineWidth = 1.5; ctx.beginPath();
          ctx.moveTo(367, 115 + i * 14); ctx.bezierCurveTo(520, 60 + i * 14, 670, 118 + i * 14, 794, 45 + i * 14); ctx.stroke();
          ctx.beginPath(); ctx.moveTo(367, 507 + i * 10); ctx.bezierCurveTo(515, 448 + i * 10, 650, 507 + i * 10, 794, 435 + i * 10); ctx.stroke();
        }
        ctx.save(); ctx.translate(391, 158); ctx.rotate(-.045);
        ctx.shadowColor = '#0004'; ctx.shadowBlur = 18; ctx.shadowOffsetY = 8;
        box(0, 0, 368, 255, paper); ctx.shadowBlur = 0; ctx.shadowOffsetY = 0;
        elementText('heading', headingText, 28, 45, 'italic bold 26px Arial');
        elementText('year', year(), 21, 185, 'italic 900 150px Arial', color, 325);
        box(0, 205, 368, 50, color); elementText('name', name, 20, 242, 'italic bold 36px Georgia', ink, 328);
        ctx.restore();
        drawRuangLariQrBadge(680, 410, 85, 96, -1);
      }
    }
    drawCustomTexts();
    return;
  }

  // =========================================================================
  // --- 2. TWO MONTHS PER PAGE MODE ---
  // =========================================================================
  if (is2M) {
    const m1 = pageIndex * 2;
    const m2 = pageIndex * 2 + 1;

    if (wall) {
      box(0, 0, 640, 960, paper);

      if (theme === 'bottom') {
        // Calendar top, photo bottom
        elementText('name', name, 28, 35, 'bold 15px Arial', foreground, 450);
        elementText('year', year(), 540, 35, 'bold 18px Arial', color);
        [m1, m2].forEach((m, colIdx) => {
          const left = colIdx === 0 ? 24 : 332, width = 284, top = 50;
          beginElement('calendar' + colIdx, left, top, width, 380);
          elementText('month' + colIdx, months[m].toUpperCase(), left, top + 20, 'bold 18px Arial', foreground, width - 40);
          text(String(m + 1).padStart(2, '0'), left + width - 26, top + 20, 'bold 16px Arial', color);
          box(left, top + 28, width, 20, color);
          const cellW = width / 7;
          ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, left + i * cellW + 4, top + 42, 'bold 8.5px Arial', ink));
          drawDaysGrid(left, top + 52, cellW, 35, m, { compact: !isRun });
          endElement();
        });
        photo(0, 480, 640, 480);
        box(0, 475, 640, 5, color);
      } else if (theme === 'split') {
        // Photo full left, two stacked months right
        photo(0, 0, 290, 960);
        box(290, 0, 6, 960, color);
        elementText('name', name, 310, 30, 'bold 14px Arial', foreground, 240);
        elementText('year', year(), 560, 30, 'bold 16px Arial', color);
        [m1, m2].forEach((m, idx) => {
          const left = 310, width = 310, top = 50 + idx * 440;
          beginElement('calendar' + idx, left, top, width, 410);
          elementText('month' + idx, months[m].toUpperCase(), left, top + 20, 'bold 20px Arial', foreground, width - 40);
          text(String(m + 1).padStart(2, '0'), left + width - 26, top + 20, 'bold 16px Arial', color);
          box(left, top + 28, width, 22, color);
          const cellW = width / 7;
          ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, left + i * cellW + 4, top + 43, 'bold 9px Arial', ink));
          drawDaysGrid(left, top + 54, cellW, 36, m, { compact: !isRun });
          endElement();
        });
      } else {
        // Standard Top Photo 2-month
        photo(0, 0, 640, 410);
        box(0, 410, 640, 6, color);
        elementText('name', name, 28, 445, 'bold 15px Arial', foreground, 450);
        elementText('year', year(), 540, 445, 'bold 18px Arial', color);

        [m1, m2].forEach((m, colIdx) => {
          const left = colIdx === 0 ? 24 : 332;
          const width = 284;
          const top = 465;
          beginElement('calendar' + colIdx, left, top, width, 352);
          elementText('month' + colIdx, months[m].toUpperCase(), left, top + 20, theme === 'bold' ? 'bold 20px Arial' : 'italic bold 20px Georgia', foreground, width - 40);
          text(String(m + 1).padStart(2, '0'), left + width - 26, top + 20, 'bold 16px Arial', color);
          box(left, top + 30, width, 22, color);
          const cellW = width / 7;
          ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, left + i * cellW + 4, top + 45, 'bold 9px Arial', ink));
          drawDaysGrid(left, top + 58, cellW, 38, m, { compact: !isRun });

          const botY = top + 58 + 6 * 38 + 6;
          line(left, botY, left + width, botY, color);
          beginElement('notes' + colIdx, left, botY, width, 32);
          const mHols = showHol ? getHolidays(year(), m) : [];
          if (mHols.length) {
            const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
            text('Libur: ' + holStr, left + 4, botY + 16, 'bold 8.5px Arial', redHolidayColor, width - 8);
          } else if (isRun) {
            text('TARGET: _____ KM   |   RACE / AGENDA', left + 4, botY + 16, 'bold 8px Arial', '#555');
          } else {
            text('CATATAN & AGENDA', left + 4, botY + 16, 'bold 8px Arial', '#555');
          }
          line(left, botY + 28, left + width, botY + 28, dark ? '#334460' : '#bbb');
          endElement(); endElement();
        });
        if (byId('ruangLariQr')?.checked) {
          drawRuangLariQrBadge(520, 835, 86, 96, m1);
        }
      }
    } else {
      // Landscape 2-month
      box(0, 0, 794, 530, paper);
      box(0, 0, 794, 26, color);
      elementText('name', name.toUpperCase(), 20, 18, 'bold 12px Arial', ink);
      elementText('year', year(), 740, 18, 'bold 14px Arial', ink);
      photo(20, 38, 205, 475);

      [m1, m2].forEach((m, colIdx) => {
        const left = 240 + colIdx * 274;
        const width = 260;
        const top = 38;
        beginElement('calendar' + colIdx, left, top, width, 368);
        box(left, top, width, 30, surface);
        elementText('month' + colIdx, months[m].toUpperCase(), left + 8, top + 21, theme === 'bold' ? 'bold 18px Arial' : 'italic bold 18px Georgia', foreground);
        text(String(m + 1).padStart(2, '0'), left + width - 28, top + 21, 'bold 16px Arial', color);
        box(left, top + 34, width, 20, color);
        const cellW = width / 7;
        ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, left + i * cellW + 3, top + 48, 'bold 8.5px Arial', ink));
        drawDaysGrid(left, top + 58, cellW, 46, m, { boxBg: true, verticalLines: true });

        const botY = top + 58 + 6 * 46 + 4;
        beginElement('notes' + colIdx, left, botY, width, 32);
        const mHols = showHol ? getHolidays(year(), m) : [];
        if (mHols.length) {
          box(left, botY, width, 30, dark ? '#3d1c1c' : '#fdf1f1');
          const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
          text('Libur: ' + holStr, left + 6, botY + 19, 'bold 8.5px Arial', redHolidayColor, width - 12);
        } else if (isRun) {
          box(left, botY, width, 30, surface);
          text('Target: ___ km  |  Aktual: ___ km', left + 8, botY + 19, 'bold 9px Arial', foreground);
        } else {
          box(left, botY, width, 30, surface);
          text('Catatan & Agenda Bulanan', left + 8, botY + 19, 'bold 9px Arial', foreground);
        }
        endElement(); endElement();
      });
      if (byId('ruangLariQr')?.checked) {
        drawRuangLariQrBadge(700, 420, 82, 94, m1);
      }
    }
    drawCustomTexts();
    return;
  }

  // =========================================================================
  // --- 2.5. FOUR MONTHS PER PAGE MODE ---
  // =========================================================================
  function drawFourMonthPage() {
    const startM = Math.max(0, Math.min(8, pageIndex * 4));
    const fourMonths = [startM, startM + 1, startM + 2, startM + 3];

    if (wall) {
      box(0, 0, 640, 960, paper);

      // Top Hero Photo
      photo(0, 0, 640, 340);
      box(0, 340, 640, 4, color);

      // Header Bar (Name, Subtitle, Year)
      const caturwulanTitle = 'CATURWULAN ' + (pageIndex + 1) + ' // ' + (fourMonthLabels[pageIndex] || '') + ' ' + year();
      text(caturwulanTitle.toUpperCase(), 24, 364, 'bold 10px "Inter Tight", "Sora", sans-serif', color);

      elementText('name', name, 24, 392, 'bold 22px ' + currentFontFamily, foreground, 420);
      elementText('year', year(), 530, 392, 'bold 36px ' + currentFontFamily, color);

      // Ruang Lari QR Code badge if enabled
      if (typeof drawRuangLariQrBadge === 'function' && byId('ruangLariQr')?.checked) {
        drawRuangLariQrBadge(555, 348, 60, 68, startM);
      }

      // Divider line
      box(24, 404, 592, 1, dark ? '#222f42' : '#e2e8f0');

      // 4-Month Grid (2 Columns x 2 Rows)
      fourMonths.forEach((m, idx) => {
        const col = idx % 2;
        const row = Math.floor(idx / 2);
        const bx = 24 + col * (286 + 20);
        const by = 416 + row * (248 + 16);
        const bw = 286;
        const bh = 248;

        beginElement('calendar' + idx, bx, by, bw, bh);

        // Container Box
        const cardBg = theme === 'minimal' ? paper : (dark ? '#131924' : '#ffffff');
        const cardBorder = dark ? '#222f42' : '#e2e8f0';
        ctx.fillStyle = cardBg;
        ctx.strokeStyle = cardBorder;
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.roundRect(bx, by, bw, bh, 6);
        ctx.fill();
        ctx.stroke();

        // Month Header
        const monthNum = String(m + 1).padStart(2, '0');
        const monthName = months[m].toUpperCase();

        if (theme === 'bold') {
          box(bx + 8, by + 8, 24, 16, color);
          text(monthNum, bx + 12, by + 20, 'bold 10px "JetBrains Mono", monospace', ink);
          text(monthName, bx + 38, by + 20, 'bold 13px "Inter Tight", "Sora", sans-serif', foreground);
        } else {
          text(monthNum, bx + 10, by + 20, 'bold 12px "JetBrains Mono", monospace', color);
          text(monthName, bx + 34, by + 20, 'bold 13px "Inter Tight", "Sora", sans-serif', foreground);
        }

        line(bx + 8, by + 27, bx + bw - 8, by + 27, dark ? '#222f42' : '#f1f5f9');

        // Weekday Header
        const cellW = (bw - 12) / 7;
        const xOffset = bx + 6;
        const dayNames = ['S', 'S', 'R', 'K', 'J', 'S', 'M'];
        for (let dIdx = 0; dIdx < 7; dIdx++) {
          const dx = xOffset + dIdx * cellW;
          const isSun = dIdx === 6;
          ctx.textAlign = 'center';
          text(dayNames[dIdx], dx + cellW / 2, by + 41, 'bold 8.5px "Inter Tight", "Sora", sans-serif', isSun ? redHolidayColor : (dark ? '#64748b' : '#94a3b8'));
        }
        ctx.textAlign = 'left';

        // Days Grid with drawDaysGrid
        const cellH = 32;
        drawDaysGrid(xOffset, by + 46, cellW, cellH, m, { compact: false });

        endElement();
      });
    } else {
      // Landscape fallback (794 x 530)
      box(0, 0, 794, 530, paper);
      photo(16, 16, 260, 498);
      box(276, 16, 2, 498, color);

      const caturwulanTitle = 'CATURWULAN ' + (pageIndex + 1) + ' // ' + (fourMonthLabels[pageIndex] || '') + ' ' + year();
      text(caturwulanTitle.toUpperCase(), 290, 34, 'bold 9px "Inter Tight", "Sora", sans-serif', color);
      elementText('name', name, 290, 56, 'bold 16px ' + currentFontFamily, foreground, 340);
      elementText('year', year(), 730, 56, 'bold 24px ' + currentFontFamily, color);

      fourMonths.forEach((m, idx) => {
        const col = idx % 2;
        const row = Math.floor(idx / 2);
        const bx = 290 + col * (236 + 12);
        const by = 68 + row * (216 + 10);
        const bw = 236;
        const bh = 216;

        beginElement('calendar' + idx, bx, by, bw, bh);
        const cardBg = theme === 'minimal' ? paper : (dark ? '#131924' : '#ffffff');
        const cardBorder = dark ? '#222f42' : '#e2e8f0';
        ctx.fillStyle = cardBg; ctx.strokeStyle = cardBorder; ctx.lineWidth = 1;
        ctx.beginPath(); ctx.roundRect(bx, by, bw, bh, 5); ctx.fill(); ctx.stroke();

        const monthNum = String(m + 1).padStart(2, '0');
        text(monthNum + ' ' + months[m].toUpperCase(), bx + 8, by + 18, 'bold 10px "Inter Tight", "Sora", sans-serif', color);
        line(bx + 6, by + 24, bx + bw - 6, by + 24, dark ? '#222f42' : '#f1f5f9');

        const cellW = (bw - 8) / 7;
        const xOffset = bx + 4;
        const cellH = 29;
        drawDaysGrid(xOffset, by + 34, cellW, cellH, m, { compact: false });
        endElement();
      });
    }
  }

  if (is4M) {
    drawFourMonthPage();
    drawCustomTexts();
    return;
  }

  // =========================================================================
  // --- 3. SINGLE MONTH PER PAGE (pageIndex 0 to 11) ---
  // =========================================================================
  const month = pageIndex;
  const mHols = showHol ? getHolidays(year(), month) : [];

  // -------------------------------------------------------------------------
  // WALL CALENDAR (Portrait 640 × 960)
  // -------------------------------------------------------------------------
  if (wall) {
    box(0, 0, 640, 960, paper);

    // --- VARIATION A: SPLIT 50/50 ---
    if (theme === 'split') {
      photo(0, 0, 310, 960);
      box(310, 0, 6, 960, color);
      const left = 325, top = 30, width = 300;
      elementText('month', months[month].toUpperCase(), left, top + 35, 'bold 30px Arial', color, width - 50);
      text(String(month + 1).padStart(2, '0'), left + width - 36, top + 35, 'bold 24px Arial', color);
      elementText('year', year(), left, top + 65, 'bold 16px Arial', foreground);
      elementText('name', name, left, top + 90, '12px Arial', foreground);

      if (isRun) {
        beginElement('targets', left, top + 105, width, 22);
        text('TARGET VOL: ___ KM | LONG RUN: ___ KM', left, top + 120, 'bold 9.5px Arial', color);
        endElement();
      }

      const gridTop = isRun ? top + 130 : top + 105;
      beginElement('calendar', left, gridTop, width, 400);
      box(left, gridTop, width, 24, color);
      const cellW = width / 7, cellH = isRun ? 56 : 58;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, left + i * cellW + 3, gridTop + 16, 'bold 8.5px Arial', ink));
      drawDaysGrid(left, gridTop + 28, cellW, cellH, month, { boxBg: true, verticalLines: true });
      endElement();

      beginElement('notes', left, gridTop + 380, width, 140);
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, left, gridTop + 400, 'bold 9px Arial', redHolidayColor, width);
      }
      if (isRun) {
        line(left, gridTop + 415, left + width, gridTop + 415, color);
        text('AGENDA / RACE: ___________________', left, gridTop + 435, 'bold 9.5px Arial', foreground);
        text('TOTAL: ___ KM  |  SESI: ___ KALI', left, gridTop + 460, '9.5px Arial', foreground);
      } else {
        line(left, gridTop + 415, left + width, gridTop + 415, dark ? '#3a4b66' : '#ccc');
        text('CATATAN BULANAN: _________________', left, gridTop + 435, '9.5px Arial', foreground);
      }
      endElement();
    }
    // --- VARIATION B: PHOTO BOTTOM (INVERTED) ---
    else if (theme === 'bottom') {
      const left = 32, top = 25, width = 576;
      elementText('month', months[month].toUpperCase(), left, top + 35, 'bold 36px Arial', color, 400);
      elementText('year', year(), left + width - 65, top + 35, 'bold 25px Arial', color);
      elementText('name', name, left, top + 60, '12px Arial', foreground);

      if (isRun) {
        beginElement('targets', left, top + 70, width, 20);
        text('TARGET VOLUME: _____ KM   |   TARGET LONG RUN: _____ KM', left, top + 84, 'bold 10px Arial', color);
        endElement();
      }

      const gridTop = isRun ? top + 92 : top + 70;
      beginElement('calendar', left, gridTop, width, 320);
      box(left, gridTop, width, 24, color);
      const cellW = width / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, left + i * cellW + 8, gridTop + 17, 'bold 11px Arial', ink));
      drawDaysGrid(left, gridTop + 26, cellW, cellH, month, { verticalLines: true });
      endElement();

      photo(0, 480, 640, 480);
      box(0, 475, 640, 5, color);
    }
    // --- VARIATION C: HERO SHOWCASE (PHOTO 70%) ---
    else if (theme === 'hero') {
      photo(0, 0, 640, 660);
      box(0, 660, 640, 6, color);
      const left = 32, top = 675, width = 576;
      elementText('month', months[month].toUpperCase(), left, top + 25, 'bold 28px Arial', color, 300);
      elementText('year', year(), left + width - 60, top + 25, 'bold 20px Arial', color);
      elementText('name', name, left + 180, top + 24, '12px Arial', foreground);

      const gridTop = top + 36;
      beginElement('calendar', left, gridTop, width, 220);
      box(left, gridTop, width, 20, color);
      const cellW = width / 7, cellH = 29;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, left + i * cellW + 8, gridTop + 14, 'bold 9px Arial', ink));
      drawDaysGrid(left, gridTop + 22, cellW, cellH, month, { compact: true });
      endElement();
    }
    // --- VARIATION D: POLAROID GALLERY ---
    else if (theme === 'polaroid') {
      box(0, 0, 640, 960, '#faf8f5');
      ctx.save(); ctx.translate(320, 240); ctx.rotate(-.02);
      ctx.shadowColor = '#0003'; ctx.shadowBlur = 16; ctx.shadowOffsetY = 8;
      box(-210, -210, 420, 430, '#ffffff');
      ctx.shadowBlur = 0;
      photo(-190, -190, 380, 340);
      text(name + ' · ' + months[month] + ' ' + year(), -170, 195, 'italic bold 18px Georgia', '#333');
      ctx.restore();

      const left = 32, top = 485, width = 576;
      elementText('month', months[month], left, top + 30, 'italic bold 32px Georgia', color);
      elementText('year', year(), left + width - 60, top + 30, 'bold 24px Arial', color);

      beginElement('calendar', left, top + 45, width, 340);
      box(left, top + 45, width, 24, color);
      const cellW = width / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, left + i * cellW + 8, top + 62, 'bold 11px Arial', ink));
      drawDaysGrid(left, top + 72, cellW, cellH, month);
      endElement();
    }
    // --- VARIATION E: MAGAZINE COVER STYLE ---
    else if (theme === 'magazine') {
      photo(0, 0, 640, 580);
      ctx.fillStyle = '#00000055'; ctx.fillRect(0, 0, 640, 160);
      elementText('month', months[month].toUpperCase(), 32, 110, 'italic 900 85px Arial', '#ffffff', 576);
      elementText('year', year(), 480, 150, 'bold 36px Arial', color);
      elementText('name', 'FEATURED: ' + name.toUpperCase(), 36, 145, 'bold 14px Arial', '#eee');

      const left = 32, top = 595, width = 576;
      if (isRun) {
        beginElement('targets', left, top, width, 18);
        text('TARGET VOLUME: _____ KM   |   LONG RUN: _____ KM', left, top + 14, 'bold 10px Arial', color);
        endElement();
      }

      const gridTop = isRun ? top + 24 : top + 10;
      beginElement('calendar', left, gridTop, width, 310);
      box(left, gridTop, width, 24, color);
      const cellW = width / 7, cellH = 42;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, left + i * cellW + 8, gridTop + 17, 'bold 11px Arial', ink));
      drawDaysGrid(left, gridTop + 26, cellW, cellH, month, { boxBg: true, verticalLines: true });
      endElement();
    }
    // --- VARIATION G: POSTAGE STAMP ---
    else if (theme === 'stamp') {
      photo(40, 30, 560, 410);
      drawStampPerforations(40, 30, 560, 410, 3.5, 15);
      drawPostmark(535, 75, 38, 'KM ' + (month + 1) * 3);
      box(48, 38, 54, 24, color);
      text('42K', 58, 55, 'bold 13px Arial', ink);

      elementText('month', months[month].toUpperCase(), 40, 480, 'bold 34px Georgia', color, 450);
      elementText('year', year(), 520, 478, 'bold 24px Arial', color);
      elementText('name', name, 40, 505, '12px Arial', foreground, 480);

      if (isRun) {
        beginElement('targets', 40, 515, 560, 18);
        text('TARGET VOL: _____ KM   |   LONG RUN: _____ KM', 40, 528, 'bold 10px Arial', color);
        endElement();
      }

      const gridTop = isRun ? 538 : 520;
      beginElement('calendar', 40, gridTop, 560, 290);
      box(40, gridTop, 560, 24, color);
      const cellW = 560 / 7, cellH = 44;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 40 + i * cellW + 8, gridTop + 17, 'bold 11px Arial', ink));
      drawDaysGrid(40, gridTop + 26, cellW, cellH, month, { verticalLines: true });
      endElement();

      beginElement('notes', 40, gridTop + 295, 560, 65);
      line(40, gridTop + 295, 600, gridTop + 295, color);
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ' ' + months[month].substring(0, 3) + ': ' + h.n).join('   |   ');
        text('LIBUR: ' + holStr, 40, gridTop + 314, 'bold 9.5px Arial', redHolidayColor, 560);
      }
      text('CATATAN POS & RACE: __________________________________________________', 40, gridTop + 338, '9.5px Arial', foreground);
      endElement();
    }
    // --- VARIATION H: FILMSTRIP ---
    else if (theme === 'filmstrip') {
      drawFilmstripRails(0, 20, 640, 440);
      photo(20, 40, 600, 400);
      elementText('month', months[month].toUpperCase(), 32, 505, 'bold 36px monospace', color, 450);
      elementText('year', year(), 520, 502, 'bold 24px monospace', color);
      elementText('name', 'ATHLETE: ' + name.toUpperCase(), 32, 530, 'bold 11px monospace', foreground, 480);

      if (isRun) {
        beginElement('targets', 32, 538, 576, 18);
        text('TARGET VOLUME: _____ KM   |   FRAME: ' + String(month + 1).padStart(2, '0') + 'A', 32, 550, 'bold 10px monospace', color);
        endElement();
      }

      const gridTop = isRun ? 560 : 544;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, '#111');
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'bold 10.5px monospace', '#ffffff'));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month, { boxBg: true, verticalLines: true });
      endElement();

      beginElement('notes', 32, gridTop + 292, 576, 65);
      line(32, gridTop + 292, 608, gridTop + 292, '#111');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join('   |   ');
        text('LIBUR: ' + holStr, 32, gridTop + 310, 'bold 9.5px monospace', redHolidayColor, 576);
      }
      text('ROLL NOTES & TRAINING LOG: ___________________________________________', 32, gridTop + 334, '9.5px monospace', foreground);
      endElement();
    }
    // --- VARIATION I: MARATHON BIB ---
    else if (theme === 'badge') {
      box(32, 20, 576, 35, color);
      text('RACE BIB • MONTH ' + String(month + 1).padStart(2, '0') + ' • ' + name.toUpperCase(), 44, 43, 'bold 13px Arial', ink);
      photo(32, 65, 576, 385);
      drawBibPins(32, 65, 576, 385);

      elementText('month', months[month].toUpperCase(), 32, 495, 'bold 36px Arial', '#111', 400);
      elementText('year', year(), 520, 492, 'bold 24px Arial', color);
      elementText('name', 'DIVISI: MARATHON   |   STATUS: ACTIVE', 34, 520, 'bold 11px Arial', '#555');

      if (isRun) {
        beginElement('targets', 32, 530, 576, 18);
        text('TARGET VOLUME: _____ KM   |   PACE TARGET: _____ /KM', 32, 542, 'bold 10px Arial', color);
        endElement();
      }

      const gridTop = isRun ? 554 : 536;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, color);
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'bold 11px Arial', ink));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month, { boxBg: true, verticalLines: true });
      endElement();

      beginElement('notes', 32, gridTop + 292, 576, 65);
      line(32, gridTop + 292, 608, gridTop + 292, color);
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, 32, gridTop + 310, 'bold 9.5px Arial', redHolidayColor, 576);
      }
      text('TIMING SPLIT & RACE DAY: _____________________________________________', 32, gridTop + 334, '9.5px Arial', foreground);
      endElement();
    }
    // --- VARIATION J: ARCH WINDOW ---
    else if (theme === 'arch') {
      photo(70, 30, 500, 420);
      ctx.save();
      ctx.strokeStyle = color; ctx.lineWidth = 1.8;
      const ar = 250;
      ctx.beginPath();
      ctx.moveTo(70, 30 + ar);
      ctx.arc(70 + ar, 30 + ar, ar, Math.PI, 0, false);
      ctx.lineTo(70 + 500, 450);
      ctx.lineTo(70, 450);
      ctx.closePath();
      ctx.stroke();
      ctx.restore();

      elementText('month', months[month], 70, 500, 'italic bold 36px Georgia', color, 400);
      elementText('year', year(), 510, 498, 'bold 22px Georgia', color);
      elementText('name', name, 70, 526, 'italic 13px Georgia', foreground, 450);

      const gridTop = 544;
      beginElement('calendar', 70, gridTop, 500, 280);
      box(70, gridTop, 500, 24, color);
      const cellW = 500 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 70 + i * cellW + 6, gridTop + 17, 'bold 10px Georgia', ink));
      drawDaysGrid(70, gridTop + 26, cellW, cellH, month);
      endElement();

      beginElement('notes', 70, gridTop + 292, 500, 65);
      line(70, gridTop + 292, 570, gridTop + 292, color);
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('Libur: ' + holStr, 70, gridTop + 310, 'bold 9px Georgia', redHolidayColor, 500);
      }
      text('Refleksi & Catatan Bulanan: ___________________________________________', 70, gridTop + 334, '10px Georgia', foreground);
      endElement();
    }
    // --- VARIATION K: DIAGONAL MOTION ---
    else if (theme === 'diagonal') {
      photo(0, 0, 640, 440);
      ctx.save();
      ctx.fillStyle = color;
      ctx.beginPath();
      ctx.moveTo(0, 420); ctx.lineTo(640, 455); ctx.lineTo(640, 465); ctx.lineTo(0, 430);
      ctx.closePath();
      ctx.fill();
      ctx.restore();

      elementText('month', months[month].toUpperCase(), 32, 505, 'italic 900 38px Arial', color, 450);
      elementText('year', year(), 520, 502, 'italic bold 26px Arial', foreground);
      elementText('name', name.toUpperCase(), 32, 530, 'italic bold 12px Arial', foreground, 480);

      const gridTop = 548;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, color);
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'italic bold 11px Arial', ink));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month, { verticalLines: true });
      endElement();

      beginElement('notes', 32, gridTop + 292, 576, 65);
      line(32, gridTop + 292, 608, gridTop + 292, color);
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, 32, gridTop + 310, 'bold 9.5px Arial', redHolidayColor, 576);
      }
      text('TARGET SPEED & WORKOUT: ___________________________________________', 32, gridTop + 334, 'italic bold 9.5px Arial', foreground);
      endElement();
    }
    // --- VARIATION L: METRO TRANSIT ---
    else if (theme === 'metro') {
      box(0, 0, 640, 26, color);
      text('M  METRO MARATHON • LINE ' + String(month + 1).padStart(2, '0') + ' • ' + months[month].toUpperCase(), 32, 18, 'bold 12px Arial', ink);
      photo(32, 38, 576, 395);
      drawMetroRoute(40, 450, 560, ['W1', 'W2', 'W3', 'W4', 'W5 / FINISH']);

      elementText('month', months[month].toUpperCase(), 32, 500, 'bold 32px Arial', foreground, 420);
      elementText('year', year(), 520, 498, 'bold 22px Arial', color);
      elementText('name', name.toUpperCase(), 32, 524, 'bold 11px Arial', '#64748b', 480);

      const gridTop = 540;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, color);
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'bold 10.5px Arial', ink));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month, { boxBg: true, verticalLines: true });
      endElement();

      beginElement('notes', 32, gridTop + 292, 576, 65);
      line(32, gridTop + 292, 608, gridTop + 292, '#cbd5e1');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('STATION HOLIDAY: ' + holStr, 32, gridTop + 310, 'bold 9.5px Arial', redHolidayColor, 576);
      }
      text('ROUTE TARGET & STATION MILESTONES: ____________________________________', 32, gridTop + 334, '9.5px Arial', foreground);
      endElement();
    }
    // --- VARIATION M: DUOTONE POSTER ---
    else if (theme === 'duotone') {
      box(0, 0, 55, 960, color);
      ctx.save();
      ctx.translate(36, 420);
      ctx.rotate(-Math.PI / 2);
      ctx.fillStyle = ink;
      ctx.font = 'bold 32px Arial';
      ctx.fillText(months[month].toUpperCase() + ' ' + year(), 0, 0);
      ctx.restore();

      photo(68, 25, 542, 425);
      elementText('month', months[month].toUpperCase(), 68, 485, 'bold 36px Arial', foreground, 420);
      elementText('year', year(), 530, 483, 'bold 24px Arial', color);
      elementText('name', name.toUpperCase(), 68, 510, 'bold 12px Arial', color, 450);

      const gridTop = 528;
      beginElement('calendar', 68, gridTop, 542, 285);
      box(68, gridTop, 542, 24, '#111');
      const cellW = 542 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 68 + i * cellW + 6, gridTop + 17, 'bold 10px Arial', '#ffffff'));
      drawDaysGrid(68, gridTop + 26, cellW, cellH, month, { boxBg: true, verticalLines: true });
      endElement();

      beginElement('notes', 68, gridTop + 292, 542, 65);
      line(68, gridTop + 292, 610, gridTop + 292, '#111');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, 68, gridTop + 310, 'bold 9.5px Arial', redHolidayColor, 542);
      }
      text('POSTER LOG: ___________________________________________________________', 68, gridTop + 334, 'bold 9.5px Arial', foreground);
      endElement();
    }
    // --- VARIATION N: KRAFT SCRAPBOOK ---
    else if (theme === 'kraft') {
      photo(40, 30, 560, 410);
      drawWashiTape(80, 30, 80, 24, -0.06);
      drawWashiTape(560, 30, 80, 24, 0.06);

      elementText('month', months[month], 40, 485, 'bold 36px "Courier New", monospace', color, 420);
      elementText('year', year(), 520, 482, 'bold 22px "Courier New", monospace', '#3d2514');
      elementText('name', name, 40, 512, '13px "Courier New", monospace', foreground, 480);

      const gridTop = 530;
      beginElement('calendar', 40, gridTop, 560, 280);
      box(40, gridTop, 560, 24, color);
      const cellW = 560 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 40 + i * cellW + 8, gridTop + 17, 'bold 10.5px "Courier New", monospace', ink));
      drawDaysGrid(40, gridTop + 26, cellW, cellH, month);
      endElement();

      beginElement('notes', 40, gridTop + 292, 560, 65);
      line(40, gridTop + 292, 600, gridTop + 292, '#b09e86');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('Libur: ' + holStr, 40, gridTop + 310, 'bold 9.5px "Courier New", monospace', redHolidayColor, 560);
      }
      text('Catatan latihan & miles harian: _______________________________________', 40, gridTop + 334, '9.5px "Courier New", monospace', foreground);
      endElement();
      drawRuangLariQrBadge(514, gridTop + 275, 86, 96, month);
    }
    // --- VARIATION O: STRAVA HEATMAP ---
    else if (theme === 'strava') {
      box(0, 0, 640, 30, '#fc5200');
      text('⚡ STRAVA // ' + months[month].toUpperCase() + ' ' + year() + ' • ATHLETE FEED', 32, 20, 'bold 12px Arial', '#ffffff');
      photo(32, 45, 576, 395);
      elementText('month', months[month].toUpperCase(), 32, 500, 'bold 36px Arial', '#ffffff', 420);
      elementText('year', year(), 520, 498, 'bold 24px Arial', '#fc5200');
      elementText('name', 'ATHLETE: ' + name.toUpperCase() + '   |   VERIFIED KUDOS', 32, 524, 'bold 11px Arial', '#8b949e', 450);

      const gridTop = 540;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, '#161b22');
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'bold 10px monospace', '#fc5200'));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month, { boxBg: true, verticalLines: true });
      endElement();

      beginElement('notes', 32, gridTop + 292, 470, 65);
      line(32, gridTop + 292, 500, gridTop + 292, '#30363d');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, 32, gridTop + 310, 'bold 9.5px Arial', redHolidayColor, 460);
      }
      text('MONTHLY TARGET: _____ KM   |   ELEVATION GAIN: _____ M', 32, gridTop + 334, 'bold 9.5px monospace', '#fc5200');
      endElement();
      drawRuangLariQrBadge(514, gridTop + 275, 86, 96, month);
    }
    // --- VARIATION P: Y2K RUN CLUB ---
    else if (theme === 'club') {
      box(0, 0, 640, 24, '#ccff00');
      text('RUN CLUB // ' + months[month].toUpperCase() + ' ' + year(), 32, 16, 'bold 11px Arial', '#111');
      photo(32, 38, 576, 400);
      elementText('month', months[month].toUpperCase(), 32, 498, 'italic 900 36px Arial', '#ccff00', 420);
      elementText('year', year(), 520, 495, 'italic bold 24px Arial', '#ffffff');
      elementText('name', 'CREW: ' + name.toUpperCase(), 32, 524, 'bold 11px Arial', '#888', 450);

      const gridTop = 540;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, '#ccff00');
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'bold 10px Arial', '#111'));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month, { boxBg: true, verticalLines: true });
      endElement();

      beginElement('notes', 32, gridTop + 292, 470, 65);
      line(32, gridTop + 292, 500, gridTop + 292, '#333');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, 32, gridTop + 310, 'bold 9.5px Arial', redHolidayColor, 460);
      }
      text('WEEKLY CLUB RUN & SOCIAL MILES: ________________________________', 32, gridTop + 334, '9.5px Arial', '#ccff00');
      endElement();
      drawRuangLariQrBadge(514, gridTop + 275, 86, 96, month);
    }
    // --- VARIATION Q: WORLD MAJORS ---
    else if (theme === 'majors') {
      box(0, 0, 640, 28, '#002b49');
      text('ABBOTT WORLD MARATHON MAJORS // ' + months[month].toUpperCase(), 32, 19, 'bold 12px Arial', '#ffffff');
      photo(32, 42, 576, 395);
      elementText('month', months[month].toUpperCase(), 32, 495, 'bold 34px Arial', '#002b49', 420);
      elementText('year', year(), 520, 492, 'bold 22px Arial', color);
      elementText('name', name.toUpperCase() + ' • MARATHON DIVISION', 32, 520, 'bold 11px Arial', '#555', 450);

      const gridTop = 538;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, '#002b49');
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'bold 10px Arial', '#ffffff'));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month, { verticalLines: true });
      endElement();

      beginElement('notes', 32, gridTop + 292, 470, 65);
      line(32, gridTop + 292, 500, gridTop + 292, '#002b49');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, 32, gridTop + 310, 'bold 9.5px Arial', redHolidayColor, 460);
      }
      text('QUALIFYING TARGET & RACE MILESTONE: ____________________________', 32, gridTop + 334, '9.5px Arial', '#002b49');
      endElement();
      drawRuangLariQrBadge(514, gridTop + 275, 86, 96, month);
    }
    // --- VARIATION R: TRAIL TOPO ---
    else if (theme === 'topography') {
      photo(32, 35, 576, 400);
      elementText('month', months[month].toUpperCase(), 32, 495, 'bold 34px Arial', '#1e392a', 420);
      elementText('year', year(), 520, 492, 'bold 22px Arial', '#1e392a');
      elementText('name', '▲ ' + name + ' • TRAIL & ULTRA', 32, 520, 'italic bold 12px Georgia', '#556b2f', 450);

      const gridTop = 538;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, '#1e392a');
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'bold 10px Arial', '#ffffff'));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month);
      endElement();

      beginElement('notes', 32, gridTop + 292, 470, 65);
      line(32, gridTop + 292, 500, gridTop + 292, '#1e392a');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, 32, gridTop + 310, 'bold 9px Georgia', redHolidayColor, 460);
      }
      text('ELEVASI GAIN (+) TARGET: _____ M   |   SUMMIT RACE: _______________', 32, gridTop + 334, '9.5px Arial', '#1e392a');
      endElement();
      drawRuangLariQrBadge(514, gridTop + 275, 86, 96, month);
    }
    // --- VARIATION S: KOM CROWN ---
    else if (theme === 'crown') {
      box(32, 20, 576, 32, '#ffb800');
      text('👑 KING OF THE MOUNTAIN // ' + months[month].toUpperCase() + ' ' + year(), 44, 41, 'bold 12px Arial', '#111');
      photo(32, 60, 576, 385);
      elementText('month', months[month].toUpperCase(), 32, 495, 'bold 36px Arial', '#111', 400);
      elementText('year', year(), 520, 492, 'bold 24px Arial', '#ffb800');
      elementText('name', name.toUpperCase() + ' • LOCAL LEGEND', 34, 520, 'bold 11px Arial', '#777');

      const gridTop = 540;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, '#ffb800');
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'bold 10px Arial', '#111'));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month, { boxBg: true, verticalLines: true });
      endElement();

      beginElement('notes', 32, gridTop + 292, 470, 65);
      line(32, gridTop + 292, 500, gridTop + 292, '#ffb800');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, 32, gridTop + 310, 'bold 9.5px Arial', redHolidayColor, 460);
      }
      text('SEGMENT KOM TARGETS & SPRINT PR: _________________________________', 32, gridTop + 334, '9.5px Arial', '#b45309');
      endElement();
      drawRuangLariQrBadge(514, gridTop + 275, 86, 96, month);
    }
    // --- VARIATION T: BLUEPRINT ---
    else if (theme === 'blueprint') {
      photo(32, 35, 576, 395);
      elementText('month', months[month].toUpperCase(), 32, 495, 'bold 32px monospace', '#ffffff', 420);
      elementText('year', year(), 520, 492, 'bold 22px monospace', '#93c5fd');
      elementText('name', 'CAD SPEC // ' + name.toUpperCase(), 32, 520, 'bold 11px monospace', '#93c5fd', 450);

      const gridTop = 538;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, '#1e40af');
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'bold 10px monospace', '#ffffff'));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month, { boxBg: true, verticalLines: true });
      endElement();

      beginElement('notes', 32, gridTop + 292, 470, 65);
      line(32, gridTop + 292, 500, gridTop + 292, '#3b82f6');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, 32, gridTop + 310, 'bold 9.5px monospace', redHolidayColor, 460);
      }
      text('CADENCE SPEC: 180 SPM   |   VERTICAL RATIO: 6.5%   |   STRIDE: 1.25M', 32, gridTop + 334, '9.5px monospace', '#93c5fd');
      endElement();
      drawRuangLariQrBadge(514, gridTop + 275, 86, 96, month);
    }
    // --- VARIATION U: ACID CYBER ---
    else if (theme === 'acid') {
      photo(32, 35, 576, 395);
      elementText('month', months[month].toUpperCase(), 32, 498, 'italic 900 36px Arial', '#b8ff00', 420);
      elementText('year', year(), 520, 495, 'italic bold 24px Arial', '#ffffff');
      elementText('name', 'CYBER // ' + name.toUpperCase(), 32, 522, 'italic bold 11px Arial', '#d8b4fe', 450);

      const gridTop = 540;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, '#b8ff00');
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'bold 10px Arial', '#140026'));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month, { boxBg: true, verticalLines: true });
      endElement();

      beginElement('notes', 32, gridTop + 292, 470, 65);
      line(32, gridTop + 292, 500, gridTop + 292, '#b8ff00');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, 32, gridTop + 310, 'bold 9.5px Arial', redHolidayColor, 460);
      }
      text('MATRIX SPEED WORKOUTS & DRILLS: _________________________________', 32, gridTop + 334, '9.5px Arial', '#b8ff00');
      endElement();
      drawRuangLariQrBadge(514, gridTop + 275, 86, 96, month);
    }
    // --- VARIATION V: TAPER & RACE ---
    else if (theme === 'taper') {
      box(0, 0, 640, 26, '#dc2626');
      text('MARATHON TAPER & PEAK // ' + months[month].toUpperCase(), 32, 18, 'bold 12px Arial', '#ffffff');
      photo(32, 38, 576, 395);
      elementText('month', months[month].toUpperCase(), 32, 495, 'bold 36px Arial', '#dc2626', 420);
      elementText('year', year(), 520, 492, 'bold 24px Arial', '#111');
      elementText('name', name.toUpperCase() + ' • RACE READY', 32, 520, 'bold 11px Arial', '#666', 450);

      const gridTop = 538;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, '#dc2626');
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'bold 10px Arial', '#ffffff'));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month, { boxBg: true, verticalLines: true });
      endElement();

      beginElement('notes', 32, gridTop + 292, 470, 65);
      line(32, gridTop + 292, 500, gridTop + 292, '#dc2626');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, 32, gridTop + 310, 'bold 9.5px Arial', redHolidayColor, 460);
      }
      text('PACE STRATEGY: KM 1-10 @4:35 | KM 11-30 @4:30 | KM 31-42 CRUSH', 32, gridTop + 334, 'bold 9.5px Arial', '#dc2626');
      endElement();
      drawRuangLariQrBadge(514, gridTop + 275, 86, 96, month);
    }
    // --- VARIATION W: YEAR IN SPORT INFOGRAPHIC ---
    else if (theme === 'infographic') {
      box(0, 0, 640, 30, '#ea580c');
      text('YEAR IN SPORT ' + year() + ' // ' + months[month].toUpperCase(), 32, 20, 'bold 13px Arial', '#ffffff');
      photo(32, 45, 576, 390);
      elementText('month', months[month].toUpperCase(), 32, 495, 'bold 34px Arial', '#ea580c', 420);
      elementText('year', year(), 520, 492, 'bold 22px Arial', '#111');
      elementText('name', name.toUpperCase() + ' • STATS BREAKDOWN', 32, 520, 'bold 11px Arial', '#777', 450);

      const gridTop = 538;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, '#ea580c');
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'bold 10px Arial', '#ffffff'));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month, { boxBg: true, verticalLines: true });
      endElement();

      beginElement('notes', 32, gridTop + 292, 470, 65);
      line(32, gridTop + 292, 500, gridTop + 292, '#ea580c');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, 32, gridTop + 310, 'bold 9.5px Arial', redHolidayColor, 460);
      }
      text('TOTAL BULAN INI: _____ KM   |   WAKTU: _____ JAM   |   KALORI: _____', 32, gridTop + 334, 'bold 9.5px Arial', '#c2410c');
      endElement();
      drawRuangLariQrBadge(514, gridTop + 275, 86, 96, month);
    }
    // --- VARIATION X: SHOEBOX LABEL ---
    else if (theme === 'shoebox') {
      box(0, 0, 640, 26, '#e11d48');
      text('SNEAKERHEAD BOX LABEL // ' + months[month].toUpperCase(), 32, 18, 'bold 12px Arial', '#ffffff');
      photo(32, 38, 576, 395);
      elementText('month', months[month].toUpperCase(), 32, 495, 'bold 36px Arial', '#e11d48', 420);
      elementText('year', year(), 520, 492, 'bold 24px Arial', '#111');
      elementText('name', name.toUpperCase() + ' • GEAR CLOSET', 32, 520, 'bold 11px Arial', '#777', 450);

      const gridTop = 538;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, '#e11d48');
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'bold 10px Arial', '#ffffff'));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month, { boxBg: true, verticalLines: true });
      endElement();

      beginElement('notes', 32, gridTop + 292, 470, 65);
      line(32, gridTop + 292, 500, gridTop + 292, '#e11d48');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, 32, gridTop + 310, 'bold 9.5px Arial', redHolidayColor, 460);
      }
      text('SEPATU: VAPORFLY / ALPHAFLY / PEGASUS   |   LOG SOL: _____ KM', 32, gridTop + 334, '9.5px monospace', '#be123c');
      endElement();
      drawRuangLariQrBadge(514, gridTop + 275, 86, 96, month);
    }
    // --- VARIATION Y: INDIE ZINE ---
    else if (theme === 'zine') {
      photo(32, 35, 576, 400);
      drawWashiTape(75, 35, 75, 22, -0.08);
      drawWashiTape(550, 35, 75, 22, 0.08);
      elementText('month', months[month].toUpperCase(), 32, 495, 'bold 36px "Courier New", monospace', '#111', 420);
      elementText('year', year(), 520, 492, 'bold 22px "Courier New", monospace', '#111');
      elementText('name', name.toUpperCase() + ' // RUN ZINE', 32, 520, 'bold 12px "Courier New", monospace', '#555', 450);

      const gridTop = 538;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, '#111');
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'bold 10.5px "Courier New", monospace', '#ffffff'));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month);
      endElement();

      beginElement('notes', 32, gridTop + 292, 470, 65);
      line(32, gridTop + 292, 500, gridTop + 292, '#111');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, 32, gridTop + 310, 'bold 9.5px "Courier New", monospace', redHolidayColor, 460);
      }
      text('DIY RUNNER MANIFESTO: RUN HARD, STAY HUMBLE, CHASE MILES.', 32, gridTop + 334, 'bold 9.5px "Courier New", monospace', '#111');
      endElement();
      drawRuangLariQrBadge(514, gridTop + 275, 86, 96, month);
    }
    // --- VARIATION Z: GOLDEN HOUR SUNSET ---
    else if (theme === 'sunset') {
      photo(32, 35, 576, 400);
      elementText('month', months[month], 32, 495, 'italic bold 36px Georgia', '#ff6b35', 420);
      elementText('year', year(), 520, 492, 'bold 22px Georgia', '#ffffff');
      elementText('name', name + ' • Sunset Miles', 32, 520, 'italic 13px Georgia', '#fed7aa', 450);

      const gridTop = 538;
      beginElement('calendar', 32, gridTop, 576, 280);
      box(32, gridTop, 576, 24, '#ff6b35');
      const cellW = 576 / 7, cellH = 43;
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, 32 + i * cellW + 8, gridTop + 17, 'bold 10px Georgia', '#ffffff'));
      drawDaysGrid(32, gridTop + 26, cellW, cellH, month, { boxBg: true, verticalLines: true });
      endElement();

      beginElement('notes', 32, gridTop + 292, 470, 65);
      line(32, gridTop + 292, 500, gridTop + 292, '#ff6b35');
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ': ' + h.n).join(' · ');
        text('LIBUR: ' + holStr, 32, gridTop + 310, 'bold 9.5px Georgia', redHolidayColor, 460);
      }
      text('CHASING THE SUNSET: TOTAL KM: _____ | RECOVERY: SERENE', 32, gridTop + 334, '9.5px Georgia', '#fed7aa');
      endElement();
      drawRuangLariQrBadge(514, gridTop + 275, 86, 96, month);
    }
    // --- VARIATION F: STANDARD TOP PHOTO (Classic, Bold, Minimal, Botanical, Night, Retro, Nordic, Modernist, Neon, Journal, Sidebar) ---
    else {
      photo(0, 0, 640, 460);
      box(0, 460, 640, 6, color);
      elementText('month', theme === 'bold' ? months[month].toUpperCase() : months[month], 32, 515, theme === 'bold' ? 'bold 36px Arial' : '36px Georgia', foreground, 450);
      elementText('year', year(), 520, 512, 'bold 25px Arial', color);
      elementText('name', name, 33, 538, '12px Arial', foreground, 500);

      // Running Mode Header on Wall Calendar
      if (isRun) {
        beginElement('targets', 32, 545, 576, 20);
        text('TARGET VOLUME: _____ KM   |   TARGET LONG RUN: _____ KM', 32, 558, 'bold 10px Arial', color);
        endElement();
      }

      const gridTop = isRun ? 568 : 552;
      beginElement('calendar', 32, gridTop, 576, 280);
      const start = new Date(year(), month, 1).getDay();
      const days = new Date(year(), month + 1, 0).getDate();
      const left = 32, width = 576 / 7, height = 44;
      box(left, gridTop, 576, 24, color);
      ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'].forEach((d, i) => text(d, left + i * width + 8, gridTop + 17, 'bold 11px Arial', ink));
      drawDaysGrid(left, gridTop + 26, width, height, month, { verticalLines: theme === 'bold' || theme === 'journal' });
      endElement();

      beginElement('notes', 32, gridTop + 295, 576, 65);
      line(32, gridTop + 295, 608, gridTop + 295, color);
      if (mHols.length) {
        const holStr = mHols.map(h => h.d + ' ' + months[month].substring(0, 3) + ': ' + h.n).join('   |   ');
        text('HARI LIBUR NASIONAL: ' + holStr, 32, gridTop + 314, 'bold 10px Arial', redHolidayColor, 576);
      }
      if (isRun) {
        line(32, gridTop + 326, 608, gridTop + 326, dark ? '#2e415e' : '#ddd');
        text('AGENDA / RACE: ______________________   |   TOTAL KM: ______   |   SESI: ______', 32, gridTop + 342, 'bold 9.5px Arial', foreground);
      } else {
        line(32, gridTop + 326, 608, gridTop + 326, dark ? '#2e415e' : '#ddd');
        text('CATATAN & AGENDA BULANAN: __________________________________________________', 32, gridTop + 342, 'bold 9.5px Arial', foreground);
      }
      endElement();
    }
  }

  // -------------------------------------------------------------------------
  // LANDSCAPE CALENDAR (A4 Lanskap 794 × 530)
  // -------------------------------------------------------------------------
  else {
    box(0, 0, 794, 530, paper);

    // Split 50/50 Landscape
    if (theme === 'split') {
      photo(0, 0, 380, 530);
      box(380, 0, 5, 530, color);
      const x0 = 400, y0 = 30;
      elementText('month', months[month].toUpperCase(), x0, y0 + 30, 'bold 30px Arial', color);
      elementText('year', year(), 720, y0 + 30, 'bold 20px Arial', color);
      elementText('name', name, x0, y0 + 55, '12px Arial', foreground);

      if (isRun) {
        beginElement('targets', x0, y0 + 65, 370, 20);
        text('TARGET VOLUME: ___ KM | LONG RUN: ___ KM', x0, y0 + 78, 'bold 10px Arial', color);
        endElement();
      }

      const gridTop = isRun ? y0 + 88 : y0 + 65;
      beginElement('calendar', x0, gridTop, 370, 360);
      box(x0, gridTop, 370, 22, color);
      const w = 370 / 7, h = 53;
      'MIN SEN SEL RAB KAM JUM SAB'.split(' ').forEach((day, i) => text(day, x0 + i * w + 6, gridTop + 16, 'bold 9.5px Arial', ink));
      drawDaysGrid(x0, gridTop + 24, w, h, month, { boxBg: true, verticalLines: true });
      endElement();
      if (byId('ruangLariQr')?.checked) {
        drawRuangLariQrBadge(695, y0 + 10, 82, 94, month);
      }
    }
    // Bottom / Inverted Landscape (Photo right, calendar left)
    else if (theme === 'bottom') {
      photo(414, 0, 380, 530);
      box(410, 0, 4, 530, color);
      const x0 = 20, y0 = 30;
      elementText('month', months[month].toUpperCase(), x0, y0 + 30, 'bold 30px Arial', color);
      elementText('year', year(), 350, y0 + 30, 'bold 20px Arial', color);
      elementText('name', name, x0, y0 + 55, '12px Arial', foreground);

      const gridTop = y0 + 68;
      beginElement('calendar', x0, gridTop, 375, 370);
      box(x0, gridTop, 375, 22, color);
      const w = 375 / 7, h = 53;
      'MIN SEN SEL RAB KAM JUM SAB'.split(' ').forEach((day, i) => text(day, x0 + i * w + 6, gridTop + 16, 'bold 9.5px Arial', ink));
      drawDaysGrid(x0, gridTop + 24, w, h, month, { boxBg: true, verticalLines: true });
      endElement();
      if (byId('ruangLariQr')?.checked) {
        drawRuangLariQrBadge(305, y0 + 10, 82, 94, month);
      }
    }
    // Standard Landscape (Left Photo, Right Calendar with Running Log & Weekly Tracker)
    else {
      if (theme === 'bold') { box(0, 0, 794, 24, color); }
      if (theme === 'botanical') {
        ctx.fillStyle = color;
        for (let i = 0; i < 7; i++) { ctx.beginPath(); ctx.ellipse(25 + i * 24, 16, 15, 4, i % 2 ? .3 : -.3, 0, Math.PI * 2); ctx.fill(); }
      }
      box(0, 20, 794, theme === 'minimal' ? 1 : 4, color); box(21, 30, 57, 38, color);
      text(String(month + 1).padStart(2, '0'), 29, 58, 'bold 28px Arial', ink);
      elementText('name', name.toUpperCase(), 87, 54, 'bold 11px Arial', foreground, 105);
      photo(21, 68, 170, theme === 'minimal' ? 220 : 288);

      if (byId('ruangLariQr')?.checked) {
        beginElement('agenda', 21, 370, 78, 70);
        text(isRun ? 'AGENDA' : 'CATATAN', 21, 383, 'bold 9px Arial', foreground);
        line(21, 411, 98, 411); line(21, 438, 98, 438);
        endElement();
        drawRuangLariQrBadge(104, 365, 86, 98, month);
      } else {
        beginElement('agenda', 21, 370, 170, 70);
        text(isRun ? 'RACE / AGENDA' : 'CATATAN & AGENDA', 21, 383, 'bold 10px Arial', foreground);
        line(21, 411, 191, 411); line(21, 438, 191, 438);
        endElement();
      }

      elementText('heading', isRun ? 'RUNNING CALENDAR' : 'CALENDAR STUDIO', 21, 484, 'bold 12px Arial');
      elementText('month', theme === 'bold' ? months[month].toUpperCase() : months[month], 206, 64, theme === 'bold' ? 'bold 32px Arial' : 'italic 33px Georgia');
      elementText('year', year(), 726, 61, 'bold 16px Arial', color);

      if (isRun) {
        beginElement('targets', 206, 78, 567, 18);
        text('TARGET VOLUME  _____ KM', 206, 90, 'bold 10px Arial');
        text('TARGET LONG RUN  _____ KM', 466, 90, 'bold 10px Arial');
        endElement();
      }

      beginElement('calendar', 206, 103, 567, 342);
      const start = new Date(year(), month, 1).getDay();
      const days = new Date(year(), month + 1, 0).getDate();
      const x0 = 206, y0 = 127, w = isRun ? 69 : 81, h = 53;
      box(x0, 103, 567, 24, color);
      'MIN SEN SEL RAB KAM JUM SAB'.split(' ').forEach((day, i) => text(day, x0 + i * w + 8, 119, 'bold 10px Arial', ink));
      if (isRun) text('REKAP KM', 695, 119, 'bold 10px Arial', ink);

      for (let row = 0; row < 6; row++) {
        const y = y0 + row * h;
        for (let col = 0; col < 7; col++) {
          const x = x0 + col * w, d = row * 7 + col - start + 1, active = d > 0 && d <= days;
          box(x, y, w, h, active ? (col === 0 || col === 6 ? '#dedede' : '#fff') : '#f1f1f1');
          if (active) {
            const hol = showHol && isHoliday(year(), month, d);
            const numColor = (col === 0 || hol) ? redHolidayColor : '#111';
            text(d, x + 6, y + 18, 'bold 14px Arial', numColor);
            if (isRun) {
              ctx.strokeStyle = '#555'; ctx.strokeRect(x + 54, y + 9, 7, 7);
              text('km', x + 6, y + 47, '9px Arial'); line(x + 22, y + 46, x + 62, y + 46);
            }
          }
          line(x, y, x, y + h);
        }
        if (isRun) {
          box(689, y, 84, h, '#ddd');
          text('W' + (row + 1) + ' / Fase ____', 695, y + 13, 'bold 9px Arial');
          text('Target  ____ km', 695, y + 28, '9px Arial');
          text('Aktual  ____ km', 695, y + 41, '9px Arial');
          line(689, y, 689, y + h);
        }
        line(x0, y, 773, y);
      }
      line(x0, 445, 773, 445);
      endElement();

      beginElement('notes', 206, 452, 567, 62);
      if (mHols.length) {
        box(x0, 452, 567, 24, dark ? '#3d1c1c' : '#fdf1f1');
        const holStr = mHols.map(h => h.d + ' ' + months[month].substring(0, 3) + ': ' + h.n).join('   |   ');
        text('LIBUR NASIONAL: ' + holStr, 214, 468, 'bold 9.5px Arial', redHolidayColor, 550);
        box(x0, 480, 567, 34, surface);
        if (isRun) {
          text('Total ____ km   |   Elevasi (+) ____ m   |   Sesi ____ kali', 214, 495, '9.5px Arial', foreground);
          text('Kondisi tubuh / recovery', 214, 508, '8.5px Arial', foreground);
        } else {
          text('Catatan bulanan & evaluasi target:', 214, 498, '9.5px Arial', foreground);
        }
      } else {
        box(x0, 455, 567, 55, surface);
        if (isRun) {
          text('REKAP BULANAN', 216, 473, 'bold 10px Arial', foreground);
          text('Total ____ km   |   Elevasi (+) ____ m   |   Sesi ____ kali', 330, 473, '10px Arial', foreground);
          text('Kondisi tubuh / recovery', 216, 495, '10px Arial', foreground);
          line(327, 497, 595, 497);
        } else {
          text('CATATAN BULANAN', 216, 475, 'bold 10px Arial', foreground);
          line(216, 500, 750, 500);
        }
      }
      endElement();
    }
  }

  drawCustomTexts();
}

function render() {
  dimensions(canvas);
  draw(canvas, selected());
  if (!dragState) {
    if (typeof syncChoices === 'function') syncChoices();
    if (typeof syncCollageControls === 'function') syncCollageControls();
    if (typeof syncElementPanel === 'function') syncElementPanel();
    if (typeof syncAssetControls === 'function') syncAssetControls();
    if (typeof syncHistoryButtons === 'function') syncHistoryButtons();
    scheduleTemplatePreviews();
  }
  const isPoster = typeof isPoster12M === 'function' && isPoster12M();
  const is4M = typeof isFourMonthMode === 'function' ? isFourMonthMode() : false;
  const is2M = isTwoMonthMode();
  const maxP = getMaxPage();
  const curTitle = isPoster ? 'Poster 12 Bulan (A3+)' : (selected() === -1 ? 'Sampul' : (is4M ? fourMonthLabels[selected()] : (is2M ? twoMonthLabels[selected()] : months[selected()])));
  const fullSlideLabel = isPoster ? 'Poster 12 Bulan (A3+) · 1 Lembar' : (curTitle + ' · ' + (selected() + 2) + ' / ' + (maxP + 2));
  byId('slideTitle').textContent = fullSlideLabel;
  if (byId('headerSlideTitle')) byId('headerSlideTitle').textContent = fullSlideLabel;
  byId('prev').disabled = isPoster || selected() === -1;
  byId('next').disabled = isPoster || selected() === maxP;
  if (byId('headerPrev')) byId('headerPrev').disabled = isPoster || selected() === -1;
  if (byId('headerNext')) byId('headerNext').disabled = isPoster || selected() === maxP;
  byId('sizeInfo').textContent = isPoster ? 'Dinding · Poster A3+ (320 × 480 mm) · 12 Bulan 1 Lembar' : ((byId('layout').value === 'wall' ? 'Dinding · A3+ (320 × 480 mm)' : 'Kalender Lari · A4 (297 × 210 mm)') + (is4M ? ' · 4 bulan per lembar' : (is2M ? ' · 2 bulan per lembar' : ' · 1 bulan per lembar')));
  if (byId('slides')) {
    for (const button of byId('slides').children) button.setAttribute('aria-current', Number(button.dataset.page) === selected());
  }
}

let templatePreviewTimer = null;
let templateChunkTimer = null;
let lastTemplateThumbSignature = '';

function getTemplateThumbSignature() {
  const wall = byId('layout') ? byId('layout').value : 'wall';
  const mpp = byId('monthsPerPage') ? byId('monthsPerPage').value : '1';
  const font = byId('font') ? byId('font').value : '';
  const yr = byId('year') ? byId('year').value : '';
  const run = byId('runningMode') ? byId('runningMode').checked : true;
  const hol = byId('showHolidays') ? byId('showHolidays').checked : true;
  const bg0Ready = defaults && defaults[0] && defaults[0].complete ? '1' : '0';
  const bg1Ready = defaults && defaults[1] && defaults[1].complete ? '1' : '0';
  const customPhoto0 = (typeof photos !== 'undefined' && photos.has(0)) ? 'c1' : 'c0';
  return [wall, mpp, font, yr, run, hol, bg0Ready, bg1Ready, customPhoto0].join('|');
}

function drawSingleTemplateMini(c, miniW, miniH) {
  const values = ['template', 'paper', 'accent'].map(id => byId(id).value);
  try {
    if (c.width !== miniW || c.height !== miniH) {
      c.width = miniW;
      c.height = miniH;
    }
    const t = c.dataset.template;
    byId('template').value = t;
    const pal = palettes[t] || ['#f45123', '#ffffff'];
    byId('accent').value = pal[0];
    byId('paper').value = pal[1];
    draw(c, 0);
  } finally {
    ['template', 'paper', 'accent'].forEach((id, i) => byId(id).value = values[i]);
  }
}

function scheduleTemplatePreviews(force = false) {
  if (templatePreviewTimer) return;
  templatePreviewTimer = setTimeout(() => {
    templatePreviewTimer = null;
    const sig = getTemplateThumbSignature();
    if (!force && sig === lastTemplateThumbSignature) return;
    lastTemplateThumbSignature = sig;

    if (templateChunkTimer) {
      clearTimeout(templateChunkTimer);
      templateChunkTimer = null;
    }

    const wall = byId('layout') ? byId('layout').value === 'wall' : true;
    const miniW = wall ? 96 : 120;
    const miniH = wall ? 144 : 80;
    const allMinis = Array.from(document.querySelectorAll('.template-mini'));
    if (!allMinis.length) return;

    // 1. Immediately draw the first 5 visible thumbnails so the strip looks ready right away
    const immediateCount = Math.min(5, allMinis.length);
    for (let i = 0; i < immediateCount; i++) {
      drawSingleTemplateMini(allMinis[i], miniW, miniH);
    }

    // 2. Progressively draw the remaining off-screen thumbnails in small non-blocking batches
    let idx = immediateCount;
    function drawNextBatch() {
      templateChunkTimer = null;
      const end = Math.min(idx + 4, allMinis.length);
      for (; idx < end; idx++) {
        drawSingleTemplateMini(allMinis[idx], miniW, miniH);
      }
      if (idx < allMinis.length) {
        templateChunkTimer = setTimeout(drawNextBatch, 24);
      }
    }
    if (idx < allMinis.length) {
      templateChunkTimer = setTimeout(drawNextBatch, 32);
    }
  }, 60);
}
