<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\MooraRankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use ZipArchive;

class VehicleController extends Controller
{
    public function index(Request $request, MooraRankingService $ranking)
    {
        $request->validate(['period' => ['nullable', 'date_format:Y-m']]);
        $selectedPeriod = $request->input('period', now()->format('Y-m'));
        $periodStart = Carbon::createFromFormat('Y-m', $selectedPeriod)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();
        $run = $ranking->latestRunForRange($periodStart->toDateString(), $periodEnd->toDateString());

        if ($run) {
            $vehicles = DB::table('vehicles as v')
                ->leftJoin('moora_results as r', function ($join) use ($run) {
                    $join->on('r.vehicle_id', '=', 'v.id')->where('r.run_id', '=', $run->id);
                })
                ->whereDate('v.period', $periodStart->toDateString())
                ->orderByRaw('CASE WHEN r.ranking IS NULL THEN 1 ELSE 0 END')
                ->orderBy('r.ranking')
                ->orderBy('v.vehicle_number')
                ->select('v.*', 'r.ranking', 'r.optimization_value')
                ->get();
        } else {
            $vehicles = Vehicle::whereDate('period', $periodStart->toDateString())
                ->orderBy('vehicle_number')
                ->get();
        }

        return $this->page('vehicles', compact('vehicles', 'selectedPeriod'), 'Data Kendaraan', 'vehicles');
    }

    public function store(Request $request, MooraRankingService $ranking)
    {
        $data = $this->validatedVehicle($request);
        Vehicle::create($data);
        $ranking->recalculateMonth($data['period'], (int) session('user_id'));

        return redirect()->route('vehicles', ['period' => Carbon::parse($data['period'])->format('Y-m')])
            ->with('success', 'Data kendaraan berhasil ditambahkan dan ranking bulan tersebut diperbarui.');
    }

    public function update(Request $request, Vehicle $vehicle, MooraRankingService $ranking)
    {
        $oldPeriod = $vehicle->period;
        $data = $this->validatedVehicle($request, $vehicle->id);
        $vehicle->update($data);

        $ranking->recalculateMonth($oldPeriod, (int) session('user_id'));
        if ($data['period'] !== $oldPeriod) {
            $ranking->recalculateMonth($data['period'], (int) session('user_id'));
        }

        return redirect()->route('vehicles', ['period' => Carbon::parse($data['period'])->format('Y-m')])
            ->with('success', 'Data kendaraan berhasil diperbarui dan ranking terkait disesuaikan.');
    }

    public function destroy(Vehicle $vehicle, MooraRankingService $ranking)
    {
        $period = $vehicle->period;
        $vehicle->delete();
        $ranking->recalculateMonth($period, (int) session('user_id'));

        return redirect()->route('vehicles', ['period' => Carbon::parse($period)->format('Y-m')])
            ->with('success', 'Data kendaraan berhasil dihapus dan ranking diperbarui.');
    }

    public function destroyAll(MooraRankingService $ranking)
    {
        DB::transaction(function () {
            DB::table('moora_runs')->delete();
            Vehicle::query()->delete();
        });

        return redirect()->route('vehicles', ['period' => now()->format('Y-m')])
            ->with('success', 'Seluruh data kendaraan dan hasil ranking telah dihapus.');
    }

    public function availability(Request $request, Vehicle $vehicle)
    {
        $request->validate(['availability' => ['required', 'in:Ready,Disewakan']]);
        $vehicle->update(['availability' => $request->availability]);

        return back()->with('success', 'Status kendaraan berhasil diperbarui.');
    }

    public function importPage(Request $request)
    {
        $request->validate(['period' => ['nullable', 'date_format:Y-m']]);
        $selectedPeriod = $request->input('period', now()->format('Y-m'));

        return $this->page('import', compact('selectedPeriod'), 'Import Data Kendaraan', 'import');
    }

    public function import(Request $request, MooraRankingService $ranking)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'period' => ['required', 'date_format:Y-m'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'csv', 'txt'], true)) {
            return back()->with('error', 'Format file harus .xlsx atau .csv.');
        }

        $period = Carbon::createFromFormat('Y-m', $validated['period'])->startOfMonth()->toDateString();

        try {
            $rows = $extension === 'xlsx'
                ? $this->readXlsx($file->getRealPath())
                : $this->readCsv($file->getRealPath());

            $records = $this->mapImportRows($rows, $period);
            if (count($records) === 0) {
                return back()->with('error', 'Tidak ada baris kendaraan yang valid. Periksa judul kolom dan isi data.');
            }

            DB::transaction(function () use ($records) {
                foreach ($records as $record) {
                    Vehicle::updateOrCreate(
                        ['vehicle_number' => $record['vehicle_number'], 'period' => $record['period']],
                        $record
                    );
                }
            });

            $ranking->recalculateMonth($period, (int) session('user_id'));

            return redirect()->route('vehicles', ['period' => Carbon::parse($period)->format('Y-m')])
                ->with('success', count($records) . ' data kendaraan berhasil diimpor dan ranking diperbarui.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal membaca file. Pastikan file Excel memiliki judul kolom dan data kendaraan. Detail: ' . $e->getMessage());
        }
    }

    private function validatedVehicle(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'vehicle_number' => strtoupper(trim((string) $request->input('vehicle_number'))),
        ]);

        $validated = $request->validate([
            'vehicle_number' => ['required', 'string', 'max:40'],
            'type' => ['required', 'in:TI,JEMBER'],
            'trips' => ['required', 'integer', 'min:0'],
            'gross_revenue' => ['required', 'numeric', 'min:0'],
            'travel_allowance' => ['required', 'numeric', 'min:0'],
            'operating_cost' => ['required', 'numeric', 'min:0'],
            'availability' => ['required', 'in:Ready,Disewakan'],
            'period' => ['required', 'date_format:Y-m'],
        ]);

        $periodDate = Carbon::createFromFormat('Y-m', $validated['period'])->startOfMonth()->toDateString();
        $request->validate([
            'vehicle_number' => [
                Rule::unique('vehicles', 'vehicle_number')
                    ->where(fn ($query) => $query->where('period', $periodDate))
                    ->ignore($ignoreId),
            ],
        ]);

        $grossRevenue = (float) $validated['gross_revenue'];
        $travelAllowance = (float) $validated['travel_allowance'];

        return [
            'vehicle_number' => strtoupper(trim($validated['vehicle_number'])),
            'type' => $validated['type'],
            'trips' => (int) $validated['trips'],
            'gross_revenue' => $grossRevenue,
            'travel_allowance' => $travelAllowance,
            'profit' => $grossRevenue - $travelAllowance,
            'operating_cost' => (float) $validated['operating_cost'],
            'availability' => $validated['availability'],
            'period' => $periodDate,
        ];
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \RuntimeException('File CSV tidak dapat dibuka.');
        }

        $first = fgets($handle);
        rewind($handle);
        $delimiter = substr_count((string) $first, ';') > substr_count((string) $first, ',') ? ';' : ',';
        $rows = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (isset($row[0])) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $row[0]);
            }
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    private function readXlsx(string $path): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new \RuntimeException('Ekstensi PHP zip belum aktif. Aktifkan extension=zip pada php.ini XAMPP, atau simpan Excel sebagai CSV UTF-8.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('File XLSX tidak dapat dibuka.');
        }

        $shared = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml) {
            $xml = simplexml_load_string($sharedXml);
            foreach ($xml->xpath('//*[local-name()="si"]') ?: [] as $si) {
                $parts = [];
                foreach ($si->xpath('.//*[local-name()="t"]') ?: [] as $t) {
                    $parts[] = (string) $t;
                }
                $shared[] = implode('', $parts);
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            $zip->close();
            throw new \RuntimeException('Lembar pertama Excel tidak ditemukan.');
        }

        $xml = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($xml->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: [] as $row) {
            $cells = [];
            foreach ($row->xpath('./*[local-name()="c"]') ?: [] as $cell) {
                $reference = (string) $cell['r'];
                preg_match('/^[A-Z]+/', $reference, $match);
                if (!$match) {
                    continue;
                }

                $column = 0;
                foreach (str_split($match[0]) as $character) {
                    $column = $column * 26 + ord($character) - 64;
                }

                $type = (string) $cell['t'];
                $value = '';
                $v = $cell->xpath('./*[local-name()="v"]');
                if ($type === 's' && isset($v[0])) {
                    $value = $shared[(int) $v[0]] ?? '';
                } elseif ($type === 'inlineStr') {
                    $textNodes = $cell->xpath('.//*[local-name()="t"]');
                    $value = isset($textNodes[0]) ? (string) $textNodes[0] : '';
                } else {
                    $value = isset($v[0]) ? (string) $v[0] : '';
                }

                $cells[$column - 1] = $value;
            }

            if ($cells) {
                ksort($cells);
                $max = max(array_keys($cells));
                $rows[] = array_replace(array_fill(0, $max + 1, ''), $cells);
            }
        }

        $zip->close();
        return $rows;
    }

    private function mapImportRows(array $rows, string $period): array
    {
        $headerIndex = null;
        $headers = [];

        foreach (array_slice($rows, 0, 15, true) as $index => $row) {
            $normalized = array_map(fn ($value) => $this->normalizeHeader((string) $value), $row);
            if (in_array('nokendaraan', $normalized, true)
                || in_array('nomorkendaraan', $normalized, true)
                || in_array('noplatkendaraan', $normalized, true)) {
                $headerIndex = $index;
                $headers = $normalized;
                break;
            }
        }

        if ($headerIndex === null) {
            throw new \RuntimeException('Kolom "No. Plat Kendaraan" atau "No Kendaraan" tidak ditemukan.');
        }

        $find = function (array $aliases) use ($headers) {
            foreach ($aliases as $alias) {
                $index = array_search($this->normalizeHeader($alias), $headers, true);
                if ($index !== false) {
                    return $index;
                }
            }
            return null;
        };

        $indices = [
            'vehicle_number' => $find(['No. Plat Kendaraan', 'No Kendaraan', 'Nomor Kendaraan', 'No. Kendaraan']),
            'type' => $find(['Tipe Kendaraan', 'Tipe']),
            'trips' => $find(['Jumlah Perjalanan', 'Perjalanan']),
            'gross_revenue' => $find(['Jumlah Ritase', 'Jumlah Ritase/Pendapatan', 'Pendapatan', 'Jumlah Pendapatan']),
            'travel_allowance' => $find(['Uang Jalan', 'Biaya Uang Jalan']),
            'operating_cost' => $find(['Biaya Operasional', 'Total Biaya Operasional']),
            'availability' => $find(['Status', 'Status Kendaraan']),
        ];

        foreach (['vehicle_number', 'type', 'trips', 'gross_revenue', 'travel_allowance', 'operating_cost'] as $required) {
            if ($indices[$required] === null) {
                throw new \RuntimeException('Kolom wajib belum lengkap: ' . $required);
            }
        }

        $records = [];
        foreach (array_slice($rows, $headerIndex + 1) as $row) {
            $get = fn ($key) => $indices[$key] !== null ? ($row[$indices[$key]] ?? '') : '';
            $number = strtoupper(trim((string) $get('vehicle_number')));
            if ($number === '') {
                continue;
            }

            $type = strtoupper(trim((string) $get('type')));
            if (!in_array($type, ['TI', 'JEMBER'], true)) {
                continue;
            }

            $availability = trim((string) $get('availability')) ?: 'Ready';
            if (!in_array($availability, ['Ready', 'Disewakan'], true)) {
                $availability = 'Ready';
            }

            $gross = $this->numberValue($get('gross_revenue'));
            $allowance = $this->numberValue($get('travel_allowance'));

            $records[] = [
                'vehicle_number' => $number,
                'type' => $type,
                'trips' => (int) $this->numberValue($get('trips')),
                'gross_revenue' => $gross,
                'travel_allowance' => $allowance,
                'profit' => $gross - $allowance,
                'operating_cost' => $this->numberValue($get('operating_cost')),
                'availability' => $availability,
                'period' => $period,
            ];
        }

        return $records;
    }

    private function normalizeHeader(string $value): string
    {
        $value = mb_strtolower(trim($value));
        return preg_replace('/[^a-z0-9]/u', '', str_replace(['ä', 'ö', 'ü'], ['a', 'o', 'u'], $value));
    }

    private function numberValue(mixed $value): float
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }

        $value = preg_replace('/[^0-9,.-]/', '', $value);
        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, ',')) {
            $parts = explode(',', $value);
            $value = strlen(end($parts)) <= 2
                ? str_replace(',', '.', $value)
                : str_replace(',', '', $value);
        } elseif (substr_count($value, '.') > 1) {
            $value = str_replace('.', '', $value);
        } elseif (str_contains($value, '.')) {
            $parts = explode('.', $value);
            if (strlen(end($parts)) === 3) {
                $value = str_replace('.', '', $value);
            }
        }

        return is_numeric($value) ? (float) $value : 0;
    }
}
