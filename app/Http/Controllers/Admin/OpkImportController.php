<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OpkImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OpkImportController extends Controller
{
    public function __construct(
        private readonly OpkImportService $importService,
    ) {}

    public function index()
    {
        $this->importService->pruneErrorFiles();

        return view('admin.opk.import', [
            'columns' => OpkImportService::columns(),
            'maxRows' => OpkImportService::MAX_ROWS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240',
            'dry_run' => 'nullable|boolean',
            'skip_invalid' => 'nullable|boolean',
        ], [
            'file.required' => 'Pilih file CSV/XLSX yang akan diimpor.',
            'file.mimes' => 'Format file harus .csv, .txt, atau .xlsx.',
            'file.max' => 'Ukuran file maksimal 10MB.',
            'file.uploaded' => 'File gagal diunggah. Pastikan ukurannya tidak melebihi batas server.',
        ]);

        $lock = Cache::lock('opk_import_lock', 300);
        if (! $lock->get()) {
            return redirect()->route('admin.opk.import')
                ->with('error', 'Ada proses impor lain yang sedang berjalan. Tunggu hingga selesai lalu coba lagi.');
        }

        try {
            set_time_limit(300);

            $result = $this->importService->import(
                $validated['file'],
                $request->user(),
                $request->boolean('dry_run'),
                $request->boolean('skip_invalid'),
            );
        } finally {
            $lock->release();
        }

        return redirect()->route('admin.opk.import')->with('import_result', $result);
    }

    public function template(Request $request)
    {
        $format = $request->string('format')->lower()->value() === 'csv' ? 'csv' : 'xlsx';

        $path = $this->importService->generateTemplate($format);

        return response()
            ->download($path, 'template-impor-opk.'.$format)
            ->deleteFileAfterSend();
    }

    public function errors(string $token)
    {
        abort_unless(
            preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $token) === 1,
            404
        );

        $path = $this->importService->errorFilePath($token);
        abort_unless(is_file($path), 404);

        return response()->download($path, 'kesalahan-impor-opk.csv');
    }
}
