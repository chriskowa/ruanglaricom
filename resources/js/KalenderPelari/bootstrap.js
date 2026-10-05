import './state/TrialStore.js';
import './ui/CanvasRenderer.js';
import './ui/Thumbnails.js';
import './ui/Toolbar.js';
import './ui/ElementProperties.js';
import './elements/Registry.js';

import { KpTrialStore } from './state/TrialStore.js';
import { KpCanvasRenderer } from './ui/CanvasRenderer.js';
import { KpThumbnails } from './ui/Thumbnails.js';
import { KpToolbar } from './ui/Toolbar.js';
import { KpElementProperties } from './ui/ElementProperties.js';
import { KpElementRegistry } from './elements/Registry.js';
import { KP_ELEMENT_DEFS } from './elements/Definitions.js';
import { KpDragAndDrop } from './interactions/DragAndDrop.js';
import { KpLocalStoragePersistence } from './state/LocalStoragePersistence.js';
import { KpServerAutosave } from './state/ServerAutosave.js';

export function createKpEditor(rootEl) {
    const projectData = JSON.parse(rootEl.dataset.project || '{}');
    const pagesData = JSON.parse(rootEl.dataset.pages || '[]');
    const yearMonths = JSON.parse(rootEl.dataset.yearMonths || '[]');
    const canvasMeta = JSON.parse(rootEl.dataset.canvas || '{}');
    const api = JSON.parse(rootEl.dataset.api || '{}');
    const cfg = JSON.parse(rootEl.dataset.config || '{}');

    const registry = new KpElementRegistry(KP_ELEMENT_DEFS);
    const storage = new KpLocalStoragePersistence({
        projectKey: `kp:trial:${projectData.anonymous_uuid || projectData.id}:v1`,
        maxAnonFiles: cfg.anon_max_files,
        maxTotalMB: cfg.anon_max_total_mb,
    });
    const autosave = new KpServerAutosave({
        endpoint: api.autosave,
        assetEndpoint: api.asset_mark,
        csrf: cfg.csrf,
        debounceMs: cfg.autosave_ms,
        isTrial: !!cfg.trial,
        anonymousUuid: projectData.anonymous_uuid,
        saveIndicator: document.getElementById('kp-save-indicator'),
    });

    const store = new KpTrialStore({
        project: projectData,
        pages: pagesData,
        yearMonths,
        canvasMeta,
        registry,
        storage,
        autosave,
    });

    const canvasEl = rootEl.querySelector('#kp-canvas-wrap');
    const viewportEl = rootEl.querySelector('#kp-canvas-viewport');
    const thumbsEl = rootEl.querySelector('#kp-thumbnails');
    const emptyProps = rootEl.querySelector('#kp-element-properties-empty');
    const propsPanel = rootEl.querySelector('#kp-element-properties');

    const renderer = new KpCanvasRenderer({
        store,
        rootEl,
        canvasEl,
        viewportEl,
        registry,
    });

    const thumbnails = new KpThumbnails({ store, thumbsEl, canvasMeta });
    const toolbar = new KpToolbar({ store, rootEl, renderer });
    const properties = new KpElementProperties({
        store,
        emptyEl: emptyProps,
        propsPanel,
        registry,
        api,
    });
    const dnd = new KpDragAndDrop({ store, renderer, canvasEl, rootEl });

    // Existing drafts take priority over the initial template.
    if (!store.hydrateFromStorage() && !cfg.has_saved_draft) store.initializeTemplatePages();

    // Initial render
    renderer.renderAll();
    thumbnails.render();
    toolbar.updateZoomLabel();
    requestAnimationFrame(() => renderer.fitToViewport());

    // Wire up add element buttons
    rootEl.querySelectorAll('[data-add-element]').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.addElement;
            store.addElementToCurrentPage(type);
        });
    });

    // Global delete key handler for selected element
    rootEl.addEventListener('keydown', (e) => {
        if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable)) {
            return;
        }
        if (e.key === 'Delete' || e.key === 'Backspace') {
            store.deleteSelected();
            e.preventDefault();
        }
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'z' && !e.shiftKey) {
            store.undo();
            e.preventDefault();
        }
        if (((e.metaKey || e.ctrlKey) && e.shiftKey && e.key.toLowerCase() === 'z')
            || ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'y')) {
            store.redo();
            e.preventDefault();
        }
    });
    rootEl.tabIndex = 0;
    setTimeout(() => rootEl.focus(), 50);

    return { store, renderer, thumbnails, toolbar, properties, dnd, registry, storage, autosave };
}
