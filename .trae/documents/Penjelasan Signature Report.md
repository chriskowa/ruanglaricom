## Jawaban
- Signature itu **dibuat oleh sistem (Laravel)** saat kita generate link dengan `URL::signedRoute(...)`.
- Nilainya dihitung dari: URL + parameter + secret key aplikasi (**APP_KEY** di server). Jadi hanya server yang punya APP_KEY yang bisa membuat signature valid.
- EO/peserta **tidak membuat** signature; mereka hanya menerima link jadi dan membukanya.
- Saat link dibuka, middleware `ValidateSignature` akan mengecek signature; kalau URL/param berubah atau signature tidak cocok → akses ditolak.

## Cara Praktis Dipakai EO
- Admin/EO generate link sekali (mis. lewat `php artisan tinker`), lalu share link tersebut.
- Link itu bisa dipakai berkali-kali selama APP_KEY tidak berubah dan URL tidak diutak-atik.