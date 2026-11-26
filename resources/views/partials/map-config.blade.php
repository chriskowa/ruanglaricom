@php
    $rlMapCfg = \App\Models\AppSettings::mapConfig();
    $rlMapClientCfg = [
        'provider' => $rlMapCfg['provider'],
        'osmStyle' => $rlMapCfg['osm_style'],
        'mapboxToken' => $rlMapCfg['mapbox_token'],
    ];
@endphp
<script>
(function () {
    const cfg = @json($rlMapClientCfg);

    window.RL_MAP_CONFIG = cfg;
    window.RL_MAPBOX_TOKEN = cfg.mapboxToken || '';

    function resolveOsmDefault() {
        if (cfg.osmStyle === 'carto_voyager') {
            return {
                url: 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
                options: {
                    maxZoom: 20,
                    subdomains: ['a', 'b', 'c', 'd'],
                    attribution: '&copy; OpenStreetMap &copy; CARTO',
                },
            };
        }
        if (cfg.osmStyle === 'carto_dark') {
            return {
                url: 'https://{s}.basemaps.cartocdn.com/rastertiles/dark_all/{z}/{x}/{y}{r}.png',
                options: {
                    maxZoom: 20,
                    subdomains: ['a', 'b', 'c', 'd'],
                    attribution: '&copy; OpenStreetMap &copy; CARTO',
                },
            };
        }
        return {
            url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            options: {
                maxZoom: 19,
                subdomains: ['a', 'b', 'c'],
                attribution: '&copy; OpenStreetMap contributors',
            },
        };
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
                url: 'https://api.mapbox.com/styles/v1/' + mapboxStyleId + '/tiles/{z}/{x}/{y}?access_token=' + cfg.mapboxToken,
                options: {
                    tileSize: 512,
                    zoomOffset: -1,
                    maxZoom: 19,
                    attribution: '&copy; Mapbox &copy; OpenStreetMap',
                },
            };
        }

        if (key === 'satellite') {
            return {
                url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                options: {
                    maxZoom: 19,
                    attribution: '&copy; Esri &copy; OpenStreetMap',
                },
            };
        }
        if (key === 'dark') {
            return {
                url: 'https://{s}.basemaps.cartocdn.com/rastertiles/dark_all/{z}/{x}/{y}{r}.png',
                options: {
                    maxZoom: 20,
                    subdomains: ['a', 'b', 'c', 'd'],
                    attribution: '&copy; OpenStreetMap &copy; CARTO',
                },
            };
        }
        if (key === 'outdoors' || key === 'carto') {
            return {
                url: 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
                options: {
                    maxZoom: 20,
                    subdomains: ['a', 'b', 'c', 'd'],
                    attribution: '&copy; OpenStreetMap &copy; CARTO',
                },
            };
        }
        if (key === 'osm') {
            return {
                url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                options: {
                    maxZoom: 19,
                    subdomains: ['a', 'b', 'c'],
                    attribution: '&copy; OpenStreetMap contributors',
                },
            };
        }

        return resolveOsmDefault();
    };

    window.rlCreateTileLayer = function (styleKey, extraOptions) {
        const def = window.rlGetTileDefinition(styleKey);
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
        const osmStd = window.rlCreateTileLayer('osm');
        const cartoVoyager = window.rlCreateTileLayer('carto');
        const satellite = window.rlCreateTileLayer('satellite');
        const dark = window.rlCreateTileLayer('dark');

        if (cfg.osmStyle === 'carto_voyager') {
            return {
                defaultLayer: primary,
                layers: {
                    'CARTO Voyager': primary,
                    'OpenStreetMap': osmStd,
                    'Satelit': satellite,
                    'Mode Gelap': dark,
                },
            };
        }

        if (cfg.osmStyle === 'carto_dark') {
            return {
                defaultLayer: primary,
                layers: {
                    'Mode Gelap': primary,
                    'OpenStreetMap': osmStd,
                    'CARTO Voyager': cartoVoyager,
                    'Satelit': satellite,
                },
            };
        }

        return {
            defaultLayer: primary,
            layers: {
                'OpenStreetMap': primary,
                'CARTO Voyager': cartoVoyager,
                'Satelit': satellite,
                'Mode Gelap': dark,
            },
        };
    };

    window.rlResolveTileUrl = function (z, x, y, styleKey) {
        const def = window.rlGetTileDefinition(styleKey || 'street');
        const subs = Array.isArray(def.options.subdomains)
            ? def.options.subdomains
            : ['a', 'b', 'c'];
        const sub = subs[Math.abs(parseInt(x, 10) + parseInt(y, 10)) % subs.length];
        return def.url
            .replace('{s}', sub)
            .replace('{z}', String(z))
            .replace('{x}', String(x))
            .replace('{y}', String(y))
            .replace('{r}', '');
    };
})();
</script>
