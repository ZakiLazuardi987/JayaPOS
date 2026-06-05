<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Favorite;
use App\Models\Modifier;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        // Ambil data meja dan area aktif
        $areas = \App\Models\Area::with('tables')->where('outlet_id', $outletId)->get();

        // Return ke view khusus favorit
        return view('pos.favorit', compact('products', 'totalPages', 'availableProducts', 'discounts', 'serviceCharges', 'taxes', 'staffs', 'areas'));
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

        // Ambil data meja dan area aktif
        $areas = \App\Models\Area::with('tables')->where('outlet_id', session('active_outlet'))->get();

        return view('pos.library', compact('allProducts', 'discounts', 'serviceCharges', 'taxes', 'staffs', 'areas'));
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

        // Ambil data meja dan area aktif
        $areas = \App\Models\Area::with('tables')->where('outlet_id', session('active_outlet'))->get();

        return view('pos.custom', compact('discounts', 'serviceCharges', 'taxes', 'staffs', 'areas'));
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

    // ==========================================
    // AJAX: Simpan Bill (Order Pending)
    // ==========================================
    public function saveBill(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:tables,table_id',
            'pax' => 'required|integer|min:1',
            'waiter_id' => 'required|exists:staff,staff_id',
            'cart' => 'required|array',
            'subtotal' => 'required|numeric',
            'discount_amount' => 'nullable|numeric',
            'total_final' => 'required|numeric',
        ]);

        $outletId = session('active_outlet');
        if (!$outletId) {
            return response()->json(['success' => false, 'message' => 'Outlet tidak aktif.'], 400);
        }

        try {
            DB::transaction(function () use ($request, $outletId) {
                // Pastikan ada produk fallback untuk custom amount
                DB::table('products')->updateOrInsert(
                    ['product_id' => 999],
                    [
                        'category_id' => 1,
                        'name' => 'Custom Item',
                        'base_price' => 0,
                        'is_available' => 0,
                    ]
                );

                // Buat Order baru
                $order = \App\Models\Order::create([
                    'staff_id' => auth()->user()->staff_id,
                    'outlet_id' => $outletId,
                    'table_id' => $request->table_id,
                    'pax' => $request->pax,
                    'waiter_id' => $request->waiter_id,
                    'order_type' => 'dine-in',
                    'table_number' => \App\Models\Table::find($request->table_id)->name ?? '',
                    'status' => 'pending',
                    'subtotal' => $request->subtotal,
                    'discount_amount' => $request->discount_amount ?? 0,
                    'total_final' => $request->total_final,
                    'created_at' => now(),
                    'tax_id' => $request->tax_id ?: null,
                    'service_charge_id' => $request->service_charge_id ?: null,
                    'discount_id' => $request->discount_id ?: null,
                ]);

                // Buat Order Items dan Modifiers
                foreach ($request->cart as $item) {
                    $productId = $item['product_id'];
                    $isCustom = str_starts_with($productId, 'custom_');
                    $dbProductId = $isCustom ? 999 : (int)$productId;

                    $orderItem = \App\Models\OrderItem::create([
                        'order_id' => $order->order_id,
                        'product_id' => $dbProductId,
                        'quantity' => $isCustom ? 1 : (int)$item['qty'],
                        'price_at_purchase' => $isCustom ? (float)$item['total_price'] : (float)$item['unit_price'],
                        'created_at' => now(),
                    ]);

                    // Simpan Modifiers
                    if (isset($item['modifiers']) && is_array($item['modifiers'])) {
                        foreach ($item['modifiers'] as $mod) {
                            DB::table('order_item_modifier')->insert([
                                'order_item_id' => $orderItem->order_item_id,
                                'modifier_id' => (int)$mod['id'],
                                'price_added' => (float)($mod['price'] ?? 0),
                            ]);
                        }
                    }
                }

                // Update status meja menjadi 'occupied'
                DB::table('tables')
                    ->where('table_id', $request->table_id)
                    ->update(['status' => 'occupied']);
            });

            return response()->json(['success' => true, 'message' => 'Bill berhasil disimpan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan bill: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // AJAX: Daftar Bill (Pending Orders)
    // ==========================================
    public function getPendingBills()
    {
        $outletId = session('active_outlet');

        $orders = Order::with(['table.area', 'waiter'])
            ->where('outlet_id', $outletId)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($order) {
                $createdAt = Carbon::parse($order->created_at);
                $now = Carbon::now();
                $diffMinutes = (int) $createdAt->diffInMinutes($now);

                if ($diffMinutes < 60) {
                    $waktu = $diffMinutes . ' menit';
                } else {
                    $hours = floor($diffMinutes / 60);
                    $mins  = $diffMinutes % 60;
                    $waktu = $hours . ' jam' . ($mins > 0 ? ' ' . $mins . ' menit' : '');
                }

                return [
                    'order_id'   => $order->order_id,
                    'meja'       => $order->table->name ?? $order->table_number ?? '-',
                    'grup_meja'  => $order->table->area->name ?? '-',
                    'pelayan'    => $order->waiter->name ?? '-',
                    'waktu'      => $waktu,
                    'created_at' => $order->created_at,
                    'total'      => $order->total_final,
                    'pax'        => $order->pax,
                ];
            });

        return response()->json(['success' => true, 'data' => $orders]);
    }

    // ==========================================
    // AJAX: Detail Order (untuk follow-up bill)
    // ==========================================
    public function getOrderDetail($orderId)
    {
        $outletId = session('active_outlet');

        $order = Order::with([
            'table.area',
            'waiter',
            'items.product',
        ])
            ->where('outlet_id', $outletId)
            ->where('status', 'pending')
            ->findOrFail($orderId);

        // Bangun cart items dari order_items (kompatibel dengan format cart JS)
        $cartItems = $order->items->map(function ($item) {
            $isCustom = $item->product_id == 999;

            // Ambil modifier dari pivot order_item_modifier
            $modifiers = DB::table('order_item_modifier')
                ->join('modifier', 'modifier.modifier_id', '=', 'order_item_modifier.modifier_id')
                ->where('order_item_modifier.order_item_id', $item->order_item_id)
                ->select('modifier.modifier_id as id', 'modifier.name', 'order_item_modifier.price_added as price')
                ->get()
                ->toArray();

            return [
                'id'          => $item->order_item_id,          // dipakai sebagai cart item id
                'product_id'  => $isCustom ? 'custom_' . $item->order_item_id : $item->product_id,
                'name'        => $isCustom ? 'Custom Amount' : ($item->product->name ?? 'Produk'),
                'qty'         => $item->quantity,
                'unit_price'  => (float) $item->price_at_purchase,
                'total_price' => (float) ($item->price_at_purchase * $item->quantity),
                'modifiers'   => $modifiers,
                'discounts'   => [],
                'order_type'  => $order->order_type ?? 'dine-in',
            ];
        });

        return response()->json([
            'success' => true,
            'order'   => [
                'order_id'     => $order->order_id,
                'meja'         => $order->table->name ?? $order->table_number ?? '-',
                'grup_meja'    => $order->table->area->name ?? '-',
                'table_id'     => $order->table_id,
                'pax'          => $order->pax,
                'waiter_id'    => $order->waiter_id,
                'waiter_name'  => $order->waiter->name ?? '-',
                'order_type'   => $order->order_type ?? 'dine-in',
                'subtotal'     => (float) $order->subtotal,
                'total_final'  => (float) $order->total_final,
            ],
            'cart'    => $cartItems,
        ]);
    }

    // ==========================================
    // AJAX: Update Meja Order Pending
    // ==========================================
    public function updateOrderTable(Request $request, $orderId)
    {
        $outletId = session('active_outlet');

        try {
            DB::transaction(function () use ($request, $orderId, $outletId) {
                $order = Order::where('outlet_id', $outletId)
                    ->where('status', 'pending')
                    ->findOrFail($orderId);

                $oldTableId = $order->table_id;
                $newTableId = $request->table_id;

                // Update order
                $order->table_id     = $newTableId;
                $order->table_number = \App\Models\Table::find($newTableId)->name ?? '';
                $order->pax          = $request->pax;
                $order->waiter_id    = $request->waiter_id;
                $order->save();

                // Bebaskan meja lama (jika berbeda dari meja baru)
                if ($oldTableId && $oldTableId != $newTableId) {
                    DB::table('tables')
                        ->where('table_id', $oldTableId)
                        ->update(['status' => 'available']);
                }

                // Tandai meja baru sebagai occupied
                DB::table('tables')
                    ->where('table_id', $newTableId)
                    ->update(['status' => 'occupied']);
            });

            return response()->json(['success' => true, 'message' => 'Meja berhasil diupdate.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}