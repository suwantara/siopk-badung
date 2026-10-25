<?php

namespace Tests\Feature;

use App\Models\DesaDinas;
use App\Models\Kecamatan;
use App\Models\KondisiOpk;
use App\Models\OpkCategory;
use App\Models\OpkLaporan;
use App\Models\OpkRiwayatStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminOpkImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        OpkCategory::create(['nomor' => 1, 'nama' => 'Seni', 'ikon' => '🎨']);
        $kecamatan = Kecamatan::create(['nama' => 'Mengwi', 'kode' => 'MG']);
        DesaDinas::create(['nama' => 'Mengwi', 'kecamatan_id' => $kecamatan->id]);
        KondisiOpk::create(['kode' => 'baik', 'nama' => 'Baik', 'warna' => '#2D5A27', 'urutan' => 1]);
    }

    private function csvFile(string $content): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('opk.csv', $content);
    }

    private function csv(string $namaOpk = 'Tari Test Impor', string $nik = '5103000000000001'): string
    {
        $header = implode(',', [
            'Nama OPK', 'Kategori', 'Kecamatan', 'Desa Dinas', 'Nama Desa Adat',
            'Deskripsi Umum', 'Nama Pelapor', 'NIK Pelapor', 'WhatsApp Pelapor',
        ]);

        $deskripsi = 'Deskripsi lengkap objek pemajuan kebudayaan untuk pengujian impor massal.';
        $row = implode(',', [
            '"'.$namaOpk.'"', '"1 - Seni"', 'Mengwi', 'Mengwi', '"Desa Adat Test"',
            '"'.$deskripsi.'"', '"Petugas Dinas"', $nik, '081234567890',
        ]);

        return $header."\n".$row."\n";
    }

    public function test_admin_can_import_valid_csv(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/opk/import', ['file' => $this->csvFile($this->csv())]);

        $response->assertRedirect('/admin/opk/import');
        $response->assertSessionHas('import_result', fn ($r) => $r['imported'] === 1);

        $opk = OpkLaporan::first();
        $this->assertNotNull($opk);
        $this->assertSame('Tari Test Impor', $opk->nama_opk);
        $this->assertSame('disetujui', $opk->status_verifikasi);
        $this->assertSame($this->admin->id, $opk->diverifikasi_oleh);
        $this->assertSame(1, $opk->kategori_id);
        $this->assertSame(1, $opk->kecamatan_id);
        $this->assertSame(1, $opk->desa_dinas_id);
        $this->assertSame(1, OpkRiwayatStatus::where('laporan_id', $opk->id)->count());
        $this->assertDatabaseHas('opk_riwayat_status', [
            'laporan_id' => $opk->id,
            'status_baru' => 'disetujui',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_dry_run_does_not_persist_rows(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/opk/import', [
                'file' => $this->csvFile($this->csv()),
                'dry_run' => 1,
            ]);

        $response->assertSessionHas('import_result', fn ($r) => $r['dry_run'] === true && $r['valid'] === 1 && $r['imported'] === 0);
        $this->assertSame(0, OpkLaporan::count());
    }

    public function test_invalid_row_aborts_whole_import(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/opk/import', [
                'file' => $this->csvFile($this->csv(nik: '123')),
            ]);

        $response->assertSessionHas('import_result', fn ($r) => $r['imported'] === 0 && $r['invalid'] === 1 && $r['fatal'] !== null);
        $this->assertSame(0, OpkLaporan::count());
    }

    public function test_missing_required_header_is_rejected(): void
    {
        $csv = "Nama OPK,Kecamatan\n\"Tari Test\",Mengwi\n";

        $response = $this->actingAs($this->admin)
            ->post('/admin/opk/import', ['file' => $this->csvFile($csv)]);

        $response->assertSessionHas('import_result', fn ($r) => $r['fatal'] !== null && str_contains($r['fatal'], 'Kolom wajib'));
        $this->assertSame(0, OpkLaporan::count());
    }

    public function test_duplicate_with_existing_data_is_rejected(): void
    {
        OpkLaporan::create([
            'kode_laporan' => 'SIOPK-2025-00001',
            'nama_opk' => 'Tari Test Impor',
            'kategori_id' => 1,
            'kondisi' => 'baik',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Desa Adat Test',
            'deskripsi_umum' => 'Deskripsi',
            'status_verifikasi' => 'disetujui',
            'pelapor_nama' => 'Test',
            'pelapor_nik' => '5103000000000099',
            'pelapor_whatsapp' => '08123456789',
        ]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/opk/import', ['file' => $this->csvFile($this->csv())]);

        $response->assertSessionHas('import_result', fn ($r) => $r['imported'] === 0 && str_contains($r['errors'][0]['messages'][0] ?? '', 'sudah ada di database'));
        $this->assertSame(1, OpkLaporan::count());
    }

    public function test_import_without_nik_and_whatsapp_is_allowed(): void
    {
        $csv = implode(',', [
            'Nama OPK', 'Kategori', 'Kecamatan', 'Desa Dinas', 'Nama Desa Adat',
            'Deskripsi Umum', 'Nama Pelapor',
        ])."\n".implode(',', [
            '"Tari Tanpa Kontak"', '"1 - Seni"', 'Mengwi', 'Mengwi', '"Desa Adat Test"',
            '"Deskripsi lengkap objek pemajuan kebudayaan untuk pengujian impor massal."', '"Petugas Dinas"',
        ])."\n";

        $response = $this->actingAs($this->admin)
            ->post('/admin/opk/import', ['file' => $this->csvFile($csv)]);

        $response->assertSessionHas('import_result', fn ($r) => $r['imported'] === 1);

        $opk = OpkLaporan::first();
        $this->assertNotNull($opk);
        $this->assertSame('', $opk->pelapor_nik);
        $this->assertSame('', $opk->pelapor_whatsapp);
    }

    public function test_same_name_in_different_desa_adat_is_allowed(): void
    {
        $header = implode(',', [
            'Nama OPK', 'Kategori', 'Kecamatan', 'Desa Dinas', 'Nama Desa Adat',
            'Deskripsi Umum', 'Nama Pelapor',
        ]);
        $deskripsi = 'Deskripsi lengkap objek pemajuan kebudayaan untuk pengujian impor massal.';

        $csv = $header."\n"
            .'"Tari Baris"'.',"1 - Seni",Mengwi,Mengwi,"Desa Adat A","'.$deskripsi.'","Petugas Dinas"'."\n"
            .'"Tari Baris"'.',"1 - Seni",Mengwi,Mengwi,"Desa Adat B","'.$deskripsi.'","Petugas Dinas"'."\n";

        $response = $this->actingAs($this->admin)
            ->post('/admin/opk/import', ['file' => $this->csvFile($csv)]);

        $response->assertSessionHas('import_result', fn ($r) => $r['imported'] === 2);
        $this->assertSame(2, OpkLaporan::where('nama_opk', 'Tari Baris')->count());
    }

    public function test_blank_rows_are_ignored(): void
    {
        $csv = $this->csv().",,,,,,,,\n";

        $response = $this->actingAs($this->admin)
            ->post('/admin/opk/import', [
                'file' => $this->csvFile($csv),
                'dry_run' => 1,
            ]);

        $response->assertSessionHas('import_result', fn ($r) => $r['total'] === 1 && $r['valid'] === 1 && $r['invalid'] === 0);
    }

    public function test_non_admin_cannot_access_import_page(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);

        $this->actingAs($petugas)->get('/admin/opk/import')->assertForbidden();
        $this->actingAs($this->admin)->get('/admin/opk/import')->assertSuccessful();
    }

    public function test_templates_can_be_downloaded(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/opk/import/template?format=xlsx')
            ->assertSuccessful();

        $this->actingAs($this->admin)
            ->get('/admin/opk/import/template?format=csv')
            ->assertSuccessful();
    }
}
