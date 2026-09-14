# Update 4 — Login & Hak Akses (Admin / Staff)

## File yang TINGGAL DITIMPA (sudah lengkap di zip ini)
```
database/migrations/2026_09_17_000001_add_role_to_users_table.php   -> BARU
database/seeders/AdminUserSeeder.php                                -> BARU
app/Http/Controllers/Auth/LoginController.php                       -> BARU
app/Http/Controllers/UserController.php                              -> BARU
app/Http/Middleware/EnsureUserIsAdmin.php                            -> BARU
resources/views/auth/login.blade.php                                 -> BARU
resources/views/users/index.blade.php                                -> BARU
resources/views/users/create.blade.php                               -> BARU
resources/views/layouts/app.blade.php                                -> TIMPA (sudah ada tombol logout & menu Kelola User)
resources/views/documents/index.blade.php                            -> TIMPA (tombol hapus disembunyikan utk staff)
resources/views/invoices/index.blade.php                             -> TIMPA (tombol hapus disembunyikan utk staff)
routes/web.php                                                       -> TIMPA TOTAL (sudah lengkap semua route + auth)
```

## File yang HARUS KAMU EDIT MANUAL (tidak saya timpa karena sudah ada isi bawaan Laravel)

### 1. `app/Models/User.php`
Buka file itu, cari baris `protected $fillable = [...]` dan tambahkan `'role'` di dalamnya.
Lalu tambahkan method `isAdmin()` di dalam class. Contoh akhir:

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'role', // <-- tambahkan ini
];

// tambahkan method ini di dalam class User
public function isAdmin(): bool
{
    return $this->role === 'admin';
}
```

### 2. `bootstrap/app.php`
Buka file itu, cari bagian `->withMiddleware(function (Middleware $middleware) {`.
Kalau belum ada bagian itu, tambahkan. Daftarkan middleware `admin` di dalamnya:

```php
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Configuration\Middleware;

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => EnsureUserIsAdmin::class,
    ]);
})
```

Kalau file `bootstrap/app.php` kamu sudah ada `->withMiddleware(...)` sebelumnya (misalnya untuk keperluan lain), cukup tambahkan baris `$middleware->alias([...])` di dalam closure yang sudah ada — jangan bikin blok `->withMiddleware()` dobel.

## Langkah eksekusi

1. Timpa semua file di daftar pertama sesuai lokasinya.
2. Edit manual 2 file di atas (`User.php` dan `bootstrap/app.php`).
3. Jalankan:
   ```bash
   php artisan migrate
   php artisan db:seed --class=AdminUserSeeder
   ```
4. Buka `http://nama-project-kamu.test/login`, login dengan:
   - Email: `admin@arbajasteelindo.co.id`
   - Password: `password123`
5. **Segera ganti password ini** lewat menu Kelola User setelah login pertama kali.
6. Untuk bikin akun staff/kasir: login sebagai admin, buka menu **Kelola User** di navbar, klik **Tambah User**, pilih role **Staff / Kasir**.

## Perilaku hak akses

| Aksi | Admin | Staff/Kasir |
|---|---|---|
| Lihat & cari dokumen/invoice | ✅ | ✅ |
| Buat & edit dokumen/invoice | ✅ | ✅ |
| Ubah status pembayaran invoice | ✅ | ✅ |
| Cetak PDF (Invoice/DO/Surat) | ✅ | ✅ |
| **Hapus** dokumen/invoice | ✅ | ❌ (403 kalau dicoba lewat URL langsung) |
| Kelola User (tambah/edit/hapus akun) | ✅ | ❌ (403) |

Semua pembatasan ini dikunci di level **route middleware** (`admin`), bukan cuma
disembunyikan di tampilan — jadi staff tidak bisa akal-akalan lewat URL langsung.
