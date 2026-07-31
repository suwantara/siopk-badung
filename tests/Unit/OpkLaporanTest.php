<?php

namespace Tests\Unit;

use App\Models\DesaDinas;
use App\Models\Kecamatan;
use App\Models\KondisiOpk;
use App\Models\OpkCategory;
use App\Models\OpkLaporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpkLaporanTest extends TestCase
{
    use RefreshDatabase;

    private OpkLaporan $laporan;

    protected function setUp(): void
    {
        parent::setUp();

        KondisiOpk::create(['kode' => 'baik', 'nama' => 'Baik', 'warna' => '#2D5A27', 'urutan' => 1]);
        KondisiOpk::create(['kode' => 'waspada', 'nama' => 'Waspada', 'warna' => '#B8860B', 'urutan' => 2]);
        KondisiOpk::create(['kode' => 'kritis', 'nama' => 'Kritis', 'warna' => '#C0392B', 'urutan' => 3]);

        OpkCategory::create(['nomor' => 7, 'nama' => 'Seni', 'ikon' => '🎨']);
        $kecamatan = Kecamatan::create(['nama' => 'Kuta Selatan', 'kode' => 'KS']);
        DesaDinas::create(['nama' => 'Jimbaran', 'kecamatan_id' => $kecamatan->id]);

        $this->laporan = OpkLaporan::create([
            'kode_laporan' => 'SIOPK-2025-00001',
            'nama_opk' => 'Tari Barong',
            'kategori_id' => 1,
            'kondisi' => 'baik',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Desa Adat Jimbaran',
            'deskripsi_umum' => 'Tarian sakral Bali',
            'status_verifikasi' => 'disetujui',
            'pelapor_nama' => 'Test User',
            'pelapor_nik' => '1234567890123456',
            'pelapor_whatsapp' => '08123456789',
        ]);
    }

    public function test_generate_kode_returns_valid_format(): void
    {
        $kode = OpkLaporan::generateKode();
        $this->assertStringStartsWith('SIOPK-'.date('Y').'-', $kode);
    }

    public function test_status_badge_returns_correct_label(): void
    {
        $badge = $this->laporan->status_badge;
        $this->assertIsArray($badge);
        $this->assertEquals('Disetujui', $badge['label']);
        $this->assertEquals('success', $badge['color']);
    }

    public function test_kondisi_badge_returns_correct_label(): void
    {
        $badge = $this->laporan->kondisi_badge;
        $this->assertIsArray($badge);
        $this->assertEquals('Baik', $badge['label']);
        $this->assertEquals('success', $badge['color']);
    }

    public function test_status_badge_for_menunggu(): void
    {
        $this->laporan->update(['status_verifikasi' => 'menunggu']);
        $badge = $this->laporan->status_badge;
        $this->assertEquals('Menunggu', $badge['label']);
        $this->assertEquals('secondary', $badge['color']);
    }

    public function test_status_badge_for_ditolak(): void
    {
        $this->laporan->update(['status_verifikasi' => 'ditolak']);
        $badge = $this->laporan->status_badge;
        $this->assertEquals('Ditolak', $badge['label']);
        $this->assertEquals('danger', $badge['color']);
    }

    public function test_scope_disetujui(): void
    {
        OpkLaporan::create([
            'kode_laporan' => 'SIOPK-2025-00002',
            'nama_opk' => 'Tari Legong',
            'kategori_id' => 1,
            'kondisi' => 'baik',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Test',
            'deskripsi_umum' => 'Test description',
            'status_verifikasi' => 'menunggu',
            'pelapor_nama' => 'Test',
            'pelapor_nik' => '1234567890123457',
            'pelapor_whatsapp' => '08123456780',
        ]);

        $count = OpkLaporan::disetujui()->count();
        $this->assertEquals(1, $count);
    }

    public function test_scope_kritis(): void
    {
        OpkLaporan::create([
            'kode_laporan' => 'SIOPK-2025-00002',
            'nama_opk' => 'Tari Kritis',
            'kategori_id' => 1,
            'kondisi' => 'kritis',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Test',
            'deskripsi_umum' => 'Test description',
            'status_verifikasi' => 'disetujui',
            'pelapor_nama' => 'Test',
            'pelapor_nik' => '1234567890123457',
            'pelapor_whatsapp' => '08123456780',
        ]);

        $count = OpkLaporan::kritis()->count();
        $this->assertEquals(1, $count);
    }
}
