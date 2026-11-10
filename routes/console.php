<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('app:about-ranking', function () {
    $this->comment('Aplikasi Ranking Kendaraan - SPK MOORA');
})->purpose('Menampilkan informasi aplikasi Ranking Kendaraan');
