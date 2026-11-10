<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $criteria = [
            ['code'=>'C1','name'=>'Jumlah Perjalanan','criterion_type'=>'benefit','weight'=>30,'description'=>'Semakin banyak perjalanan, semakin baik.'],
            ['code'=>'C2','name'=>'Keuntungan Perusahaan','criterion_type'=>'benefit','weight'=>45,'description'=>'Semakin besar keuntungan, semakin baik.'],
            ['code'=>'C3','name'=>'Biaya Operasional','criterion_type'=>'cost','weight'=>25,'description'=>'Semakin kecil biaya operasional, semakin baik.'],
        ];
        foreach ($criteria as $item) {
            // Support databases created by older project versions where the
            // criteria table may not have Laravel timestamp columns.
            $values = $item;

            if (Schema::hasColumn('criteria', 'created_at')) {
                $values['created_at'] = now();
            }

            if (Schema::hasColumn('criteria', 'updated_at')) {
                $values['updated_at'] = now();
            }

            DB::table('criteria')->updateOrInsert(
                ['code' => $item['code']],
                $values
            );
        }
    }
}
