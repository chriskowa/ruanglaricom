I will implement a "Champions Podium" section that is interactive and visually striking. This involves adding prize data to the database and creating a dynamic frontend component.

### **1. Backend Implementation**
- **Database Schema**:
  - Add a JSON column `prizes` to the `race_categories` table.
  - This allows flexible prize structures (e.g., Money + Goods, Top 3 or Top 5).
- **Model Update**:
  - Update `RaceCategory.php` to cast `prizes` as an array.

### **2. Frontend Design: "The Champions Podium"**
I will create a new section that features:
- **Interactive Tabs**: Users can switch between race categories (e.g., 5K, 10K) to see specific prizes.
- **Animated Podium**:
  - **1st Place (Center)**: Largest card, Gold theme, glowing effect, "Crown" icon.
  - **2nd Place (Left)**: Silver theme.
  - **3rd Place (Right)**: Bronze theme.
- **Motion Effects**:
  - **Scroll Reveal**: The podium pillars will animate upwards when the user scrolls to the section.
  - **Hover Interaction**: Cards will "float" and glow when hovered.

### **3. Implementation Steps**
1.  **Create Migration**: Add `prizes` column to `race_categories`.
2.  **Seed Dummy Data**: Populate existing categories with sample prize data (e.g., "Rp 5.000.000") so you can see the UI immediately.
3.  **Create Blade Component**: Build `resources/views/events/partials/prizes-section.blade.php`.
4.  **Integrate**: Add this section to your active themes (e.g., `modern-dark`, `light-clean`).

### **4. Verification**
- I will verify the section renders correctly on both Mobile and Desktop.
- I will ensure the animations trigger smoothly.
