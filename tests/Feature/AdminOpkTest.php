<?php

namespace Tests\Feature;

use App\Models\DesaDinas;
use App\Models\Kecamatan;
use App\Models\KondisiOpk;
use App\Models\OpkCategory;
use App\Models\OpkLaporan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOpkTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private OpkLaporan $laporan;

    protected function setUp(): void
    {
        parent::setUp();

        KondisiOpk::create(['kode' => 'baik', 'nama' => 'Baik', 'warna' => '#2D5A27', 'urutan' => 1]);
        KondisiOpk::create(['kode' => 'waspada', 'nama' => 'Waspada', 'warna' => '#B8860B', 'urutan' => 2]);
        KondisiOpk::create(['kode' => 'kritis', 'nama' => 'Kritis', 'warna' => '#C0392B', 'urutan' => 3]);

        $this->admin = User::factory()->create(['role' => 'admin']);

        OpkCategory::create(['nomor' => 1, 'nama' => 'Seni', 'ikon' => '🎨']);
        $kecamatan = Kecamatan::create(['nama' => 'Kuta', 'kode' => 'KT']);
        DesaDinas::create(['nama' => 'Kuta', 'kecamatan_id' => $kecamatan->id]);

        $this->laporan = OpkLaporan::create([
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
    }

    public function test_opk_index_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/opk');
        $response->assertSuccessful();
        $response->assertSee('Tari Kecak');
    }

    public function test_opk_show_detail_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/opk/'.$this->laporan->id);
        $response->assertSuccessful();
    }

    public function test_opk_edit_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/opk/'.$this->laporan->id.'/edit');
        $response->assertSuccessful();
    }

    public function test_opk_update_changes_data(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/opk/'.$this->laporan->id, [
            'nama_opk' => 'Tari Legong Updated',
            'kategori_id' => 1,
            'kondisi' => 'waspada',
            'status_pelindungan' => 'belum_terdaftar',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Desa Adat Test',
            'deskripsi_umum' => 'Updated description for testing.',
            'tipe_pelapor' => 'masyarakat',
            'pelapor_nama' => 'Test User Updated',
            'pelapor_nik' => '1234567890123456',
            'pelapor_whatsapp' => '08123456789',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('opk_laporans', [
            'id' => $this->laporan->id,
            'nama_opk' => 'Tari Legong Updated',
            'kondisi' => 'waspada',
        ]);
    }

    public function test_opk_soft_delete(): void
    {
        $response = $this->actingAs($this->admin)->delete('/admin/opk/'.$this->laporan->id);
        $response->assertRedirect();

        $this->assertSoftDeleted('opk_laporans', ['id' => $this->laporan->id]);
    }

    public function test_opk_restore(): void
    {
        $this->laporan->delete();

        $response = $this->actingAs($this->admin)->post('/admin/opk/'.$this->laporan->id.'/restore');
        $response->assertRedirect();

        $this->assertNotSoftDeleted('opk_laporans', ['id' => $this->laporan->id]);
    }

    public function test_opk_peta_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/opk/peta');
        $response->assertSuccessful();
    }

    public function test_opk_arsip_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/opk/arsip');
        $response->assertSuccessful();
    }

    public function test_admin_peta_data_returns_json(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/peta/data');
        $response->assertSuccessful();
        $json = $response->json();
        $this->assertIsArray($json);
    }

    public function test_petugas_cannot_edit_opk(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);

        $response = $this->actingAs($petugas)->get('/admin/opk/'.$this->laporan->id.'/edit');
        $response->assertForbidden();
    }

    public function test_petugas_can_view_opk_show(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);

        $response = $this->actingAs($petugas)->get('/admin/opk/'.$this->laporan->id);
        $response->assertSuccessful();
    }
}
