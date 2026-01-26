I will implement the following features:

1. **Ticket Template Management (Email)**

   * Add a `custom_email_message` (text/HTML) field to the `events` table via a migration.

   * Update `EO\EventController` to validate and save this field.

   * Update `resources/views/eo/events/edit.blade.php` to add a WYSIWYG editor (CKEditor) for this field.

   * Update `resources/views/emails/events/registration-success.blade.php` to display this custom message if it exists, overriding or supplementing the default message.

2. **Instant Email Notification Option**

   * Add an `is_instant_notification` (boolean) field to the `events` table via the same migration.

   * Update `EO\EventController` to save this setting (checkbox in Edit form).

   * Update `app/Jobs/ProcessPaidEventTransaction.php` to check this flag. If true, use `dispatchSync()`; otherwise, use `dispatch()` (queue).

3. **Blast Email Feature**

   * **Routes**: Add `/eo/events/{event}/blast` (GET, POST) in `web.php`.

   * **Controller**: Add `blast` (view form) and `sendBlast` (process logic) methods to `EO\EventController`.

   * **View**: Create `resources/views/eo/events/blast.blade.php` with Subject and Body fields.

   * **Mailable**: Create `App\Mail\EventBlastEmail`.

   * **Job**: Create `App\Jobs\SendEventBlastEmail` to handle the bulk sending (chunking participants).

**Implementation Steps:**

1. Run migration for `custom_email_message` and `is_instant_notification`.
2. Update `Event` model.
3. Update `EO\EventController` and Edit View.
4. Update `ProcessPaidEventTransaction`.
5. Create Blast Email routes, controller methods, view, job, and mailable.

