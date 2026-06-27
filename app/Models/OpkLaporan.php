<?php

namespace App\Models;

use App\Enums\{StatusVerifikasi, KondisiOpk};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class OpkLaporan extends Model
{
    use SoftDeletes;

    protected $table = 'opk_laporans';

    protected $fillable = [
        'kode_laporan', 'nama_opk', 'kategori_id',
        'tahun_diketahui', 'tahun_keterangan', 'status_pelindungan', 'kondisi',
        'kecamatan_id', 'desa_dinas_id', 'nama_desa_adat', 'banjar_adat',
        'lokasi_spesifik', 'latitude', 'longitude',
        'deskripsi_umum', 'sejarah_asal_usul', 'nilai_makna_budaya',
        'bahasa_digunakan', 'aksara_digunakan',
        'frekuensi_pelaksanaan', 'status_kepemilikan',
        'praktisi_nama', 'praktisi_usia', 'praktisi_kontak',
        'tipe_pelapor', 'pelapor_nama', 'pelapor_nik',
        'pelapor_whatsapp', 'pelapor_email',
        'status_verifikasi', 'diverifikasi_oleh', 'tanggal_verifikasi', 'catatan_verifikasi',
        'ai_urgency_score', 'ai_duplikat_score', 'ai_rekomendasi', 'ai_duplikat_of',
        'link_video', 'link_dokumen_eksternal',
    ];

    protected $casts = [
        'latitude'           => 'float',
        'longitude'          => 'float',
        'ai_urgency_score'   => 'float',
        'ai_duplikat_score'  => 'float',
        'tahun_diketahui'    => 'integer',
        'tanggal_verifikasi' => 'datetime',
    ];

    protected $hidden = [
        'pelapor_nik',
        'pelapor_whatsapp',
        'pelapor_email',
    ];

    // ---- Relasi ----
    public function kategori()    { return $this->belongsTo(OpkCategory::class, 'kategori_id'); }
    public function kecamatan()   { return $this->belongsTo(Kecamatan::class, 'kecamatan_id'); }
    public function desaDinas()   { return $this->belongsTo(DesaDinas::class, 'desa_dinas_id'); }
    public function verifikator() { return $this->belongsTo(User::class, 'diverifikasi_oleh'); }
    public function duplikatDari(){ return $this->belongsTo(OpkLaporan::class, 'ai_duplikat_of'); }

    public function fotos()     { return $this->hasMany(OpkFoto::class, 'laporan_id')->orderBy('urutan'); }
    public function fotoUtama() { return $this->hasOne(OpkFoto::class, 'laporan_id')->where('is_utama', true); }
    public function dokumens()  { return $this->hasMany(OpkDokumen::class, 'laporan_id'); }
    public function videos()    { return $this->hasMany(OpkVideo::class, 'laporan_id'); }
    public function riwayat()   { return $this->hasMany(OpkRiwayatStatus::class, 'laporan_id')->latest(); }

    // ---- Helpers ----
    public static function generateKode(): string
    {
        return 'SIOPK-' . date('Y') . '-' . Str::upper(Str::random(8));
    }

    // ---- Scopes ----
    public function scopeDisetujui($query)    { return $query->where('status_verifikasi', StatusVerifikasi::Disetujui->value); }
    public function scopeKritis($query)       { return $query->where('kondisi', KondisiOpk::Kritis->value); }
    public function scopeWaspada($query)      { return $query->where('kondisi', KondisiOpk::Waspada->value); }
    public function scopeMenunggu($query)     { return $query->whereIn('status_verifikasi', [StatusVerifikasi::Menunggu->value, StatusVerifikasi::AiReview->value, StatusVerifikasi::ReviewDinas->value]); }
    public function scopePrioritas($query)    { return $query->where('ai_urgency_score', '>=', 7); }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status_verifikasi) {
            StatusVerifikasi::Menunggu->value     => ['label' => 'Menunggu',     'color' => 'secondary'],
            StatusVerifikasi::AiReview->value     => ['label' => 'AI Review',    'color' => 'info'],
            StatusVerifikasi::ReviewDinas->value  => ['label' => 'Review Dinas', 'color' => 'warning'],
            StatusVerifikasi::Disetujui->value    => ['label' => 'Disetujui',    'color' => 'success'],
            StatusVerifikasi::Ditolak->value      => ['label' => 'Ditolak',      'color' => 'danger'],
            StatusVerifikasi::Duplikat->value     => ['label' => 'Duplikat',     'color' => 'dark'],
            default                               => ['label' => 'Unknown',      'color' => 'secondary'],
        };
    }

    public function getKondisiBadgeAttribute(): array
    {
        return match($this->kondisi) {
            KondisiOpk::Baik->value    => ['label' => 'Baik',    'color' => 'success'],
            KondisiOpk::Waspada->value => ['label' => 'Waspada', 'color' => 'warning'],
            KondisiOpk::Kritis->value  => ['label' => 'Kritis',  'color' => 'danger'],
            default                    => ['label' => '-',        'color' => 'secondary'],
        };
    }
}
