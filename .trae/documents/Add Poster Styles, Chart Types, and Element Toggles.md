I will update `resources/views/calendar/index.blade.php` to implement the advanced poster customization features:

1. **Add 2 New "Cool & Modern" Styles:**

   * **Cyber:** A futuristic, dark-grid design with neon borders and tech-inspired fonts.

   * **Elegant:** A high-end, clean serif typography layout with a glassmorphism frame.

2. **Implement "Topping" System (Toggleable Elements):**

   * I will refactor the visibility logic so users can manually toggle specific elements: **Title, Stats Grid, Map, Splits, Profile, and Chart**.

   * Selecting a "Style" will set default presets, but users can customize further.

3. **Advanced Chart Options:**

   * **Chart Types:** Allow users to switch the chart between **Pace, Heart Rate, and Elevation**.

   * **Data Processing:** I will update the logic to extract heart rate and elevation profiles from the activity splits.

   * **Visuals:** I will make the chart overlay clearer (increased opacity) and color-coded (Green for Pace, Red for HR, Blue for Elev).

**Implementation Steps:**

1. **Vue Data Update:** Add `posterOptions` (visibility flags), `chartType`, and new styles.
2. **Logic Update:** Enhance `shareActivityPoster` to process Elevation and HR data arrays from splits. Add a watcher or method to regenerate the SVG path when `chartType` changes.
3. **UI Update (Sidebar):** Add controls for "Chart Type" and a grid of toggles for "Visible Elements".
4. **Poster Template Update:** Apply the new styles and bind element visibility to the new toggle flags.

