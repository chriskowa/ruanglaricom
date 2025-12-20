I will update `resources/views/calendar/index.blade.php` to implement the requested features:

1. **Add 3 New "Cool" Poster Styles:**

   * **Magazine:** Bold typography layout resembling a fitness magazine cover.

   * **Impact:** High-contrast, monochromatic background with neon stats, very aggressive and bold.

   * **Zen:** Minimalist, centered circular layout with elegant typography.

2. **Add "Transparent Chart" Feature:**

   * I will calculate a pace/elevation chart from the activity data (using `splits`).

   * Add a "Show Chart" toggle in the poster options.

   * Overlay this chart as a smooth SVG area graph on the poster (translucent fill).

3. **Mobile Optimization (Horizontal Scroll):**

   * Refactor the style selector container. On mobile, it will be a horizontal scrollable strip below the preview, ensuring the preview image remains the focus and isn't pushed off-screen.

4. **Loading Screen Animation:**

   * Implement a full-screen overlay with a pulsating logo/loader during the `html2canvas` generation and download process to provide better feedback.

**Implementation Steps:**

1. Modify Vue `data`: Add new styles, `showChart` toggle, and `isDownloading` state.
2. Update `shareActivityPoster`: Calculate SVG path for the chart based on splits.
3. Update HTML/Tailwind:

   * Implement CSS for the 3 new styles.

   * Add the SVG Chart overlay layer.

   * Restructure the Modal Sidebar for responsive design (horizontal on mobile, vertical on desktop).

   * Add the full-screen loader component.

