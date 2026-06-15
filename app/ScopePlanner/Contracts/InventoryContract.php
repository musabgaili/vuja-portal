<?php

namespace App\ScopePlanner\Contracts;

use App\ScopePlanner\DTO\InventoryItemDTO;
use Illuminate\Support\Collection;

/** Bound to the existing Inventory (StockItem) module — do not reimplement it. */
interface InventoryContract
{
    /** Free-text search of the catalog (name, category, product_id). @return Collection<int,InventoryItemDTO> */
    public function search(string $query, int $limit = 25): Collection;

    public function find(int $id): ?InventoryItemDTO;

    /** Selling price for a tier: 'student' | 'entrepreneur' | 'company'. */
    public function priceFor(int $itemId, string $tier): float;
}
