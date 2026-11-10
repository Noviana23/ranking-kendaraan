<section class="page-heading">
    <div>
        <span class="eyebrow">HASIL ANALISIS SISTEM</span>
        <h1>Ranking Kendaraan</h1>
        <p>Urutan prioritas kendaraan berdasarkan nilai optimasi metode MOORA.</p>
    </div>
    <div class="heading-art">
        <img src="<?= asset('images/truck-ti.svg') ?>" alt="Armada TI">
        <img src="<?= asset('images/truck-jember.svg') ?>" alt="Armada JEMBER">
    </div>
</section>

<section class="panel ranking-filter-panel">
    <div class="panel-head"><div><h2>Kurun Waktu Penilaian</h2><p>Pilih tanggal awal dan akhir. Data bulanan di dalam rentang tersebut akan ikut dinilai.</p></div></div>
    <form method="get" action="<?= route('ranking') ?>" class="ranking-date-form">
        <label>Dari Tanggal<input type="date" name="start_date" value="<?= e($startDate) ?>" required></label>
        <span class="date-separator">sampai</span>
        <label>Sampai Tanggal<input type="date" name="end_date" value="<?= e($endDate) ?>" required></label>
        <button class="btn outline" type="submit">Tampilkan Periode</button>
    </form>
    <?php if (session('user_role') === 'admin'): ?>
        <form method="post" action="<?= route('ranking.calculate') ?>" class="calculate-form">
            <?= csrf_field() ?>
            <input type="hidden" name="start_date" value="<?= e($startDate) ?>">
            <input type="hidden" name="end_date" value="<?= e($endDate) ?>">
            <button class="btn primary" type="submit">↻ Hitung Ranking Kendaraan</button>
        </form>
    <?php endif; ?>
</section>

<?php if ($run): ?>
    <?php
    $weightMap = [];
    foreach ($weights as $weightItem) {
        $weightMap[$weightItem['code']] = (float) $weightItem['weight'];
    }
    ?>
    <div class="summary-strip ranking-summary">
        <div><span class="eyebrow">JUMLAH KENDARAAN DINILAI</span><strong><?= number_format($run->total_vehicles) ?> Unit</strong></div>
        <div><span class="eyebrow">RENTANG WAKTU DATA</span><strong><?= \Illuminate\Support\Carbon::parse($run->period_start)->format('d/m/Y') ?> – <?= \Illuminate\Support\Carbon::parse($run->period_end)->format('d/m/Y') ?></strong></div>
        <div><span class="eyebrow">METODE</span><strong>MOORA</strong></div>
        <div><span class="eyebrow">BOBOT</span><strong>C1 <?= number_format($weightMap['C1'] ?? 0, 0) ?>% · C2 <?= number_format($weightMap['C2'] ?? 0, 0) ?>% · C3 <?= number_format($weightMap['C3'] ?? 0, 0) ?>%</strong></div>
    </div>
<?php endif; ?>

<section class="panel">
    <div class="panel-head">
        <div><h2>Hasil Peringkat</h2><p>Semakin tinggi nilai optimasi, semakin tinggi prioritas penjadwalan kendaraan.</p></div>
        <?php if ($results->isNotEmpty()): ?>
            <a class="btn outline small" href="<?= route('ranking.export', ['start_date' => $startDate, 'end_date' => $endDate]) ?>">⇩ Ekspor CSV</a>
        <?php endif; ?>
    </div>

    <?php if ($results->isEmpty()): ?>
        <div class="empty-state"><div class="empty-icon">🏆</div><h3>Belum ada hasil ranking untuk periode ini</h3><p>Pilih rentang tanggal, tambahkan data kendaraan pada bulan terkait, lalu klik Hitung Ranking Kendaraan.</p><a class="btn outline" href="<?= route('vehicles') ?>">Buka Data Kendaraan</a></div>
    <?php else: ?>
        <div class="table-wrap">
            <table id="ranking-table">
                <thead><tr><th>Peringkat</th><th>No. Plat Kendaraan</th><th>Tipe</th><th>Jumlah Perjalanan</th><th>Keuntungan</th><th>Biaya Operasional</th><th>Nilai Optimasi</th><th>Status</th><th>Bulan</th></tr></thead>
                <tbody>
                <?php foreach ($results as $r): ?>
                    <tr>
                        <td><span class="rank-pill <?= (int) $r->ranking === 1 ? 'first' : '' ?>"><?= (int) $r->ranking === 1 ? '★' : $r->ranking ?></span></td>
                        <td><b><?= e($r->vehicle_number) ?></b></td>
                        <td><span class="type-chip <?= $r->type === 'TI' ? 'ti' : '' ?>"><?= e($r->type) ?></span></td>
                        <td><?= number_format($r->trips) ?></td>
                        <td class="positive">Rp <?= number_format($r->profit, 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($r->operating_cost, 0, ',', '.') ?></td>
                        <td><b><?= number_format($r->optimization_value, 7, ',', '.') ?></b></td>
                        <td><span class="status-chip <?= $r->availability === 'Ready' ? 'ready' : '' ?>"><?= e($r->availability) ?></span></td>
                        <td><?= \Illuminate\Support\Carbon::parse($r->period)->format('m/Y') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <details class="detail-fold">
            <summary>Rincian Normalisasi dan Normalisasi Berbobot</summary>
            <div class="table-wrap">
                <table><thead><tr><th>Kendaraan</th><th>N(C1)</th><th>N(C2)</th><th>N(C3)</th><th>Terbobot C1</th><th>Terbobot C2</th><th>Terbobot C3</th><th>Optimasi</th></tr></thead>
                    <tbody><?php foreach ($results as $r): ?><tr><td><b><?= e($r->vehicle_number) ?></b></td><td><?= number_format($r->norm_c1, 6) ?></td><td><?= number_format($r->norm_c2, 6) ?></td><td><?= number_format($r->norm_c3, 6) ?></td><td><?= number_format($r->weighted_c1, 6) ?></td><td><?= number_format($r->weighted_c2, 6) ?></td><td><?= number_format($r->weighted_c3, 6) ?></td><td><?= number_format($r->optimization_value, 6) ?></td></tr><?php endforeach; ?></tbody>
                </table>
            </div>
        </details>
        <div class="note-box"><b>Rumus MOORA:</b> Yi = (C1 × W1) + (C2 × W2) − (C3 × W3). C1 dan C2 merupakan kriteria benefit, sedangkan C3 merupakan kriteria cost. Data yang dihitung hanya kendaraan dalam kurun waktu yang dipilih.</div>
    <?php endif; ?>
</section>
