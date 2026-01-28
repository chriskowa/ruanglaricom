I will implement comprehensive form validation and error handling for the "Prizes" (Hadiah Juara) section in both Create and Edit Event pages.

### Backend Changes (`EventController.php`)
1.  **Tighten Validation:** Update the validation rule for `categories.*.prizes.*` from `nullable` to `required`. This ensures that if a user adds a prize row, they must fill it out. Empty rows will trigger a validation error.

### Frontend Changes (`create.blade.php` & `edit.blade.php`)
1.  **Pass Errors to JavaScript:** Inject the Laravel validation errors into a JavaScript variable (`window.laravelErrors`) so the dynamic form handler can access them.
2.  **Client-side Validation:** Add the `required` attribute to the Prize input fields in the JavaScript template. This provides immediate browser-based feedback (preventing submission if empty).
3.  **Error Message Display:** Update the `addPrizeRow` JavaScript function to:
    *   Check if there is a server-side error for the specific prize field (e.g., `categories.0.prizes.1`).
    *   Dynamically render the error message in red text immediately below the input field if an error exists.
    *   Apply red border styling to the input field when it has an error.

This approach ensures users receive immediate feedback if they forget to fill a prize, and specific error messages from the server if the data is invalid.