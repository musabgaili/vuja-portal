<?php

namespace App\ScopePlanner\Adapters;

use App\Models\StockItem;
use App\ScopePlanner\Contracts\InventoryContract;
use App\ScopePlanner\DTO\InventoryItemDTO;
use Illuminate\Support\Collection;

/** Wraps the existing StockItem model + its priceFor($tier) tier pricing. */
class StockItemInventoryAdapter implements InventoryContract
{
    public function search(string $query, int $limit = 25): Collection
    {
        $q = trim($query);

        return StockItem::query()
            ->where('is_active', true)
            ->when($q !== '', function ($b) use ($q) {
                $b->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('category', 'like', "%{$q}%")
                        ->orWhere('product_id', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (StockItem $i) => $this->toDto($i));
    }

    public function find(int $id): ?InventoryItemDTO
    {
        $item = StockItem::find($id);

        return $item ? $this->toDto($item) : null;
    }

    public function priceFor(int $itemId, string $tier): float
    {
        $item = StockItem::find($itemId);

        return $item ? (float) $item->priceFor($tier) : 0.0;
    }

    private function toDto(StockItem $i): InventoryItemDTO
    {
        return new InventoryItemDTO(
            id: $i->id,
            productId: $i->product_id,
            name: $i->name,
            category: $i->category,
            imageUrl: $i->image_url,
            unit: $i->unit,
            purchasePrice: (float) $i->purchase_price,
        );
    }
}
