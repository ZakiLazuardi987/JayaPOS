<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductEarningPointsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all products
        $products = DB::table('products')->get();

        foreach ($products as $product) {
            // Misalnya: kita atur pointnya menjadi (harga / 10.000) lalu dibulatkan ke bawah.
            // Atau bisa dikali variasi tertentu, misal minimal 1 poin.
            $points = floor($product->base_price / 10000);
            
            // Atau jika harganya murah banget, beri minimal 1 poin.
            if ($points < 1) {
                $points = 1;
            }

            DB::table('products')
                ->where('product_id', $product->product_id)
                ->update(['earning_points' => $points]);
        }
    }
}
