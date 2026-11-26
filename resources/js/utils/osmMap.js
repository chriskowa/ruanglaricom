let leafletLoadPromise = null;

const OPENFREEMAP_STYLES = {
    liberty: 'https://tiles.openfreemap.org/styles/liberty',
    bright: 'https://tiles.openfreemap.org/styles/bright',
    positron: 'https://tiles.openfreemap.org/styles/positron',
    dark: 'https://tiles.openfreemap.org/styles/dark',
    fiord: 'https://tiles.openfreemap.org/styles/fiord',
};

function loadScriptOnce(src) {
    return new Promise((resolve, reject) => {
        const existing = document.querySelector(`script[src="${src}"]`);
        if (existing) {
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

function ensureCssOnce(href) {
    if (!document.querySelector(`link[href="${href}"]`)) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        document.head.appendChild(link);
    }
}

export function ensureLeafletLoaded() {
    if (typeof window !== 'undefined' && window.L && typeof window.L.maplibreGL === 'function') {
        return Promise.resolve(window.L);
    }
    if (leafletLoadPromise) {
        return leafletLoadPromise;
    }

    ensureCssOnce('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
    ensureCssOnce('https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css');

    leafletLoadPromise = loadScriptOnce('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js')
        .then(() => loadScriptOnce('https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js'))
        .then(() => loadScriptOnce('https://unpkg.com/@maplibre/maplibre-gl-leaflet@0.1.4/leaflet-maplibre-gl.js'))
        .catch((err) => {
            console.warn('MapLibre GL Leaflet load fallback to OSM raster:', err);
        })
        .then(() => window.L);

    return leafletLoadPromise;
}

export function createOsmLayer(L, theme = 'dark', osmStyle = 'liberty') {
    const normalized = String(osmStyle || 'liberty').toLowerCase();
    let styleId = OPENFREEMAP_STYLES[normalized] ? normalized : 'liberty';

    if (theme === 'dark' && normalized !== 'fiord' && normalized !== 'osm') {
        styleId = 'dark';
    } else if (theme === 'light' && (normalized === 'dark' || normalized === 'fiord')) {
        styleId = 'liberty';
    }

    if (normalized !== 'osm' && L && typeof L.maplibreGL === 'function' && OPENFREEMAP_STYLES[styleId]) {
        return L.maplibreGL({
            style: OPENFREEMAP_STYLES[styleId],
            attribution: '&copy; OpenFreeMap &copy; OpenStreetMap contributors',
        });
    }

    return L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        subdomains: ['a', 'b', 'c'],
        attribution: '&copy; OpenStreetMap contributors',
    });
}

export async function searchNominatim(query) {
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=5`;
    const response = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!response.ok) throw new Error('Nominatim geocoding failed');
    const data = await response.json();
    if (!Array.isArray(data)) return [];
    return data.map((item) => ({
        id: String(item.place_id || `${item.lat},${item.lon}`),
        text: String(item.display_name || '').split(',')[0] || item.display_name,
        place_name: item.display_name,
        center: [parseFloat(item.lon), parseFloat(item.lat)],
    }));
}
