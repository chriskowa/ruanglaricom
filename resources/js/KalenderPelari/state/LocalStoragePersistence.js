export class KpLocalStoragePersistence {
    constructor({ projectKey, maxAnonFiles = 8, maxTotalMB = 20 } = {}) {
        this.projectKeyTemplate = projectKey;
        this.maxAnonFiles = maxAnonFiles;
        this.maxTotalBytes = maxTotalMB * 1024 * 1024;
        this._memoryFileRefs = new Map(); // clientRef -> { url, size, name, type }
        this._projectScope = null;
    }

    _key(scope) {
        if (scope) return `${this.projectKeyTemplate.replace(/:.*?:/, '')}:${scope}`.replace('::', ':');
        return this.projectKeyTemplate;
    }

    persistImmediate(snapshot, scope) {
        const key = this._key(scope);
        try {
            const json = JSON.stringify(snapshot);
            let payload = json;
            if (typeof CompressionStream !== 'undefined' && json.length > 200 * 1024) {
                this._compress(json).then(compressed => {
                    try { localStorage.setItem(`${key}__gzip`, btoa(compressed)); } catch {}
                }).catch(() => {});
            }
            try { localStorage.setItem(key, payload); } catch (e) {
                console.warn('KP localStorage full, falling back to sessionStorage', e);
                try { sessionStorage.setItem(key, payload); } catch {}
            }
        } catch (e) {
            console.warn('KP persist failed', e);
        }
    }

    async _compress(txt) {
        const enc = new TextEncoder().encode(txt);
        const cs = new CompressionStream('gzip');
        const writer = cs.writable.getWriter();
        writer.write(enc);
        writer.close();
        const out = await new Response(cs.readable).arrayBuffer();
        return String.fromCharCode(...new Uint8Array(out));
    }

    load(scope) {
        const key = this._key(scope);
        try {
            const raw = localStorage.getItem(key) || sessionStorage.getItem(key);
            if (!raw) return null;
            return JSON.parse(raw);
        } catch (e) {
            console.warn('KP load failed', e);
            return null;
        }
    }

    acceptFile(file, scope) {
        const clientRef = `local_${Math.random().toString(36).slice(2, 10)}_${Date.now().toString(36)}`;
        const size = file.size || 0;
        const currentTotal = [...this._memoryFileRefs.values()].reduce((s, x) => s + (x.size || 0), 0);
        if (this._memoryFileRefs.size + 1 > this.maxAnonFiles) return false;
        if (currentTotal + size > this.maxTotalBytes) return false;
        const objectUrl = URL.createObjectURL(file);
        this._memoryFileRefs.set(clientRef, { url: objectUrl, size, name: file.name, type: file.type });
        file.clientRef = clientRef;
        return true;
    }

    resolveRef(clientRef) {
        return this._memoryFileRefs.get(clientRef) || null;
    }

    cleanAllFor(scope) {
        const key = this._key(scope);
        localStorage.removeItem(key);
        localStorage.removeItem(`${key}__gzip`);
        sessionStorage.removeItem(key);
    }

    revokeAll() {
        for (const { url } of this._memoryFileRefs.values()) {
            try { URL.revokeObjectURL(url); } catch {}
        }
        this._memoryFileRefs.clear();
    }
}
