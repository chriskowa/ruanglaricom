export class KpToolbar {
    constructor({ store, rootEl, renderer }) {
        this.store = store;
        this.rootEl = rootEl;

        this.undoBtn = rootEl.querySelector('#kp-tool-undo');
        this.redoBtn = rootEl.querySelector('#kp-tool-redo');
        this.zoomOutBtn = rootEl.querySelector('#kp-tool-zoom-out');
        this.zoomInBtn = rootEl.querySelector('#kp-tool-zoom-in');
        this.fitBtn = rootEl.querySelector('#kp-tool-fit');
        this.zoomLabel = rootEl.querySelector('#kp-zoom-label');

        this.store.addEventListener('history', () => this.refreshHistoryButtons());
        this.store.addEventListener('changed', () => this.refreshHistoryButtons());
        this.store.addEventListener('zoom', ({ detail: { zoom } }) => this.updateZoomLabel());

        this.undoBtn?.addEventListener('click', () => this.store.undo());
        this.redoBtn?.addEventListener('click', () => this.store.redo());
        this.zoomOutBtn?.addEventListener('click', () => this.store.setZoom(this.store.zoom - 0.1));
        this.zoomInBtn?.addEventListener('click', () => this.store.setZoom(this.store.zoom + 0.1));
        this.fitBtn?.addEventListener('click', () => renderer.fitToViewport());

        const vp = rootEl.querySelector('#kp-canvas-viewport');
        if (vp) {
            vp.addEventListener('wheel', (e) => {
                if (!e.ctrlKey && !e.metaKey) return;
                e.preventDefault();
                const delta = e.deltaY < 0 ? +0.1 : -0.1;
                this.store.setZoom(this.store.zoom + delta);
            }, { passive: false });
        }

        const nameEl = rootEl.querySelector('#kp-project-name');
        if (nameEl) {
            nameEl.style.cursor = 'text';
            nameEl.addEventListener('dblclick', () => {
                const cur = this.store.project.name;
                const next = prompt('Nama proyek:', cur);
                if (next != null) this.store.setProjectName(next.trim());
            });
            nameEl.title = 'Klik 2x untuk ganti nama';
        }
    }

    updateZoomLabel() {
        if (this.zoomLabel) this.zoomLabel.textContent = `${Math.round(this.store.zoom * 100)}%`;
    }

    refreshHistoryButtons() {
        if (this.undoBtn) {
            this.undoBtn.disabled = this.store.history.length === 0;
            this.undoBtn.title = this.undoBtn.disabled ? 'Belum ada perubahan untuk dibatalkan' : 'Batalkan perubahan';
        }
        if (this.redoBtn) {
            this.redoBtn.disabled = this.store.future.length === 0;
            this.redoBtn.title = this.redoBtn.disabled ? 'Belum ada perubahan untuk diulangi' : 'Ulangi perubahan';
        }
    }
}
