<section class="page-heading">
    <div>
        <span class="eyebrow">PENGATURAN PENGGUNA</span>
        <h1>Profil Akun</h1>
        <p>Perbarui foto, nama, username, dan password akun Anda.</p>
    </div>
    <div class="heading-art small-art">
        <img src="<?= asset('images/truck-ti.svg') ?>" alt="Ilustrasi armada TI">
        <img src="<?= asset('images/truck-jember.svg') ?>" alt="Ilustrasi armada JEMBER">
    </div>
</section>

<section class="panel profile-panel">
    <div class="profile-intro">
        <?php if ($user->profile_photo): ?>
            <img class="profile-photo-preview" src="<?= asset('storage/' . $user->profile_photo) ?>" alt="Foto profil saat ini">
        <?php else: ?>
            <div class="profile-photo-fallback"><?= e(mb_strtoupper(mb_substr($displayName, 0, 1))) ?></div>
        <?php endif; ?>
        <div>
            <h2><?= e($displayName) ?></h2>
            <p>@<?= e($user->username) ?> · <?= $user->role === 'admin' ? 'Administrator' : 'Pimpinan' ?></p>
        </div>
    </div>

    <form method="post" action="<?= route('profile.update') ?>" enctype="multipart/form-data" class="profile-form">
        <?= csrf_field() ?>
        <?= method_field('PUT') ?>
        <div class="profile-fields">
            <label>Foto Profil
                <input type="file" name="profile_photo" accept="image/png,image/jpeg,image/webp">
                <small>JPG, PNG, atau WebP. Ukuran maksimal 2 MB.</small>
            </label>
            <label>Nama Lengkap<input type="text" name="name" value="<?= e(old('name', $displayName)) ?>" required></label>
            <label>Username<input type="text" name="username" value="<?= e(old('username', $user->username)) ?>" required></label>
        </div>
        <div class="profile-password-section">
            <h3>Ganti Password</h3>
            <p class="muted">Kosongkan bagian ini apabila tidak ingin mengubah password.</p>
            <div class="profile-fields">
                <label>Password Saat Ini
                    <span class="password-wrap"><input type="password" name="current_password" autocomplete="current-password"><button type="button" class="password-toggle" data-password-toggle aria-label="Lihat password" aria-pressed="false">◉</button></span>
                </label>
                <label>Password Baru
                    <span class="password-wrap"><input type="password" name="password" autocomplete="new-password" minlength="8"><button type="button" class="password-toggle" data-password-toggle aria-label="Lihat password" aria-pressed="false">◉</button></span>
                </label>
                <label>Konfirmasi Password Baru
                    <span class="password-wrap"><input type="password" name="password_confirmation" autocomplete="new-password" minlength="8"><button type="button" class="password-toggle" data-password-toggle aria-label="Lihat password" aria-pressed="false">◉</button></span>
                </label>
            </div>
        </div>
        <div class="profile-actions">
            <button class="btn primary" type="submit">Simpan Perubahan</button>
            <a class="btn outline" href="<?= route('dashboard') ?>">Kembali ke Dashboard</a>
        </div>
    </form>
</section>
