const PX_PER_MM = 3.7795275591;
const SNAP_MM = 2;
const MM_THRESHOLD_SNAP = 0.8;

export class KpDragAndDrop {
    constructor({ store, renderer, canvasEl, rootEl }) {
        this.store = store;
        this.renderer = renderer;
        this.canvasEl = canvasEl;
        this.rootEl = rootEl;

        this.active = null;

        rootEl.addEventListener('pointerdown', (ev) => this.onDown(ev));
        document.addEventListener('pointermove', (ev) => this.onMove(ev));
        document.addEventListener('pointerup', (ev) => this.onUp(ev));
        document.addEventListener('pointercancel', () => this.onUp());
    }

    onDown(ev) {
        const handle = ev.target.closest?.('.kp-element-resize-handle');
        const elementEl = ev.target.closest?.('.kp-element');
        if (!elementEl) return;
        const elementId = elementEl.dataset.elementId;
        if (!elementId) return;
        const elementModel = this.store.findElement(this.store.activePageMonthNumber, elementId);
        if (!elementModel) return;
        if (elementModel.locked) return;
        this.store.selectElement(elementId);

        const rect = this.canvasEl.getBoundingClientRect();
        const pageEl = elementEl.parentElement;
        const pageRect = pageEl.getBoundingClientRect();

        this.active = handle
            ? this._beginResize(ev, elementEl, elementModel, handle, { rect, pageRect })
            : this._beginMove(ev, elementEl, elementModel, { rect, pageRect });

        if (this.active?.ghost) {
            this.active.page.appendChild(this.active.ghost);
        }
        try { elementEl.setPointerCapture?.(ev.pointerId); } catch {}
    }

    _beginMove(ev, domEl, model, { rect, pageRect }) {
        const ghost = document.createElement('div');
        ghost.className = 'kp-drag-ghost';
        const ghostWidth = domEl.offsetWidth;
        const ghostHeight = domEl.offsetHeight;
        ghost.style.width = `${ghostWidth}px`;
        ghost.style.height = `${ghostHeight}px`;
        const page = domEl.parentElement;
        ghost.style.left = `${domEl.offsetLeft}px`;
        ghost.style.top = `${domEl.offsetTop}px`;
        ghost.style.transform = domEl.style.transform || '';
        const startX = ev.clientX;
        const startY = ev.clientY;
        const origX = model.x_mm || 0;
        const origY = model.y_mm || 0;
        return {
            mode: 'move',
            pointerId: ev.pointerId,
            elementId: model.id,
            domEl,
            page,
            ghost,
            ghostWidth,
            ghostHeight,
            startX, startY,
            origX, origY,
        };
    }

    _beginResize(ev, domEl, model, handle, { rect, pageRect }) {
        const ghost = document.createElement('div');
        ghost.className = 'kp-drag-ghost';
        ghost.style.width = `${domEl.offsetWidth}px`;
        ghost.style.height = `${domEl.offsetHeight}px`;
        ghost.style.left = `${domEl.offsetLeft}px`;
        ghost.style.top = `${domEl.offsetTop}px`;
        const page = domEl.parentElement;
        const dir = handle.dataset.handleDir;
        const startX = ev.clientX;
        const startY = ev.clientY;
        const orig = {
            x_mm: model.x_mm || 0,
            y_mm: model.y_mm || 0,
            w_mm: model.width_mm || 0,
            h_mm: model.height_mm || 0,
        };
        return {
            mode: 'resize',
            dir,
            pointerId: ev.pointerId,
            elementId: model.id,
            domEl,
            page,
            ghost,
            startX, startY,
            orig,
        };
    }

    onMove(ev) {
        const a = this.active;
        if (!a) return;
        const dxPx = ev.clientX - a.startX;
        const dyPx = ev.clientY - a.startY;

        if (a.mode === 'move') {
            const dxMm = dxPx / (PX_PER_MM * this.store.zoom);
            const dyMm = dyPx / (PX_PER_MM * this.store.zoom);
            let xMm = a.origX + dxMm;
            let yMm = a.origY + dyMm;
            const page = this.store.getActivePage();
            xMm = Math.max(0, Math.min(Number(page.canvas_width_mm) - a.ghostWidth / PX_PER_MM, Math.round(xMm / MM_THRESHOLD_SNAP) * MM_THRESHOLD_SNAP));
            yMm = Math.max(0, Math.min(Number(page.canvas_height_mm) - a.ghostHeight / PX_PER_MM, Math.round(yMm / MM_THRESHOLD_SNAP) * MM_THRESHOLD_SNAP));
            const leftPx = xMm * PX_PER_MM;
            const topPx = yMm * PX_PER_MM;
            a.ghost.style.transform = `translate(${leftPx - a.ghost.offsetLeft}px, ${topPx - a.ghost.offsetTop}px)`;
            a._pendingX = xMm;
            a._pendingY = yMm;
        } else if (a.mode === 'resize') {
            const d = a.dir;
            const { orig } = a;
            let xMm = orig.x_mm;
            let yMm = orig.y_mm;
            let wMm = orig.w_mm;
            let hMm = orig.h_mm;
            const dxMm = dxPx / (PX_PER_MM * this.store.zoom);
            const dyMm = dyPx / (PX_PER_MM * this.store.zoom);
            if (d.includes('e')) wMm = Math.max(5, roundMm(orig.w_mm + dxMm));
            if (d.includes('s')) hMm = Math.max(5, roundMm(orig.h_mm + dyMm));
            if (d.includes('w')) {
                const newW = Math.max(5, roundMm(orig.w_mm - dxMm));
                xMm = roundMm(orig.x_mm + (orig.w_mm - newW));
                wMm = newW;
            }
            if (d.includes('n')) {
                const newH = Math.max(5, roundMm(orig.h_mm - dyMm));
                yMm = roundMm(orig.y_mm + (orig.h_mm - newH));
                hMm = newH;
            }
            a._pending = { x_mm: xMm, y_mm: yMm, width_mm: wMm, height_mm: hMm };
            const lPx = xMm * PX_PER_MM;
            const tPx = yMm * PX_PER_MM;
            const wPx = wMm * PX_PER_MM;
            const hPx = hMm * PX_PER_MM;
            a.ghost.style.left = `${lPx}px`;
            a.ghost.style.top = `${tPx}px`;
            a.ghost.style.width = `${wPx}px`;
            a.ghost.style.height = `${hPx}px`;
        }
    }

    onUp(ev) {
        const a = this.active;
        if (!a) return;
        if (a.mode === 'move') {
            if (a._pendingX != null || a._pendingY != null) {
                this.store.updateElement(a.elementId, {
                    x_mm: a._pendingX ?? a.origX,
                    y_mm: a._pendingY ?? a.origY,
                }, { msg: 'move' });
            }
        } else if (a.mode === 'resize') {
            if (a._pending) {
                this.store.updateElement(a.elementId, a._pending, { msg: `resize-${a.dir}` });
            }
        }
        if (a?.ghost?.parentElement) a.ghost.parentElement.removeChild(a.ghost);
        this.active = null;
    }
}

function roundMm(v) {
    return Math.round(v / 0.5) * 0.5;
}
