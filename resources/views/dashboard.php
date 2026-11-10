<section class="page-heading">
    <div>
        <span class="eyebrow">CV ANGKUTAN TERANG MANDIRI</span>
        <h1>Dashboard</h1>
        <p>Ringkasan aktivitas dan performa armada untuk bulan yang dipilih.</p>
    </div>
    <div class="heading-period">
        <label for="dashboard-period">Periode Dashboard</label>
        <form method="get" action="<?= route('dashboard') ?>">
            <input id="dashboard-period" type="month" name="period" value="<?= e($selectedPeriod) ?>" onchange="this.form.submit()">
        </form>
    </div>
</section>

<div class="stat-grid">
    <article class="stat-card"><span class="stat-icon yellow">▤</span><small>Total Kendaraan</small><strong><?= number_format($summary['total_vehicles']) ?></strong><span class="stat-note">Unit armada pada periode ini</span></article>
    <article class="stat-card"><span class="stat-icon green">✓</span><small>Kendaraan Ready</small><strong><?= number_format($summary['ready_vehicles']) ?></strong><span class="stat-note">Siap dijadwalkan</span></article>
    <article class="stat-card"><span class="stat-icon pink">↗</span><small>Total Perjalanan</small><strong><?= number_format($summary['total_trips']) ?></strong><span class="stat-note">Akumulasi periode ini</span></article>
    <article class="stat-card"><span class="stat-icon gray">Rp</span><small>Biaya Operasional</small><strong class="money-value">Rp <?= number_format($summary['total_operating_cost'], 0, ',', '.') ?></strong><span class="stat-note">Total biaya tercatat</span></article>
</div>

<div class="content-grid">
    <section class="panel panel-large">
        <div class="panel-head">
            <div><span class="eyebrow">PERINGKAT TERBARU</span><h2>Ranking Kendaraan</h2><p>Hasil perhitungan MOORA bulan <?= \Illuminate\Support\Carbon::createFromFormat('Y-m', $selectedPeriod)->translatedFormat('F Y') ?>.</p></div>
            <a class="btn outline small" href="<?= route('ranking', ['start_date' => $selectedPeriod . '-01', 'end_date' => \Illuminate\Support\Carbon::createFromFormat('Y-m', $selectedPeriod)->endOfMonth()->toDateString()]) ?>">Lihat Semua →</a>
        </div>
        <?php if ($topResults->isEmpty()): ?>
            <div class="empty-state"><div class="empty-icon">🏆</div><h3>Belum ada hasil ranking</h3><p>Tambahkan data kendaraan. Sistem akan menghitung ranking otomatis.</p>
                <?php if (session('user_role') === 'admin'): ?><a class="btn primary" href="<?= route('vehicles', ['period' => $selectedPeriod]) ?>">Tambah Kendaraan</a><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-wrap"><table><thead><tr><th>Rank</th><th>No. Plat Kendaraan</th><th>Tipe</th><th>Perjalanan</th><th>Keuntungan</th><th>Nilai Optimasi</th></tr></thead><tbody>
                <?php foreach ($topResults as $row): ?>
                    <tr><td><span class="rank-pill <?= (int) $row->ranking === 1 ? 'first' : '' ?>"><?= $row->ranking ?></span></td><td><b><?= e($row->vehicle_number) ?></b></td><td><span class="type-chip <?= $row->type === 'TI' ? 'ti' : '' ?>"><?= e($row->type) ?></span></td><td><?= number_format($row->trips) ?></td><td class="positive">Rp <?= number_format($row->profit, 0, ',', '.') ?></td><td><b><?= number_format($row->optimization_value, 6) ?></b></td></tr>
                <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>

    <section class="panel quick-panel">
        <span class="eyebrow">AKSES CEPAT</span><h2>Kelola Armada</h2><p>Gunakan menu berikut untuk memperbarui data operasional.</p>
        <a class="quick-link" href="<?= route('vehicles', ['period' => $selectedPeriod]) ?>"><span>▤</span><div><b>Data Kendaraan</b><small>Tambah dan perbarui armada</small></div>→</a>
        <a class="quick-link" href="<?= route('ranking', ['start_date' => $selectedPeriod . '-01', 'end_date' => \Illuminate\Support\Carbon::createFromFormat('Y-m', $selectedPeriod)->endOfMonth()->toDateString()]) ?>"><span>🏆</span><div><b>Ranking Kendaraan</b><small>Lihat hasil penilaian MOORA</small></div>→</a>
        <a class="quick-link" href="<?= route('profit', ['period' => $selectedPeriod]) ?>"><span>↗</span><div><b>Untung Perusahaan</b><small>Lihat laporan keuntungan</small></div>→</a>
        <div class="mini-trucks"><img src="<?= asset('images/truck-ti.svg') ?>" alt="Armada TI"><img src="<?= asset('images/truck-jember.svg') ?>" alt="Armada JEMBER"></div>
    </section>
</div>

<div class="summary-strip">
    <div><span class="eyebrow">KEUNTUNGAN PERUSAHAAN</span><strong>Rp <?= number_format($summary['total_profit'], 0, ',', '.') ?></strong></div>
    <div><span class="eyebrow">KENDARAAN DISEWAKAN</span><strong><?= number_format($summary['rented_vehicles']) ?> Unit</strong></div>
    <div><span class="eyebrow">STATUS DATABASE</span><strong><i class="status-dot"></i> Terhubung</strong></div>
</div>
