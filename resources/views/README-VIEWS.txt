CATATAN TEMPLATE TAMPILAN
========================
Semua file tampilan di folder ini adalah PHP biasa (.php), BUKAN Blade.
Contoh: login.php, register.php, layout.php, dashboard.php, vehicles.php,
import.php, criteria.php, ranking.php, profit.php, costs.php.

Controller memanggil view('nama-file') menggunakan resolver view Laravel.
Isi halaman dirender terlebih dahulu, lalu disisipkan ke layout.php melalui
variabel $content. Form POST menggunakan csrf_field() dari Laravel.
Tidak ada file berekstensi .blade.php pada proyek ini.
