<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Favorite;
use App\Models\Modifier;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    // ==========================================
    // 1. HALAMAN FAVORIT (Default)
    // ==========================================
    public function index()
    {
        if (!session('active_outlet')) {
            return redirect('/login')->withErrors(['msg' => 'Silakan pilih outlet terlebih dahulu.']);
        }
        $outletId = session('active_outlet');

        // Ambil produk yang SUDAH di favorit
        $favorites = Favorite::with('product')->where('outlet_id', $outletId)->get();
        $products = $favorites->pluck('product')->filter()->values();
        $totalPages = floor($products->count() / 16) + 1;

        // Ambil produk yang BELUM di favorit (untuk modal pop-up)
        $favoriteProductIds = $favorites->pluck('product_id')->toArray();
        $availableProducts = Product::where('is_available', 1)
                                    ->whereNotIn('product_id', $favoriteProductIds)
                                    ->get();

        // Ambil semua diskon yang aktif
        $discounts = DB::table('discount')
                        ->where('is_active', 1)
                        ->get();

        // Ambil service charge, tax, dan staff aktif
        $serviceCharges = DB::table('service_charge')->where('is_active', 1)->get();
        $taxes = DB::table('tax')->where('is_active', 1)->get();
        $staffs = DB::table('staff')->where('is_active', 1)->where('outlet_id', $outletId)->get();

        // Return ke view khusus favorit
        return view('pos.favorit', compact('products', 'totalPages', 'availableProducts', 'discounts', 'serviceCharges', 'taxes', 'staffs'));
    }

    // ==========================================
    // AJAX: Detail produk + modifier tergroup
    // ==========================================
    public function getProductDetail($id)
    {
        $product = Product::with(['modifiers'])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        // Group modifier berdasarkan group_name & selection_type
        $groups = $product->modifiers
            ->groupBy('group_name')
            ->map(function ($items, $groupName) {
                return [
                    'group_name'     => $groupName ?: 'Pilihan',
                    'selection_type' => $items->first()->selection_type, // single / multiple
                    'options'        => $items->map(function ($m) {
                        return [
                            'modifier_id' => $m->modifier_id,
                            'name'        => $m->name,
                            'extra_price' => (float) $m->extra_price,
                        ];
                    })->values(),
                ];
            })->values();

        return response()->json([
            'product_id' => $product->product_id,
            'name'       => $product->name,
            'price'      => (float) $product->base_price,
            'img_url'    => $product->img_url,
            'groups'     => $groups,
        ]);
    }

    // ==========================================
    // 2. HALAMAN LIBRARY
    // ==========================================
    public function library()
    {
        if (!session('active_outlet')) {
            return redirect('/login')->withErrors(['msg' => 'Silakan pilih outlet terlebih dahulu.']);
        }

        $allProducts = Product::where('is_available', 1)
                              ->orderBy('name', 'asc')
                              ->get();

        // Ambil semua diskon yang aktif
        $discounts = DB::table('discount')
                        ->where('is_active', 1)
                        ->get();

        // Ambil service charge & tax dari database
        $serviceCharges = DB::table('service_charge')->where('is_active', 1)->get();
        $taxes = DB::table('tax')->where('is_active', 1)->get();
        $staffs = DB::table('staff')->where('is_active', 1)->where('outlet_id', session('active_outlet'))->get();

        return view('pos.library', compact('allProducts', 'discounts', 'serviceCharges', 'taxes', 'staffs'));
    }

    // ==========================================
    // 3. HALAMAN CUSTOM (Kalkulator)
    // ==========================================
    public function custom()
    {
        if (!session('active_outlet')) {
            return redirect('/login')->withErrors(['msg' => 'Silakan pilih outlet terlebih dahulu.']);
        }

        $discounts = DB::table('discount')->where('is_active', 1)->get();
        $serviceCharges = DB::table('service_charge')->where('is_active', 1)->get();
        $taxes = DB::table('tax')->where('is_active', 1)->get();
        $staffs = DB::table('staff')->where('is_active', 1)->where('outlet_id', session('active_outlet'))->get();

        return view('pos.custom', compact('discounts', 'serviceCharges', 'taxes', 'staffs'));
    }

    // ==========================================
    // FUNGSI AJAX ACTION — FAVORIT
    // ==========================================
    public function addFavorite(Request $request)
    {
        $outletId  = session('active_outlet');
        $productId = $request->product_id;

        $exists = Favorite::where('outlet_id', $outletId)->where('product_id', $productId)->first();

        if (!$exists) {
            Favorite::create([
                'outlet_id'  => $outletId,
                'product_id' => $productId
            ]);
        }

        session()->flash('success', 'Produk berhasil ditambahkan ke favorit!');
        return response()->json(['success' => true]);
    }

    public function removeFavorite(Request $request)
    {
        $outletId  = session('active_outlet');
        $productId = $request->product_id;

        Favorite::where('outlet_id', $outletId)
                ->where('product_id', $productId)
                ->delete();

        session()->flash('success', 'Produk dihapus dari favorit.');
        return response()->json(['success' => true]);
    }
}