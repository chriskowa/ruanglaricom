I will implement the comprehensive Nominatim CORS proxy strategy across the requested theme files.

**Plan:**

1.  **Inject Proxy Script:** I will add the JavaScript interceptor (for both `fetch` and `XMLHttpRequest`) to the following files, just before the closing `</body>` tag or inside the main script block:
    *   `resources/views/events/themes/light-clean.blade.php`
    *   `resources/views/events/themes/modern-dark.blade.php`
    *   `resources/views/events/themes/professional-city-run.blade.php`
    *   `resources/views/events/latbar3.blade.php`

**Script Logic:**
The script will override `window.fetch` and `XMLHttpRequest.prototype.open` to check if a URL targets `nominatim.openstreetmap.org`. If it does, it will redirect the request through your server's `/image-proxy` endpoint.

**Note:** The Service Worker (`sw.js`) was already updated in the previous step, so it will automatically handle background requests for these themes as well once they are loaded.

**Files to be modified:**
*   `c:\laragon\www\ruanglari\resources\views\events\themes\light-clean.blade.php`
*   `c:\laragon\www\ruanglari\resources\views\events\themes\modern-dark.blade.php`
*   `c:\laragon\www\ruanglari\resources\views\events\themes\professional-city-run.blade.php`
*   `c:\laragon\www\ruanglari\resources\views\events\latbar3.blade.php`
