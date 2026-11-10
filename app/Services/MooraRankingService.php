<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MooraRankingService
{
    public function calculate(string $startDate, string $endDate, ?int $userId = null): ?int
    {
        $start = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->endOfMonth();

        if ($start->gt($end)) {
            throw new RuntimeException('Tanggal awal tidak boleh melewati tanggal akhir.');
        }

        $vehicles = DB::table('vehicles')
            ->whereBetween('period', [$start->toDateString(), $end->copy()->startOfMonth()->toDateString()])
            ->orderBy('id')
            ->get();

        if ($vehicles->isEmpty()) {
            return null;
        }

        $criteria = DB::table('criteria')->orderBy('id')->get();
        if (abs((float) $criteria->sum('weight') - 100) > 0.001) {
            throw new RuntimeException('Total bobot kriteria harus tepat 100% sebelum perhitungan.');
        }

        $fields = ['C1' => 'trips', 'C2' => 'profit', 'C3' => 'operating_cost'];
        $denominators = [];
        foreach ($fields as $code => $field) {
            $denominators[$code] = sqrt($vehicles->sum(
                fn ($vehicle) => pow((float) $vehicle->{$field}, 2)
            ));
        }

        $weights = $criteria->keyBy('code')
            ->map(fn ($criterion) => (float) $criterion->weight / 100)
            ->all();

        $results = [];
        foreach ($vehicles as $vehicle) {
            $normalized = [];
            foreach ($fields as $code => $field) {
                $normalized[$code] = $denominators[$code] > 0
                    ? (float) $vehicle->{$field} / $denominators[$code]
                    : 0.0;
            }

            $weighted = [];
            foreach ($fields as $code => $field) {
                $weighted[$code] = $normalized[$code] * ($weights[$code] ?? 0);
            }

            $results[] = [
                'vehicle_id' => $vehicle->id,
                'norm_c1' => $normalized['C1'],
                'norm_c2' => $normalized['C2'],
                'norm_c3' => $normalized['C3'],
                'weighted_c1' => $weighted['C1'],
                'weighted_c2' => $weighted['C2'],
                'weighted_c3' => $weighted['C3'],
                'optimization_value' => $weighted['C1'] + $weighted['C2'] - $weighted['C3'],
                'availability_snapshot' => $vehicle->availability,
            ];
        }

        usort($results, fn ($a, $b) => $b['optimization_value'] <=> $a['optimization_value']);
        foreach ($results as $index => &$result) {
            $result['ranking'] = $index + 1;
        }
        unset($result);

        return DB::transaction(function () use ($results, $vehicles, $criteria, $start, $end, $userId) {
            $runId = DB::table('moora_runs')->insertGetId([
                'total_vehicles' => count($vehicles),
                'weight_snapshot' => $criteria->toJson(),
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($results as $result) {
                DB::table('moora_results')->insert($result + [
                    'run_id' => $runId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $runId;
        });
    }

    public function recalculateMonth(string $period, ?int $userId = null): ?int
    {
        $month = Carbon::parse($period)->startOfMonth();
        $start = $month->toDateString();
        $end = $month->copy()->endOfMonth()->toDateString();

        $hasVehicles = DB::table('vehicles')->whereDate('period', $start)->exists();
        if (!$hasVehicles) {
            DB::table('moora_runs')
                ->whereDate('period_start', $start)
                ->whereDate('period_end', $end)
                ->delete();

            return null;
        }

        $weightTotal = (float) DB::table('criteria')->sum('weight');
        if (abs($weightTotal - 100) > 0.001) {
            return null;
        }

        return $this->calculate($start, $end, $userId);
    }

    public function latestRunForRange(string $startDate, string $endDate): ?object
    {
        $start = Carbon::parse($startDate)->startOfMonth()->toDateString();
        $end = Carbon::parse($endDate)->endOfMonth()->toDateString();

        return DB::table('moora_runs')
            ->whereDate('period_start', $start)
            ->whereDate('period_end', $end)
            ->latest('id')
            ->first();
    }
}
