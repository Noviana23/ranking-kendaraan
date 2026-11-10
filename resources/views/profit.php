<section class="page-heading">
    <div><span class="eyebrow">LAPORAN KEUANGAN ARMADA</span><h1>Untung Perusahaan</h1><p>Rekapitulasi pendapatan, uang jalan, dan keuntungan berdasarkan ranking kendaraan.</p></div>
    <div class="heading-art"><img src="<?= asset('images/truck-jember.svg') ?>" alt="Armada JEMBER"><img src="<?= asset('images/truck-ti.svg') ?>" alt="Armada TI"></div>
</section>

<div class="report-toolbar">
    <form method="get" action="<?= route('profit') ?>" class="period-filter">
        <label for="profit-period">Pilih Bulan</label>
        <input id="profit-period" type="month" name="period" value="<?= e($selectedPeriod) ?>" onchange="this.form.submit()">
    </form>
</div>

<div class="stat-grid three">
    <article class="stat-card"><span class="stat-icon green">↗</span><small>Total Pendapatan / Ritase</small><strong class="money-value">Rp <?= number_format($revenue, 0, ',', '.') ?></strong></article>
    <article class="stat-card"><span class="stat-icon yellow">↘</span><small>Total Uang Jalan</small><strong class="money-value">Rp <?= number_format($allowance, 0, ',', '.') ?></strong></article>
    <article class="stat-card"><span class="stat-icon pink">Rp</span><small>Total Keuntungan</small><strong class="money-value">Rp <?= number_format($total, 0, ',', '.') ?></strong></article>
</div>

<section class="panel">
    <div class="panel-head"><div><h2>Rincian Keuntungan per Kendaraan</h2><p>Urutan mengikuti ranking MOORA terbaru untuk periode <?= e(\Illuminate\Support\Carbon::createFromFormat('Y-m', $selectedPeriod)->translatedFormat('F Y')) ?>.</p></div><button class="btn outline small" data-export-table="profit-table" data-filename="laporan-untung-perusahaan-<?= e($selectedPeriod) ?>.csv">⇩ Ekspor CSV</button></div>
    <div class="table-wrap"><table id="profit-table"><thead><tr><th>Ranking</th><th>No. Plat Kendaraan</th><th>Tipe</th><th>Jumlah Ritase / Pendapatan</th><th>Uang Jalan</th><th>Keuntungan Perusahaan</th></tr></thead><tbody>
        <?php foreach ($vehicles as $i => $v): ?>
            <tr><td><span class="rank-pill <?= isset($v->ranking) && (int) $v->ranking === 1 ? 'first' : '' ?>"><?= isset($v->ranking) ? e($v->ranking) : ($i + 1) ?></span></td><td><b><?= e($v->vehicle_number) ?></b></td><td><span class="type-chip <?= $v->type === 'TI' ? 'ti' : '' ?>"><?= e($v->type) ?></span></td><td>Rp <?= number_format($v->gross_revenue, 0, ',', '.') ?></td><td>Rp <?= number_format($v->travel_allowance, 0, ',', '.') ?></td><td class="<?= $v->profit >= 0 ? 'positive' : 'cost-value' ?>"><b>Rp <?= number_format($v->profit, 0, ',', '.') ?></b></td></tr>
        <?php endforeach; ?>
        <?php if ($vehicles->isEmpty()): ?><tr><td colspan="6" class="empty-cell">Belum ada data keuntungan pada bulan ini.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
