export class KpThumbnails {
    constructor({ store, thumbsEl, canvasMeta }) {
        this.store = store;
        this.thumbsEl = thumbsEl;
        this.canvasMeta = canvasMeta;

        this.store.addEventListener('active-page-changed', () => this.render());
        this.store.addEventListener('changed', () => this.render());
        this.store.addEventListener('history', () => this.render());
    }

    render() {
        const pages = this.store.pages;
        const active = this.store.activePageMonthNumber;
        this.thumbsEl.innerHTML = '';
        for (const p of pages) {
            const card = document.createElement('button');
            card.type = 'button';
            card.className = `kp-thumb${p.month_number === active ? ' active' : ''}`;
            card.dataset.monthNumber = String(p.month_number);
            card.title = p.month_label;
            card.setAttribute('aria-label', `Buka halaman ${this._label(p)}`);
            card.setAttribute('aria-pressed', String(p.month_number === active));
            const label = document.createElement('span');
            label.className = 'kp-thumb-label';
            label.textContent = this._label(p);
            card.appendChild(label);
            card.addEventListener('click', () => {
                this.store.setActivePage(p.month_number);
                this.render();
            });
            this.thumbsEl.appendChild(card);
        }
    }

    _label(p) {
        if (p.page_type === 'COVER') return 'Cover';
        if (p.page_type === 'YEAR_REVIEW') return 'Ringkasan';
        const map = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        const m = Number(p.month_number);
        if (m >= 1 && m <= 12) return map[m - 1];
        return `#${m}`;
    }
}
