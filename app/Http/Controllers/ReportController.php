<?php

namespace App\Http\Controllers;

use App\Services\MooraRankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function monthFromRequest(Request $request): array
    {
        $request->validate(['period' => ['nullable', 'date_format:Y-m']]);
        $period = $request->input('period', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return [$period, $start, $end];
    }

    private function rankedVehicles(string $periodStart, string $periodEnd, MooraRankingService $ranking)
    {
        $run = $ranking->latestRunForRange($periodStart, $periodEnd);
        $query = DB::table('vehicles as v')
            ->whereDate('v.period', $periodStart);

        if ($run) {
            return $query
                ->leftJoin('moora_results as r', function ($join) use ($run) {
                    $join->on('r.vehicle_id', '=', 'v.id')->where('r.run_id', '=', $run->id);
                })
                ->orderByRaw('CASE WHEN r.ranking IS NULL THEN 1 ELSE 0 END')
                ->orderBy('r.ranking')
                ->orderBy('v.vehicle_number')
                ->select('v.*', 'r.ranking', 'r.optimization_value')
                ->get();
        }

        return $query->orderByDesc('v.profit')->orderBy('v.vehicle_number')->select('v.*')->get();
    }

    public function profit(Request $request, MooraRankingService $ranking)
    {
        [$selectedPeriod, $start, $end] = $this->monthFromRequest($request);
        $vehicles = $this->rankedVehicles($start->toDateString(), $end->toDateString(), $ranking);

        $total = (float) $vehicles->sum('profit');
        $revenue = (float) $vehicles->sum('gross_revenue');
        $allowance = (float) $vehicles->sum('travel_allowance');

        return $this->page('profit', compact('vehicles', 'total', 'revenue', 'allowance', 'selectedPeriod'), 'Untung Perusahaan', 'profit');
    }

    public function costs(Request $request, MooraRankingService $ranking)
    {
        [$selectedPeriod, $start, $end] = $this->monthFromRequest($request);
        $vehicles = $this->rankedVehicles($start->toDateString(), $end->toDateString(), $ranking);
        $total = (float) $vehicles->sum('operating_cost');

        return $this->page('costs', compact('vehicles', 'total', 'selectedPeriod'), 'Biaya Operasional', 'costs');
    }
}
