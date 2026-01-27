I will implement the WhatsApp message template feature as requested.

### 1. Database Migration
- Create a new migration to add a `whatsapp_config` column (JSON type) to the `events` table.
- This column will store:
  - `enabled` (boolean): Whether the WhatsApp feature is active.
  - `template` (string): The message template content.

### 2. Model Update (`app/Models/Event.php`)
- Add `whatsapp_config` to the `$fillable` array.
- Add `whatsapp_config` => `array` to the `$casts` array.

### 3. Controller Update (`app/Http/Controllers/EO/EventController.php`)
- Update `store` and `update` methods to validate `whatsapp_config`.
- Validation rules:
  - `whatsapp_config` => `nullable|array`
  - `whatsapp_config.enabled` => `nullable|boolean`
  - `whatsapp_config.template` => `nullable|string` (required if enabled)

### 4. View Updates
- **`resources/views/eo/events/create.blade.php`** & **`edit.blade.php`**:
  - Add a new "WhatsApp Notification" section (likely below "Custom Email Message").
  - Add Radio Buttons/Toggle for "Enable WhatsApp Notification".
  - Add a Textarea for the message template.
  - Add JavaScript logic to:
    - Enable/disable the textarea based on the toggle.
    - Show helper text for dynamic variables (e.g., `{{name}}`, `{{amount}}`, `{{event_name}}`, `{{transaction_id}}`).

### 5. Implementation Steps
1.  Run migration command.
2.  Update Model.
3.  Update Controller.
4.  Update Views.
5.  Verify the implementation.
