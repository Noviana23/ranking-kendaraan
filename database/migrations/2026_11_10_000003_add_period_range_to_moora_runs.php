<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('moora_runs', function (Blueprint $table) {
            $table->date('period_start')->nullable()->after('total_vehicles');
            $table->date('period_end')->nullable()->after('period_start');
        });

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        DB::table('moora_runs')->whereNull('period_start')->update([
            'period_start' => $monthStart,
            'period_end' => $monthEnd,
        ]);
    }

    public function down(): void
    {
        Schema::table('moora_runs', function (Blueprint $table) {
            $table->dropColumn(['period_start', 'period_end']);
        });
    }
};
