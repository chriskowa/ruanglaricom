@php
    $rawContact = trim($event->organizer_contact ?? '');
    $waUrl = null;
    if (!empty($rawContact)) {
        if (str_starts_with($rawContact, 'http://') || str_starts_with($rawContact, 'https://') || str_starts_with($rawContact, 'wa.me/')) {
            $waUrl = str_starts_with($rawContact, 'wa.me/') ? 'https://' . $rawContact : $rawContact;
        } else {
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawContact);
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            }
            if (!empty($cleanPhone)) {
                $waText = rawurlencode('Halo Panitia ' . $event->name . ', saya ingin bertanya seputar event ini.');
                $waUrl = 'https://wa.me/' . $cleanPhone . '?text=' . $waText;
            }
        }
    }
@endphp

@if($waUrl)
<aside aria-label="Bantuan WhatsApp" class="print:hidden">
    <a href="{{ $waUrl }}"
       target="_blank"
       rel="noopener noreferrer"
       class="fixed bottom-5 right-5 sm:bottom-6 sm:right-6 z-40 inline-flex items-center gap-2 px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-md bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm shadow-lg hover:shadow-xl transition-all duration-150 border border-emerald-500/80 group focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2"
       aria-label="Hubungi Panitia {{ $event->name }} via WhatsApp"
       title="Hubungi Panitia via WhatsApp">
        <i class="fab fa-whatsapp text-lg sm:text-xl text-white"></i>
        <span class="font-semibold hidden sm:inline">Tanya Panitia</span>
    </a>
</aside>
@endif
