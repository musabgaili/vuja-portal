<?php

namespace App\Http\Controllers;

use App\Exports\StockExport;
use App\Exports\StockTemplateExport;
use App\Imports\StockImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StockImportController extends Controller
{
    public function create()
    {
        return view('inventory.import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'], // 10 MB
        ]);

        $import = new StockImport();
        Excel::import($import, $request->file('file'));

        return redirect()->route('inventory.index')
            ->with('status', __('portal.inventory.import_done', ['created' => $import->created, 'updated' => $import->updated]))
            ->with('import_failures', $import->failures);
    }

    public function template()
    {
        return Excel::download(new StockTemplateExport(), 'inventory-template.xlsx');
    }

    public function export()
    {
        return Excel::download(new StockExport(), 'inventory-export.xlsx');
    }
}
