@php
    $rlMapCfg = \App\Models\AppSettings::mapConfig();
    $rlMapClientCfg = [
        'provider' => $rlMapCfg['provider'],
        'osmStyle' => $rlMapCfg['osm_style'],
        'mapboxToken' => $rlMapCfg['mapbox_token'],
    ];
    $rlNeedMaplibrePlugin = ($rlMapCfg['provider'] !== 'mapbox' || $rlMapCfg['mapbox_token'] === '') && !($rlDeferMaplibrePlugin ?? false);
@endphp
@if($rlNeedMaplibrePlugin)
<link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" />
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>
<script src="https://unpkg.com/@maplibre/maplibre-gl-leaflet@0.1.4/leaflet-maplibre-gl.js"></script>
@endif
<script>
(function () {
    const cfg = @json($rlMapClientCfg);

    window.RL_MAP_CONFIG = cfg;
    window.RL_MAPBOX_TOKEN = cfg.mapboxToken || '';

    const openFreeMapStyles = {
        liberty: 'https://tiles.openfreemap.org/styles/liberty',
        bright: 'https://tiles.openfreemap.org/styles/bright',
        positron: 'https://tiles.openfreemap.org/styles/positron',
        dark: 'https://tiles.openfreemap.org/styles/dark',
        fiord: 'https://tiles.openfreemap.org/styles/fiord',
    };

    const osmRasterDef = {
        type: 'raster',
        url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        options: {
            maxZoom: 19,
            subdomains: ['a', 'b', 'c'],
            attribution: '&copy; OpenStreetMap contributors',
        },
    };

    function createOpenFreeMapLayer(styleId, extraOptions) {
        if (typeof L !== 'undefined' && typeof L.maplibreGL === 'function' && openFreeMapStyles[styleId]) {
            return L.maplibreGL(Object.assign({
                style: openFreeMapStyles[styleId],
                attribution: '&copy; <a href="https://openfreemap.org" target="_blank" rel="noopener">OpenFreeMap</a> &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>',
            }, extraOptions || {}));
        }
        return L.tileLayer(osmRasterDef.url, Object.assign({}, osmRasterDef.options, extraOptions || {}));
    }

    window.rlGetTileDefinition = function (styleKey) {
        const key = String(styleKey || 'street').toLowerCase();
        const useMapbox = cfg.provider === 'mapbox' && Boolean(cfg.mapboxToken);

        if (useMapbox) {
            let mapboxStyleId = 'mapbox/outdoors-v12';
            if (key === 'satellite') {
                mapboxStyleId = 'mapbox/satellite-streets-v12';
            } else if (key === 'dark') {
                mapboxStyleId = 'mapbox/navigation-night-v1';
            } else if (key === 'streets') {
                mapboxStyleId = 'mapbox/streets-v12';
            }
            return {
                type: 'raster',
                url: 'https://api.mapbox.com/styles/v1/' + mapboxStyleId + '/tiles/{z}/{x}/{y}?access_token=' + cfg.mapboxToken,
                options: {
                    tileSize: 512,
                    zoomOffset: -1,
                    maxZoom: 19,
                    attribution: '&copy; Mapbox &copy; OpenStreetMap',
                },
            };
        }

        if (key === 'osm') {
            return osmRasterDef;
        }
        if (key === 'dark') {
            return { type: 'vector', styleId: 'dark' };
        }
        if (key === 'fiord' || key === 'satellite') {
            return { type: 'vector', styleId: 'fiord' };
        }
        if (key === 'bright' || key === 'outdoors') {
            return { type: 'vector', styleId: 'bright' };
        }
        if (key === 'positron' || key === 'light') {
            return { type: 'vector', styleId: 'positron' };
        }
        if (key === 'liberty') {
            return { type: 'vector', styleId: 'liberty' };
        }

        if (cfg.osmStyle === 'osm') {
            return osmRasterDef;
        }
        return {
            type: 'vector',
            styleId: openFreeMapStyles[cfg.osmStyle] ? cfg.osmStyle : 'liberty',
        };
    };

    window.rlCreateTileLayer = function (styleKey, extraOptions) {
        const def = window.rlGetTileDefinition(styleKey);
        if (def.type === 'vector') {
            return createOpenFreeMapLayer(def.styleId, extraOptions);
        }
        return L.tileLayer(def.url, Object.assign({}, def.options, extraOptions || {}));
    };

    window.rlGetBaseLayers = function () {
        if (cfg.provider === 'mapbox' && cfg.mapboxToken) {
            const outdoors = window.rlCreateTileLayer('outdoors');
            const satellite = window.rlCreateTileLayer('satellite');
            const dark = window.rlCreateTileLayer('dark');
            return {
                defaultLayer: outdoors,
                layers: {
                    'Peta Lari (Outdoors)': outdoors,
                    'Satelit': satellite,
                    'Mode Gelap': dark,
                },
            };
        }

        const primary = window.rlCreateTileLayer('street');
        const catalog = [
            { id: 'liberty', label: 'OpenFreeMap Liberty' },
            { id: 'bright', label: 'OpenFreeMap Bright' },
            { id: 'positron', label: 'OpenFreeMap Positron' },
            { id: 'dark', label: 'OpenFreeMap Dark' },
            { id: 'fiord', label: 'OpenFreeMap Fiord' },
            { id: 'osm', label: 'OSM Standard' },
        ];

        const layers = {};
        const activeId = (cfg.osmStyle === 'osm' || openFreeMapStyles[cfg.osmStyle]) ? cfg.osmStyle : 'liberty';
        const activeMeta = catalog.find((item) => item.id === activeId) || catalog[0];
        layers[activeMeta.label] = primary;

        catalog.forEach((item) => {
            if (item.id !== activeId) {
                layers[item.label] = window.rlCreateTileLayer(item.id);
            }
        });

        return {
            defaultLayer: primary,
            layers: layers,
        };
    };

    window.rlResolveTileUrl = function (z, x, y, styleKey) {
        const def = window.rlGetTileDefinition(styleKey || 'street');
        const target = def.type === 'raster' ? def : osmRasterDef;
        const subs = Array.isArray(target.options.subdomains)
            ? target.options.subdomains
            : ['a', 'b', 'c'];
        const sub = subs[Math.abs(parseInt(x, 10) + parseInt(y, 10)) % subs.length];
        return target.url
            .replace('{s}', sub)
            .replace('{z}', String(z))
            .replace('{x}', String(x))
            .replace('{y}', String(y))
            .replace('{r}', '');
    };
})();
</script>
