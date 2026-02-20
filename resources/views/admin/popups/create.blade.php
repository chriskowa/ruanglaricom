@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Create Popup')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="text-xs font-mono text-slate-400 uppercase tracking-widest">Popup Builder</div>
            <h1 class="text-3xl font-black text-white">Create New Popup</h1>
        </div>
        <a href="{{ route('admin.popups.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-600 text-white">Back</a>
    </div>

    <form method="POST" action="{{ route('admin.popups.store') }}" id="popup-form" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        <input type="hidden" name="content_json" id="content_json">
        <input type="hidden" name="settings_json" id="settings_json">
        <input type="hidden" name="rules_json" id="rules_json">

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-slate-900/60 border border-slate-700 rounded-2xl p-5 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Name</div>
                        <input name="name" id="popup-name" value="{{ old('name', $popup->name) }}" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white" required>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Slug</div>
                        <input name="slug" id="popup-slug" value="{{ old('slug', $popup->slug) }}" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Status</div>
                        <select name="status" id="popup-status" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                            @foreach(['draft','scheduled','active','expired'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $popup->status)===$s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Starts At</div>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($popup->starts_at)->format('Y-m-d\\TH:i')) }}" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Ends At</div>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($popup->ends_at)->format('Y-m-d\\TH:i')) }}" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                    </div>
                </div>
                <div>
                    <div class="text-xs text-slate-400 mb-1">Timezone</div>
                    <input name="timezone" value="{{ old('timezone', $popup->timezone) }}" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white" placeholder="Asia/Jakarta">
                </div>
            </div>

            <div class="bg-slate-900/60 border border-slate-700 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-bold text-white">Content Blocks</div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-add="text" class="px-3 py-1 rounded-lg bg-slate-800 text-xs text-white">Add Text</button>
                        <button type="button" data-add="image" class="px-3 py-1 rounded-lg bg-slate-800 text-xs text-white">Add Image</button>
                        <button type="button" data-add="button" class="px-3 py-1 rounded-lg bg-slate-800 text-xs text-white">Add Button</button>
                        <button type="button" data-add="form" class="px-3 py-1 rounded-lg bg-slate-800 text-xs text-white">Add Form</button>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="text-xs text-slate-400 mb-2">Templates</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($templates as $idx => $template)
                            <button type="button" data-template="{{ $idx }}" class="px-3 py-1 rounded-lg bg-slate-800 text-xs text-white">{{ $template['name'] }}</button>
                        @endforeach
                    </div>
                </div>
                <div id="blocks-list" class="space-y-3"></div>
            </div>

            <div class="bg-slate-900/60 border border-slate-700 rounded-2xl p-5 space-y-4">
                <div class="text-sm font-bold text-white">Display Rules</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Include Paths</div>
                        <textarea id="rules-include" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white" rows="3">/{{ '*' }}</textarea>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Exclude Paths</div>
                        <textarea id="rules-exclude" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white" rows="3"></textarea>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Devices</div>
                        <div class="space-y-1 text-sm">
                            <label class="flex items-center gap-2"><input type="checkbox" class="rules-device" value="desktop" checked> Desktop</label>
                            <label class="flex items-center gap-2"><input type="checkbox" class="rules-device" value="mobile" checked> Mobile</label>
                            <label class="flex items-center gap-2"><input type="checkbox" class="rules-device" value="tablet" checked> Tablet</label>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Segments</div>
                        <div class="space-y-1 text-sm">
                            <label class="flex items-center gap-2"><input type="checkbox" class="rules-segment" value="guest" checked> Guest</label>
                            <label class="flex items-center gap-2"><input type="checkbox" class="rules-segment" value="new" checked> New</label>
                            <label class="flex items-center gap-2"><input type="checkbox" class="rules-segment" value="returning" checked> Returning</label>
                            <label class="flex items-center gap-2"><input type="checkbox" class="rules-segment" value="premium" checked> Premium</label>
                            <label class="flex items-center gap-2"><input type="checkbox" class="rules-segment" value="member" checked> Member</label>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Frequency</div>
                        <select id="rules-frequency" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                            <option value="session">Once per session</option>
                            <option value="day">Once per day</option>
                            <option value="interval">Custom interval</option>
                        </select>
                        <input type="number" id="rules-interval" value="24" class="w-full mt-2 px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                    </div>
                </div>
                <div>
                    <div class="text-xs text-slate-400 mb-1">City IDs</div>
                    <input id="rules-cities" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white" placeholder="1,2,3">
                </div>
                <div>
                    <div class="text-xs text-slate-400 mb-2">Time Windows</div>
                    <div id="time-windows" class="space-y-2"></div>
                    <button type="button" id="add-window" class="mt-2 px-3 py-1 rounded-lg bg-slate-800 text-xs text-white">Add Window</button>
                </div>
            </div>

            <div class="bg-slate-900/60 border border-slate-700 rounded-2xl p-5 space-y-4">
                <div class="text-sm font-bold text-white">Styling & Behavior</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Position</div>
                        <select id="set-position" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                            <option value="center">Center</option>
                            <option value="bottom">Bottom</option>
                            <option value="top">Top</option>
                        </select>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Animation</div>
                        <select id="set-animation" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                            <option value="fade">Fade</option>
                            <option value="slide">Slide</option>
                            <option value="zoom">Zoom</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Overlay</div>
                        <input id="set-overlay" value="rgba(15, 23, 42, 0.7)" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Background</div>
                        <input id="set-background" value="#0f172a" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Text Color</div>
                        <input id="set-text" value="#e2e8f0" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Accent</div>
                        <input id="set-accent" value="#ccff00" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        <label class="flex items-center gap-2"><input type="checkbox" id="set-close-backdrop" checked> Close on backdrop</label>
                        <label class="flex items-center gap-2"><input type="checkbox" id="set-close-esc" checked> Close on ESC</label>
                    </div>
                </div>
            </div>

            <button class="px-6 py-2 rounded-xl bg-primary text-slate-900 font-bold">Save Popup</button>
        </div>

        <div class="bg-slate-900/60 border border-slate-700 rounded-2xl p-5 sticky top-24 h-fit">
            <div class="text-sm font-bold text-white mb-3">Live Preview</div>
            <div id="popup-preview" class="bg-slate-950 border border-slate-700 rounded-2xl p-4 min-h-[300px]"></div>
        </div>
    </form>
</div>

<script>
    const popupState = @json($popup);
    const templates = @json($templates);
    const blocksList = document.getElementById('blocks-list');
    const preview = document.getElementById('popup-preview');
    const timeWindowsWrap = document.getElementById('time-windows');
    const addWindowBtn = document.getElementById('add-window');
    let blocks = Array.isArray(popupState.content?.blocks) ? popupState.content.blocks : [];

    const buildBlock = (type) => {
        if (type === 'image') return { type: 'image', content: '', style: { radius: 'xl' } };
        if (type === 'button') return { type: 'button', content: 'Learn More', style: { variant: 'primary', url: '/' } };
        if (type === 'form') return { type: 'form', content: 'Stay updated', style: { action: '/', submit_label: 'Submit', fields: ['name','email'] } };
        return { type: 'text', content: 'New text', style: { size: 'md', weight: 'normal', align: 'left' } };
    };

    const renderBlocks = () => {
        blocksList.innerHTML = '';
        blocks.forEach((block, index) => {
            const item = document.createElement('div');
            item.className = 'border border-slate-700 rounded-xl p-3 bg-slate-900/80';
            item.setAttribute('draggable', 'true');
            item.dataset.index = index;
            item.innerHTML = `
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs text-slate-400 uppercase">Block ${index + 1}</div>
                    <div class="flex gap-2">
                        <button type="button" data-action="up" data-index="${index}" class="px-2 py-1 rounded bg-slate-800 text-xs text-white">Up</button>
                        <button type="button" data-action="down" data-index="${index}" class="px-2 py-1 rounded bg-slate-800 text-xs text-white">Down</button>
                        <button type="button" data-action="remove" data-index="${index}" class="px-2 py-1 rounded bg-rose-600/80 text-xs text-white">Remove</button>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Type</div>
                        <select data-field="type" data-index="${index}" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white">
                            <option value="text" ${block.type === 'text' ? 'selected' : ''}>Text</option>
                            <option value="image" ${block.type === 'image' ? 'selected' : ''}>Image</option>
                            <option value="button" ${block.type === 'button' ? 'selected' : ''}>Button</option>
                            <option value="form" ${block.type === 'form' ? 'selected' : ''}>Form</option>
                        </select>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="text-xs text-slate-400">Content</div>
                            <button type="button" data-action="pick-image" data-index="${index}" class="text-[10px] px-2 py-1 rounded-lg bg-slate-800 text-slate-200 border border-slate-600 ${block.type === 'image' ? '' : 'hidden'}">
                                Select from media
                            </button>
                        </div>
                        <input data-field="content" data-index="${index}" value="${block.content || ''}" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Style</div>
                        <input data-field="style" data-index="${index}" value='${JSON.stringify(block.style || {})}' class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white">
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 mb-1">Notes</div>
                        <input disabled value="${block.type}" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-slate-500">
                    </div>
                </div>
            `;
            blocksList.appendChild(item);
        });
        syncAll();
    };

    const renderPreview = () => {
        preview.innerHTML = '';
        const container = document.createElement('div');
        container.className = 'rounded-2xl p-5';
        const background = document.getElementById('set-background').value || '#0f172a';
        const textColor = document.getElementById('set-text').value || '#e2e8f0';
        container.style.background = background;
        container.style.color = textColor;
        blocks.forEach((block) => {
            const wrap = document.createElement('div');
            wrap.className = 'mb-3';
            if (block.type === 'text') {
                const p = document.createElement('div');
                p.textContent = block.content || '';
                wrap.appendChild(p);
            } else if (block.type === 'image') {
                const img = document.createElement('img');
                img.src = block.content || '';
                img.alt = 'popup image';
                img.className = 'w-full rounded-xl';
                wrap.appendChild(img);
            } else if (block.type === 'button') {
                const btn = document.createElement('div');
                btn.textContent = block.content || 'Button';
                btn.className = 'inline-flex px-4 py-2 rounded-xl bg-primary text-slate-900 font-bold';
                wrap.appendChild(btn);
            } else if (block.type === 'form') {
                const label = document.createElement('div');
                label.textContent = block.content || 'Form';
                label.className = 'font-bold mb-2';
                wrap.appendChild(label);
                const input = document.createElement('input');
                input.className = 'w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white mb-2';
                input.placeholder = 'Email';
                wrap.appendChild(input);
                const submit = document.createElement('div');
                submit.textContent = 'Submit';
                submit.className = 'inline-flex px-4 py-2 rounded-xl bg-primary text-slate-900 font-bold';
                wrap.appendChild(submit);
            }
            container.appendChild(wrap);
        });
        preview.appendChild(container);
    };

    const syncRules = () => {
        const include = document.getElementById('rules-include').value.split('\n').map((v) => v.trim()).filter(Boolean);
        const exclude = document.getElementById('rules-exclude').value.split('\n').map((v) => v.trim()).filter(Boolean);
        const devices = Array.from(document.querySelectorAll('.rules-device')).filter((i) => i.checked).map((i) => i.value);
        const segments = Array.from(document.querySelectorAll('.rules-segment')).filter((i) => i.checked).map((i) => i.value);
        const frequency = document.getElementById('rules-frequency').value;
        const interval = Number(document.getElementById('rules-interval').value || 24);
        const cityIds = (document.getElementById('rules-cities').value || '').split(',').map((v) => v.trim()).filter(Boolean).map((v) => Number(v));
        const windows = Array.from(timeWindowsWrap.querySelectorAll('[data-window]')).map((row) => ({
            start: row.querySelector('[data-start]').value,
            end: row.querySelector('[data-end]').value,
        })).filter((w) => w.start && w.end);
        return { include_paths: include, exclude_paths: exclude, devices, segments, frequency: { mode: frequency, interval_hours: interval }, city_ids: cityIds, time_windows: windows };
    };

    const syncSettings = () => {
        return {
            position: document.getElementById('set-position').value,
            overlay: document.getElementById('set-overlay').value,
            background: document.getElementById('set-background').value,
            text_color: document.getElementById('set-text').value,
            accent: document.getElementById('set-accent').value,
            animation: document.getElementById('set-animation').value,
            close_on_backdrop: document.getElementById('set-close-backdrop').checked,
            close_on_esc: document.getElementById('set-close-esc').checked,
        };
    };

    const syncAll = () => {
        document.getElementById('content_json').value = JSON.stringify({ blocks });
        document.getElementById('settings_json').value = JSON.stringify(syncSettings());
        document.getElementById('rules_json').value = JSON.stringify(syncRules());
        renderPreview();
    };

    document.querySelectorAll('[data-add]').forEach((btn) => {
        btn.addEventListener('click', () => {
            blocks.push(buildBlock(btn.dataset.add));
            renderBlocks();
        });
    });

    document.querySelectorAll('[data-template]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const template = templates[Number(btn.dataset.template)];
            if (!template) return;
            blocks = template.blocks || [];
            renderBlocks();
        });
    });

    blocksList.addEventListener('input', (e) => {
        const idx = e.target.dataset.index;
        if (idx === undefined) return;
        const index = Number(idx);
        if (!blocks[index]) return;
        const field = e.target.dataset.field;
        if (field === 'style') {
            try {
                blocks[index].style = JSON.parse(e.target.value || '{}');
            } catch (err) {
                blocks[index].style = blocks[index].style || {};
            }
        } else if (field) {
            blocks[index][field] = e.target.value;
        }
        syncAll();
    });
    blocksList.addEventListener('click', (e) => {
        const action = e.target.dataset.action;
        const idx = e.target.dataset.index;
        if (!action || idx === undefined) return;
        const index = Number(idx);
        if (!blocks[index]) return;
        if (action === 'remove') {
            blocks.splice(index, 1);
            renderBlocks();
            return;
        }
        if (action === 'up' && index > 0) {
            const temp = blocks[index - 1];
            blocks[index - 1] = blocks[index];
            blocks[index] = temp;
            renderBlocks();
            return;
        }
        if (action === 'down' && index < blocks.length - 1) {
            const temp = blocks[index + 1];
            blocks[index + 1] = blocks[index];
            blocks[index] = temp;
            renderBlocks();
            return;
        }
        if (action === 'pick-image') {
            if (blocks[index].type !== 'image') return;
            if (window.phPopupPickImage && typeof window.phPopupPickImage === 'function') {
                window.phPopupPickImage().then(function (url) {
                    if (!url) return;
                    blocks[index].content = url;
                    renderBlocks();
                }).catch(function () {});
            } else {
                const existing = blocks[index].content || '';
                const url = window.prompt('Image URL', existing);
                if (!url) return;
                blocks[index].content = url;
                renderBlocks();
            }
        }
    });

    let dragIndex = null;
    blocksList.addEventListener('dragstart', (e) => {
        dragIndex = Number(e.target.dataset.index);
    });
    blocksList.addEventListener('dragover', (e) => {
        e.preventDefault();
    });
    blocksList.addEventListener('drop', (e) => {
        e.preventDefault();
        const targetIndex = Number(e.target.closest('[draggable="true"]')?.dataset.index);
        if (isNaN(dragIndex) || isNaN(targetIndex) || dragIndex === targetIndex) return;
        const item = blocks.splice(dragIndex, 1)[0];
        blocks.splice(targetIndex, 0, item);
        renderBlocks();
        dragIndex = null;
    });

    addWindowBtn.addEventListener('click', () => {
        const row = document.createElement('div');
        row.dataset.window = '1';
        row.className = 'flex items-center gap-2';
        row.innerHTML = `
            <input type="time" data-start class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white">
            <span class="text-slate-400">-</span>
            <input type="time" data-end class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white">
            <button type="button" data-remove class="px-2 py-1 rounded bg-rose-600/80 text-xs text-white">Remove</button>
        `;
        timeWindowsWrap.appendChild(row);
        syncAll();
    });

    timeWindowsWrap.addEventListener('click', (e) => {
        if (e.target.dataset.remove) {
            e.target.closest('[data-window]').remove();
            syncAll();
        }
    });

    document.querySelectorAll('#rules-include,#rules-exclude,#rules-frequency,#rules-interval,#rules-cities,#set-position,#set-animation,#set-overlay,#set-background,#set-text,#set-accent,#set-close-backdrop,#set-close-esc').forEach((el) => {
        el.addEventListener('input', syncAll);
        el.addEventListener('change', syncAll);
    });
    document.querySelectorAll('.rules-device,.rules-segment').forEach((el) => {
        el.addEventListener('change', syncAll);
    });

    renderBlocks();
    syncAll();
</script>
@endsection
