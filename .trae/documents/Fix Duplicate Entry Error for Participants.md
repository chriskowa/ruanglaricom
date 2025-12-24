Based on the analysis, the error `Integrity constraint violation: 1062 Duplicate entry ... for key 'participants.participants_unique_category_idcard'` is caused by a **strict unique constraint** on the `participants` table that prevents a user (ID Card) from being registered in the same category more than once, **regardless of the transaction status**.

This means if a user attempts to register but their payment fails or expires, they **cannot try again** because the database still holds the previous "failed" record, and the unique constraint blocks the new insertion. This is likely what is filling up your logs, although it is unusual for this to trigger specifically when *viewing* the participants list (it usually triggers during Registration).

To fix this and allow retries for failed transactions while still preventing double-active registrations, I propose:

1. **Remove the Database Unique Constraint:** Create a migration to drop the `participants_unique_category_idcard` index. This will allow multiple records for the same ID Card (e.g., one failed, one successful).
2. **Add Application-Level Validation:** Update `App\Actions\Events\StoreRegistrationAction.php` to explicitly check if the user already has a **pending** or **paid** registration in the category. If they do, block the new registration with a friendly message. If they only have failed/expired ones, allow the new registration.

**Plan:**

1. Create a migration `2025_12_24_000000_remove_unique_constraint_from_participants.php` to drop the index.
2. Modify `app/Actions/Events/StoreRegistrationAction.php` to add the validation logic.

