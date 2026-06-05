<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assuming outlet_id 1 exists (Toko Kopi Jaya)
        $areas = [
            ['outlet_id' => 1, 'name' => 'Indoor 1'],
            ['outlet_id' => 1, 'name' => 'Outdoor 1'],
            ['outlet_id' => 1, 'name' => 'VIP Room'],
        ];

        foreach ($areas as $area) {
            \App\Models\Area::create($area);
        }
    }
}
