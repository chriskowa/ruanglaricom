I have analyzed the codebase and identified that the Coupon/Promo Code functionality is currently implemented in the `modern-dark` theme but missing in `simple-minimal`, `professional-city-run`, and `light-clean` themes.

I will proceed to implement the coupon module across all remaining themes to ensure consistent functionality.

### **Plan: Integrate Master Coupon into All Event Themes**

**1. Theme: Simple Minimal (`simple-minimal.blade.php`)**
-   **UI Changes:**
    -   Insert the "Kode Promo" input field and "Pakai" button inside the registration form sidebar, just above the "Ringkasan Biaya" (Summary) section.
    -   Add a "Diskon" row in the payment summary to display the deducted amount.
-   **Logic Changes (JS):**
    -   Implement the `applyCoupon` event listener to trigger an AJAX request to the backend.
    -   Update the `updateTotal()` function to subtract the discount amount from the total.
    -   Add safety logic to **reset** the coupon if the user changes participants or categories after applying a coupon (to ensure validation rules like "minimum transaction" remain valid).

**2. Theme: Professional City Run (`professional-city-run.blade.php`)**
-   **UI Changes:**
    -   Adapt the coupon input UI to match the "Professional" design language (using `Plus Jakarta Sans` font, specific border styles, and brand colors).
-   **Logic Changes (JS):**
    -   Inject the same validation and calculation logic as above, tailored to the specific DOM structure of this theme.

**3. Theme: Light Clean (`light-clean.blade.php`)**
-   **UI Changes:**
    -   Implement the coupon UI with a clean, white-space-oriented design consistent with the theme.
-   **Logic Changes (JS):**
    -   Apply the standard coupon JS logic.

### **Technical Implementation Details**
-   **Backend Endpoint:** All themes will consume the existing `POST /events/{slug}/register/coupon` endpoint.
-   **Concurrency:** The backend handles the quota and concurrency checks; the frontend simply displays the result.
-   **Reset Mechanism:** To prevent "hacky" usage (e.g., reaching a minimum amount to apply a coupon and then removing participants), the frontend will automatically remove the applied coupon whenever the participant list changes, forcing the user to re-apply it.

I will start by modifying `simple-minimal.blade.php` and then proceed to the others.