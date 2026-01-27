I will address both the email template update and the pricing logic issue.

**1. Update Email Template (`resources/views/emails/events/registration-success.blade.php`)**
I will implement the detailed ticket layout as requested:

* Replace the current simple ticket view with a detailed table.

* Include: Name, Email, Phone, Category, Payment Status (color-coded), Ticket Number.

* Add a footer in the ticket for Location and Date.

**2. Update Pricing Logic (Priority: Early > Late > Regular)**
The user clarified that Early/Late prices do not have date settings and should be applied based on availability. I will update the logic to:

* **Backend (`app/Actions/Events/StoreRegistrationAction.php`):**

  * Modify `getCategoryPrice` to ignore dates.

  * Logic: If `price_early` exists (>0), use it. Else if `price_late` exists (>0), use it. Else use `price_regular`.

* **Frontend Themes (`paolo-fest.blade.php`,** **`simple-minimal.blade.php`,** **`professional-city-run.blade.php`):**

  * Update the `@php` block in the category loop to match this priority logic, ensuring the displayed price matches the checkout price.

