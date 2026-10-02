export class KpTrialStore extends EventTarget {
    constructor({ project, pages, yearMonths, canvasMeta, registry, storage, autosave }) {
        super();
        this.project = { ...project };
        this.pages = pages.map(p => ({ ...p, elements: (p.elements || []).map(e => ({ ...e })) }));
        this.yearMonths = yearMonths;
        this.canvasMeta = canvasMeta;
        this.registry = registry;
        this.storage = storage;
        this.autosave = autosave;

        this.activePageMonthNumber = this.pages.find(p => p.month_number === 1)?.month_number ?? (this.pages[0]?.month_number ?? 0);
        this.selectedElementId = null;

        this.zoom = 1;
        this.minZoom = 0.1;
        this.maxZoom = 3;

        this.history = [];
        this.future = [];
        this._historyMax = 100;
        this._suspendHistory = false;

        this._emit = (type, detail) => this.dispatchEvent(new CustomEvent(type, { detail }));
    }

    // ----- Hydrate from storage
    hydrateFromStorage() {
        const snapshot = this.storage.load(this.project.anonymous_uuid || this.project.id);
        if (!snapshot) return false;
        if (snapshot.pages) this.pages = snapshot.pages;
        if (snapshot.project?.name) this.project.name = snapshot.project.name;
        if (snapshot.activePageMonthNumber != null) this.activePageMonthNumber = snapshot.activePageMonthNumber;
        this._emit('hydrated');
        return true;
    }

    initializeTemplatePages() {
        if (this.pages.some(p => (p.elements || []).length)) return false;
        this._commit('initial-layout', () => {
            for (const page of this.pages) {
                const w = Number(page.canvas_width_mm || this.canvasMeta.width_mm);
                const h = Number(page.canvas_height_mm || this.canvasMeta.height_mm);
                const margin = Math.max(10, Math.round(Math.min(w, h) * 0.075));
                const config = page.layout_config || {};
                const family = config.template_family || 'MINIMAL_RUNNER';
                const kind = config.calendar_type || 'MY_RUNNING_YEAR';
                if (kind === 'BLANK_FROM_SCRATCH') continue;
                const dark = family === 'RACE_SEASON';
                page.layout_config = { ...config, background_color: dark ? '#172333' : '#ffffff' };
                const ink = dark ? '#ffffff' : '#172333';
                const muted = dark ? '#cbd5e1' : '#475569';
                const accent = family === 'RACE_SEASON' ? '#bce64a' : '#305a49';
                let layer = 1;
                const put = (type, x, y, width, height, content, style = {}) => {
                    const el = this.registry.defaultElement(type, {
                        x_mm: x, y_mm: y, width_mm: width, height_mm: height,
                    });
                    el.z_index = layer++;
                    el.content_json = { ...el.content_json, ...content };
                    el.style_config = { ...el.style_config, ...style };
                    page.elements.push(el);
                };
                const textStyle = (size, weight = 600, color = ink) => ({
                    font_family: 'Inter, sans-serif', font_size_pt: size,
                    font_weight: weight, color, line_height: 1.15,
                });
                if (page.page_type === 'COVER') {
                    put('TEXT', margin, h * 0.22, w - 2 * margin, h * 0.15,
                        { text: this.project.name }, textStyle(34, 800));
                    put('TEXT', margin, h * 0.43, w - 2 * margin, 18,
                        { text: `Kalender lari ${this.project.year}` }, textStyle(16, 500, muted));
                    put('TEXT', margin, h - margin - 14, w - 2 * margin, 14,
                        { text: 'RUANGLARI  /  KALENDER PELARI' }, textStyle(10, 700, accent));
                } else if (page.page_type === 'YEAR_REVIEW') {
                    put('TEXT', margin, margin, w - 2 * margin, 22,
                        { text: `Tahun lari ${this.project.year}` }, textStyle(28, 800));
                    put('TEXT', margin, margin + 35, w - 2 * margin, 20,
                        { text: 'Catat hal terbaik dari perjalanan lari tahun ini.' }, textStyle(13, 400, muted));
                    put('NOTES', margin, margin + 64, w - 2 * margin, h - 2 * margin - 70,
                        { text: 'Momen yang ingin diingat:\n\n\nTarget untuk tahun berikutnya:' },
                        { background: dark ? '#26384a' : '#f1f5f3', color: ink });
                } else {
                    const label = page.month_label || this.yearMonths.find(m => m.month_number === page.month_number)?.month_label || '';
                    const isCompact = w < 240;
                    const isClean = family === 'CLEAN_GRID';
                    const gridWidth = isCompact || isClean ? w - 2 * margin : (w - 3 * margin) * 0.69;
                    const gridTop = margin + (isCompact ? 32 : 41);
                    const gridHeight = h - gridTop - margin;
                    put('TEXT', margin, margin, w - 2 * margin, 27,
                        { text: label }, textStyle(isCompact ? 22 : 30, 800));
                    put('TEXT', w - margin - 36, margin + 6, 36, 16,
                        { text: String(this.project.year) },
                        { ...textStyle(13, 600, muted), align: 'right' });
                    put('CALENDAR_GRID', margin, gridTop, gridWidth, isCompact || isClean ? gridHeight * 0.58 : gridHeight,
                        {}, { header_color: accent, weekend_bg: dark ? '#26384a' : '#f1f5f3',
                            text_color: ink, border_color: dark ? '#344454' : '#dce4df' });
                    const rightX = isCompact || isClean ? margin : margin * 2 + gridWidth;
                    const rightY = isCompact || isClean ? gridTop + gridHeight * 0.61 : gridTop;
                    const rightW = isCompact || isClean ? gridWidth : w - margin - rightX;
                    const rightH = isCompact || isClean ? gridHeight * 0.39 : gridHeight;
                    if (kind === 'TRAINING_PLANNER') {
                        const split = isCompact || isClean;
                        put('MONTHLY_GOAL', rightX, rightY, split ? rightW * 0.47 : rightW, split ? rightH : rightH * 0.46,
                            { target_value: 0, current_value: 0 }, { accent });
                        put('NOTES', split ? rightX + rightW * 0.5 : rightX,
                            split ? rightY : rightY + rightH * 0.51,
                            split ? rightW * 0.5 : rightW, split ? rightH : rightH * 0.49,
                            { text: 'Fokus latihan:\n\nRace tujuan:' },
                            { background: dark ? '#26384a' : '#f1f5f3', color: ink });
                    } else if (kind === 'PHOTO_MEMORIES') {
                        const split = isCompact || isClean;
                        put('PHOTO', rightX, rightY, split ? rightW * 0.47 : rightW, split ? rightH : rightH * 0.58,
                            {}, { border_radius_mm: 1 });
                        put('NOTES', split ? rightX + rightW * 0.5 : rightX,
                            split ? rightY : rightY + rightH * 0.61,
                            split ? rightW * 0.5 : rightW, split ? rightH : rightH * 0.39,
                            { text: 'Cerita lari bulan ini:' },
                            { background: dark ? '#26384a' : '#f1f5f3', color: ink });
                    } else if (isClean || kind === 'RACE_SEASON') {
                        put('NOTES', rightX, rightY, rightW, rightH,
                            { text: kind === 'RACE_SEASON' ? 'Race bulan ini:\n\nCatat tanggal dan target race Anda.'
                                : 'Cerita lari bulan ini:\n\nTambahkan foto dan cerita Anda.' },
                            { background: dark ? '#26384a' : '#f1f5f3', color: ink });
                    } else {
                        put('MONTHLY_STATS', rightX, rightY, rightW, rightH * 0.52,
                            {}, { background: dark ? '#26384a' : '#f1f5f3',
                                text_color: ink, accent });
                        put('MONTHLY_GOAL', rightX, rightY + rightH * 0.55, rightW, rightH * 0.45,
                            { target_value: 0, current_value: 0 }, { accent });
                    }
                }
            }
        });
        this.history = [];
        this.future = [];
        this._emit('history');
        return true;
    }

    // ----- Queries
    getActivePage() {
        return this.pages.find(p => p.month_number === this.activePageMonthNumber) ?? this.pages[0];
    }

    getPageByMonth(m) {
        return this.pages.find(p => p.month_number === m);
    }

    findElement(pageMonthNumber, elementId) {
        const page = this.getPageByMonth(pageMonthNumber);
        if (!page) return null;
        return page.elements.find(e => e.id === elementId) ?? null;
    }

    getSelectedElement() {
        if (!this.selectedElementId) return null;
        return this.findElement(this.activePageMonthNumber, this.selectedElementId);
    }

    // ----- Mutations (with history)
    _commit(msg, fn) {
        const before = this._snapshot();
        fn();
        const after = this._snapshot();
        if (JSON.stringify(before) === JSON.stringify(after)) return;

        if (!this._suspendHistory) {
            this.history.push({ msg, before });
            if (this.history.length > this._historyMax) this.history.shift();
            this.future = [];
        }
        this.storage.persistImmediate(this._snapshot(), this.project.anonymous_uuid || this.project.id);
        this.autosave.schedule(this._serverPayload());
        this._emit('changed', { msg });
    }

    _snapshot() {
        return {
            pages: structuredClone(this.pages),
            project: { name: this.project.name, version_counter: this.project.version_counter },
            activePageMonthNumber: this.activePageMonthNumber,
            savedAt: new Date().toISOString(),
        };
    }

    _restoreSnapshot(snap) {
        if (snap?.pages) this.pages = structuredClone(snap.pages);
        if (snap?.project?.name != null) this.project.name = snap.project.name;
        if (snap?.activePageMonthNumber != null) this.activePageMonthNumber = snap.activePageMonthNumber;
        this._emit('changed', { msg: 'history' });
        this.storage.persistImmediate(this._snapshot(), this.project.anonymous_uuid || this.project.id);
        this.autosave.schedule(this._serverPayload());
    }

    undo() {
        const entry = this.history.pop();
        if (!entry) return;
        const current = this._snapshot();
        this.future.push({ before: current });
        this._restoreSnapshot(entry.before);
        this._emit('history');
    }

    redo() {
        const entry = this.future.pop();
        if (!entry) return;
        const current = this._snapshot();
        this.history.push({ before: current });
        this._restoreSnapshot(entry.before);
        this._emit('history');
    }

    // ----- Page actions
    setActivePage(monthNumber) {
        if (monthNumber === this.activePageMonthNumber) return;
        this._commit('set-active-page', () => {
            this.activePageMonthNumber = monthNumber;
            this.selectedElementId = null;
        });
        this._emit('active-page-changed', { monthNumber });
    }

    setZoom(next) {
        const z = Math.max(this.minZoom, Math.min(this.maxZoom, next));
        if (z === this.zoom) return;
        this.zoom = z;
        this._emit('zoom', { zoom: z });
    }

    // ----- Element actions
    addElementToCurrentPage(type, posMm) {
        if (!this.registry.has(type)) return;
        this._commit(`add-${type}`, () => {
            const page = this.getActivePage();
            const el = this.registry.defaultElement(type);
            if (posMm) {
                el.x_mm = posMm.x_mm;
                el.y_mm = posMm.y_mm;
            } else {
                const w = Number(page.canvas_width_mm || this.canvasMeta.width_mm);
                const h = Number(page.canvas_height_mm || this.canvasMeta.height_mm);
                el.x_mm = Math.max(0, Math.round((w - el.width_mm) / 2));
                el.y_mm = Math.max(0, Math.round((h - el.height_mm) / 2));
            }
            el.z_index = page.elements.reduce((m, e) => Math.max(m, e.z_index ?? 0), 0) + 1;
            page.elements.push(el);
            this.selectedElementId = el.id;
        });
        this._emit('selection-changed', { id: this.selectedElementId });
    }

    selectElement(elementId) {
        this.selectedElementId = elementId || null;
        this._emit('selection-changed', { id: this.selectedElementId });
    }

    clearSelection() { this.selectElement(null); }

    deleteSelected() {
        if (!this.selectedElementId) return;
        const id = this.selectedElementId;
        this._commit('delete-element', () => {
            const page = this.getActivePage();
            page.elements = page.elements.filter(e => e.id !== id);
        });
        this.selectedElementId = null;
        this._emit('selection-changed', { id: null });
    }

    updateElement(id, patch, opts = {}) {
        this._commit(opts.msg || 'update-element', () => {
            const page = this.getActivePage();
            const el = page.elements.find(e => e.id === id);
            if (!el) return;
            Object.assign(el, patch);
        });
    }

    bringForward(id, amount = 1) {
        this._commit('z-order', () => {
            const page = this.getActivePage();
            const el = page.elements.find(e => e.id === id);
            if (!el) return;
            el.z_index = (el.z_index || 0) + amount;
        });
    }

    toggleLocked(id) {
        const page = this.getActivePage();
        const el = page.elements.find(e => e.id === id);
        if (!el) return;
        this.updateElement(id, { locked: !el.locked }, { msg: 'toggle-locked' });
    }

    duplicateSelected() {
        const sel = this.getSelectedElement();
        if (!sel) return;
        this._commit('duplicate', () => {
            const page = this.getActivePage();
            const clone = structuredClone(sel);
            clone.id = `el_${Math.random().toString(36).slice(2, 10)}_${Date.now().toString(36)}`;
            clone.x_mm = (sel.x_mm || 0) + 5;
            clone.y_mm = (sel.y_mm || 0) + 5;
            clone.z_index = page.elements.reduce((m, e) => Math.max(m, e.z_index ?? 0), 0) + 1;
            page.elements.push(clone);
            this.selectedElementId = clone.id;
        });
        this._emit('selection-changed', { id: this.selectedElementId });
    }

    setLocalPhotoForElement(elementId, fileRef) {
        if (!this.storage.acceptFile(fileRef, this.project.anonymous_uuid || this.project.id)) {
            alert('Batas foto trial terlampaui. Silakan login untuk menambah lebih banyak.');
            return false;
        }
        this.updateElement(elementId, {
            local_asset_ref: fileRef.clientRef,
            content_json: { ...(this.findElement(this.activePageMonthNumber, elementId)?.content_json || {}), local_filename: fileRef.name },
        }, { msg: 'set-photo' });
        return true;
    }

    // ----- Server payload
    _serverPayload() {
        const isTrial = !this.project.user_id && !!this.project.anonymous_uuid;
        const base = {
            name: this.project.name,
            state_draft: this._snapshot(),
            version_counter: (this.project.version_counter || 1),
        };
        if (isTrial) {
            base.anonymous_uuid = this.project.anonymous_uuid;
        } else {
            base.project_id = this.project.id;
        }
        return base;
    }

    setProjectName(name) {
        if (!name || name === this.project.name) return;
        this._commit('rename', () => { this.project.name = name.slice(0, 120); });
        const el = document.getElementById('kp-project-name');
        if (el) el.textContent = this.project.name;
    }
}
