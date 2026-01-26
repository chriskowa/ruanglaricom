User wants to remove the Content Security Policy (CSP) entirely because it is causing blocking errors across the site.

**Plan:**
1.  **Remove CSP Meta Tag**: Delete the `<meta http-equiv="Content-Security-Policy" ...>` line from `resources/views/layouts/pacerhub.blade.php`.
2.  **Clear Cache**: Run `php artisan view:clear` to ensure the old cached view with the CSP header is removed.

This will revert the behavior to the default (no CSP), which allows all external resources to load without restriction.