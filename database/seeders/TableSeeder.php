<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Indoor 1 (area_id = 1) -> 12 tables
        for ($i = 1; $i <= 12; $i++) {
            \App\Models\Table::create([
                'area_id' => 1,
                'name' => 'Table ' . $i,
                'capacity' => 4,
                'status' => ($i === 1) ? 'occupied' : 'available' // Table 1 is occupied
            ]);
        }

        // Outdoor 1 (area_id = 2) -> 15 tables
        for ($i = 13; $i <= 27; $i++) {
            \App\Models\Table::create([
                'area_id' => 2,
                'name' => 'Table ' . $i,
                'capacity' => 4,
                'status' => 'available'
            ]);
        }

        // VIP Room (area_id = 3) -> 4 tables
        \App\Models\Table::create(['area_id' => 3, 'name' => 'VIP 1', 'capacity' => 6, 'status' => 'available']);
        \App\Models\Table::create(['area_id' => 3, 'name' => 'VIP 2', 'capacity' => 12, 'status' => 'available']);
        \App\Models\Table::create(['area_id' => 3, 'name' => 'VIP 3', 'capacity' => 8, 'status' => 'available']);
        \App\Models\Table::create(['area_id' => 3, 'name' => 'VIP 4', 'capacity' => 6, 'status' => 'available']);
    }
}
