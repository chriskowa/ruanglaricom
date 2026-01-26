I will implement the "Buy X Get 1 Free" promo feature as follows:

1. **Database & Model**:

   * Create a migration to add a `promo_buy_x` (integer, nullable) column to the `events` table.

   * Update `App\Models\Event.php` to include `promo_buy_x` in the `$fillable` array.

2. **EO Event Management (Create/Edit)**:

   * Modify `resources/views/eo/events/create.blade.php` and `resources/views/eo/events/edit.blade.php` to add a number input field labeled "Promo Beli X Gratis 1 (Isi jumlah beli, misal 10)".

   * Update `App\Http\Controllers\EO\EventController.php` to validate and save this field in the `store` and `update` methods.

3. **Registration Logic**:

   * Update `App\Actions\Events\StoreRegistrationAction.php` to apply the promo logic during checkout.

   * The logic will be: For every `X` paid participants in a category, the user gets 1 free.

   * Formula: `Paid Count = Total - floor(Total / (X + 1))`.

**Example Logic**:
If configured as "Buy 10", then `X=10`.

* User registers 11 people: `11 / 11 = 1` free slot. Pays for 10.

* User registers 22 people: `22 / 11 = 2` free slots. Pays for 20.

