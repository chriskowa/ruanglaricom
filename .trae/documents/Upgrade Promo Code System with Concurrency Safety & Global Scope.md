I will implement the robust Promo Code system by enhancing the existing foundation to meet all your requirements, specifically focusing on the "Start Date", "Global/Event-Specific" scope, and "Concurrency Safety".

Here is the plan:

### 1. Database Schema Updates
I will create a new migration to upgrade the `coupons` table:
*   **`start_at`**: Add a timestamp column to support the "Start Date" requirement.
*   **`event_id`**: Modify this column to be `nullable`. This allows creating **Global Codes** (valid for all events) when `NULL`, while keeping the ability to restrict codes to specific events.
*   **Indexes**: Ensure proper indexing for performance.

### 2. Backend Logic Enhancements

#### A. Concurrency Safety (The "Race Condition" Fix)
This is the most critical technical challenge. Currently, the system only checks `used_count` (paid users). To prevent overselling (e.g., 100 users checking out for 50 slots), I will update `StoreRegistrationAction.php` to:
1.  **Atomic Locking**: Use `Cache::lock()` on the coupon code during the checkout process to ensure only one person can validate/reserve a slot at a time.
2.  **Pending Count Logic**: Instead of just checking `used_count < max_uses`, I will check:
    ```php
    $totalUsage = $coupon->used_count + $coupon->transactions()->where('payment_status', 'pending')->count();
    if ($totalUsage >= $coupon->max_uses) { throw new Exception("Fully Redeemed"); }
    ```
    This ensures that "Pending" payments reserve a slot. If they expire (don't pay), the slot naturally frees up.

#### B. Validation Logic
I will update the `Coupon` model's `isValid()` method to check:
*   **Date Range**: `now()` must be between `start_at` and `expires_at`.
*   **Event Scope**: The code must match the current `event_id` OR have `event_id = NULL` (Global).

#### C. Payment Completion
The system already has logic to increment `used_count` in `ProcessPaidEventTransaction`. I will verify this remains intact and works correctly with the new schema.

### 3. API Response Structure
I will ensure the `applyCoupon` endpoint returns the requested JSON format, clearly showing the `original_price`, `discount_amount`, and `final_price`.

### Execution Steps
1.  Create migration `add_start_at_and_nullable_event_id_to_coupons`.
2.  Update `App\Models\Coupon` to handle new fields and validation logic.
3.  Modify `App\Actions\Events\StoreRegistrationAction` to implement the **Concurrency Safety** (Lock + Pending Count).
4.  Modify `App\Http\Controllers\EventRegistrationController` to handle the global vs. specific logic.
