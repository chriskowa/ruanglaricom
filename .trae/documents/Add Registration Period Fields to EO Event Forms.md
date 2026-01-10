I will separate the event dates from the registration dates by adding dedicated input fields for the registration period in the Event Organizer (EO) forms.

### Plan
1.  **Modify `resources/views/eo/events/create.blade.php`**:
    *   Add a new row of input fields for `registration_open_at` and `registration_close_at` immediately below the existing `Start Date` and `End Date` fields.
    *   These fields will allow EOs to set a specific registration window independent of the actual event schedule.

2.  **Modify `resources/views/eo/events/edit.blade.php`**:
    *   Add the same corresponding input fields (`registration_open_at`, `registration_close_at`) to the edit form, pre-filled with existing data.

This ensures that "Event Schedule" (when the run happens) and "Registration Schedule" (when people can buy tickets) are clearly distinguished in the UI, as requested. The backend already supports these fields, so no database changes are needed.