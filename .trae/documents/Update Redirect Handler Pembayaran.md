Untuk membuat halaman register kembali (reload) setelah proses pembayaran Midtrans selesai atau ditutup, kita perlu menambahkan handler callback pada fungsi `window.snap.pay`.

Saat ini kode hanya memanggil:
```javascript
window.snap.pay(data.snap_token);
```

Rencana perubahannya adalah menambahkan callback object sebagai parameter kedua:
```javascript
window.snap.pay(data.snap_token, {
    onSuccess: function(result) {
        // Redirect atau reload halaman setelah sukses
        window.location.reload(); 
    },
    onPending: function(result) {
        // Reload juga jika pending (menunggu pembayaran) agar status terbaru terambil
        window.location.reload();
    },
    onError: function(result) {
        alert("Pembayaran gagal!");
        window.location.reload();
    },
    onClose: function() {
        // Opsional: reload jika user menutup popup tanpa membayar, atau biarkan saja
        // window.location.reload();
    }
});
```

Juga untuk metode COD, kita akan menambahkan `window.location.reload()` setelah alert sukses.

Langkah implementasi:
1. **Modifikasi `resources/views/events/latbar3.blade.php`**:
   - Update logika `window.snap.pay` untuk menyertakan callback `onSuccess` dan `onPending` yang melakukan `window.location.reload()`.
   - Tambahkan `window.location.reload()` pada blok sukses COD.

Apakah Anda setuju dengan rencana ini?
