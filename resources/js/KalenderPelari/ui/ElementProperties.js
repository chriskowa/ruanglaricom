export class KpElementProperties {
    constructor({ store, emptyEl, propsPanel, registry }) {
        this.store = store;
        this.emptyEl = emptyEl;
        this.propsPanel = propsPanel;
        this.registry = registry;
        this.store.addEventListener('selection-changed', ({ detail: { id } }) => this.renderFor(id));
        this.store.addEventListener('changed', () => {
            if (!this.propsPanel?.contains(document.activeElement)) this.renderFor(this.store.selectedElementId);
        });
        this.store.addEventListener('history', () => this.renderFor(this.store.selectedElementId));
    }

    renderFor(elementId, silent = false) {
        if (!elementId) {
            if (this.emptyEl) this.emptyEl.classList.remove('hidden');
            if (this.propsPanel) this.propsPanel.classList.add('hidden');
            return;
        }
        const el = this.store.getSelectedElement();
        if (!el) {
            if (this.emptyEl) this.emptyEl.classList.remove('hidden');
            if (this.propsPanel) this.propsPanel.classList.add('hidden');
            return;
        }
        if (this.emptyEl) this.emptyEl.classList.add('hidden');
        if (this.propsPanel) {
            this.propsPanel.classList.remove('hidden');
            this._render(el, silent);
        }
    }

    _render(e, silent) {
        const p = this.propsPanel;
        const html = [];
        html.push(
            row('Tipe', `<div class="kp-prop-input" style="border-style: dashed; pointer-events: none">${e.element_type}</div>`)
        );
        html.push(row('Posisi X (mm)', numInput('x_mm', e.x_mm || 0, 0, 1000)));
        html.push(row('Posisi Y (mm)', numInput('y_mm', e.y_mm || 0, 0, 1000)));
        html.push(row('Lebar (mm)', numInput('width_mm', e.width_mm || 0, 1, 1000)));
        html.push(row('Tinggi (mm)', numInput('height_mm', e.height_mm || 0, 1, 1000)));
        html.push(row('Z-Index Layer', numInput('z_index', e.z_index || 0, -1000, 1000, 1)));
        html.push(row('Putar (°)', numInput('rotation_deg', e.rotation_deg || 0, -180, 180, 1, 0.1)));
        html.push(row('Terkunci',
            `<label class="inline-flex items-center gap-3 text-sm font-bold text-slate-200">
                <input type="checkbox" data-prop="locked" class="w-5 h-5 rounded accent-[--kp-accent]" ${e.locked ? 'checked' : ''}>
                <span>${e.locked ? 'Ya, tidak bisa digeser' : 'Tidak (bisa diedit)'}</span>
            </label>`
        ));

        const typeSpecific = {
            TEXT: this._textFields.bind(this),
            PHOTO: this._photoFields.bind(this),
            CALENDAR_GRID: this._gridFields.bind(this),
            MONTHLY_STATS: this._statsFields.bind(this),
            MONTHLY_GOAL: this._goalFields.bind(this),
            NOTES: this._noteFields.bind(this),
        };
        const f = typeSpecific[e.element_type];
        if (f) html.push(...f(e));

        html.push(`<div class="flex items-center gap-2 pt-5 border-t border-slate-800">
            <button data-action="duplicate" class="kp-prop-btn flex-1">Duplikat</button>
            <button data-action="bringforward" class="kp-prop-btn flex-1">Ke depan</button>
            <button data-action="delete" class="kp-prop-btn-danger flex-1">Hapus</button>
        </div>`);

        p.innerHTML = `
            <p class="text-sm font-semibold text-white mb-4">Pengaturan ${this.registry.label(e.element_type)}</p>
            <div class="space-y-0">${html.join('')}</div>
        `;

        this._bindEvents(p, e.id);
    }

    _bindEvents(p, id) {
        const self = this;
        p.querySelectorAll('[data-prop], [data-path]').forEach(input => {
            const key = input.dataset.prop;
            const path = input.dataset.path;
            const isNum = input.type === 'number';
            input.addEventListener('input', () => {
                let val = isNum || input.type === 'range' ? (parseFloat(input.value) || 0) : input.value;
                const checked = input.type === 'checkbox' ? input.checked : null;
                if (input.type === 'checkbox') {
                    self.store.updateElement(id, { [key]: checked }, { msg: `set-${key}` });
                    return;
                }
                if (!path) {
                    self.store.updateElement(id, { [key]: val }, { msg: `set-${key}` });
                    return;
                }
                const [group, ...keys] = path.split('.');
                const element = self.store.getSelectedElement();
                if (!element) return;
                const base = { ...(element[group] || {}) };
                if (keys.join('.') === 'focal_point.x' || keys.join('.') === 'focal_point.y') val /= 100;
                let target = base;
                for (const segment of keys.slice(0, -1)) {
                    target[segment] = { ...(target[segment] || {}) };
                    target = target[segment];
                }
                target[keys.at(-1)] = val;
                self.store.updateElement(id, { [group]: base }, { msg: `set-${path}` });
            });
        });
        const editText = p.querySelector('[data-action="edit-text"]');
        if (editText) editText.addEventListener('click', () => {
            const text = prompt('Isi teks:', self.store.getSelectedElement()?.content_json?.text || '');
            if (text == null) return;
            const element = self.store.getSelectedElement();
            const cj = { ...(element?.content_json || {}), text };
            self.store.updateElement(id, { content_json: cj }, { msg: 'set-text' });
        });
        const changePhoto = p.querySelector('[data-action="change-photo"]');
        if (changePhoto) changePhoto.addEventListener('click', () => {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.addEventListener('change', (e) => {
                const f = e.target.files?.[0];
                if (!f) return;
                const ok = self.store.setLocalPhotoForElement(id, f);
                if (!ok) alert('Batas foto trial terlampaui. Silakan login untuk menambah lebih banyak.');
            });
            input.click();
        });
        p.querySelector('[data-action="duplicate"]')?.addEventListener('click', () => self.store.duplicateSelected());
        p.querySelector('[data-action="delete"]')?.addEventListener('click', () => self.store.deleteSelected());
        p.querySelector('[data-action="bringforward"]')?.addEventListener('click', () => self.store.bringForward(id, 1));
    }

    _textFields(e) {
        const s = e.style_config || {};
        const c = e.content_json || {};
        return [
            row('Ukuran Font (pt)', numInput('', s.font_size_pt || 14, 6, 72, 1, 1, 'size_pt', 'style_config.font_size_pt')),
            row('Berat Font', `<select data-path="style_config.font_weight" class="kp-prop-input">
                ${[400,500,600,700,800,900].map(w => `<option ${(s.font_weight||600)==w?'selected':''} value="${w}">${w}${w===400?' (Regular)':w===700?' (Bold)':w===900?' (Black)':''}</option>`).join('')}
            </select>`),
            row('Warna Teks', colorInput('style_config.color', s.color || '#0f172a')),
            row('Rata', `<select data-path="style_config.align" class="kp-prop-input">
                ${['left','center','right','justify'].map(a => `<option value="${a}" ${(s.align||'left')===a?'selected':''}>${a[0].toUpperCase()+a.slice(1)}</option>`).join('')}
            </select>`),
            row('Isi Teks', `<button data-action="edit-text" class="kp-prop-btn w-full mt-2">Edit teks</button>`),
        ];
    }

    _photoFields(e) {
        const s = e.style_config || {};
        return [
            row('Ganti Foto', `<button data-action="change-photo" class="kp-prop-btn w-full">Pilih foto lain</button>`),
            row('Crop Focal X', rangeInput('style_config.focal_point.x', (s.focal_point?.x ?? 0.5) * 100, 0, 100)),
            row('Crop Focal Y', rangeInput('style_config.focal_point.y', (s.focal_point?.y ?? 0.5) * 100, 0, 100)),
            row('Kecerahan', rangeInput('style_config.brightness', s.brightness ?? 100, 20, 140)),
            row('Fit', `<select data-path="style_config.object_fit" class="kp-prop-input">
                <option value="cover" ${(s.object_fit||'cover')==='cover'?'selected':''}>Cover</option>
                <option value="contain" ${s.object_fit==='contain'?'selected':''}>Contain</option>
                <option value="fill" ${s.object_fit==='fill'?'selected':''}>Fill</option>
            </select>`),
        ];
    }

    _gridFields(e) {
        const s = e.style_config || {};
        return [
            row('Warna Header', colorInput('style_config.header_color', s.header_color || '#305a49')),
            row('Aksen Weekend', colorInput('style_config.weekend_bg', s.weekend_bg || '#f1f5f3')),
        ];
    }

    _statsFields(e) {
        const s = e.style_config || {};
        const c = e.content_json || {};
        return [
            row('Background', colorInput('style_config.background', s.background || '#0f172a')),
            row('Teks', colorInput('style_config.text_color', s.text_color || '#ffffff')),
            row('Aksen', colorInput('style_config.accent', s.accent || '#ccff00')),
            `<div class="kp-prop-row"><span class="kp-prop-label">Isi Data Manual (trial)</span>
                <div class="grid grid-cols-2 gap-2">
                    ${numInput('', c.total_km || 0, 0, 10000, 2, 0.1, 'total_km', 'content_json.total_km', 'km')}
                    ${numInput('', c.total_runs || 0, 0, 9999, 0, 1, 'total_runs', 'content_json.total_runs', 'kali')}
                    ${numInput('', c.total_hours || 0, 0, 9999, 1, 0.1, 'total_hours', 'content_json.total_hours', 'jam')}
                    ${numInput('', c.longest_run_km || 0, 0, 1000, 2, 0.1, 'longest_run_km', 'content_json.longest_run_km', 'km')}
                </div>
            </div>`,
        ];
    }

    _goalFields(e) {
        const s = e.style_config || {};
        const c = e.content_json || {};
        return [
            row('Aksen', colorInput('style_config.accent', s.accent || '#ccff00')),
            row('Jenis Target', `<select data-path="content_json.goal_type" class="kp-prop-input">
                ${['mileage','runs','longest','race','pace','strength','custom'].map(t => `<option value="${t}" ${(c.goal_type||'mileage')===t?'selected':''}>${t[0].toUpperCase()+t.slice(1)}</option>`).join('')}
            </select>`),
            row('Target', numInput('', c.target_value || 0, 0, 100000, 2, 1, '', 'content_json.target_value')),
            row('Progress Saat Ini', numInput('', c.current_value || 0, 0, 100000, 2, 1, '', 'content_json.current_value')),
            row('Unit', `<input data-path="content_json.unit" class="kp-prop-input" value="${c.unit || 'km'}" placeholder="km / kali / menit">`),
        ];
    }

    _noteFields(e) {
        const s = e.style_config || {};
        const c = e.content_json || {};
        return [
            row('Warna Kertas', colorInput('style_config.background', s.background || '#f1f5f3')),
            row('Warna Teks', colorInput('style_config.color', s.color || '#0f172a')),
            row('Isi Catatan', `<button data-action="edit-text" class="kp-prop-btn w-full">Edit catatan</button>`),
        ];
    }
}

function row(label, controlHtml) {
    return `<div class="kp-prop-row"><span class="kp-prop-label">${label}</span>${controlHtml}</div>`;
}

function numInput(prop, val, min, max, step=0, digits=1, displaySuffix='', path='', suffix='') {
    step = step || 1;
    const dp = Math.max(0, digits);
    return `<div class="flex items-center gap-2">
        <input type="number" ${prop ? `data-prop="${prop}"` : ''} ${path ? `data-path="${path}"` : ''} value="${Number(val).toFixed(dp)}" step="${step}" ${min!=null?`min="${min}"`:''} ${max!=null?`max="${max}"`:''} class="kp-prop-input flex-1 ${suffix?'rounded-r-none':''}">
        ${suffix?`<span class="shrink-0 rounded-r-xl border border-l-0 border-slate-700 bg-slate-800/70 px-3 h-[42px] flex items-center text-xs font-bold text-slate-300">${suffix}</span>`:''}
    </div>`;
}

function colorInput(path, value) {
    return `<div class="flex items-center gap-2">
        <input type="color" data-path="${path}" value="${toHex(value)}" class="w-12 h-[42px] shrink-0 rounded-xl border border-slate-700 bg-slate-800 cursor-pointer">
        <input type="text" data-path="${path}" value="${value}" maxlength="32" class="kp-prop-input flex-1 font-mono">
    </div>`;
}

function rangeInput(path, value, min, max) {
    return `<div class="flex items-center gap-3">
        <input type="range" data-path="${path}" value="${value}" min="${min}" max="${max}" class="flex-1 accent-[--kp-accent]">
        <span class="text-xs font-mono text-slate-300 w-10 text-right">${Math.round(value)}</span>
    </div>`;
}

function toHex(c) {
    if (!c) return '#ccff00';
    const trimmed = c.trim();
    if (trimmed.startsWith('#') && trimmed.length === 7) return trimmed;
    const map = { black:'#000000', white:'#ffffff' };
    if (map[trimmed.toLowerCase()]) return map[trimmed.toLowerCase()];
    return '#ccff00';
}
