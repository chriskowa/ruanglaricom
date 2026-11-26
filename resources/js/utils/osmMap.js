let leafletLoadPromise = null;

export function ensureLeafletLoaded() {
    if (typeof window !== 'undefined' && window.L) {
        return Promise.resolve(window.L);
    }
    if (leafletLoadPromise) {
        return leafletLoadPromise;
    }

    leafletLoadPromise = new Promise((resolve, reject) => {
        if (!document.querySelector('link[href*="leaflet.css"]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(link);
        }

        const existingScript = document.querySelector('script[src*="leaflet.js"]');
        if (existingScript) {
            if (window.L) {
                resolve(window.L);
                return;
            }
            existingScript.addEventListener('load', () => resolve(window.L), { once: true });
            existingScript.addEventListener('error', reject, { once: true });
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.async = true;
        script.onload = () => resolve(window.L);
        script.onerror = reject;
        document.head.appendChild(script);
    });

    return leafletLoadPromise;
}

export function getOsmTileConfig(theme = 'dark', osmStyle = 'osm') {
    if (theme === 'light') {
        if (osmStyle === 'carto_voyager') {
            return {
                url: 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
                options: { maxZoom: 20, subdomains: ['a', 'b', 'c', 'd'], attribution: '&copy; OpenStreetMap &copy; CARTO' },
            };
        }
        return {
            url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            options: { maxZoom: 19, subdomains: ['a', 'b', 'c'], attribution: '&copy; OpenStreetMap contributors' },
        };
    }

    return {
        url: 'https://{s}.basemaps.cartocdn.com/rastertiles/dark_all/{z}/{x}/{y}{r}.png',
        options: { maxZoom: 20, subdomains: ['a', 'b', 'c', 'd'], attribution: '&copy; OpenStreetMap &copy; CARTO' },
    };
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
