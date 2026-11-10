<?php use Illuminate\Support\Facades\Vite; ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun · Ranking Kendaraan</title>
    <link rel="stylesheet" href="<?= e(Vite::asset('resources/css/app.css')) ?>">
    <script type="module" src="<?= e(Vite::asset('resources/js/app.js')) ?>"></script>
</head>
<body class="auth-body">
<div class="auth-shell">
    <section class="auth-visual">
        <div class="auth-brand"><span class="brand-mark">RK</span><b>Ranking <span>Kendaraan</span></b></div>
        <div class="auth-copy">
            <span class="eyebrow">PENDAFTARAN PENGGUNA</span>
            <h1>Kelola armada<br><em>dalam satu sistem.</em></h1>
            <p>Buat akun sesuai peran dan tanggung jawab di perusahaan.</p>
            <div class="truck-duo">
                <figure><img src="<?= asset('images/truck-ti.svg') ?>" alt="Truk TI"><figcaption>ARMADA TI</figcaption></figure>
                <figure><img src="<?= asset('images/truck-jember.svg') ?>" alt="Truk JEMBER"><figcaption>ARMADA JEMBER</figcaption></figure>
            </div>
        </div>
        <div class="auth-foot">CV ANGKUTAN TERANG MANDIRI</div>
    </section>
    <section class="auth-panel">
        <div class="auth-card">
            <div class="auth-icon">＋</div>
            <span class="eyebrow">BUAT AKUN BARU</span>
            <h2>Daftar Akun</h2>
            <p class="muted">Isi informasi akun dan pilih jenis akses.</p>
            <?php if ($errors->any()): ?><div class="alert danger"><?= e($errors->first()) ?></div><?php endif; ?>
            <form method="post" action="<?= route('register.submit') ?>" class="form-stack">
                <?= csrf_field() ?>
                <label>Nama Lengkap<input name="name" value="<?= e(old('name')) ?>" required></label>
                <label>Username<input name="username" value="<?= e(old('username')) ?>" required></label>
                <label>Password
                    <span class="password-wrap"><input type="password" name="password" required minlength="8"><button type="button" class="password-toggle" data-password-toggle aria-label="Lihat password" aria-pressed="false">◉</button></span>
                </label>
                <label>Konfirmasi Password
                    <span class="password-wrap"><input type="password" name="password_confirmation" required minlength="8"><button type="button" class="password-toggle" data-password-toggle aria-label="Lihat password" aria-pressed="false">◉</button></span>
                </label>
                <label>Daftar Sebagai<select name="role" required><option value="">Pilih peran akun</option><option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option><option value="pimpinan" <?= old('role') === 'pimpinan' ? 'selected' : '' ?>>Pimpinan</option></select></label>
                <button class="btn primary full">Buat Akun <span>→</span></button>
            </form>
            <p class="auth-switch">Sudah punya akun? <a href="<?= route('login') ?>">Masuk</a></p>
        </div>
    </section>
</div>
</body>
</html>
