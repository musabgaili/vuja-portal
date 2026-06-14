<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Raspberry Pi 4 (4GB)',      'category' => 'Electronics', 'internal_cost' => 220, 'markup_percentage' => 35, 'unit' => 'unit'],
            ['name' => 'Ultrasonic Sensor HC-SR04',  'category' => 'Electronics', 'internal_cost' => 12,  'markup_percentage' => 50, 'unit' => 'unit'],
            ['name' => '7" Touch Display',            'category' => 'Electronics', 'internal_cost' => 180, 'markup_percentage' => 30, 'unit' => 'unit'],
            ['name' => 'HD Camera Module',            'category' => 'Electronics', 'internal_cost' => 95,  'markup_percentage' => 40, 'unit' => 'unit'],
            ['name' => 'Aluminium Kiosk Enclosure',   'category' => 'Structural',  'internal_cost' => 600, 'markup_percentage' => 25, 'unit' => 'unit'],
            ['name' => 'Powder-coated Steel Frame',   'category' => 'Structural',  'internal_cost' => 350, 'markup_percentage' => 25, 'unit' => 'unit'],
            ['name' => 'Firmware Development',         'category' => 'Labor',       'internal_cost' => 1500, 'markup_percentage' => 60, 'unit' => 'package'],
            ['name' => 'UI/UX Design',                'category' => 'Labor',       'internal_cost' => 1200, 'markup_percentage' => 60, 'unit' => 'package'],
            ['name' => 'Cloud Setup & Integration',   'category' => 'Labor',       'internal_cost' => 900,  'markup_percentage' => 55, 'unit' => 'package'],
        ];

        foreach ($items as $item) {
            InventoryItem::firstOrCreate(['name' => $item['name']], $item);
        }
    }
}
