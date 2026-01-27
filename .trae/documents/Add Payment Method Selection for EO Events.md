I will add a feature for Event Organizers (EO) to select the payment method for their events using radio buttons.

### 1. Database & Model
*   **Migration**: Create a new migration to add a `payment_config` (JSON) column to the `events` table.
*   **Model**: Update `App\Models\Event` to add `payment_config` to `$fillable` and cast it as an `array`.

### 2. EO Dashboard (Create & Edit Event)
*   **Views**: Update `resources/views/eo/events/create.blade.php` and `edit.blade.php` to include a **Payment Settings** section with Radio Buttons:
    *   🔘 **Otomatis (Midtrans)**: QRIS, E-Wallet, Virtual Account.
    *   🔘 **Transfer Bank (Moota)**: Transfer Manual dengan Kode Unik.
    *   🔘 **Semua Metode**: Mengaktifkan keduanya.
*   **Controller**: Update `App\Http\Controllers\EO\EventController` to save the selected payment configuration.

### 3. Public Event Page
*   **View**: Update `resources/views/events/themes/modern-dark.blade.php` to check the event's `payment_config` before displaying payment options.
    *   If only Midtrans is selected, hide the Bank Transfer option.
    *   If only Moota is selected, hide the Midtrans option.
    *   If both are selected, show both.

### 4. Verification
*   I will verify by checking the database after saving an event and ensuring the public page reflects the chosen payment method.