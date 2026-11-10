<section class="page-heading">
    <div><span class="eyebrow">LAPORAN BIAYA ARMADA</span><h1>Biaya Operasional</h1><p>Rekapitulasi biaya operasional setiap kendaraan mengikuti urutan ranking terbaru.</p></div>
    <div class="heading-art"><img src="<?= asset('images/truck-ti.svg') ?>" alt="Armada TI"><img src="<?= asset('images/truck-jember.svg') ?>" alt="Armada JEMBER"></div>
</section>

<div class="report-toolbar">
    <form method="get" action="<?= route('costs') ?>" class="period-filter">
        <label for="cost-period">Pilih Bulan</label>
        <input id="cost-period" type="month" name="period" value="<?= e($selectedPeriod) ?>" onchange="this.form.submit()">
    </form>
</div>

<div class="stat-grid">
    <article class="stat-card"><span class="stat-icon yellow">Rp</span><small>Total Biaya Operasional</small><strong class="money-value">Rp <?= number_format($total, 0, ',', '.') ?></strong><span class="stat-note">Akumulasi bulan terpilih</span></article>
    <article class="stat-card"><span class="stat-icon gray">▤</span><small>Kendaraan Tercatat</small><strong><?= number_format($vehicles->count()) ?></strong><span class="stat-note">Unit memiliki data biaya</span></article>
</div>

<section class="panel">
    <div class="panel-head"><div><h2>Rincian Biaya per Kendaraan</h2><p>Urutan mengikuti ranking MOORA terbaru untuk periode <?= e(\Illuminate\Support\Carbon::createFromFormat('Y-m', $selectedPeriod)->translatedFormat('F Y')) ?>.</p></div><button class="btn outline small" data-export-table="cost-table" data-filename="laporan-biaya-operasional-<?= e($selectedPeriod) ?>.csv">⇩ Ekspor CSV</button></div>
    <div class="table-wrap"><table id="cost-table"><thead><tr><th>Ranking</th><th>No. Plat Kendaraan</th><th>Tipe</th><th>Jumlah Perjalanan</th><th>Biaya Operasional</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($vehicles as $i => $v): ?>
            <tr><td><span class="rank-pill <?= isset($v->ranking) && (int) $v->ranking === 1 ? 'first' : '' ?>"><?= isset($v->ranking) ? e($v->ranking) : ($i + 1) ?></span></td><td><b><?= e($v->vehicle_number) ?></b></td><td><span class="type-chip <?= $v->type === 'TI' ? 'ti' : '' ?>"><?= e($v->type) ?></span></td><td><?= number_format($v->trips) ?></td><td class="cost-value">Rp <?= number_format($v->operating_cost, 0, ',', '.') ?></td><td><span class="status-chip <?= $v->availability === 'Ready' ? 'ready' : '' ?>"><?= e($v->availability) ?></span></td></tr>
        <?php endforeach; ?>
        <?php if ($vehicles->isEmpty()): ?><tr><td colspan="6" class="empty-cell">Belum ada data biaya operasional pada bulan ini.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
