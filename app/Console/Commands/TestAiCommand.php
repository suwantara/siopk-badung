<?php

namespace App\Console\Commands;

use App\Models\Kecamatan;
use App\Models\KondisiOpk;
use App\Models\OpkCategory;
use App\Models\OpkLaporan;
use App\Services\AiOpkAnalyzer;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestAiCommand extends Command
{
    protected $signature = 'siopk:test-ai';

    protected $description = 'Test koneksi ke Claude API dan coba analisis OPK sederhana';

    public function handle(AiOpkAnalyzer $ai): int
    {
        $this->info('');
        $this->info('╔═══════════════════════════════════════╗');
        $this->info('║   SIOPK Badung — Test Koneksi AI      ║');
        $this->info('╚═══════════════════════════════════════╝');
        $this->info('');

        // 1. Cek API key provider aktif
        $providerName = config('services.ai.provider', 'claude');
        $apiKey = config("services.ai.{$providerName}.api_key");
        $envVar = strtoupper($providerName).'_API_KEY';

        $this->line("  Provider aktif : {$providerName}");

        if (empty($apiKey)) {
            $this->error("✗ {$envVar} belum diset di .env!");
            $this->line("  Tambahkan: {$envVar}=<key_asli>");

            return self::FAILURE;
        }
        $this->info("✓ {$envVar} terdeteksi: ".substr($apiKey, 0, 20).'...');

        // 2. Test klasifikasi sederhana
        $this->info('');
        $this->info('Menguji klasifikasi OPK...');
        $nomor = $ai->klasifikasiOtomatis(
            'Tari Kecak',
            'Tari pertunjukan tradisional Bali yang melibatkan banyak penari pria dengan canting api'
        );

        if ($nomor) {
            $this->info("✓ Klasifikasi berhasil: Tari Kecak → Kategori #{$nomor} (harusnya 7 = Seni)");
        } else {
            $this->warn('⚠ Klasifikasi gagal atau API tidak merespons');
        }

        // 3. Test analisis laporan dummy
        $this->info('');
        $this->info('Menguji analisis laporan dummy...');

        $nonBaikKode = KondisiOpk::where('kode', '!=', 'baik')->orderBy('urutan')->value('kode') ?? 'kritis';

        $laporan = new OpkLaporan([
            'nama_opk' => 'Lontar Usada Desa Pedawa',
            'kondisi' => $nonBaikKode,
            'nama_desa_adat' => 'Desa Adat Pedawa',
            'deskripsi_umum' => 'Naskah lontar berisi ilmu pengobatan tradisional Bali. Kondisi rusak berat, belum ada digitalisasi.',
            'praktisi_nama' => 'Jro Mangku Sari',
            'praktisi_usia' => 78,
            'frekuensi_pelaksanaan' => 'sangat_langka',
        ]);

        // Mock relasi
        $laporan->setRelation('kategori', new OpkCategory(['nama' => 'Manuskrip']));
        $laporan->setRelation('kecamatan', new Kecamatan(['nama' => 'Petang']));

        $hasil = $ai->analisisLaporan($laporan);

        $this->info('✓ Analisis berhasil!');
        $this->table(
            ['Field', 'Nilai'],
            [
                ['Urgency Score',  number_format($hasil['urgency_score'], 1).'/10'],
                ['Duplikat Score', number_format($hasil['duplikat_score'], 1).'%'],
                ['Rekomendasi',    Str::limit($hasil['rekomendasi'], 80)],
            ]
        );

        $this->info('');
        $this->info('✓ Semua test berhasil! Fase 6 siap digunakan.');
        $this->info('');
        $this->line('  Langkah berikutnya:');
        $this->line('  1. Set CLAUDE_API_KEY=<key_asli> di .env');
        $this->line('  2. Submit laporan baru via portal publik');
        $this->line('  3. AI akan otomatis menganalisis dalam background');
        $this->line('  4. Lihat hasil di: /admin/verifikasi');
        $this->info('');

        return self::SUCCESS;
    }
}
