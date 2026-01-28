I will implement the requested changes to the "Manage Event" and Registration process.

### **Plan: Enhanced Event Management & Registration**

**1. Database Migrations**
*   Create a migration to add `early_bird_quota` (int) and `early_bird_end_at` (datetime) to the `race_categories` table.
*   Create a migration to add `date_of_birth` (date) and `price_type` (string, for tracking pricing tier) to the `participants` table.

**2. Backend Logic (Models & Actions)**
*   **Update `RaceCategory` Model:** Add new fields to `$fillable`.
*   **Update `Participant` Model:** Add new fields to `$fillable`.
*   **Update `StoreRegistrationAction.php`:**
    *   Implement `date_of_birth` validation.
    *   Refactor `getCategoryPrice` to enforce Early Bird rules:
        *   Check if `now() <= early_bird_end_at`.
        *   Check if `early_bird_sold_count < early_bird_quota` (using the new `price_type` column to count).
        *   If valid, assign `early` price and set `price_type = 'early'`.
        *   Otherwise, fallback to `regular` (or `late` if applicable).
    *   Save `date_of_birth` and `price_type` during participant creation.

**3. EO Dashboard (Event Management)**
*   **Categories Form:** Modify `resources/views/eo/events/steps/categories.blade.php` (or equivalent) to:
    *   Add inputs for "Early Bird Quota" and "Early Bird End Date" under the Early Price field.
    *   Make the "Prizes" (Hadiah Juara) section dynamic, allowing the EO to add/remove rows for Winner 1, Winner 2, etc. (up to 10+).

**4. Frontend (Registration Themes)**
*   **Target Themes:** `paolo-fest`, `modern-dark`, `simple-minimal`, `professional-city-run`, `light-clean`.
*   **Registration Form:**
    *   Add a "Tanggal Lahir" (Date of Birth) input field (`type="date"`).
*   **Price Logic (Blade & JS):**
    *   Pass `early_bird_end_at`, `early_bird_quota`, and `early_bird_sold` to the frontend.
    *   Update the JavaScript price calculator to strictly follow the backend rules (disable Early Bird if expired or sold out).

**5. Verification**
*   Verify that saving an event with dynamic prizes works.
*   Verify that the "Early Bird" price is only applied when valid.
*   Verify that "Date of Birth" is saved correctly.

I will proceed in the optimal order: Database -> Backend Logic -> EO UI -> Frontend UI.