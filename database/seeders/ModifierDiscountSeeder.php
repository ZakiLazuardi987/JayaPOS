<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModifierDiscountSeeder extends Seeder
{
    public function run()
    {
        // ============================================
        // 1. INSERT MODIFIER DATA
        // ============================================
        DB::table('modifier')->insertOrIgnore([
            // Ukuran (single select)
            ['name' => 'Small',        'group_name' => 'Ukuran',       'selection_type' => 'single',   'extra_price' => 0,    'type' => 'add', 'is_active' => 1],
            ['name' => 'Medium',       'group_name' => 'Ukuran',       'selection_type' => 'single',   'extra_price' => 5000, 'type' => 'add', 'is_active' => 1],
            ['name' => 'Large',        'group_name' => 'Ukuran',       'selection_type' => 'single',   'extra_price' => 8000, 'type' => 'add', 'is_active' => 1],
            // Tingkat Es (single select)
            ['name' => 'Less Ice',     'group_name' => 'Tingkat Es',   'selection_type' => 'single',   'extra_price' => 0,    'type' => 'add', 'is_active' => 1],
            ['name' => 'Normal Ice',   'group_name' => 'Tingkat Es',   'selection_type' => 'single',   'extra_price' => 0,    'type' => 'add', 'is_active' => 1],
            ['name' => 'Full Ice',     'group_name' => 'Tingkat Es',   'selection_type' => 'single',   'extra_price' => 0,    'type' => 'add', 'is_active' => 1],
            // Tingkat Gula (single select)
            ['name' => 'Less Sugar',   'group_name' => 'Tingkat Gula', 'selection_type' => 'single',   'extra_price' => 0,    'type' => 'add', 'is_active' => 1],
            ['name' => 'Normal Sugar', 'group_name' => 'Tingkat Gula', 'selection_type' => 'single',   'extra_price' => 0,    'type' => 'add', 'is_active' => 1],
        ]);

        // ============================================
        // 2. ATTACH MODIFIER KE PRODUK MINUMAN (id 1-31)
        // ============================================
        $drinkIds = range(1, 31);
        $sizeIds  = DB::table('modifier')->whereIn('name', ['Small', 'Medium', 'Large'])->pluck('modifier_id');
        $iceIds   = DB::table('modifier')->whereIn('name', ['Less Ice', 'Normal Ice', 'Full Ice'])->pluck('modifier_id');
        $sugarIds = DB::table('modifier')->whereIn('name', ['Less Sugar', 'Normal Sugar'])->pluck('modifier_id');
        $allModIds = $sizeIds->merge($iceIds)->merge($sugarIds);

        $pivotRows = [];
        foreach ($drinkIds as $pid) {
            foreach ($allModIds as $mid) {
                $pivotRows[] = [
                    'product_id'  => $pid,
                    'modifier_id' => $mid,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }
        DB::table('product_modifier')->insertOrIgnore($pivotRows);

        // ============================================
        // 3. INSERT DISKON CONTOH
        // ============================================
        DB::table('discount')->insertOrIgnore([
            ['name' => 'Diskon Lebaran',  'code' => 'LEBARAN10',  'type' => 'percentage', 'value' => 10, 'min_purchase' => 0, 'is_active' => 1, 'usage_count' => 0],
            ['name' => 'Diskon Karyawan', 'code' => 'KARYAWAN5',  'type' => 'percentage', 'value' => 5,  'min_purchase' => 0, 'is_active' => 1, 'usage_count' => 0],
            ['name' => 'Diskon Opening',  'code' => 'OPENING15',  'type' => 'percentage', 'value' => 15, 'min_purchase' => 0, 'is_active' => 1, 'usage_count' => 0],
            ['name' => 'Diskon Member',   'code' => 'MEMBER10',   'type' => 'percentage', 'value' => 10, 'min_purchase' => 0, 'is_active' => 1, 'usage_count' => 0],
        ]);

        echo "Seeder OK: modifier, product_modifier, discount\n";
    }
}
