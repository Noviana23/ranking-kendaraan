<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Make the migration safe to retry if the column was created before a failure.
        if (!Schema::hasColumn('vehicles', 'period')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->date('period')->nullable()->after('availability');
            });
        }

        // Assign existing records to the current month before adding the monthly key.
        $currentMonth = now()->startOfMonth()->toDateString();
        DB::table('vehicles')->whereNull('period')->update(['period' => $currentMonth]);

        // The existing database may use a different index name, or have no such
        // unique index at all. Detect the actual one-column unique index first.
        foreach (Schema::getIndexes('vehicles') as $index) {
            $columns = array_map('strtolower', $index['columns'] ?? []);

            if (!empty($index['unique']) && $columns === ['vehicle_number']) {
                Schema::table('vehicles', function (Blueprint $table) use ($index) {
                    $table->dropUnique($index['name']);
                });
            }
        }

        // Add the monthly uniqueness rule only when it does not already exist.
        $hasMonthlyUnique = false;
        foreach (Schema::getIndexes('vehicles') as $index) {
            $columns = array_map('strtolower', $index['columns'] ?? []);
            if (!empty($index['unique']) && $columns === ['vehicle_number', 'period']) {
                $hasMonthlyUnique = true;
                break;
            }
        }

        if (!$hasMonthlyUnique) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->unique(['vehicle_number', 'period'], 'vehicles_plate_period_unique');
            });
        }
    }

    public function down(): void
    {
        // Remove the monthly unique index by its actual presence, not by assumption.
        foreach (Schema::getIndexes('vehicles') as $index) {
            $columns = array_map('strtolower', $index['columns'] ?? []);
            if (!empty($index['unique']) && $columns === ['vehicle_number', 'period']) {
                Schema::table('vehicles', function (Blueprint $table) use ($index) {
                    $table->dropUnique($index['name']);
                });
            }
        }

        if (Schema::hasColumn('vehicles', 'period')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->dropColumn('period');
            });
        }

        // Restore the former unique rule only when existing data allows it.
        $hasVehicleNumberUnique = false;
        foreach (Schema::getIndexes('vehicles') as $index) {
            $columns = array_map('strtolower', $index['columns'] ?? []);
            if (!empty($index['unique']) && $columns === ['vehicle_number']) {
                $hasVehicleNumberUnique = true;
                break;
            }
        }

        $hasDuplicateVehicleNumbers = DB::table('vehicles')
            ->select('vehicle_number')
            ->groupBy('vehicle_number')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if (!$hasVehicleNumberUnique && !$hasDuplicateVehicleNumbers) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->unique('vehicle_number', 'vehicles_vehicle_number_unique');
            });
        }
    }
};
