<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockItemRequest;
use App\Models\Lab;
use App\Models\StockItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Inventory CRUD. Stock per lab lives on the lab_stock_item pivot; the product
 * image is stored on the public disk.
 */
class StockItemController extends Controller
{
    public function index(Request $request)
    {
        $labs = Lab::orderBy('name')->get();
        $query = StockItem::with('labs');

        if ($search = $request->get('q')) {
            $query->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                    ->orWhere('product_id', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($labId = $request->get('lab')) {
            $query->whereHas('labs', fn ($q) => $q->where('labs.id', $labId));
        }

        $items = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('inventory.index', compact('items', 'labs'));
    }

    public function create()
    {
        return view('inventory.create', ['labs' => Lab::orderBy('name')->get()]);
    }

    public function store(StockItemRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('inventory', 'public');
        }

        $item = StockItem::create($data);
        $this->syncLabs($item, $request->input('quantities', []));

        return redirect()->route('inventory.index')->with('status', __('portal.inventory.added'));
    }

    public function edit(StockItem $stockItem)
    {
        $stockItem->load('labs');

        return view('inventory.edit', [
            'item' => $stockItem,
            'labs' => Lab::orderBy('name')->get(),
        ]);
    }

    public function update(StockItemRequest $request, StockItem $stockItem)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            if ($stockItem->image_path) {
                Storage::disk('public')->delete($stockItem->image_path); // replace, don't orphan
            }
            $data['image_path'] = $request->file('image')->store('inventory', 'public');
        }

        $stockItem->update($data);
        $this->syncLabs($stockItem, $request->input('quantities', []));

        return redirect()->route('inventory.index')->with('status', __('portal.inventory.updated'));
    }

    public function destroy(StockItem $stockItem)
    {
        if ($stockItem->image_path) {
            Storage::disk('public')->delete($stockItem->image_path);
        }
        $stockItem->delete(); // pivot rows cascade via FK

        return redirect()->route('inventory.index')->with('status', __('portal.inventory.deleted'));
    }

    /** $quantities is [lab_id => qty]; writes one pivot row per lab. */
    protected function syncLabs(StockItem $item, array $quantities): void
    {
        $pivot = [];
        foreach ($quantities as $labId => $qty) {
            $pivot[(int) $labId] = ['quantity' => max(0, (int) $qty)];
        }
        $item->labs()->sync($pivot);
    }
}
