<?php
$role = session('user_role', '');
$name = session('user_name', 'Pengguna');
$username = session('user_username', '');
$photo = session('user_photo');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · Ranking Kendaraan</title>
    <link rel="stylesheet" href="<?= e(\Illuminate\Support\Facades\Vite::asset('resources/css/app.css')) ?>">
    <script type="module" src="<?= e(\Illuminate\Support\Facades\Vite::asset('resources/js/app.js')) ?>"></script>
</head>
<body class="app-body">
<aside class="sidebar" id="sidebar">
    <a class="side-brand" href="<?= route('dashboard') ?>">
        <span class="brand-mark">RK</span>
        <span><b>Ranking <em>Kendaraan</em></b><small>DECISION SUPPORT SYSTEM</small></span>
    </a>

    <div class="side-label">MENU UTAMA</div>
    <nav class="side-nav">
        <?php
        $items = [
            ['dashboard', 'Dashboard', '⌂'],
            ['vehicles', 'Data Kendaraan', '▤'],
            ['criteria', 'Kriteria MOORA', '⚙'],
            ['ranking', 'Ranking Kendaraan', '🏆'],
            ['profit', 'Untung Perusahaan', '↗'],
            ['costs', 'Biaya Operasional', '◷'],
        ];
        foreach ($items as [$key, $label, $icon]):
        ?>
            <a class="nav-link <?= $active === $key ? 'active' : '' ?>" href="<?= route($key) ?>">
                <span class="nav-icon"><?= $icon ?></span><?= e($label) ?>
            </a>
        <?php endforeach; ?>

        <a class="nav-link <?= $active === 'profile' ? 'active' : '' ?>" href="<?= route('profile') ?>">
            <span class="nav-icon">◉</span>Profil Akun
        </a>

        <form method="post" action="<?= route('logout') ?>" class="logout-nav-form" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari akun ini?')">
            <?= csrf_field() ?>
            <button type="submit" class="nav-link logout-nav-link">
                <span class="nav-icon">↪</span>Logout
            </button>
        </form>
    </nav>

    <div class="side-illustration">
        <div class="side-truck-duo">
            <img src="<?= asset('images/truck-ti.svg') ?>" alt="Ilustrasi armada TI">
            <img src="<?= asset('images/truck-jember.svg') ?>" alt="Ilustrasi armada JEMBER">
        </div>
        <small>FLEET MANAGEMENT</small>
    </div>

    <a class="side-user" href="<?= route('profile') ?>" title="Buka profil akun">
        <?php if ($photo): ?>
            <img class="avatar avatar-photo" src="<?= asset('storage/' . $photo) ?>" alt="Foto profil">
        <?php else: ?>
            <span class="avatar"><?= e(mb_strtoupper(mb_substr($name, 0, 1))) ?></span>
        <?php endif; ?>
        <span class="side-user-copy">
            <b><?= e($name) ?></b>
            <small><?= $role === 'admin' ? 'Administrator' : 'Pimpinan' ?></small>
        </span>
        <span class="profile-arrow">↗</span>
    </a>
</aside>

<div class="main-area">
    <header class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle" data-menu-toggle aria-label="Buka menu">☰</button>
            <div class="topbar-trucks" aria-label="Ilustrasi dua kendaraan">
                <img src="<?= asset('images/truck-ti.svg') ?>" alt="Armada TI">
                <img src="<?= asset('images/truck-jember.svg') ?>" alt="Armada JEMBER">
            </div>
            <div><span class="muted">Workspace / </span><b><?= e($title) ?></b></div>
        </div>

        <a class="top-user" href="<?= route('profile') ?>" title="Ubah profil dan pengaturan akun">
            <span class="status-dot"></span>
            <span class="top-user-copy"><b><?= e($name) ?></b><small><?= e($role === 'admin' ? 'Admin' : 'Pimpinan') ?> · <?= e($username) ?></small></span>
            <?php if ($photo): ?>
                <img class="top-avatar" src="<?= asset('storage/' . $photo) ?>" alt="Foto profil">
            <?php else: ?>
                <span class="top-avatar top-avatar-fallback"><?= e(mb_strtoupper(mb_substr($name, 0, 1))) ?></span>
            <?php endif; ?>
        </a>
    </header>

    <main class="content-area">
        <?php if (session('success')): ?><div class="alert success dismissible"><?= e(session('success')) ?></div><?php endif; ?>
        <?php if (session('error')): ?><div class="alert danger dismissible"><?= e(session('error')) ?></div><?php endif; ?>
        <?php if ($errors->any()): ?><div class="alert danger dismissible"><?= e($errors->first()) ?></div><?php endif; ?>
        <?= $content ?>
    </main>

    <footer class="app-footer">
        <span>Ranking Kendaraan</span>
        <span>Metode MOORA · CV Angkutan Terang Mandiri</span>
    </footer>
</div>
</body>
</html>
