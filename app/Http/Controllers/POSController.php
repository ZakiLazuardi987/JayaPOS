<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Favorite;
use App\Models\Modifier;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
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
            'earning_points' => (int) $product->earning_points,
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
                    'order_type' => $request->order_type ?? 'dine-in',
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
    // AJAX: Fetch Pending Order by Pickup Code
    // ==========================================
    public function getOrderByPickupCode($code)
    {
        $outletId = session('active_outlet');

        $order = Order::with([
            'table.area',
            'waiter',
            'items.product',
        ])
            ->where('outlet_id', $outletId)
            ->where('status', 'pending')
            ->where('pickup_code', strtoupper($code))
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan dengan kode ' . strtoupper($code) . ' tidak ditemukan atau sudah tidak berstatus pending.'
            ], 404);
        }

        // Bangun cart items dari order_items (kompatibel dengan format cart JS)
        $cartItems = $order->items->map(function ($item) use ($order) {
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

    // ==========================================
    // AJAX: Checkout Cash
    // ==========================================
    public function checkoutCash(Request $request)
    {
        $request->validate([
            'cart'        => 'required|array|min:1',
            'subtotal'    => 'required|numeric|min:0',
            'total_final' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'order_type'  => 'required|string',
        ]);

        $outletId = session('active_outlet');
        $staffId  = auth()->user()->staff_id;
        $createdOrderId = null;

        try {
            DB::transaction(function () use ($request, $outletId, $staffId, &$createdOrderId) {

                // 1. INSERT orders (langsung status paid)
                $tableId   = $request->table_id ?: null;
                $tableName = $tableId
                    ? (\App\Models\Table::find($tableId)->name ?? '')
                    : ($request->table_number ?? '');

                // Calculate earning points from DB securely
                $pointsEarned = 0;
                foreach ($request->cart as $item) {
                    $productId = $item['product_id'];
                    $isCustom  = str_starts_with((string)$productId, 'custom_');
                    if (!$isCustom) {
                        $product = \App\Models\Product::find($productId);
                        if ($product) {
                            $pointsEarned += ($product->earning_points * (int)$item['qty']);
                        }
                    }
                }

                if ($request->active_order_id) {
                    $order = Order::findOrFail($request->active_order_id);
                    $order->update([
                        'staff_id'          => $staffId,
                        'outlet_id'         => $outletId,
                        'member_id'         => $request->customer_id ?: null,
                        'source'            => $order->source ?? 'POS - In-Store',
                        'order_type'        => $request->order_type,
                        'table_id'          => $tableId,
                        'table_number'      => $tableName,
                        'pax'               => $request->pax ?? 1,
                        'waiter_id'         => $request->waiter_id ?: null,
                        'status'            => 'paid',
                        'subtotal'          => $request->subtotal,
                        'tax_id'            => $request->tax_id ?: null,
                        'service_charge_id' => $request->service_charge_id ?: null,
                        'discount_id'       => $request->discount_id ?: null,
                        'discount_amount'   => $request->discount_amount ?? 0,
                        'total_final'       => $request->total_final,
                        'points_earned'     => $pointsEarned,
                    ]);
                    $oldItemIds = \App\Models\OrderItem::where('order_id', $order->order_id)->pluck('order_item_id');
                    \Illuminate\Support\Facades\DB::table('order_item_modifier')->whereIn('order_item_id', $oldItemIds)->delete();
                    \App\Models\OrderItem::where('order_id', $order->order_id)->delete();
                } else {
                    $order = Order::create([
                        'staff_id'          => $staffId,
                        'outlet_id'         => $outletId,
                        'member_id'         => $request->customer_id ?: null,
                        'source'            => 'POS - In-Store',
                        'order_type'        => $request->order_type,
                        'table_id'          => $tableId,
                        'table_number'      => $tableName,
                        'pax'               => $request->pax ?? 1,
                        'waiter_id'         => $request->waiter_id ?: null,
                        'status'            => 'paid',
                        'subtotal'          => $request->subtotal,
                        'tax_id'            => $request->tax_id ?: null,
                        'service_charge_id' => $request->service_charge_id ?: null,
                        'discount_id'       => $request->discount_id ?: null,
                        'discount_amount'   => $request->discount_amount ?? 0,
                        'total_final'       => $request->total_final,
                        'points_earned'     => $pointsEarned,
                        'created_at'        => now(),
                    ]);
                }

                // Set paid_at agar trigger kredit poin berjalan
                DB::table('orders')
                    ->where('order_id', $order->order_id)
                    ->update(['paid_at' => now()]);

                $createdOrderId = $order->order_id;

                // 2. INSERT order_items + modifiers
                foreach ($request->cart as $item) {
                    $productId   = $item['product_id'];
                    $isCustom    = str_starts_with((string)$productId, 'custom_');
                    $dbProductId = $isCustom ? 999 : (int)$productId;

                    $orderItem = OrderItem::create([
                        'order_id'          => $order->order_id,
                        'product_id'        => $dbProductId,
                        'quantity'          => $isCustom ? 1 : (int)$item['qty'],
                        'price_at_purchase' => $isCustom
                            ? (float)$item['total_price']
                            : (float)$item['unit_price'],
                        'created_at'        => now(),
                    ]);

                    if (!empty($item['modifiers']) && is_array($item['modifiers'])) {
                        foreach ($item['modifiers'] as $mod) {
                            DB::table('order_item_modifier')->insert([
                                'order_item_id' => $orderItem->order_item_id,
                                'modifier_id'   => (int)$mod['id'],
                                'price_added'   => (float)($mod['price'] ?? 0),
                            ]);
                        }
                    }
                }

                // 3. INSERT payment (cash = langsung success)
                Payment::create([
                    'order_id'         => $order->order_id,
                    'payment_method'   => 'Cash',
                    'payment_gateway'  => null,
                    'status'           => 'success',
                    'amount'           => $request->total_final,
                    'payment_response' => [
                        'amount_tendered' => $request->amount_paid,
                        'change'          => $request->amount_paid - $request->total_final,
                    ],
                    'paid_at'          => now(),
                ]);

                // 4. Update points member
                if ($order->member_id && $order->points_earned > 0) {
                    $member = \App\Models\Member::find($order->member_id);
                    if ($member) {
                        $member->current_points += $order->points_earned;
                        $member->lifetime_points_earned += $order->points_earned;
                        $member->save();
                    }
                }

                // 5. Bebaskan meja jika ada
                if ($tableId) {
                    DB::table('tables')
                        ->where('table_id', $tableId)
                        ->update(['status' => 'available']);
                }
            });

            return response()->json([
                'success'   => true,
                'order_id'  => $createdOrderId,
                'kembalian' => $request->amount_paid - $request->total_final,
                'message'   => 'Transaksi berhasil disimpan.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ==========================================
    // AJAX: Checkout QRIS
    // ==========================================
    public function checkoutQris(Request $request)
    {
        $request->validate([
            'cart'        => 'required|array|min:1',
            'subtotal'    => 'required|numeric|min:0',
            'total_final' => 'required|numeric|min:0',
            'order_type'  => 'required|string',
        ]);

        $outletId = session('active_outlet');
        $staffId  = auth()->user()->staff_id;
        $createdOrderId = null;
        $qrCodeUrl = null;

        try {
            DB::transaction(function () use ($request, $outletId, $staffId, &$createdOrderId, &$qrCodeUrl) {

                // 1. INSERT orders (status pending)
                $tableId   = $request->table_id ?: null;
                $tableName = $tableId
                    ? (\App\Models\Table::find($tableId)->name ?? '')
                    : ($request->table_number ?? '');

                // Calculate earning points from DB securely
                $pointsEarned = 0;
                foreach ($request->cart as $item) {
                    $productId = $item['product_id'];
                    $isCustom  = str_starts_with((string)$productId, 'custom_');
                    if (!$isCustom) {
                        $product = \App\Models\Product::find($productId);
                        if ($product) {
                            $pointsEarned += ($product->earning_points * (int)$item['qty']);
                        }
                    }
                }

                if ($request->active_order_id) {
                    $order = Order::findOrFail($request->active_order_id);
                    $order->update([
                        'staff_id'          => $staffId,
                        'outlet_id'         => $outletId,
                        'member_id'         => $request->customer_id ?: null,
                        'source'            => $order->source ?? 'POS - In-Store',
                        'order_type'        => $request->order_type,
                        'table_id'          => $tableId,
                        'table_number'      => $tableName,
                        'pax'               => $request->pax ?? 1,
                        'waiter_id'         => $request->waiter_id ?: null,
                        'status'            => 'pending',
                        'subtotal'          => $request->subtotal,
                        'tax_id'            => $request->tax_id ?: null,
                        'service_charge_id' => $request->service_charge_id ?: null,
                        'discount_id'       => $request->discount_id ?: null,
                        'discount_amount'   => $request->discount_amount ?? 0,
                        'total_final'       => $request->total_final,
                        'points_earned'     => $pointsEarned,
                    ]);
                    $oldItemIds = \App\Models\OrderItem::where('order_id', $order->order_id)->pluck('order_item_id');
                    \Illuminate\Support\Facades\DB::table('order_item_modifier')->whereIn('order_item_id', $oldItemIds)->delete();
                    \App\Models\OrderItem::where('order_id', $order->order_id)->delete();
                } else {
                    $order = Order::create([
                        'staff_id'          => $staffId,
                        'outlet_id'         => $outletId,
                        'member_id'         => $request->customer_id ?: null,
                        'source'            => 'POS - In-Store',
                        'order_type'        => $request->order_type,
                        'table_id'          => $tableId,
                        'table_number'      => $tableName,
                        'pax'               => $request->pax ?? 1,
                        'waiter_id'         => $request->waiter_id ?: null,
                        'status'            => 'pending',
                        'subtotal'          => $request->subtotal,
                        'tax_id'            => $request->tax_id ?: null,
                        'service_charge_id' => $request->service_charge_id ?: null,
                        'discount_id'       => $request->discount_id ?: null,
                        'discount_amount'   => $request->discount_amount ?? 0,
                        'total_final'       => $request->total_final,
                        'points_earned'     => $pointsEarned,
                        'created_at'        => now(),
                    ]);
                }

                $createdOrderId = $order->order_id;

                // 2. INSERT order_items + modifiers
                foreach ($request->cart as $item) {
                    $productId   = $item['product_id'];
                    $isCustom    = str_starts_with((string)$productId, 'custom_');
                    $dbProductId = $isCustom ? 999 : (int)$productId;

                    $orderItem = OrderItem::create([
                        'order_id'          => $order->order_id,
                        'product_id'        => $dbProductId,
                        'quantity'          => $isCustom ? 1 : (int)$item['qty'],
                        'price_at_purchase' => $isCustom
                            ? (float)$item['total_price']
                            : (float)$item['unit_price'],
                        'created_at'        => now(),
                    ]);

                    if (!empty($item['modifiers']) && is_array($item['modifiers'])) {
                        foreach ($item['modifiers'] as $mod) {
                            DB::table('order_item_modifier')->insert([
                                'order_item_id' => $orderItem->order_item_id,
                                'modifier_id'   => (int)$mod['id'],
                                'price_added'   => (float)($mod['price'] ?? 0),
                            ]);
                        }
                    }
                }

                // 3. Request QRIS ke Midtrans Core API
                $serverKey = config('services.midtrans.server_key');
                $isProduction = config('services.midtrans.is_production', false);
                $baseUrl = $isProduction 
                    ? 'https://api.midtrans.com/v2/charge' 
                    : 'https://api.sandbox.midtrans.com/v2/charge';

                $payload = [
                    'payment_type' => 'qris',
                    'transaction_details' => [
                        'order_id'     => 'ORDER-' . $order->order_id . '-' . time(),
                        'gross_amount' => (int) $request->total_final,
                    ],
                    'custom_field1' => (string) $order->order_id, // Simpan real order_id di custom_field
                ];

                $response = \Illuminate\Support\Facades\Http::withBasicAuth($serverKey, '')
                    ->post($baseUrl, $payload);

                $midtransData = $response->json();

                if ($response->failed() || !isset($midtransData['status_code']) || $midtransData['status_code'] != '201') {
                    throw new \Exception('Gagal generate QRIS Midtrans: ' . ($midtransData['status_message'] ?? 'Unknown Error'));
                }

                $actions = $midtransData['actions'] ?? [];
                foreach ($actions as $action) {
                    if ($action['name'] === 'generate-qr-code') {
                        $qrCodeUrl = $action['url'];
                        break;
                    }
                }

                if (!$qrCodeUrl) {
                    throw new \Exception('URL QR Code tidak ditemukan di response Midtrans.');
                }

                // 4. INSERT payment (status pending)
                Payment::create([
                    'order_id'        => $order->order_id,
                    'payment_method'  => 'QRIS',
                    'payment_gateway' => 'midtrans',
                    'transaction_id'  => $midtransData['transaction_id'] ?? null,
                    'payment_url'     => $qrCodeUrl,
                    'payment_response'=> $midtransData,
                    'status'          => 'pending',
                    'amount'          => $request->total_final,
                    'created_at'      => now(),
                ]);

                // Meja tetap occupied (tidak dibebaskan sampai lunas)
            });

            return response()->json([
                'success'     => true,
                'order_id'    => $createdOrderId,
                'qr_code_url' => $qrCodeUrl,
                'message'     => 'QRIS berhasil dibuat.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat QRIS: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ==========================================
    // AJAX: Cek Status QRIS (Polling)
    // ==========================================
    public function checkQrisStatus($orderId)
    {
        $payment = Payment::where('order_id', $orderId)
                          ->where('payment_gateway', 'midtrans')
                          ->first();

        if (!$payment) {
            return response()->json(['status' => 'not_found']);
        }

        if ($payment->status === 'success') {
            return response()->json(['status' => 'success']);
        }

        if ($payment->status === 'failed' || $payment->status === 'expired') {
            return response()->json(['status' => 'failed']);
        }

        // Jika masih pending, tembak API Midtrans untuk cek status asli
        // (Sangat berguna untuk testing di localhost karena Webhook tidak jalan)
        $serverKey = config('services.midtrans.server_key');
        $isProduction = config('services.midtrans.is_production', false);
        $baseUrl = $isProduction 
            ? 'https://api.midtrans.com/v2/' 
            : 'https://api.sandbox.midtrans.com/v2/';

        if ($payment->transaction_id) {
            try {
                $response = \Illuminate\Support\Facades\Http::withBasicAuth($serverKey, '')
                    ->get($baseUrl . $payment->transaction_id . '/status');
                
                if ($response->successful()) {
                    $midtransStatus = $response->json();
                    $transactionStatus = $midtransStatus['transaction_status'] ?? '';

                    if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                        DB::transaction(function() use ($payment, $midtransStatus, $orderId) {
                            $payment->status = 'success';
                            $payment->paid_at = now();
                            $payment->payment_response = array_merge((array)$payment->payment_response, (array)$midtransStatus);
                            $payment->save();

                            $order = Order::find($orderId);
                            if ($order) {
                                $order->status = 'paid';
                                $order->save();
                                DB::table('orders')->where('order_id', $order->order_id)->update(['paid_at' => now()]);
                                
                                // Poin member sudah otomatis ditambahkan oleh MySQL Trigger trg_credit_points_after_payment

                                if ($order->table_id) {
                                    DB::table('tables')->where('table_id', $order->table_id)->update(['status' => 'available']);
                                }
                            }
                        });
                        return response()->json(['status' => 'success']);
                    } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                        DB::transaction(function() use ($payment, $midtransStatus, $orderId) {
                            $payment->status = $transactionStatus == 'expire' ? 'expired' : 'failed';
                            $payment->expired_at = now();
                            $payment->payment_response = array_merge((array)$payment->payment_response, (array)$midtransStatus);
                            $payment->save();

                            $order = Order::find($orderId);
                            if ($order) {
                                $order->status = 'cancelled';
                                $order->save();
                                if ($order->table_id) {
                                    DB::table('tables')->where('table_id', $order->table_id)->update(['status' => 'available']);
                                }
                            }
                        });
                        return response()->json(['status' => 'failed']);
                    }
                }
            } catch (\Exception $e) {
                // Ignore error, kita coba lagi di interval polling berikutnya
                \Illuminate\Support\Facades\Log::error('QRIS Error: ' . $e->getMessage());
            }
        }

        return response()->json(['status' => 'pending']);
    }

    public function checkCustomer(Request $request)
    {
        $phone = $request->query('phone');
        if (!$phone) {
            return response()->json(['status' => 'error', 'message' => 'Phone number required'], 400);
        }

        // Cari berdasarkan phone_number di tabel member
        // Misal kolom phone number bisa bervariasi +62 atau 08
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Strip leading 0 or 62
        if (str_starts_with($cleanPhone, '62')) {
            $basePhone = substr($cleanPhone, 2);
        } elseif (str_starts_with($cleanPhone, '0')) {
            $basePhone = substr($cleanPhone, 1);
        } else {
            $basePhone = $cleanPhone;
        }

        $possiblePhones = [
            $basePhone,
            '0' . $basePhone,
            '62' . $basePhone,
            '+62' . $basePhone
        ];
        
        $member = \App\Models\Member::whereIn('phone_number', $possiblePhones)
            ->where('is_active', 1)
            ->first();

        if ($member) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $member->member_id,
                    'name' => $member->name,
                    'phone' => $member->phone_number,
                    'points' => $member->current_points
                ]
            ]);
        }

        return response()->json(['status' => 'not_found'], 404);
    }

    // ==========================================
    // AJAX: Search Members
    // ==========================================
    public function searchMembers(Request $request)
    {
        $query = $request->query('q', '');
        
        $membersQuery = \App\Models\Member::where('is_active', 1);

        if ($query) {
            $membersQuery->where(function($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%')
                  ->orWhere('phone_number', 'LIKE', '%' . $query . '%')
                  ->orWhere('email', 'LIKE', '%' . $query . '%');
            });
        }

        $totalMembers = \App\Models\Member::where('is_active', 1)->count();
        $members = $membersQuery->orderBy('created_at', 'desc')->limit(20)->get();

        return response()->json([
            'status' => 'success',
            'total' => $totalMembers,
            'data' => $members->map(function($m) {
                return [
                    'id' => $m->member_id,
                    'name' => $m->name,
                    'phone' => $m->phone_number,
                    'email' => $m->email,
                    'points' => $m->current_points
                ];
            })
        ]);
    }

    // ==========================================
    // AJAX: Checkout Pisah Bayar (Split Payment)
    // ==========================================
    public function checkoutSplit(Request $request)
    {
        $request->validate([
            'cart'           => 'required|array|min:1',
            'subtotal'       => 'required|numeric|min:0',
            'total_final'    => 'required|numeric|min:0',
            'order_type'     => 'required|string',
            'split_payments' => 'required|array|min:1',
        ]);

        $outletId = session('active_outlet');
        $staffId  = auth()->user()->staff_id;
        $createdOrderId = null;

        try {
            DB::transaction(function () use ($request, $outletId, $staffId, &$createdOrderId) {

                // 1. INSERT orders (langsung status paid)
                $tableId   = $request->table_id ?: null;
                $tableName = $tableId
                    ? (\App\Models\Table::find($tableId)->name ?? '')
                    : ($request->table_number ?? '');

                // Calculate earning points from DB securely
                $pointsEarned = 0;
                foreach ($request->cart as $item) {
                    $productId = $item['product_id'];
                    $isCustom  = str_starts_with((string)$productId, 'custom_');
                    if (!$isCustom) {
                        $product = \App\Models\Product::find($productId);
                        if ($product) {
                            $pointsEarned += ($product->earning_points * (int)$item['qty']);
                        }
                    }
                }

                if ($request->active_order_id) {
                    $order = Order::findOrFail($request->active_order_id);
                    $order->update([
                        'staff_id'          => $staffId,
                        'outlet_id'         => $outletId,
                        'member_id'         => $request->customer_id ?: null,
                        'source'            => $order->source ?? 'POS - In-Store',
                        'order_type'        => $request->order_type,
                        'table_id'          => $tableId,
                        'table_number'      => $tableName,
                        'pax'               => $request->pax ?? 1,
                        'waiter_id'         => $request->waiter_id ?: null,
                        'status'            => 'paid',
                        'subtotal'          => $request->subtotal,
                        'tax_id'            => $request->tax_id ?: null,
                        'service_charge_id' => $request->service_charge_id ?: null,
                        'discount_id'       => $request->discount_id ?: null,
                        'discount_amount'   => $request->discount_amount ?? 0,
                        'total_final'       => $request->total_final,
                        'points_earned'     => $pointsEarned,
                    ]);
                    $oldItemIds = \App\Models\OrderItem::where('order_id', $order->order_id)->pluck('order_item_id');
                    \Illuminate\Support\Facades\DB::table('order_item_modifier')->whereIn('order_item_id', $oldItemIds)->delete();
                    \App\Models\OrderItem::where('order_id', $order->order_id)->delete();
                } else {
                    $order = Order::create([
                        'staff_id'          => $staffId,
                        'outlet_id'         => $outletId,
                        'member_id'         => $request->customer_id ?: null,
                        'source'            => 'POS - In-Store',
                        'order_type'        => $request->order_type,
                        'table_id'          => $tableId,
                        'table_number'      => $tableName,
                        'pax'               => $request->pax ?? 1,
                        'waiter_id'         => $request->waiter_id ?: null,
                        'status'            => 'paid',
                        'subtotal'          => $request->subtotal,
                        'tax_id'            => $request->tax_id ?: null,
                        'service_charge_id' => $request->service_charge_id ?: null,
                        'discount_id'       => $request->discount_id ?: null,
                        'discount_amount'   => $request->discount_amount ?? 0,
                        'total_final'       => $request->total_final,
                        'points_earned'     => $pointsEarned,
                        'created_at'        => now(),
                    ]);
                }

                DB::table('orders')
                    ->where('order_id', $order->order_id)
                    ->update(['paid_at' => now()]);

                $createdOrderId = $order->order_id;

                // 2. INSERT order_items
                foreach ($request->cart as $item) {
                    $productId   = $item['product_id'];
                    $isCustom    = str_starts_with((string)$productId, 'custom_');
                    $dbProductId = $isCustom ? 999 : (int)$productId;

                    $orderItem = OrderItem::create([
                        'order_id'          => $order->order_id,
                        'product_id'        => $dbProductId,
                        'quantity'          => $isCustom ? 1 : (int)$item['qty'],
                        'price_at_purchase' => $isCustom
                            ? (float)$item['total_price']
                            : (float)$item['unit_price'],
                        'created_at'        => now(),
                    ]);

                    if (!empty($item['modifiers']) && is_array($item['modifiers'])) {
                        foreach ($item['modifiers'] as $mod) {
                            DB::table('order_item_modifier')->insert([
                                'order_item_id' => $orderItem->order_item_id,
                                'modifier_id'   => (int)$mod['id'],
                                'price_added'   => (float)($mod['price'] ?? 0),
                            ]);
                        }
                    }
                }

                // 3. INSERT multiple payment records (one per split row)
                foreach ($request->split_payments as $pay) {
                    $method = $pay['method'] ?? 'Cash';
                    // Normalize method to enum values
                    $allowedMethods = ['QRIS','GoPay','OVO','Dana','ShopeePay','Cash','Debit Card','Credit Card','Bank Transfer'];
                    if (!in_array($method, $allowedMethods)) $method = 'Cash';

                    Payment::create([
                        'order_id'         => $order->order_id,
                        'payment_method'   => $method,
                        'payment_gateway'  => null,
                        'status'           => 'success',
                        'amount'           => (float)$pay['amount'],
                        'payment_response' => ['split_payment' => true],
                        'created_at'       => now(),
                    ]);
                }

                // 4. Kredit poin ke member
                if ($order->member_id && $pointsEarned > 0) {
                    $member = \App\Models\Member::find($order->member_id);
                    if ($member) {
                        $member->current_points += $pointsEarned;
                        $member->lifetime_points_earned += $pointsEarned;
                        $member->save();
                    }
                }

                // 5. Free up table
                if ($tableId) {
                    DB::table('tables')->where('table_id', $tableId)->update(['status' => 'available']);
                }
            });

            return response()->json([
                'success'  => true,
                'order_id' => $createdOrderId,
                'message'  => 'Pisah bayar berhasil disimpan.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ==========================================
    // AJAX: Cancel Temp QRIS Order (Split Payment Cleanup)
    // ==========================================
    public function cancelTempOrder($orderId)
    {
        $outletId = session('active_outlet');
        $order = Order::where('order_id', $orderId)
                      ->where('outlet_id', $outletId)
                      ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order tidak ditemukan.'], 404);
        }

        // Safety: hanya cancel order yang dibuat dalam 30 menit terakhir
        $createdAt = \Carbon\Carbon::parse($order->created_at);
        if ($createdAt->diffInMinutes(now()) > 30) {
            return response()->json(['success' => false, 'message' => 'Order terlalu lama untuk dibatalkan.'], 400);
        }

        try {
            DB::transaction(function () use ($order) {
                // Hapus payment records terkait
                Payment::where('order_id', $order->order_id)->delete();

                // Kembalikan meja ke occupied (masih dalam proses pisah bayar)
                if ($order->table_id) {
                    DB::table('tables')->where('table_id', $order->table_id)->update(['status' => 'occupied']);
                }

                // Hapus order_item_modifier dan order_items
                $itemIds = \App\Models\OrderItem::where('order_id', $order->order_id)->pluck('order_item_id');
                DB::table('order_item_modifier')->whereIn('order_item_id', $itemIds)->delete();
                \App\Models\OrderItem::where('order_id', $order->order_id)->delete();

                // Tandai order sebagai cancelled
                DB::table('orders')->where('order_id', $order->order_id)
                    ->update(['status' => 'cancelled', 'paid_at' => null]);
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // CETAK STRUK (PDF & Backup Thermal ESC/POS)
    // ==========================================
    public function cetakStruk($orderId)
    {
        $order = Order::with('items.product')->find($orderId);
        
        if (!$order) {
            return abort(404, 'Order tidak ditemukan');
        }

        $outlet = DB::table('outlet')->where('outlet_id', $order->outlet_id)->first();
        $staff = DB::table('staff')->where('staff_id', $order->staff_id)->first();
        $member = DB::table('member')->where('member_id', $order->member_id)->first();
        
        $tax = null;
        if ($order->tax_id) {
            $tax = DB::table('tax')->where('tax_id', $order->tax_id)->first();
        }

        $service_charge = null;
        if ($order->service_charge_id) {
            $service_charge = DB::table('service_charge')->where('service_charge_id', $order->service_charge_id)->first();
        }

        /*
        // =========================================================================
        // KODE BACKUP UNTUK PRINTER THERMAL ESC/POS (MENGGUNAKAN mike42/escpos-php)
        // =========================================================================
        // Pastikan composer package sudah terinstall: composer require mike42/escpos-php
        // 
        // use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
        // use Mike42\Escpos\Printer;
        //
        // try {
        //     // Ganti "POS-58" dengan nama printer thermal yang tersambung di komputer/Jaringan
        //     $connector = new WindowsPrintConnector("POS-58");
        //     $printer = new Printer($connector);
        //     
        //     // Header Toko
        //     $printer->setJustification(Printer::JUSTIFY_CENTER);
        //     $printer->text(($outlet->name ?? 'Toko Kopi Jaya') . "\n");
        //     $printer->text(($outlet->address ?? 'Alamat Toko') . "\n");
        //     $printer->text("--------------------------------\n");
        //     
        //     // Info Order
        //     $printer->setJustification(Printer::JUSTIFY_LEFT);
        //     $printer->text("Nota   : " . $order->order_id . "\n");
        //     $printer->text("Kasir  : " . ($staff->name ?? 'Kasir') . "\n");
        //     $printer->text("Tanggal: " . \Carbon\Carbon::parse($order->created_at)->format('d-m-Y H:i') . "\n");
        //     $printer->text("--------------------------------\n");
        //     
        //     // Detail Barang
        //     foreach ($order->items as $item) {
        //         $productName = $item->product ? $item->product->name : 'Produk';
        //         $printer->text($productName . "\n");
        //         $itemTotal = $item->quantity * $item->price_at_purchase;
        //         $printer->text($item->quantity . " x " . number_format($item->price_at_purchase, 0, ',', '.') . " = " . number_format($itemTotal, 0, ',', '.') . "\n");
        //     }
        //     
        //     // Totalan
        //     $printer->text("--------------------------------\n");
        //     $printer->setJustification(Printer::JUSTIFY_RIGHT);
        //     $printer->text("Subtotal: Rp " . number_format($order->subtotal, 0, ',', '.') . "\n");
        //     if ($order->discount_amount > 0) {
        //         $printer->text("Diskon: Rp " . number_format($order->discount_amount, 0, ',', '.') . "\n");
        //     }
        //     $printer->text("Total: Rp " . number_format($order->total_final, 0, ',', '.') . "\n");
        //     
        //     $printer->text("\n");
        //     $printer->setJustification(Printer::JUSTIFY_CENTER);
        //     $printer->text("Terima Kasih Atas Kunjungan Anda\n");
        //     
        //     // Potong Kertas & Tutup
        //     $printer->cut();
        //     $printer->close();
        //     
        // } catch (\Exception $e) {
        //     \Log::error("Gagal print struk thermal: " . $e->getMessage());
        // }
        // =========================================================================
        */

        // GENERATE PDF MENGGUNAKAN barryvdh/laravel-dompdf
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pos.struk-pdf', compact('order', 'outlet', 'staff', 'member', 'tax', 'service_charge'));
        // Ukuran kertas thermal 80mm. 80mm ~ 226.77 pt width. Height di set otomatis atau panjang.
        $pdf->setPaper([0, 0, 226.77, 800], 'portrait'); 
        
        return $pdf->stream('struk-' . $order->order_id . '.pdf');
    }
}