<?php

namespace Tests\Feature;

use App\Models\DesaDinas;
use App\Models\Kecamatan;
use App\Models\KondisiOpk;
use App\Models\OpkCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LaporControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        KondisiOpk::create(['kode' => 'baik', 'nama' => 'Baik', 'warna' => '#2D5A27', 'urutan' => 1]);
        KondisiOpk::create(['kode' => 'waspada', 'nama' => 'Waspada', 'warna' => '#B8860B', 'urutan' => 2]);
        KondisiOpk::create(['kode' => 'kritis', 'nama' => 'Kritis', 'warna' => '#C0392B', 'urutan' => 3]);

        OpkCategory::create(['nomor' => 1, 'nama' => 'Seni', 'ikon' => '🎨']);
        $kecamatan = Kecamatan::create(['nama' => 'Mengwi', 'kode' => 'MW']);
        DesaDinas::create(['nama' => 'Mengwi', 'kecamatan_id' => $kecamatan->id]);
    }

    public function test_lapor_page_loads(): void
    {
        $response = $this->get('/lapor');
        $response->assertStatus(200);
        $response->assertSee('Lapor OPK');
    }

    public function test_submit_valid_report(): void
    {
        Storage::fake('public');

        $response = $this->post('/lapor/kirim', [
            'nama_opk' => 'Tari Legong',
            'kategori_id' => 1,
            'kondisi' => 'baik',
            'status_pelindungan' => 'belum_terdaftar',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Desa Adat Mengwi',
            'deskripsi_umum' => 'Tarian klasik Bali yang sangat indah dan memiliki sejarah panjang dalam tradisi kerajaan Bali.',
            'tipe_pelapor' => 'masyarakat',
            'pelapor_nama' => 'I Made Test',
            'pelapor_nik' => '5102012345678901',
            'pelapor_whatsapp' => '081122334455',
            'setuju_1' => 'on',
            'setuju_2' => 'on',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('opk_laporans', [
            'nama_opk' => 'Tari Legong',
            'status_verifikasi' => 'review_dinas',
        ]);
    }

    public function test_submit_report_validation_fails_without_required_fields(): void
    {
        $response = $this->post('/lapor/kirim', [
            'nama_opk' => '',
        ]);

        $response->assertSessionHasErrors([
            'nama_opk',
            'kategori_id',
            'kondisi',
            'kecamatan_id',
            'desa_dinas_id',
            'nama_desa_adat',
            'deskripsi_umum',
            'pelapor_nama',
            'pelapor_nik',
            'pelapor_whatsapp',
        ]);
    }

    public function test_check_status_with_valid_kode(): void
    {
        $this->post('/lapor/kirim', [
            'nama_opk' => 'Tari Pendet',
            'kategori_id' => 1,
            'kondisi' => 'baik',
            'status_pelindungan' => 'belum_terdaftar',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Desa Adat Mengwi',
            'deskripsi_umum' => 'Tarian selamat datang khas Bali yang sering ditampilkan di berbagai acara adat.',
            'tipe_pelapor' => 'masyarakat',
            'pelapor_nama' => 'Ni Made Test',
            'pelapor_nik' => '5102012345678902',
            'pelapor_whatsapp' => '081122334456',
            'setuju_1' => 'on',
            'setuju_2' => 'on',
        ]);

        $response = $this->get('/lapor/status?kode_laporan=SIOPK-'.date('Y').'-00001');
        $response->assertStatus(200);
    }

    public function test_rate_limiting_blocks_excessive_submissions(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $response = $this->post('/lapor/kirim', [
                'nama_opk' => "Tari Test {$i}",
                'kategori_id' => 1,
                'kondisi' => 'baik',
                'status_pelindungan' => 'belum_terdaftar',
                'kecamatan_id' => 1,
                'desa_dinas_id' => 1,
                'nama_desa_adat' => 'Desa Adat Mengwi',
                'deskripsi_umum' => 'Deskripsi tarian yang cukup panjang untuk memenuhi minimal 50 karakter agar bisa lolos validasi.',
                'tipe_pelapor' => 'masyarakat',
                'pelapor_nama' => 'Test User',
                'pelapor_nik' => '510201234567'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'pelapor_whatsapp' => '0811223344'.str_pad($i, 2, '0', STR_PAD_LEFT),
                'setuju_1' => 'on',
                'setuju_2' => 'on',
            ]);

            if ($i >= 3) {
                $response->assertStatus(429);
            }
        }
    }
}
