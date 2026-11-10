<?php

namespace App\Http\Controllers;

use App\Services\MooraRankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MooraController extends Controller
{
    public function index(Request $request, MooraRankingService $ranking)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $validated['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? now()->endOfMonth()->toDateString();
        $run = $ranking->latestRunForRange($startDate, $endDate);
        $results = collect();
        $weights = [];

        if ($run) {
            $results = DB::table('moora_results as r')
                ->join('vehicles as v', 'v.id', '=', 'r.vehicle_id')
                ->where('r.run_id', $run->id)
                ->orderBy('r.ranking')
                ->select(
                    'r.*',
                    'v.vehicle_number',
                    'v.type',
                    'v.trips',
                    'v.profit',
                    'v.operating_cost',
                    'v.availability',
                    'v.period'
                )
                ->get();

            $weights = json_decode($run->weight_snapshot, true) ?: [];
        }

        return $this->page(
            'ranking',
            compact('run', 'results', 'weights', 'startDate', 'endDate'),
            'Ranking Kendaraan',
            'ranking'
        );
    }

    public function calculate(Request $request, MooraRankingService $ranking)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        try {
            $runId = $ranking->calculate(
                $validated['start_date'],
                $validated['end_date'],
                (int) session('user_id')
            );
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        if (!$runId) {
            return back()->withInput()->with('error', 'Tidak ada data kendaraan pada rentang waktu yang dipilih.');
        }

        return redirect()->route('ranking', [
            'start_date' => Carbon::parse($validated['start_date'])->toDateString(),
            'end_date' => Carbon::parse($validated['end_date'])->toDateString(),
        ])->with('success', 'Ranking kendaraan berhasil dihitung ulang untuk periode yang dipilih.');
    }

    public function export(Request $request, MooraRankingService $ranking)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $validated['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? now()->endOfMonth()->toDateString();
        $run = $ranking->latestRunForRange($startDate, $endDate);

        abort_unless($run, 404, 'Belum ada hasil ranking pada rentang tanggal tersebut.');

        $rows = DB::table('moora_results as r')
            ->join('vehicles as v', 'v.id', '=', 'r.vehicle_id')
            ->where('r.run_id', $run->id)
            ->orderBy('r.ranking')
            ->select('r.ranking', 'v.vehicle_number', 'v.type', 'v.trips', 'v.profit', 'v.operating_cost', 'r.optimization_value', 'v.availability', 'v.period')
            ->get();

        $filename = 'ranking-kendaraan-' . Carbon::parse($startDate)->format('Ymd') . '-' . Carbon::parse($endDate)->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($rows, $startDate, $endDate) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Ranking Kendaraan'], ';');
            fputcsv($output, ['Periode', Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y')], ';');
            fputcsv($output, [], ';');
            fputcsv($output, [
                'Peringkat',
                'No. Plat Kendaraan',
                'Tipe Kendaraan',
                'Jumlah Perjalanan',
                'Keuntungan (Rp)',
                'Biaya Operasional (Rp)',
                'Nilai Optimasi MOORA',
                'Status',
                'Bulan Data',
            ], ';');

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row->ranking,
                    $row->vehicle_number,
                    $row->type,
                    $row->trips,
                    $row->profit,
                    $row->operating_cost,
                    $row->optimization_value,
                    $row->availability,
                    Carbon::parse($row->period)->format('m/Y'),
                ], ';');
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
