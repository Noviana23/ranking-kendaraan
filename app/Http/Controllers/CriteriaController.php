<?php

namespace App\Http\Controllers;

use App\Services\MooraRankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CriteriaController extends Controller
{
    public function index()
    {
        $criteria = DB::table('criteria')->orderBy('id')->get();

        return $this->page('criteria', compact('criteria'), 'Kriteria MOORA', 'criteria');
    }

    public function update(Request $request, MooraRankingService $ranking)
    {
        $data = $request->validate([
            'C1' => ['required', 'numeric', 'between:0,100'],
            'C2' => ['required', 'numeric', 'between:0,100'],
            'C3' => ['required', 'numeric', 'between:0,100'],
        ]);

        $total = array_sum(array_map('floatval', array_values($data)));
        if (abs($total - 100) > 0.001) {
            return back()->withInput()->with(
                'error',
                'Total bobot harus tepat 100%. Total saat ini ' . number_format($total, 2, ',', '.') . '%.'
            );
        }

        foreach ($data as $code => $weight) {
            DB::table('criteria')->where('code', $code)->update([
                'weight' => $weight,
                'updated_at' => now(),
            ]);
        }

        $periods = DB::table('vehicles')->distinct()->orderBy('period')->pluck('period');
        foreach ($periods as $period) {
            $ranking->recalculateMonth($period, (int) session('user_id'));
        }

        return back()->with('success', 'Bobot kriteria disimpan dan ranking untuk seluruh bulan yang memiliki data diperbarui.');
    }
}
