<?php

use App\Http\Controllers\StockImportController;
use App\Http\Controllers\StockItemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Inventory Module (Manager only)
|--------------------------------------------------------------------------
| Physical stock: products/SKUs, per-lab quantities, three price tiers and
| Excel bulk import/export. Separate from the AI-planner InventoryItem.
*/
Route::prefix('internal/inventory')
    ->middleware(['auth', 'is_internal', 'is_manager'])
    ->name('inventory.')
    ->group(function () {
        // Static routes first so they never get caught by {stockItem}.
        Route::get('/', [StockItemController::class, 'index'])->name('index');
        Route::get('/create', [StockItemController::class, 'create'])->name('create');
        Route::post('/', [StockItemController::class, 'store'])->name('store');

        // Bulk Excel
        Route::get('/import', [StockImportController::class, 'create'])->name('import.create');
        Route::post('/import', [StockImportController::class, 'store'])->name('import.store');
        Route::get('/import/template', [StockImportController::class, 'template'])->name('import.template');
        Route::get('/export', [StockImportController::class, 'export'])->name('export');

        Route::get('/{stockItem}/edit', [StockItemController::class, 'edit'])->name('edit');
        Route::put('/{stockItem}', [StockItemController::class, 'update'])->name('update');
        Route::delete('/{stockItem}', [StockItemController::class, 'destroy'])->name('destroy');
    });
