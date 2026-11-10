<section class="page-heading">
    <div>
        <span class="eyebrow">MANAJEMEN ARMADA</span>
        <h1>Data Kendaraan</h1>
        <p>Kelola data kendaraan per bulan. Keuntungan dihitung otomatis dari pendapatan dikurangi uang jalan.</p>
    </div>
    <div class="heading-art">
        <img src="<?= asset('images/truck-ti.svg') ?>" alt="Armada TI">
        <img src="<?= asset('images/truck-jember.svg') ?>" alt="Armada JEMBER">
    </div>
</section>

<?php if (session('user_role') === 'admin'): ?>
<details class="panel form-panel" open>
    <summary><b>＋ Tambah Kendaraan</b><span>Data yang dimasukkan akan langsung dihitung dan diurutkan berdasarkan ranking bulan terkait.</span></summary>
    <form method="post" action="<?= route('vehicles.store') ?>" class="form-grid">
        <?= csrf_field() ?>
        <label>No. Plat Kendaraan<input name="vehicle_number" placeholder="Contoh: BG 8306 OH" maxlength="40" required></label>
        <label>Tipe Kendaraan<select name="type" required><option value="TI">TI</option><option value="JEMBER">JEMBER</option></select></label>
        <label>Jumlah Perjalanan<input type="number" min="0" name="trips" value="0" required></label>
        <label>Jumlah Ritase / Pendapatan (Rp)<input type="number" min="0" step="0.01" name="gross_revenue" value="0" required></label>
        <label>Uang Jalan (Rp)<input type="number" min="0" step="0.01" name="travel_allowance" value="0" required></label>
        <label>Biaya Operasional (Rp)<input type="number" min="0" step="0.01" name="operating_cost" value="0" required></label>
        <label>Status<select name="availability" required><option value="Ready">Ready</option><option value="Disewakan">Disewakan</option></select></label>
        <label>Bulan Data<input type="month" name="period" value="<?= e($selectedPeriod) ?>" required></label>
        <div class="form-actions"><button class="btn primary">Simpan Kendaraan</button></div>
    </form>
    <p class="form-footnote">Keuntungan = Jumlah Ritase / Pendapatan − Uang Jalan. Kolom keuntungan tidak perlu diisi manual.</p>
</details>
<?php endif; ?>

<section class="panel">
    <div class="panel-head vehicle-panel-head">
        <div><h2>Daftar Armada</h2><p><?= $vehicles->count() ?> kendaraan terdaftar pada bulan <?= e(\Illuminate\Support\Carbon::createFromFormat('Y-m', $selectedPeriod)->translatedFormat('F Y')) ?>.</p></div>
        <div class="vehicle-tools">
            <form method="get" action="<?= route('vehicles') ?>" class="period-filter">
                <label for="vehicle-period">Periode</label>
                <input id="vehicle-period" type="month" name="period" value="<?= e($selectedPeriod) ?>" onchange="this.form.submit()">
            </form>
            <?php if (session('user_role') === 'admin'): ?>
                <a class="btn outline small" href="<?= route('import', ['period' => $selectedPeriod]) ?>">⇧ Import Data</a>
                <form method="post" action="<?= route('vehicles.destroyAll') ?>" onsubmit="return confirm('PERINGATAN: Semua data kendaraan dari seluruh bulan dan seluruh hasil ranking akan dihapus. Tindakan ini tidak dapat dibatalkan. Lanjutkan?')">
                    <?= csrf_field() ?><?= method_field('DELETE') ?>
                    <button class="btn danger small" type="submit">Hapus Semua Data</button>
                </form>
            <?php endif; ?>
            <input class="table-search" type="search" placeholder="Cari plat / tipe..." data-table-search="vehicles-table">
        </div>
    </div>

    <div class="table-wrap">
        <table id="vehicles-table" class="vehicle-table">
            <thead><tr>
                <th>Rank</th><th>No. Plat Kendaraan</th><th>Tipe</th><th>Perjalanan</th>
                <th>Ritase / Pendapatan</th><th>Uang Jalan</th><th>Keuntungan</th>
                <th>Biaya Operasional</th><th>Status</th><th>Bulan</th>
                <?php if (session('user_role') === 'admin'): ?><th>Kelola</th><?php endif; ?>
            </tr></thead>
            <tbody>
            <?php foreach ($vehicles as $v): ?>
                <?php $formId = 'vehicle-form-' . $v->id; ?>
                <tr data-vehicle-row>
                    <td><span class="view-value rank-pill <?= (isset($v->ranking) && (int) $v->ranking === 1) ? 'first' : '' ?>"><?= isset($v->ranking) ? e($v->ranking) : '—' ?></span></td>
                    <td>
                        <b class="view-value"><?= e($v->vehicle_number) ?></b>
                        <?php if (session('user_role') === 'admin'): ?><input class="edit-control" form="<?= $formId ?>" name="vehicle_number" value="<?= e($v->vehicle_number) ?>" maxlength="40" required><?php endif; ?>
                    </td>
                    <td>
                        <span class="view-value type-chip <?= $v->type === 'TI' ? 'ti' : '' ?>"><?= e($v->type) ?></span>
                        <?php if (session('user_role') === 'admin'): ?><select class="edit-control" form="<?= $formId ?>" name="type"><option value="TI" <?= $v->type === 'TI' ? 'selected' : '' ?>>TI</option><option value="JEMBER" <?= $v->type === 'JEMBER' ? 'selected' : '' ?>>JEMBER</option></select><?php endif; ?>
                    </td>
                    <td>
                        <span class="view-value"><?= number_format($v->trips) ?></span>
                        <?php if (session('user_role') === 'admin'): ?><input class="edit-control" form="<?= $formId ?>" name="trips" type="number" min="0" value="<?= e($v->trips) ?>" required><?php endif; ?>
                    </td>
                    <td>
                        <span class="view-value">Rp <?= number_format($v->gross_revenue, 0, ',', '.') ?></span>
                        <?php if (session('user_role') === 'admin'): ?><input class="edit-control" form="<?= $formId ?>" name="gross_revenue" type="number" step="0.01" min="0" value="<?= e($v->gross_revenue) ?>" data-gross-input required><?php endif; ?>
                    </td>
                    <td>
                        <span class="view-value">Rp <?= number_format($v->travel_allowance, 0, ',', '.') ?></span>
                        <?php if (session('user_role') === 'admin'): ?><input class="edit-control" form="<?= $formId ?>" name="travel_allowance" type="number" step="0.01" min="0" value="<?= e($v->travel_allowance) ?>" data-allowance-input required><?php endif; ?>
                    </td>
                    <td class="positive">
                        <b class="view-value" data-profit-display>Rp <?= number_format($v->profit, 0, ',', '.') ?></b>
                        <?php if (session('user_role') === 'admin'): ?><span class="edit-control profit-preview" data-profit-preview>Rp <?= number_format($v->profit, 0, ',', '.') ?></span><?php endif; ?>
                    </td>
                    <td>
                        <span class="view-value">Rp <?= number_format($v->operating_cost, 0, ',', '.') ?></span>
                        <?php if (session('user_role') === 'admin'): ?><input class="edit-control" form="<?= $formId ?>" name="operating_cost" type="number" min="0" step="0.01" value="<?= e($v->operating_cost) ?>" required><?php endif; ?>
                    </td>
                    <td>
                        <span class="view-value status-chip <?= $v->availability === 'Ready' ? 'ready' : '' ?>"><?= e($v->availability) ?></span>
                        <?php if (session('user_role') === 'admin'): ?><select class="edit-control" form="<?= $formId ?>" name="availability"><option value="Ready" <?= $v->availability === 'Ready' ? 'selected' : '' ?>>Ready</option><option value="Disewakan" <?= $v->availability === 'Disewakan' ? 'selected' : '' ?>>Disewakan</option></select><?php endif; ?>
                    </td>
                    <td>
                        <span class="view-value"><?= \Illuminate\Support\Carbon::parse($v->period)->format('m/Y') ?></span>
                        <?php if (session('user_role') === 'admin'): ?><input class="edit-control" form="<?= $formId ?>" type="month" name="period" value="<?= \Illuminate\Support\Carbon::parse($v->period)->format('Y-m') ?>" required><?php endif; ?>
                    </td>
                    <?php if (session('user_role') === 'admin'): ?>
                    <td class="inline-actions">
                        <form id="<?= $formId ?>" method="post" action="<?= route('vehicles.update', $v->id) ?>">
                            <?= csrf_field() ?><?= method_field('PUT') ?>
                        </form>
                        <button class="btn outline small view-value" type="button" data-edit-row>Kelola</button>
                        <button class="btn primary small edit-control" type="submit" form="<?= $formId ?>">Simpan</button>
                        <button class="btn outline small edit-control" type="button" data-cancel-row>Batal</button>
                        <form method="post" action="<?= route('vehicles.destroy', $v->id) ?>" onsubmit="return confirm('Hapus kendaraan <?= e($v->vehicle_number) ?> dari bulan ini?')">
                            <?= csrf_field() ?><?= method_field('DELETE') ?>
                            <button class="btn danger small view-value" type="submit">Hapus</button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if ($vehicles->isEmpty()): ?>
                <tr><td colspan="<?= session('user_role') === 'admin' ? 11 : 10 ?>" class="empty-cell">Belum ada data kendaraan pada bulan ini. Silakan tambahkan kendaraan atau gunakan Import Data.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="note-box"><b>Informasi ranking:</b> Setelah kendaraan ditambahkan, diubah, atau dihapus, sistem akan menghitung ulang ranking MOORA untuk bulan terkait. Data kendaraan dengan nomor plat sama dapat dicatat kembali pada bulan yang berbeda.</div>
</section>
