I have identified the issue. The `moota_is_active` key (and other Moota settings) are missing from the `$settings` array in the `index` method of `IntegrationSettingsController.php`. This causes the "Undefined array key" error when the view tries to access these values.

I will update `app/Http/Controllers/Admin/IntegrationSettingsController.php` to include these keys in the `index` method.

**Plan:**

1. Edit `app/Http/Controllers/Admin/IntegrationSettingsController.php`.
2. Add the following lines to the `$settings` array in the `index` method:

   ```php
           // Moota Payment
           'moota_api_token' => AppSettings::get('moota_api_token'),
           'moota_webhook_secret' => AppSettings::get('moota_webhook_secret'),
           'moota_bank_id' => AppSettings::get('moota_bank_id'),
           'moota_is_active' => AppSettings::get('moota_is_active', false),
           'moota_instructions' => AppSettings::get('moota_instructions'),
   ```
3. Verify the file content after the update.

