I will enhance the navigation bar contrast by making the text white when it's over the hero section (transparent background) and switching to dark text when the user scrolls down (white background).

**Implementation Plan:**
1.  **Modify `paolo-fest.blade.php` HTML**:
    -   Update navigation links to use `text-white/90` by default (instead of `text-slate-600`).
    -   Update the logo text to `text-white`.
    -   Update the mobile menu button to `text-white`.
    -   Add marker classes (`nav-link`, `nav-brand`, `mobile-toggle`) to these elements to allow CSS targeting.

2.  **Modify CSS (`<style>` block)**:
    -   Add rules for the `.nav-scrolled` state to override the text colors back to dark slate/brand colors.
    -   This ensures that when the background turns white (on scroll), the text becomes readable again.

This approach is highly performant as it relies on simple CSS class switching triggered by the existing scroll listener.