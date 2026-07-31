<?php

namespace Tests\Unit;

use App\Models\DesaDinas;
use App\Models\Kecamatan;
use App\Models\KondisiOpk;
use App\Models\OpkCategory;
use App\Models\OpkLaporan;
use App\Services\OpkStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpkStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    private OpkStatsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        KondisiOpk::create(['kode' => 'baik', 'nama' => 'Baik', 'warna' => '#2D5A27', 'urutan' => 1]);
        KondisiOpk::create(['kode' => 'waspada', 'nama' => 'Waspada', 'warna' => '#B8860B', 'urutan' => 2]);
        KondisiOpk::create(['kode' => 'kritis', 'nama' => 'Kritis', 'warna' => '#C0392B', 'urutan' => 3]);

        OpkCategory::create(['nomor' => 1, 'nama' => 'Seni', 'ikon' => '🎨']);
        $kecamatan = Kecamatan::create(['nama' => 'Kuta', 'kode' => 'KT']);
        DesaDinas::create(['nama' => 'Kuta', 'kecamatan_id' => $kecamatan->id]);

        OpkLaporan::create([
            'kode_laporan' => 'SIOPK-2025-00001',
            'nama_opk' => 'Tari Kecak',
            'kategori_id' => 1,
            'kondisi' => 'baik',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Desa Adat Test',
            'deskripsi_umum' => 'Test description',
            'status_verifikasi' => 'disetujui',
            'pelapor_nama' => 'Test User',
            'pelapor_nik' => '1234567890123456',
            'pelapor_whatsapp' => '08123456789',
        ]);

        OpkLaporan::create([
            'kode_laporan' => 'SIOPK-2025-00002',
            'nama_opk' => 'Tari Kritis',
            'kategori_id' => 1,
            'kondisi' => 'kritis',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Desa Adat Test',
            'deskripsi_umum' => 'Test description',
            'status_verifikasi' => 'disetujui',
            'pelapor_nama' => 'Test User',
            'pelapor_nik' => '1234567890123457',
            'pelapor_whatsapp' => '08123456780',
        ]);

        OpkLaporan::create([
            'kode_laporan' => 'SIOPK-2025-00003',
            'nama_opk' => 'Tari Baru',
            'kategori_id' => 1,
            'kondisi' => 'baik',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Desa Adat Test',
            'deskripsi_umum' => 'Test description',
            'status_verifikasi' => 'menunggu',
            'pelapor_nama' => 'Test User',
            'pelapor_nik' => '1234567890123458',
            'pelapor_whatsapp' => '08123456781',
        ]);

        $this->service = new OpkStatsService;
    }

    public function test_dashboard_admin_returns_correct_counts(): void
    {
        $stats = $this->service->dashboardAdmin();

        $this->assertEquals(2, $stats['total_opk']);
        $this->assertEquals(1, $stats['kritis']);
        $this->assertEquals(1, $stats['terlindungi']);
    }

    public function test_dashboard_publik_returns_correct_counts(): void
    {
        $stats = $this->service->dashboardPublik();

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['kritis']);
        $this->assertEquals(1, $stats['baik']);
        $this->assertEquals(0, $stats['waspada']);
    }

    public function test_laporan_admin_returns_correct_counts(): void
    {
        $stats = $this->service->laporanAdmin();

        $this->assertEquals(2, $stats['disetujui']);
        $this->assertEquals(1, $stats['kritis']);
        $this->assertEquals(1, $stats['menunggu']);
        $this->assertEquals(0, $stats['ditolak']);
    }

    public function test_kategori_with_opk_count_returns_collection(): void
    {
        $kategoris = $this->service->kategoriWithOpkCount();

        $this->assertCount(1, $kategoris);
        $this->assertEquals(2, $kategoris->first()->total);
    }

    public function test_kecamatan_with_opk_count_returns_collection(): void
    {
        $kecamatans = $this->service->kecamatanWithOpkCount();

        $this->assertCount(1, $kecamatans);
        $this->assertEquals(2, $kecamatans->first()->total);
    }

    public function test_ringkasan_eksekutif_returns_array(): void
    {
        $stats = $this->service->ringkasanEksekutif();

        $this->assertArrayHasKey('total_opk', $stats);
        $this->assertArrayHasKey('kritis', $stats);
        $this->assertArrayHasKey('opk_kritis_list', $stats);
    }
}
