export class KpElementProperties {
    constructor({ store, emptyEl, propsPanel, registry, api }) {
        this.store = store;
        this.emptyEl = emptyEl;
        this.propsPanel = propsPanel;
        this.registry = registry;
        this.api = api || {};
        this._raceTab = 'search';
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

        if (self.store.getSelectedElement()?.element_type === 'CALENDAR_GRID') {
            self._bindGridEvents(p, id);
        }
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
        const c = e.content_json || {};
        const races = Array.isArray(c.races) ? c.races : [];
        const activePage = this.store.getActivePage();
        const activeMonth = activePage?.month_number || 1;
        const year = this.store.project?.year || new Date().getFullYear();
        const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const activeMonthName = monthNames[activeMonth] || `Bulan ${activeMonth}`;
        const daysInMonth = new Date(Date.UTC(year, activeMonth, 0)).getUTCDate();

        const raceListHtml = races.length === 0
            ? `<div class="p-2.5 rounded-md border border-dashed border-slate-800 text-xs text-slate-400">Belum ada race yang ditandai untuk ${activeMonthName}.</div>`
            : `<div class="space-y-1.5">
                ${races.map(r => `
                    <div class="kp-race-item">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-xs font-mono font-bold text-slate-300 shrink-0">Tgl ${r.day}</span>
                            <span class="text-[11px] font-bold px-1.5 py-0.5 rounded text-white shrink-0" style="background:${r.color || '#E63946'}">${r.distance || 'Race'}</span>
                            <span class="text-xs text-white truncate" title="${r.name || ''}">${r.name || 'Jadwal Race'}</span>
                        </div>
                        <button type="button" data-delete-race="${r.id}" class="text-xs font-medium text-slate-400 hover:text-red-400 p-1 shrink-0 transition" title="Hapus tanda race">Hapus</button>
                    </div>
                `).join('')}
            </div>`;

        const dayOptions = [];
        for (let d = 1; d <= daysInMonth; d++) {
            dayOptions.push(`<option value="${d}">Tgl ${d} ${activeMonthName}</option>`);
        }

        const isSearchTab = this._raceTab !== 'manual';

        return [
            row('Warna Header', colorInput('style_config.header_color', s.header_color || '#305a49')),
            row('Aksen Weekend', colorInput('style_config.weekend_bg', s.weekend_bg || '#f1f5f3')),
            `<div class="kp-prop-row">
                <div class="flex items-center justify-between mb-2">
                    <span class="kp-prop-label">Jadwal Race (${activeMonthName})</span>
                    <span class="text-xs font-mono text-slate-400">${races.length} race</span>
                </div>
                ${raceListHtml}
            </div>`,
            `<div class="kp-prop-row">
                <span class="kp-prop-label mb-2">Tandai Jadwal Race Baru</span>
                <div class="flex gap-1.5 mb-3">
                    <button type="button" data-race-tab="search" class="kp-race-tab-btn ${isSearchTab ? 'active' : ''}">Cari Event RuangLari</button>
                    <button type="button" data-race-tab="manual" class="kp-race-tab-btn ${!isSearchTab ? 'active' : ''}">Input Manual</button>
                </div>

                <div id="kp-race-tab-search-content" class="${isSearchTab ? '' : 'hidden'} space-y-2">
                    <div class="relative">
                        <input type="text" id="kp-race-search-input" class="kp-prop-input" placeholder="Cari event (cth: Pocari, Borobudur)...">
                        <div id="kp-race-search-spinner" class="hidden absolute right-3 top-2.5 text-xs text-slate-400">Mencari...</div>
                    </div>
                    <div id="kp-race-search-results" class="space-y-1.5 max-h-48 overflow-y-auto pr-1"></div>
                    <div id="kp-race-search-selected" class="hidden p-2.5 bg-slate-900 border border-slate-700 rounded-md space-y-2">
                        <div class="text-xs font-bold text-white" id="kp-race-selected-title"></div>
                        <div class="text-xs text-slate-300" id="kp-race-selected-meta"></div>
                        <div class="space-y-1">
                            <span class="text-[11px] font-semibold text-slate-300">Pilih / Ketik Jarak:</span>
                            <div class="flex flex-wrap gap-1" id="kp-race-selected-dist-pills"></div>
                            <input type="text" id="kp-race-search-custom-dist" class="kp-prop-input text-xs py-1.5 mt-1" placeholder="Jarak (cth: 21K, 10K, Marathon)">
                        </div>
                        <div class="space-y-1">
                            <span class="text-[11px] font-semibold text-slate-300">Warna Badge:</span>
                            <div class="flex items-center gap-1.5" id="kp-race-search-color-row">
                                ${['#E63946', '#ccff00', '#2563EB', '#16A34A', '#F97316', '#8B5CF6'].map((hex, i) => `
                                    <button type="button" data-pick-color="${hex}" class="w-6 h-6 rounded border ${i === 0 ? 'border-white' : 'border-slate-700'} kp-color-swatch" style="background:${hex};"></button>
                                `).join('')}
                                <input type="color" id="kp-race-search-color-input" value="#E63946" class="w-6 h-6 rounded border border-slate-700 bg-slate-800 cursor-pointer">
                            </div>
                        </div>
                        <button type="button" id="kp-btn-confirm-search-race" class="kp-prop-btn w-full mt-2 font-bold text-slate-950 bg-white hover:bg-slate-200 border-none">+ Tandai di Kalender</button>
                    </div>
                </div>

                <div id="kp-race-tab-manual-content" class="${!isSearchTab ? '' : 'hidden'} space-y-2.5">
                    <div>
                        <label class="text-[11px] font-semibold text-slate-300 block mb-1">Tanggal</label>
                        <select id="kp-race-manual-day" class="kp-prop-input">
                            ${dayOptions.join('')}
                        </select>
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-slate-300 block mb-1">Nama Race / Acara</label>
                        <input type="text" id="kp-race-manual-name" class="kp-prop-input" placeholder="Cth: Half Marathon Race">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-slate-300 block mb-1">Jarak / Kategori</label>
                        <div class="flex gap-1 mb-1.5">
                            ${['5K', '10K', '21K', '42K', 'Ultra'].map(d => `
                                <button type="button" data-set-manual-dist="${d}" class="flex-1 py-1 text-xs rounded border border-slate-700 bg-slate-800 text-slate-200 hover:text-white">${d}</button>
                            `).join('')}
                        </div>
                        <input type="text" id="kp-race-manual-dist" class="kp-prop-input text-xs" value="21K" placeholder="Cth: 21K">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-slate-300 block mb-1">Warna Badge</label>
                        <div class="flex items-center gap-1.5" id="kp-race-manual-color-row">
                            ${['#E63946', '#ccff00', '#2563EB', '#16A34A', '#F97316', '#8B5CF6'].map((hex, i) => `
                                <button type="button" data-pick-manual-color="${hex}" class="w-6 h-6 rounded border ${i === 0 ? 'border-white' : 'border-slate-700'} kp-manual-color-swatch" style="background:${hex};"></button>
                            `).join('')}
                            <input type="color" id="kp-race-manual-color-input" value="#E63946" class="w-6 h-6 rounded border border-slate-700 bg-slate-800 cursor-pointer">
                        </div>
                    </div>
                    <button type="button" id="kp-btn-add-manual-race" class="kp-prop-btn w-full mt-2 font-bold text-slate-950 bg-white hover:bg-slate-200 border-none">+ Tambah ke Kalender</button>
                </div>
            </div>`
        ];
    }

    _bindGridEvents(p, id) {
        const self = this;
        // Tab switching
        p.querySelectorAll('[data-race-tab]').forEach(btn => {
            btn.addEventListener('click', () => {
                self._raceTab = btn.dataset.raceTab;
                const isSearch = self._raceTab !== 'manual';
                p.querySelectorAll('[data-race-tab]').forEach(b => {
                    b.classList.toggle('active', b.dataset.raceTab === self._raceTab);
                });
                const searchEl = p.querySelector('#kp-race-tab-search-content');
                const manualEl = p.querySelector('#kp-race-tab-manual-content');
                if (searchEl) searchEl.classList.toggle('hidden', !isSearch);
                if (manualEl) manualEl.classList.toggle('hidden', isSearch);
                if (isSearch && searchInput && !searchResults?.hasChildNodes()) {
                    doSearch(searchInput.value.trim());
                }
            });
        });

        // Delete race button
        p.querySelectorAll('[data-delete-race]').forEach(btn => {
            btn.addEventListener('click', () => {
                const raceId = btn.dataset.deleteRace;
                const element = self.store.getSelectedElement();
                if (!element) return;
                const currentRaces = Array.isArray(element.content_json?.races) ? element.content_json.races : [];
                const nextRaces = currentRaces.filter(r => r.id !== raceId);
                self.store.updateElement(id, {
                    content_json: { ...(element.content_json || {}), races: nextRaces }
                }, { msg: 'delete-race' });
                self.renderFor(id);
            });
        });

        // Manual race inputs & presets
        p.querySelectorAll('[data-set-manual-dist]').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = p.querySelector('#kp-race-manual-dist');
                if (input) input.value = btn.dataset.setManualDist;
            });
        });

        let manualColor = '#E63946';
        p.querySelectorAll('[data-pick-manual-color]').forEach(btn => {
            btn.addEventListener('click', () => {
                manualColor = btn.dataset.pickManualColor;
                p.querySelectorAll('.kp-manual-color-swatch').forEach(s => s.classList.remove('border-white'));
                btn.classList.add('border-white');
                const colorInput = p.querySelector('#kp-race-manual-color-input');
                if (colorInput) colorInput.value = manualColor;
            });
        });
        const manualColorInput = p.querySelector('#kp-race-manual-color-input');
        if (manualColorInput) {
            manualColorInput.addEventListener('input', () => {
                manualColor = manualColorInput.value;
                p.querySelectorAll('.kp-manual-color-swatch').forEach(s => s.classList.remove('border-white'));
            });
        }

        const addManualBtn = p.querySelector('#kp-btn-add-manual-race');
        if (addManualBtn) {
            addManualBtn.addEventListener('click', () => {
                const daySelect = p.querySelector('#kp-race-manual-day');
                const nameInput = p.querySelector('#kp-race-manual-name');
                const distInput = p.querySelector('#kp-race-manual-dist');
                const day = parseInt(daySelect?.value || '1', 10);
                const name = (nameInput?.value || '').trim() || 'Race Day';
                const distance = (distInput?.value || '').trim() || '21K';

                const element = self.store.getSelectedElement();
                if (!element) return;
                const currentRaces = Array.isArray(element.content_json?.races) ? [...element.content_json.races] : [];
                const newRace = {
                    id: 'race_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
                    day,
                    name,
                    distance,
                    color: manualColor,
                };
                currentRaces.push(newRace);
                currentRaces.sort((a, b) => Number(a.day) - Number(b.day));

                self.store.updateElement(id, {
                    content_json: { ...(element.content_json || {}), races: currentRaces }
                }, { msg: 'add-race-manual' });
                self.renderFor(id);
            });
        }

        // RuangLari search inputs & events
        const searchInput = p.querySelector('#kp-race-search-input');
        const searchResults = p.querySelector('#kp-race-search-results');
        const searchSpinner = p.querySelector('#kp-race-search-spinner');
        const selectedBox = p.querySelector('#kp-race-search-selected');
        let searchTimer = null;
        let currentEventSelected = null;
        let searchColor = '#E63946';

        const doSearch = (query = '') => {
            if (!searchResults) return;
            const activePage = self.store.getActivePage();
            const activeMonth = activePage?.month_number || 1;
            const year = self.store.project?.year || new Date().getFullYear();
            const endpoint = self.api?.event_search || '/kalender-pelari/api/events/search';

            if (searchSpinner) searchSpinner.classList.remove('hidden');
            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('year', year);
            url.searchParams.set('month', activeMonth);
            if (query) url.searchParams.set('q', query);

            fetch(url.toString(), { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (searchSpinner) searchSpinner.classList.add('hidden');
                    const events = data.events || [];
                    if (events.length === 0) {
                        searchResults.innerHTML = `<div class="p-2 text-xs text-slate-400">Tidak ada event ditemukan.</div>`;
                        return;
                    }
                    searchResults.innerHTML = events.map(ev => `
                        <div class="kp-race-search-item" data-ev-id="${ev.id}">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-xs font-bold text-white truncate">${ev.name}</span>
                                <span class="text-[11px] font-mono font-bold text-emerald-400 shrink-0">${ev.day ? 'Tgl ' + ev.day : ''}</span>
                            </div>
                            <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5 truncate">
                                <span>${ev.city || 'Indonesia'}</span>
                                ${ev.month && ev.month !== activeMonth ? `<span class="text-amber-400 font-semibold">(Bulan ${ev.month})</span>` : ''}
                                ${ev.distances?.length ? `<span>· ${ev.distances.join(', ')}</span>` : ''}
                            </div>
                        </div>
                    `).join('');

                    searchResults.querySelectorAll('.kp-race-search-item').forEach(item => {
                        item.addEventListener('click', () => {
                            const evId = parseInt(item.dataset.evId, 10);
                            const ev = events.find(x => x.id === evId);
                            if (!ev) return;
                            currentEventSelected = ev;

                            searchResults.querySelectorAll('.kp-race-search-item').forEach(el => el.classList.remove('border-emerald-500', 'bg-slate-800'));
                            item.classList.add('border-emerald-500', 'bg-slate-800');

                            if (selectedBox) {
                                selectedBox.classList.remove('hidden');
                                const titleEl = selectedBox.querySelector('#kp-race-selected-title');
                                const metaEl = selectedBox.querySelector('#kp-race-selected-meta');
                                const pillsEl = selectedBox.querySelector('#kp-race-selected-dist-pills');
                                const customDistInput = selectedBox.querySelector('#kp-race-search-custom-dist');

                                if (titleEl) titleEl.textContent = ev.name;
                                const dateStr = ev.day ? `Tanggal ${ev.day} (Bulan ${ev.month || activeMonth})` : 'Jadwal race';
                                if (metaEl) metaEl.textContent = `${dateStr} · ${ev.city || 'Indonesia'}`;

                                const defaultDists = (ev.distances && ev.distances.length > 0) ? ev.distances : ['5K', '10K', '21K', '42K'];
                                if (customDistInput) customDistInput.value = defaultDists[0] || '21K';

                                if (pillsEl) {
                                    pillsEl.innerHTML = defaultDists.map(d => `
                                        <button type="button" data-pick-dist="${d}" class="px-2 py-0.5 text-xs rounded border border-slate-700 bg-slate-800 text-slate-200 hover:text-white">${d}</button>
                                    `).join('');
                                    pillsEl.querySelectorAll('[data-pick-dist]').forEach(pBtn => {
                                        pBtn.addEventListener('click', () => {
                                            if (customDistInput) customDistInput.value = pBtn.dataset.pickDist;
                                        });
                                    });
                                }
                            }
                        });
                    });
                })
                .catch(err => {
                    if (searchSpinner) searchSpinner.classList.add('hidden');
                    console.error('Failed to search events', err);
                });
        };

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    doSearch(searchInput.value.trim());
                }, 300);
            });
            // Initial auto-search for current month
            doSearch('');
        }

        p.querySelectorAll('[data-pick-color]').forEach(btn => {
            btn.addEventListener('click', () => {
                searchColor = btn.dataset.pickColor;
                p.querySelectorAll('.kp-color-swatch').forEach(s => s.classList.remove('border-white'));
                btn.classList.add('border-white');
                const colorInput = p.querySelector('#kp-race-search-color-input');
                if (colorInput) colorInput.value = searchColor;
            });
        });
        const searchColorInput = p.querySelector('#kp-race-search-color-input');
        if (searchColorInput) {
            searchColorInput.addEventListener('input', () => {
                searchColor = searchColorInput.value;
                p.querySelectorAll('.kp-color-swatch').forEach(s => s.classList.remove('border-white'));
            });
        }

        const confirmSearchBtn = p.querySelector('#kp-btn-confirm-search-race');
        if (confirmSearchBtn) {
            confirmSearchBtn.addEventListener('click', () => {
                if (!currentEventSelected) return;
                const activePage = self.store.getActivePage();
                const activeMonth = activePage?.month_number || 1;
                const distInput = p.querySelector('#kp-race-search-custom-dist');
                const distance = (distInput?.value || '').trim() || '21K';

                let day = currentEventSelected.day;
                if (!day || day < 1 || day > 31) day = 1;

                if (currentEventSelected.month && currentEventSelected.month !== activeMonth) {
                    const confirmed = confirm(`Event "${currentEventSelected.name}" terdaftar pada bulan ${currentEventSelected.month}. Halaman aktif saat ini adalah bulan ${activeMonth}. Tetap tandai di tanggal ${day} bulan ${activeMonth}?`);
                    if (!confirmed) return;
                }

                const element = self.store.getSelectedElement();
                if (!element) return;
                const currentRaces = Array.isArray(element.content_json?.races) ? [...element.content_json.races] : [];
                const newRace = {
                    id: 'race_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
                    day,
                    name: currentEventSelected.name,
                    distance,
                    color: searchColor,
                };
                currentRaces.push(newRace);
                currentRaces.sort((a, b) => Number(a.day) - Number(b.day));

                self.store.updateElement(id, {
                    content_json: { ...(element.content_json || {}), races: currentRaces }
                }, { msg: 'add-race-searched' });
                self.renderFor(id);
            });
        }
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
