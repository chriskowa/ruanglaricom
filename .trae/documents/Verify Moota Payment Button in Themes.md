Based on my analysis of the codebase, the Moota payment method (Radio Button) is **already implemented** in all event registration themes.

**Status:**
- **Already Available:** Yes, all 6 themes support Moota.
- **Condition:** It will only appear if you have enabled "Moota" in the **Event Settings > Payment Methods** for that specific event.

**Files Verified:**
1.  `resources/views/events/themes/light-clean.blade.php` (Lines 546-554)
2.  `resources/views/events/themes/modern-dark.blade.php` (Lines 853-863)
3.  `resources/views/events/themes/paolo-fest.blade.php` (Lines 1042-1050)
4.  `resources/views/events/themes/paolo-fest-dark.blade.php` (Lines 970-978)
5.  `resources/views/events/themes/professional-city-run.blade.php` (Lines 502-510)
6.  `resources/views/events/themes/simple-minimal.blade.php` (Lines 307-315)

**How it works:**
The code checks if `moota` is in the allowed payment methods for the event:
```php
@php
    $showMoota = in_array('moota', $event->payment_config['allowed_methods'] ?? []);
@endphp

@if($showMoota)
    <!-- Renders Moota Radio Button -->
@endif
```

**Action Required:**
- **No code changes needed** to add the button.
- You just need to ensure the event is configured to allow Moota.

If you want me to change the **label/text** of the radio button (currently varies between "Transfer Bank (Kode Unik)" and "Transfer Bank (Manual)"), I can standardize it for you. Otherwise, it is ready to use.