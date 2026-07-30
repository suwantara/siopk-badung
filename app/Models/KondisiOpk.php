<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KondisiOpk extends Model
{
    protected $table = 'kondisi_opks';

    protected $fillable = ['kode', 'nama', 'deskripsi', 'warna', 'urutan'];

    public function laporans()
    {
        return $this->hasMany(OpkLaporan::class, 'kondisi', 'kode');
    }
}
