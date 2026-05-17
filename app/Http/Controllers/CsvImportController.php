<?php

namespace App\Http\Controllers;

use App\Services\CsvImportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CsvImportController extends Controller
{
    public function index()
    {
        return Inertia::render('CsvImport/Index', [
            'banks' => collect(CsvImportService::BANKS)->map(fn($b, $k) => [
                'id' => $k,
                'name' => $b['name'],
                'description' => $b['description'],
                'is_generic' => $k === 'generic',
            ])->values(),
        ]);
    }

    public function preview(Request $request, CsvImportService $csv)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
            'bank' => 'required|string|in:' . implode(',', array_keys(CsvImportService::BANKS)),
        ]);

        try {
            $preview = $csv->preview($request->file('file'), $request->bank);
            // Store file for later import
            $path = $request->file('file')->store('csv-imports');
            $preview['path'] = $path;

            return Inertia::render('CsvImport/Preview', $preview);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function import(Request $request, CsvImportService $csv)
    {
        $request->validate([
            'path' => 'required|string',
            'bank' => 'required|string',
            'column_map' => 'nullable|array',
        ]);

        $file = new \Illuminate\Http\UploadedFile(
            storage_path('app/' . $request->path),
            basename($request->path)
        );

        try {
            $transactions = $csv->parse($file, $request->bank, $request->column_map);
            $result = $csv->store($request->user()->id, $transactions);

            if ($result['errors']) {
                return redirect()->route('csv-import.index')
                    ->with('warning', count($result['errors']) . ' transactions ignorées (erreurs de validation)');
            }

            return redirect()->route('transactions.index')
                ->with('success', $result['stored'] . ' transactions importées avec succès!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
