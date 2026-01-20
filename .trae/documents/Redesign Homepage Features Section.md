I will redesign the homepage (`resources/views/home/index.blade.php`) to introduce the Ruang Lari features using a high-quality "Expandable Card" (Accordion) UI.

**Implementation Details:**
1.  **Location**: Insert the new section immediately after the **Brands Section** and before the **Events Section**.
2.  **Design**:
    *   **Layout**: 4-Column Grid (responsive).
    *   **Style**: Dark Pacerhub theme (Slate-900 backgrounds, Neon accents, smooth transitions).
    *   **Interaction**: Cards will use Alpine.js (`x-data`) to toggle an accordion list of sub-features when clicked.
3.  **Content & Structure**:
    *   **Card 1: Running Tools & Performance** (Running Tips)
        *   *Items*: Kalender Lari, Pace Calculator, Running Program, Coach Marketplace.
    *   **Card 2: Community & Network** (Running Connect)
        *   *Items*: Runner, Pacers, Community, Events, Collaborations.
    *   **Card 3: Event Solutions** (Portal Registrasi)
        *   *Items*: Registration System, Analytics, Secured Payment.
    *   **Card 4: Gear Marketplace** (Runners' Kit)
        *   *Items*: Konsinasi & Jual Beli Gear.

**File to Modify:**
*   `resources/views/home/index.blade.php`

I will use existing routes (like `/calculator`, `/calendar`, `/programs`, `/coaches`) for the links. For items without specific routes, I will use placeholders or relevant section links.