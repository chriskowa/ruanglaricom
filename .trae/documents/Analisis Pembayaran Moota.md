Terima kasih atas koreksinya. Anda benar, saya menemukan bahwa **Platform Fee (Biaya Admin)** belum dimasukkan ke dalam rincian item (`item_details`) yang dikirim ke Midtrans, meskipun sudah dihitung di total akhir (`gross_amount`). Hal ini akan menyebabkan error di Midtrans karena jumlah rincian tidak sama dengan total pembayaran.

Untuk Moota, karena sistemnya hanya menerima "Total Akhir" dan tidak membutuhkan rincian item per baris seperti Midtrans, maka perhitungan di Moota sudah aman (sudah termasuk fee yang dihitung di backend).

Rencana perbaikan:
1.  **Update `MidtransService.php`**: Menambahkan logika untuk memasukkan `admin_fee` ke dalam `item_details` pada method `createEventTransaction`. Ini akan memastikan total rincian sama dengan total pembayaran.

Apakah Anda setuju dengan langkah ini?