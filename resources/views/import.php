<section class="page-heading">
    <div><span class="eyebrow">INPUT DATA OPERASIONAL</span><h1>Import Data Kendaraan</h1><p>Unggah file Excel atau CSV untuk memasukkan data armada satu bulan sekaligus.</p></div>
    <div class="heading-art"><img src="<?= asset('images/truck-jember.svg') ?>" alt="Armada JEMBER"><img src="<?= asset('images/truck-ti.svg') ?>" alt="Armada TI"></div>
</section>

<section class="panel import-panel">
    <div class="import-icon">⇧</div><h2>Unggah File Data Kendaraan</h2>
    <p>Format yang didukung adalah Excel <b>.xlsx</b> dan CSV UTF-8 <b>.csv</b>. Data akan masuk ke bulan yang dipilih dan ranking bulan tersebut dihitung ulang secara otomatis.</p>
    <form method="post" action="<?= route('import.submit') ?>" enctype="multipart/form-data" class="upload-form">
        <?= csrf_field() ?>
        <label class="import-period-label">Bulan Data<input type="month" name="period" value="<?= e($selectedPeriod) ?>" required></label>
        <label class="file-drop">
            <input type="file" name="file" accept=".xlsx,.csv,.txt" required>
            <span class="file-symbol">▧</span><b>Pilih file dari komputer</b>
            <small>Format .xlsx atau .csv · Maksimal 10 MB</small>
            <span class="selected-file" data-file-label>Belum ada file dipilih</span>
        </label>
        <button class="btn primary">Mulai Import Data</button>
    </form>
</section>

<section class="panel">
    <h2>Format Kolom Excel / CSV</h2>
    <p>Gunakan kolom berikut. Keuntungan tidak perlu dimasukkan karena dihitung otomatis oleh sistem.</p>
    <div class="table-wrap"><table><thead><tr><th>No. Plat Kendaraan</th><th>Tipe Kendaraan</th><th>Jumlah Perjalanan</th><th>Jumlah Ritase / Pendapatan</th><th>Uang Jalan</th><th>Biaya Operasional</th><th>Status</th></tr></thead><tbody><tr><td>BG 8306 OH</td><td>JEMBER</td><td>44</td><td>15000000</td><td>7000000</td><td>1500000</td><td>Ready</td></tr></tbody></table></div>
    <div class="note-box"><b>Catatan import:</b> Nomor plat dan bulan yang sama akan diperbarui, sedangkan kombinasi nomor plat dan bulan baru akan ditambahkan. Tipe kendaraan harus TI atau JEMBER, status Ready atau Disewakan. Keuntungan = pendapatan − uang jalan.</div>
    <a class="btn outline small" href="<?= route('vehicles', ['period' => $selectedPeriod]) ?>">← Kembali ke Data Kendaraan</a>
</section>
