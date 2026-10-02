const PX_PER_MM = 3.7795275591;

export class KpCanvasRenderer {
    constructor({ store, rootEl, canvasEl, viewportEl, registry }) {
        this.store = store;
        this.rootEl = rootEl;
        this.canvasEl = canvasEl;
        this.viewportEl = viewportEl;
        this.registry = registry;

        this.canvasWrapEl = canvasEl;
        this.stageEl = canvasEl.parentElement;

        this.store.addEventListener('changed', () => this.renderAll());
        this.store.addEventListener('selection-changed', () => {
            this.canvasWrapEl.querySelectorAll('.kp-element').forEach(node => {
                node.classList.toggle('selected', node.dataset.elementId === this.store.selectedElementId);
            });
        });
        this.store.addEventListener('active-page-changed', () => this.renderAll());
        this.store.addEventListener('history', () => this.renderAll());
        this.store.addEventListener('hydrated', () => this.renderAll());
        this.store.addEventListener('zoom', ({ detail: { zoom } }) => this.applyZoom(zoom));
        this.store.addEventListener('hydrated', () => {
            const meta = this.store.canvasMeta;
            if (meta) this.applyCanvasSize(meta.width_px, meta.height_px);
        });

        window.addEventListener('beforeunload', () => {
            try { this.store.storage.revokeAll(); } catch {}
        });

        const meta = this.store.canvasMeta;
        if (meta) this.applyCanvasSize(meta.width_px, meta.height_px);
        this.resizeObserver = new ResizeObserver(() => this.fitToViewport());
        this.resizeObserver.observe(this.viewportEl.parentElement);
    }

    applyCanvasSize(wPx, hPx) {
        this.canvasWrapEl.style.width = `${wPx}px`;
        this.canvasWrapEl.style.height = `${hPx}px`;
        this.canvasWrapEl.dataset.wPx = String(wPx);
        this.canvasWrapEl.dataset.hPx = String(hPx);
        this.applyZoom(this.store.zoom);
    }

    applyZoom(zoom) {
        this.canvasWrapEl.style.transform = `scale(${zoom})`;
        this.canvasWrapEl.style.transformOrigin = 'top left';
        this.stageEl.style.width = `${Math.round(Number(this.canvasWrapEl.dataset.wPx) * zoom)}px`;
        this.stageEl.style.height = `${Math.round(Number(this.canvasWrapEl.dataset.hPx) * zoom)}px`;
    }

    fitToViewport() {
        const meta = this.store.canvasMeta;
        if (!meta?.width_px || !meta?.height_px) return;
        const width = this.viewportEl.clientWidth - 64;
        const height = this.viewportEl.clientHeight - 64;
        if (width <= 0 || height <= 0) return;
        const zoom = Math.min(1, width / meta.width_px, height / meta.height_px);
        this.store.setZoom(Math.max(this.store.minZoom, Math.floor(zoom * 100) / 100));
    }

    renderAll() {
        this.renderActivePage();
    }

    mmToPx(mm) { return mm * PX_PER_MM; }

    renderActivePage() {
        const page = this.store.getActivePage();
        if (!page) return;
        const label = this.rootEl.querySelector('#kp-active-page-label');
        if (label) label.textContent = page.page_type === 'MONTH'
            ? `${page.month_label || 'Bulan'} ${this.store.project.year}`
            : page.month_label || 'Halaman';
        const meta = this.store.canvasMeta;
        this.applyCanvasSize(meta.width_px, meta.height_px);
        this.canvasWrapEl.innerHTML = '';

        const pageDiv = document.createElement('div');
        pageDiv.className = 'kp-page';
        pageDiv.dataset.pageMonth = String(page.month_number);

        if (page.layout_config?.background_color) {
            pageDiv.style.background = page.layout_config.background_color;
        } else {
            pageDiv.style.background = '#f8fafc';
        }

        const sorted = [...(page.elements || [])].sort((a, b) => (a.z_index || 0) - (b.z_index || 0));
        const selectedId = this.store.selectedElementId;

        for (const e of sorted) {
            const node = this._renderElement(page, e);
            if (!node) continue;
            if (e.id === selectedId) node.classList.add('selected');
            pageDiv.appendChild(node);
        }

        const layer = document.createElement('div');
        layer.className = 'kp-drag-ghost-layer';
        layer.id = 'kp-drag-ghost-layer';
        pageDiv.appendChild(layer);

        const self = this;
        pageDiv.addEventListener('pointerdown', (ev) => {
            if (ev.target === pageDiv || ev.target.classList.contains('kp-drag-ghost-layer')) {
                self.store.clearSelection();
                self.renderActivePage();
            }
        });

        this.canvasWrapEl.appendChild(pageDiv);
        this.applyZoom(this.store.zoom);
    }

    _renderElement(page, e) {
        if (!e.visible) return null;
        const reg = this.registry;
        const type = e.element_type;
        const el = document.createElement('div');
        el.className = `kp-element kp-${type.toLowerCase().replace(/_/g, '-')}-element`;
        el.dataset.elementId = e.id;
        el.dataset.type = type;
        el.tabIndex = 0;
        el.setAttribute('role', 'button');
        el.setAttribute('aria-label', `Pilih ${reg.label(type)}`);

        const x = this.mmToPx(e.x_mm || 0);
        const y = this.mmToPx(e.y_mm || 0);
        const w = this.mmToPx(e.width_mm || 10);
        const h = this.mmToPx(e.height_mm || 10);
        el.style.left = `${x}px`;
        el.style.top = `${y}px`;
        el.style.width = `${w}px`;
        el.style.height = `${h}px`;
        el.style.zIndex = String(e.z_index || 0);
        el.style.transform = `rotate(${e.rotation_deg || 0}deg)`;

        if (e.locked) el.classList.add('locked');

        const renderers = {
            PHOTO: this._renderPhoto.bind(this),
            TEXT: this._renderText.bind(this),
            CALENDAR_GRID: this._renderCalendarGrid.bind(this),
            MONTHLY_STATS: this._renderStats.bind(this),
            MONTHLY_GOAL: this._renderGoal.bind(this),
            NOTES: this._renderNote.bind(this),
        };

        const fn = renderers[type] || this._renderFallback.bind(this);
        fn(el, e, page);

        // Resize handles
        if (!e.locked) {
            ['nw','n','ne','w','e','sw','s','se'].forEach(dir => {
                const h = document.createElement('div');
                h.className = `kp-element-resize-handle kp-resize-${dir}`;
                h.dataset.handleDir = dir;
                el.appendChild(h);
            });
        }

        const self = this;
        el.addEventListener('pointerdown', (ev) => {
            if (e.locked && ev.target.classList.contains('kp-element-resize-handle')) return;
            self.store.selectElement(e.id);
        });
        el.addEventListener('click', () => self.store.selectElement(e.id));
        el.addEventListener('keydown', (ev) => {
            if (ev.key !== 'Enter' && ev.key !== ' ') return;
            ev.preventDefault();
            self.store.selectElement(e.id);
        });
        el.addEventListener('dblclick', (ev) => {
            if (!reg.isEditable(type) && type !== 'PHOTO') return;
            ev.stopPropagation();
            self._handleDoubleClick(el, e, page);
        });

        return el;
    }

    _renderPhoto(el, e) {
        const ref = e.local_asset_ref;
        const resolved = ref ? this.store.storage.resolveRef(ref) : null;
        if (!resolved?.url) {
            el.classList.add('kp-photo-empty');
            el.textContent = 'Klik dua kali untuk memilih foto';
            return;
        }
        const img = document.createElement('img');
        img.alt = e.content_json?.caption || 'foto';
        img.style.objectFit = e.style_config?.object_fit || 'cover';
        img.style.objectPosition = this._fpToPosition(e.style_config?.focal_point);
        img.style.borderRadius = `${this.mmToPx(e.style_config?.border_radius_mm || 0)}px`;
        img.style.opacity = `${Math.max(10, Math.min(100, (e.style_config?.brightness ?? 100))) / 100}`;
        img.src = resolved.url;
        el.appendChild(img);
    }

    _fpToPosition(fp) {
        const { x = 0.5, y = 0.5 } = fp || {};
        return `${Math.round(x * 100)}% ${Math.round(y * 100)}%`;
    }

    _renderText(el, e) {
        const txt = document.createElement('div');
        txt.className = 'kp-text-content';
        const s = e.style_config || {};
        txt.style.fontFamily = s.font_family || 'Inter, sans-serif';
        txt.style.fontSize = `${(s.font_size_pt || 14) * 1.333}px`;
        txt.style.fontWeight = s.font_weight || 600;
        txt.style.color = s.color || '#172333';
        txt.style.textAlign = s.align || 'left';
        txt.style.lineHeight = String(s.line_height || 1.3);
        txt.style.whiteSpace = 'pre-wrap';
        txt.style.wordBreak = 'break-word';
        txt.style.height = '100%';
        txt.textContent = e.content_json?.text || '';
        el.appendChild(txt);
    }

    _renderCalendarGrid(el, e, page) {
        const startOn = this.store.project.start_week_on || 'MONDAY';
        const monthNumber = page.month_number;
        const year = this.store.project.year;
        if (monthNumber < 1 || monthNumber > 12) {
            el.style.display = 'flex';
            el.style.alignItems = 'center';
            el.style.justifyContent = 'center';
            el.style.color = '#475569';
            el.innerHTML = `<span style="font-family:Inter,sans-serif;font-size:12px;font-weight:700;">Pilih halaman bulan untuk melihat grid.</span>`;
            return;
        }
        const table = document.createElement('table');
        table.className = 'kp-calendar-grid';
        const weekdaysId = startOn === 'SUNDAY'
            ? ['Min','Sen','Sel','Rab','Kam','Jum','Sab']
            : ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
        const first = new Date(Date.UTC(year, monthNumber - 1, 1));
        const last = new Date(Date.UTC(year, monthNumber, 0));
        const daysInMonth = last.getUTCDate();
        const firstDow = first.getUTCDay();
        const offset = startOn === 'SUNDAY' ? firstDow : (firstDow + 6) % 7;
        const rows = [];
        let cells = Array(offset).fill(null);
        for (let d = 1; d <= daysInMonth; d++) {
            const date = new Date(Date.UTC(year, monthNumber - 1, d));
            const dow = date.getUTCDay();
            const isWeekend = (startOn === 'SUNDAY') ? dow === 0 || dow === 6 : dow === 0 || dow === 6;
            cells.push({ day: d, weekend: isWeekend });
            if (cells.length === 7) { rows.push(cells); cells = []; }
        }
        if (cells.length) {
            while (cells.length < 7) cells.push(null);
            rows.push(cells);
        }
        const thead = document.createElement('thead');
        const trh = document.createElement('tr');
        weekdaysId.forEach(w => {
            const th = document.createElement('th');
            th.textContent = w;
            trh.appendChild(th);
        });
        thead.appendChild(trh);
        table.appendChild(thead);
        const tbody = document.createElement('tbody');
        const s = e.style_config || {};
        for (const row of rows) {
            const tr = document.createElement('tr');
            for (const c of row) {
                const td = document.createElement('td');
                if (!c) { td.classList.add('empty'); }
                else {
                    td.textContent = String(c.day);
                    if (c.weekend && s.weekend_bg) td.style.background = s.weekend_bg;
                }
                tr.appendChild(td);
            }
            tbody.appendChild(tr);
        }
        table.appendChild(tbody);
        table.style.color = s.text_color || '#172333';
        table.style.setProperty('--kp-calendar-border', s.border_color || '#dce4df');
        table.querySelectorAll('th').forEach(th => { th.style.color = s.header_color || '#305a49'; });
        el.appendChild(table);
    }

    _renderStats(el, e) {
        const s = e.style_config || {};
        el.style.background = s.background || '#0f172a';
        el.style.color = s.text_color || '#ffffff';
        el.style.padding = '12px';
        el.style.borderRadius = '4px';
        el.style.fontFamily = s.font_family || 'Inter, sans-serif';
        const c = e.content_json || {};
        const rows = [
            ['Total Jarak', `${Number(c.total_km || 0).toFixed(1)} km`],
            ['Jumlah Lari', `${c.total_runs || 0} kali`],
            ['Durasi', `${c.total_hours || 0} jam`],
            ['Terjauh', `${Number(c.longest_run_km || 0).toFixed(1)} km`],
        ];
        const hasData = rows.some(([, value]) => !value.startsWith('0.0 ') && !value.startsWith('0 '));
        if (!hasData) {
            el.innerHTML = '<div style="font-size:12px;font-weight:700;">Ringkasan lari</div><p style="font-size:11px;margin-top:10px;line-height:1.4;">Belum ada data lari. Pilih elemen ini untuk mengisi statistik.</p>';
            return;
        }
        el.innerHTML = `<div style="font-size:11px;font-weight:700;margin-bottom:8px;">Ringkasan lari</div>`
            + rows.map(([k, v]) => `<div class="kp-stat-row"><span>${k}</span><strong>${v}</strong></div>`).join('');
    }

    _renderGoal(el, e) {
        const s = e.style_config || {};
        const c = e.content_json || {};
        const target = Number(c.target_value || 0);
        const current = Number(c.current_value || 0);
        const pct = Math.max(0, Math.min(1, target ? current / target : 0));
        const accent = s.accent || '#ccff00';
        el.style.background = '#ffffff';
        el.style.border = '1px solid #dce4df';
        el.style.borderRadius = '4px';
        el.style.padding = '12px';
        el.style.fontFamily = 'Inter, sans-serif';
        el.innerHTML = `
            <div style="font-size:11px;font-weight:700;color:#334155;">Target bulan ini</div>
            <div style="font-size:${target ? 25 : 14}px;font-weight:800;color:#172333;margin-top:6px;">${target ? `${(pct * 100).toFixed(0)}%` : 'Tentukan target'}</div>
            <div style="height:6px;width:100%;background:#e2e8f0;border-radius:2px;overflow:hidden;margin-top:7px;">
                <div style="height:100%;width:${Math.round(pct * 100)}%;background:${accent};"></div>
            </div>
            <div style="margin-top:7px;font-size:11px;color:#334155;">${target ? `${current} dari ${target} ${c.unit || 'km'}` : 'Klik elemen ini untuk mengisi target.'}</div>
        `;
    }

    _renderNote(el, e) {
        const s = e.style_config || {};
        el.style.background = s.background || '#f1f5f3';
        el.style.color = s.color || '#0f172a';
        el.style.fontFamily = s.font_family || 'Inter, sans-serif';
        el.style.fontSize = `${(s.font_size_pt || 10) * 1.333}px`;
        el.style.padding = '8px';
        el.style.borderRadius = '6px';
        el.style.whiteSpace = 'pre-wrap';
        el.textContent = e.content_json?.text || '';
    }

    _renderFallback(el, e) {
        el.style.display = 'flex';
        el.style.alignItems = 'center';
        el.style.justifyContent = 'center';
        el.style.background = '#cbd5e1';
        el.style.color = '#334155';
        el.style.borderRadius = '6px';
        el.style.fontFamily = 'Inter, sans-serif';
        el.style.fontWeight = 800;
        el.style.fontSize = '10px';
        el.textContent = e.element_type;
    }

    _handleDoubleClick(containerEl, elementModel, page) {
        if (elementModel.element_type === 'PHOTO') {
            this._browsePhoto(elementModel.id);
            return;
        }
        if (!this.registry.isEditable(elementModel.element_type)) return;
        const editable = containerEl.querySelector('.kp-text-content') || containerEl;
        if (!editable) return;
        const self = this;
        editable.contentEditable = 'true';
        editable.style.outline = `2px dashed ${'#ccff00'}`;
        editable.focus();
        const finish = () => {
            editable.contentEditable = 'false';
            editable.style.outline = 'none';
            const newText = editable.textContent || '';
            if (newText !== (elementModel.content_json?.text || '')) {
                const cj = elementModel.content_json ? { ...elementModel.content_json, text: newText } : { text: newText };
                self.store.updateElement(elementModel.id, { content_json: cj }, { msg: 'edit-text' });
            }
            editable.removeEventListener('blur', finish);
            editable.removeEventListener('keydown', onKey);
        };
        const onKey = (e) => { if (e.key === 'Escape') finish(); };
        editable.addEventListener('blur', finish, { once: true });
        editable.addEventListener('keydown', onKey);
        const range = document.createRange();
        range.selectNodeContents(editable);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    }

    _browsePhoto(elementId) {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        const self = this;
        input.addEventListener('change', (e) => {
            const f = e.target.files?.[0];
            if (!f) return;
            const ok = self.store.setLocalPhotoForElement(elementId, f);
            if (ok) self.renderActivePage();
        });
        input.click();
    }
}
