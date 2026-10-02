export class KpServerAutosave {
    constructor({ endpoint, assetEndpoint, csrf, debounceMs, isTrial, anonymousUuid, saveIndicator }) {
        this.endpoint = endpoint;
        this.assetEndpoint = assetEndpoint;
        this.csrf = csrf;
        this.debounceMs = debounceMs;
        this.isTrial = isTrial;
        this.anonymousUuid = anonymousUuid;
        this.saveIndicator = saveIndicator;

        this._timer = null;
        this._pendingPayload = null;
        this._inFlight = false;
        this._retry = 0;
        this._maxRetry = 2;
    }

    _setStatus(text, tone = 'muted') {
        if (!this.saveIndicator) return;
        this.saveIndicator.textContent = text;
        this.saveIndicator.className = '';
        if (tone === 'muted') this.saveIndicator.classList.add('text-slate-400');
        if (tone === 'warn') this.saveIndicator.classList.add('text-amber-300');
        if (tone === 'ok') this.saveIndicator.classList.add('text-neon');
        if (tone === 'err') this.saveIndicator.classList.add('text-red-400');
    }

    schedule(payload) {
        this._pendingPayload = payload;
        clearTimeout(this._timer);
        this._setStatus('Menyimpan...', 'warn');
        this._timer = setTimeout(() => this._flush(), this.debounceMs);
    }

    scheduleImmediate(payload) {
        this._pendingPayload = payload;
        clearTimeout(this._timer);
        this._timer = setTimeout(() => this._flush(), 120);
    }

    async _encodePayload(payload) {
        const json = JSON.stringify(payload);
        if (json.length < 12000 || typeof CompressionStream === 'undefined') return json;
        const stream = new Blob([JSON.stringify(payload.state_draft)]).stream().pipeThrough(new CompressionStream('gzip'));
        const bytes = new Uint8Array(await new Response(stream).arrayBuffer());
        let binary = '';
        for (let i = 0; i < bytes.length; i += 8192) {
            binary += String.fromCharCode(...bytes.subarray(i, i + 8192));
        }
        const { state_draft, ...rest } = payload;
        return JSON.stringify({ ...rest, state_draft_gzip: btoa(binary) });
    }

    async _flush() {
        if (!this._pendingPayload) return;
        if (this._inFlight) {
            clearTimeout(this._timer);
            this._timer = setTimeout(() => this._flush(), 500);
            return;
        }
        const payload = this._pendingPayload;
        this._pendingPayload = null;
        this._inFlight = true;
        try {
            const body = await this._encodePayload(payload);
            const res = await fetch(this.endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body,
            });
            if (res.status === 419) {
                this._setStatus('Sesi habis. Silakan login untuk simpan permanen.', 'err');
                return;
            }
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}`);
            }
            const json = await res.json();
            if (!json.success) {
                throw new Error(json.code || 'server error');
            }
            this._retry = 0;
            const t = new Date();
            this._setStatus(`Tersimpan ${t.toLocaleTimeString('id-ID')}`, 'ok');
            if (json.project?.version_counter && payload?.project) {
                // no-op: trial mode versioning is client-led
            }
        } catch (e) {
            if (this._retry < this._maxRetry) {
                this._retry++;
                this._setStatus(`Gagal simpan. Coba lagi (${this._retry}/${this._maxRetry})...`, 'warn');
                this._pendingPayload = payload;
                this._timer = setTimeout(() => this._flush(), 1500 * this._retry);
            } else {
                this._setStatus('Gagal simpan (offline). Data tetap tersimpan lokal.', 'err');
                this._retry = 0;
            }
        } finally {
            this._inFlight = false;
            if (this._pendingPayload && !this._timer) {
                this._timer = setTimeout(() => this._flush(), 800);
            }
        }
    }
}
