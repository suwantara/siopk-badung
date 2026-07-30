<?php

namespace Database\Seeders;

use App\Models\KondisiOpk;
use Illuminate\Database\Seeder;

class KondisiOpkSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'baik',    'nama' => 'Baik',    'deskripsi' => 'Kondisi OPK baik — aktif dan terpelihara',                     'warna' => '#2D5A27', 'urutan' => 1],
            ['kode' => 'waspada', 'nama' => 'Waspada', 'deskripsi' => 'Kondisi OPK waspada — praktisi berkurang signifikan',          'warna' => '#B8860B', 'urutan' => 2],
            ['kode' => 'kritis',  'nama' => 'Kritis',  'deskripsi' => 'Kondisi OPK kritis — terancam punah atau rusak parah',        'warna' => '#C0392B', 'urutan' => 3],
        ];

        foreach ($data as $row) {
            KondisiOpk::updateOrCreate(['kode' => $row['kode']], $row);
        }
    }
}
