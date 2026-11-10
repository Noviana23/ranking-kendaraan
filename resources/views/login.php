<?php use Illuminate\Support\Facades\Vite; ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Ranking Kendaraan</title>
    <link rel="stylesheet" href="<?= e(Vite::asset('resources/css/app.css')) ?>">
    <script type="module" src="<?= e(Vite::asset('resources/js/app.js')) ?>"></script>
</head>
<body class="auth-body">
<div class="auth-shell">
    <section class="auth-visual">
        <div class="auth-brand"><span class="brand-mark">RK</span><b>Ranking <span>Kendaraan</span></b></div>
        <div class="auth-copy">
            <span class="eyebrow">SMART FLEET DECISION</span>
            <h1>Armada terkelola,<br><em>keputusan lebih baik.</em></h1>
            <p>Sistem pendukung keputusan untuk membantu menentukan kendaraan prioritas berdasarkan jumlah perjalanan, keuntungan, dan biaya operasional menggunakan metode MOORA.</p>
            <div class="truck-duo">
                <figure><img src="<?= asset('images/truck-ti.svg') ?>" alt="Ilustrasi truk TI"><figcaption>ARMADA TI</figcaption></figure>
                <figure><img src="<?= asset('images/truck-jember.svg') ?>" alt="Ilustrasi truk JEMBER"><figcaption>ARMADA JEMBER</figcaption></figure>
            </div>
        </div>
        <div class="auth-foot">CV ANGKUTAN TERANG MANDIRI <span>•</span> SISTEM INFORMASI</div>
    </section>

    <section class="auth-panel">
        <div class="auth-card">
            <div class="auth-icon">↗</div>
            <span class="eyebrow">SELAMAT DATANG</span>
            <h2>Masuk ke akun Anda</h2>
            <p class="muted">Silakan masuk untuk mengakses dashboard manajemen armada.</p>
            <?php if (session('success')): ?><div class="alert success"><?= e(session('success')) ?></div><?php endif; ?>
            <?php if (session('error')): ?><div class="alert danger"><?= e(session('error')) ?></div><?php endif; ?>
            <?php if ($errors->any()): ?><div class="alert danger"><?= e($errors->first()) ?></div><?php endif; ?>

            <form method="post" action="<?= route('login.submit') ?>" class="form-stack">
                <?= csrf_field() ?>
                <label>Username<input name="username" value="<?= e(old('username')) ?>" autocomplete="username" required autofocus></label>
                <label>Password
                    <span class="password-wrap">
                        <input type="password" name="password" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Lihat password" aria-pressed="false">◉</button>
                    </span>
                </label>
                <button class="btn primary full" type="submit">Masuk ke Dashboard <span>→</span></button>
            </form>
            <p class="auth-switch">Belum punya akun? <a href="<?= route('register') ?>">Daftar Akun</a></p>
            <small class="auth-hint">Akun Admin dan Pimpinan dibuat melalui halaman pendaftaran.</small>
        </div>
    </section>
</div>
</body>
</html>
