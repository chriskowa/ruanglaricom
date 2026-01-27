I will update the email template `resources/views/emails/events/registration-success.blade.php` to include the detailed participant information as requested.

The changes will be focused on the `@foreach($participants as $participant)` loop section. I will replace the current ticket layout with a structured table that lists:
1.  **Nama**: `{{ $participant->name }}`
2.  **Email**: `{{ $participant->email }}`
3.  **Nomor HP**: `{{ $participant->phone }}`
4.  **Kategori Lari**: `{{ $participant->category->name }}`
5.  **Status Pembayaran**: `{{ strtoupper($transaction->payment_status) }}`
6.  **Nomor Tiket**: `{{ $participant->bib_number ?? 'TICKET-'.$participant->id }}`
7.  **Lokasi**: `{{ $event->location_name }}` (Added to the ticket details as requested)
8.  **Tanggal**: `{{ $event->start_at->format('d F Y, H:i') }}` (Added to the ticket details as requested)

I will use HTML tables for better alignment in email clients, ensuring the design remains clean and consistent with the existing style.