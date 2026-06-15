<?php

namespace App\ScopePlanner\DTO;

/** A catalog item exposed to the Scope Planner (wraps a StockItem). */
final class InventoryItemDTO
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $productId,
        public readonly string $name,
        public readonly ?string $category,
        public readonly ?string $imageUrl,
        public readonly ?string $unit,
        public readonly float $purchasePrice = 0.0,
    ) {}
}
