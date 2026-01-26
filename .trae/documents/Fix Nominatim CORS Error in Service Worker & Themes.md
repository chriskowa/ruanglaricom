I will fix the Nominatim CORS error by implementing a multi-layer proxy strategy to ensure all requests are routed through your server's `/image-proxy` endpoint.

**Diagnosis:**
The error "related to sw:js" indicates the Service Worker is attempting to fetch data directly from `nominatim.openstreetmap.org`, which is blocked by CORS. This happens because:

1. The existing `fetch` interceptor in the Blade template only catches `fetch()` calls on the main thread.
2. It misses requests made via `XMLHttpRequest` (XHR) or requests that bypass the main thread (handled by Service Worker).

**Plan:**

1. **Update** **`sw.js`** **(Service Worker):**

   * Add logic to intercept any request to `nominatim.openstreetmap.org`.

   * Rewrite these requests to use `/image-proxy?url=...` before they hit the network.

   * This is the most robust fix as it catches *everything* the Service Worker handles.

2. **Update Theme Files (`paolo-fest.blade.php`** **&** **`paolo-fest-dark.blade.php`):**

   * Enhance the existing interceptor to also patch `XMLHttpRequest`.

   * This covers libraries (like older Leaflet plugins) that might prefer XHR over Fetch.

**Files to be modified:**

* `public/sw.js`

* `resources/views/events/themes/paolo-fest.blade.php`

* `resources/views/events/themes/paolo-fest-dark.blade.php`

