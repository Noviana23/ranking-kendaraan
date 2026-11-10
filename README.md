# Ranking Kendaraan — Laravel + PHP Views

Aplikasi Sistem Pendukung Keputusan kendaraan prioritas pada CV Angkutan Terang Mandiri menggunakan metode MOORA.

## Teknologi

- Laravel 12, PHP 8.2+, Composer.
- MySQL / MariaDB dari XAMPP.
- Vite untuk CSS dan JavaScript.
- Tampilan menggunakan file PHP biasa di `resources/views/*.php`.
- Tema visual pastel pink, kuning, dan putih dengan ilustrasi dua armada.

## Fitur yang sudah diperbarui

1. **Login dan akun:** tombol mata untuk melihat/menyembunyikan password. Halaman Profil dapat dibuka dengan mengklik nama/foto akun di kanan atas. Pengguna dapat mengubah foto, nama, username, dan password.
2. **Logout:** tersedia di menu sebelah kiri dan menampilkan konfirmasi sebelum keluar.
3. **Data kendaraan per bulan:** tambah data dengan nomor plat, tipe, jumlah perjalanan, pendapatan/ritase, uang jalan, biaya operasional, status, dan bulan data. Keuntungan dihitung otomatis dengan rumus `pendapatan - uang jalan`.
4. **Edit langsung:** Admin dapat menekan tombol Kelola untuk membuka kolom edit pada baris kendaraan yang sama.
5. **Hapus massal:** tombol Hapus Semua Data menghapus semua data kendaraan dan hasil ranking. Sistem meminta konfirmasi terlebih dahulu.
6. **Import:** tombol Import Data tersedia dari halaman Data Kendaraan, bukan lagi sebagai menu tersendiri di sidebar.
7. **Ranking MOORA:** dapat dihitung berdasarkan rentang tanggal. Tombol perhitungan bernama Hitung Ranking Kendaraan. Ekspor ranking dilakukan melalui unduhan CSV dari server.
8. **Pembaruan otomatis:** setelah kendaraan ditambah, diubah, dihapus, atau diimpor, ranking bulan terkait dihitung ulang. Laporan Untung Perusahaan dan Biaya Operasional mengikuti urutan ranking bulan tersebut.
9. **Tema dan ilustrasi:** seluruh halaman menggunakan gradasi pastel pink-kuning-putih dengan ilustrasi dua armada di bagian atas dan area kiri bawah.

## Persiapan komputer

Pastikan tersedia:

1. XAMPP dengan Apache dan MySQL/MariaDB.
2. PHP 8.2 atau lebih baru. Pastikan ekstensi `pdo_mysql`, `fileinfo`, dan `zip` aktif.
3. Composer.
4. Node.js LTS dan npm.
5. VS Code.

Periksa terminal:

```powershell
php -v
composer -V
node -v
npm -v
```

## Instalasi / pembaruan di Windows + XAMPP

### 1. Simpan dan buka folder proyek

Sebaiknya cadangkan folder proyek dan database lama terlebih dahulu. Ekstrak ZIP ke folder, misalnya:

```text
C:\xampp\htdocs\ranking-kendaraan
```

Buka folder tersebut melalui VS Code. Jika sedang memperbarui instalasi lama, pertahankan file `.env` milik Anda karena berisi konfigurasi database lokal.

### 2. Buat / pastikan database tersedia

- Jalankan MySQL dari XAMPP.
- Buka `http://localhost/phpmyadmin`.
- Pastikan database `spk_kendaraan` tersedia dengan collation `utf8mb4_unicode_ci`.
- Jangan hapus database lama jika ingin mempertahankan data yang sudah ada.

### 3. Periksa konfigurasi `.env`

Jika `.env` belum ada, salin `.env.example` menjadi `.env`:

```powershell
Copy-Item .env.example .env
```

Atur koneksi MySQL untuk XAMPP default:

```env
APP_NAME="Ranking Kendaraan"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spk_kendaraan
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
FILESYSTEM_DISK=local
```

Jika baru membuat `.env`, jalankan `php artisan key:generate`. Jika menggunakan `.env` lama, jangan mengganti `APP_KEY` tanpa alasan karena dapat membuat sesi lama tidak berlaku.

### 4. Jalankan migration

Dari terminal pada folder proyek:

```powershell
composer install
php artisan config:clear
php artisan migrate --seed
php artisan storage:link
```

Migration baru menambahkan periode bulanan kendaraan, foto profil, dan rentang periode hasil MOORA. Data kendaraan lama yang belum memiliki periode akan dimasukkan ke bulan berjalan saat migration dijalankan. Setelah migration, bulan data dapat disesuaikan lewat tombol Kelola pada baris kendaraan.

**Penting:** jangan menjalankan `php artisan migrate:fresh` pada database yang berisi data penting karena perintah tersebut menghapus seluruh tabel beserta isinya.

### 5. Pasang dependensi tampilan

```powershell
npm install
```

Jalankan perintah ini di komputer Windows Anda agar npm memasang paket yang sesuai dengan sistem operasi tersebut.

### 6. Jalankan aplikasi

Buka dua terminal VS Code.

**Terminal 1 — Laravel:**

```powershell
php artisan serve
```

**Terminal 2 — Vite:**

```powershell
npm run dev
```

Buka browser:

```text
http://127.0.0.1:8000
```

Biarkan kedua terminal tetap berjalan selama aplikasi digunakan. Untuk membuat aset produksi, jalankan `npm run build`.

## Cara menggunakan

1. Masuk dengan akun yang sudah terdaftar. Jika belum memiliki akun, gunakan halaman Daftar Akun.
2. Pada Data Kendaraan, pilih bulan terlebih dahulu.
3. Admin dapat menambahkan kendaraan. Keuntungan terisi otomatis dari jumlah pendapatan dikurangi uang jalan.
4. Tekan Kelola pada baris kendaraan untuk mengedit data secara langsung.
5. Untuk memasukkan banyak data, gunakan tombol Import Data di halaman Data Kendaraan. Tentukan bulan data sebelum memilih file.
6. Setelah data berubah, sistem memperbarui ranking MOORA secara otomatis untuk bulan terkait.
7. Pada halaman Ranking Kendaraan, tentukan tanggal awal dan akhir lalu gunakan Hitung Ranking Kendaraan untuk menghitung ulang seluruh bulan dalam rentang tersebut.
8. Ekspor CSV di halaman ranking akan mengunduh hasil periode yang sedang ditampilkan.
9. Halaman Untung Perusahaan dan Biaya Operasional memiliki pilihan bulan dan mengikuti urutan ranking terbaru.

## Format kolom import

Gunakan judul kolom berikut pada Excel atau CSV:

- No. Plat Kendaraan
- Tipe Kendaraan
- Jumlah Perjalanan
- Jumlah Ritase / Pendapatan
- Uang Jalan
- Biaya Operasional
- Status

Nilai tipe kendaraan harus `TI` atau `JEMBER`. Status harus `Ready` atau `Disewakan`. Kolom Keuntungan tidak perlu dimasukkan karena dihitung otomatis.

Nomor plat yang sama pada bulan yang sama akan diperbarui. Nomor plat yang sama pada bulan berbeda akan disimpan sebagai data bulanan yang terpisah. File `.xlsx` membutuhkan ekstensi PHP `zip`; jika ekstensi tersebut belum aktif, simpan Excel sebagai CSV UTF-8.

## Kriteria dan perhitungan MOORA

- C1 Jumlah Perjalanan — Benefit.
- C2 Keuntungan Perusahaan — Benefit.
- C3 Biaya Operasional — Cost.
- Rumus nilai optimasi: `Yi = (C1 × W1) + (C2 × W2) − (C3 × W3)` setelah normalisasi.
- Peringkat diurutkan dari nilai optimasi terbesar ke terkecil.

Bobot awal aplikasi adalah C1 30%, C2 45%, dan C3 25%. Pastikan bobot final telah sesuai dengan dokumen penelitian dan persetujuan perusahaan sebelum dipakai sebagai dasar keputusan operasional.

## Troubleshooting

- **Unknown database `spk_kendaraan`:** buat database terlebih dahulu melalui phpMyAdmin.
- **`could not find driver`:** aktifkan `extension=pdo_mysql` pada `C:\xampp\php\php.ini`, lalu buka ulang terminal / restart Apache.
- **`Vite manifest not found`:** jalankan `npm install`, kemudian `npm run dev` pada terminal kedua atau `npm run build`.
- **Foto profil tidak tampil:** pastikan `php artisan storage:link` sudah dijalankan.
- **Tidak dapat membaca `.xlsx`:** aktifkan ekstensi `zip` di `php.ini` atau simpan file sebagai CSV UTF-8.
- **Perubahan `.env` tidak terbaca:** jalankan `php artisan config:clear`.
