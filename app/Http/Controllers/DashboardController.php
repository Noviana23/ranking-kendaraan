<?php

namespace App\Http\Controllers;

use App\Services\MooraRankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request, MooraRankingService $ranking)
    {
        $request->validate(['period' => ['nullable', 'date_format:Y-m']]);
        $selectedPeriod = $request->input('period', now()->format('Y-m'));
        $periodStart = Carbon::createFromFormat('Y-m', $selectedPeriod)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();
        $vehicles = DB::table('vehicles')->whereDate('period', $periodStart->toDateString());

        $summary = [
            'total_vehicles' => (clone $vehicles)->count(),
            'ready_vehicles' => (clone $vehicles)->where('availability', 'Ready')->count(),
            'rented_vehicles' => (clone $vehicles)->where('availability', 'Disewakan')->count(),
            'total_trips' => (int) (clone $vehicles)->sum('trips'),
            'total_profit' => (float) (clone $vehicles)->sum('profit'),
            'total_operating_cost' => (float) (clone $vehicles)->sum('operating_cost'),
        ];

        $run = $ranking->latestRunForRange($periodStart->toDateString(), $periodEnd->toDateString());
        $topResults = $run
            ? DB::table('moora_results as r')
                ->join('vehicles as v', 'v.id', '=', 'r.vehicle_id')
                ->where('r.run_id', $run->id)
                ->orderBy('r.ranking')
                ->limit(5)
                ->select('r.ranking', 'r.optimization_value', 'v.vehicle_number', 'v.type', 'v.availability', 'v.trips', 'v.profit', 'v.operating_cost')
                ->get()
            : collect();

        return $this->page('dashboard', compact('summary', 'run', 'topResults', 'selectedPeriod'), 'Dashboard', 'dashboard');
    }
}
