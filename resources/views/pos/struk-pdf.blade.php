<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Struk #{{ $order->order_id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            margin: 0;
            padding: 10px;
            font-size: 11px;
            color: #000;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        /* Menggunakan div khusus untuk garis agar jaraknya akurat di DOMPDF */
        .separator {
            border-top: 1px dashed #000;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 2px 0;
            vertical-align: top;
        }
    </style>
</head>
<body>
    <div class="text-center">
        @php
            $imagePath = public_path('assets/jaya_text.png');
            $base64Logo = '';
            if (file_exists($imagePath)) {
                $type = pathinfo($imagePath, PATHINFO_EXTENSION);
                $data = file_get_contents($imagePath);
                $base64Logo = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        @endphp
        @if($base64Logo)
            <img src="{{ $base64Logo }}" alt="Logo" style="width: 120px; margin-bottom: 6px;">
        @endif
        <h2 style="margin: 0; font-size: 14px; margin-bottom: 2px;">{{ $outlet->name ?? 'TOKO KOPI JAYA' }}</h2>
        <div>{{ $outlet->address ?? 'Alamat Outlet' }}</div>
        @if($outlet && $outlet->phone)
            <div>Telp: {{ $outlet->phone }}</div>
        @endif
    </div>

    <div class="separator"></div>

    <table>
        <tr>
            <td style="width: 30%;">Nota</td>
            <td style="width: 5%;">:</td>
            <td style="width: 65%;">#{{ $order->order_id }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>:</td>
            <td>{{ $staff->name ?? 'Kasir' }}</td>
        </tr>

        @if($order->pickup_code)
        <tr>
            <td>
                Kode Pesanan
            </td>
            <td>:</td>
            <td style="font-weight: bold;">{{ $order->pickup_code }}</td>
        </tr>
        <tr>
            <td>
                Sumber
            </td>
            <td>:</td>
            <td style="font-weight: bold; text-transform: uppercase;">{{ $order->source ?? 'POS - In-Store' }}</td>
        </tr>
        @endif
        @if($member)
        <tr>
            <td>Pelanggan</td>
            <td>:</td>
            <td>{{ $member->name }}</td>
        </tr>
        @endif
    </table>

    <div class="separator"></div>

    <table>
        @foreach($order->items as $item)
        <tr>
            <td colspan="3" class="font-bold">{{ $item->product->name ?? 'Produk' }}</td>
        </tr>
        <tr>
            <td style="width: 15%;">{{ $item->quantity }}x</td>
            <td style="width: 45%;">Rp {{ number_format($item->price_at_purchase, 0, ',', '.') }}</td>
            <td class="text-right" style="width: 40%;">Rp {{ number_format($item->quantity * $item->price_at_purchase, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="separator"></div>

    <table>
        <tr>
            <td style="width: 60%;">Subtotal</td>
            <td class="text-right" style="width: 40%; white-space: nowrap;">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($order->discount_amount > 0)
        <tr>
            <td>Diskon</td>
            <td class="text-right" style="white-space: nowrap;">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if(isset($service_charge))
        <tr>
            <td>{{ $service_charge->name }} ({{ $service_charge->type == 'percentage' ? rtrim(rtrim($service_charge->value, '0'), '.') . '%' : 'Nominal' }})</td>
            <td class="text-right" style="white-space: nowrap;">Rp {{ number_format($service_charge->type == 'percentage' ? ($order->subtotal - $order->discount_amount) * ($service_charge->value / 100) : $service_charge->value, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if(isset($tax))
        @php
            $sc_amount = isset($service_charge) ? ($service_charge->type == 'percentage' ? ($order->subtotal - $order->discount_amount) * ($service_charge->value / 100) : $service_charge->value) : 0;
            $taxable = ($order->subtotal - $order->discount_amount) + $sc_amount;
        @endphp
        <tr>
            <td>{{ $tax->name }} ({{ $tax->type == 'percentage' ? rtrim(rtrim($tax->value, '0'), '.') . '%' : 'Nominal' }})</td>
            <td class="text-right" style="white-space: nowrap;">Rp {{ number_format($tax->type == 'percentage' ? $taxable * ($tax->value / 100) : $tax->value, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td class="font-bold" style="font-size: 13px; padding-top: 6px;">Total</td>
            <td class="font-bold text-right" style="font-size: 13px; padding-top: 6px; white-space: nowrap;">Rp {{ number_format($order->total_final, 0, ',', '.') }}</td>
        </tr>
        @if(isset($payment))
        <tr>
            <td style="padding-top: 6px; font-size: 12px;">Metode Pembayaran</td>
            <td class="text-right" style="padding-top: 6px; font-size: 12px; white-space: nowrap;">{{ strtoupper($payment->payment_method) }}</td>
        </tr>
        @if(strtolower($payment->payment_method) == 'cash' || strtolower($payment->payment_method) == 'tunai')
        @php
            // Ambil nominal bayar & kembalian (fallback dari DB JSON jika kolom baru belum terisi)
            $amountPaid = $payment->amount_paid;
            $changeAmount = $payment->change_amount;
            if (is_null($amountPaid)) {
                $resp = json_decode($payment->payment_response, true);
                if (is_array($resp) && isset($resp['amount_tendered'])) {
                    $amountPaid = $resp['amount_tendered'];
                    $changeAmount = $resp['change'] ?? 0;
                } else {
                    $amountPaid = $payment->amount ?? $order->total_final;
                    $changeAmount = 0;
                }
            }
        @endphp
        <tr>
            <td style="font-size: 12px;">Tunai</td>
            <td class="text-right" style="font-size: 12px; white-space: nowrap;">Rp {{ number_format($amountPaid, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="font-size: 12px;">Kembalian</td>
            <td class="text-right" style="font-size: 12px; white-space: nowrap;">Rp {{ number_format($changeAmount, 0, ',', '.') }}</td>
        </tr>
        @endif
        @endif
    </table>

    <div class="separator"></div>

    <div class="text-center">
        <div>Pembayaran: {{ strtoupper($order->order_type ?? 'Tunai') }}</div>
        @if($order->status == 'paid')
            <div class="font-bold" style="margin-top: 4px; font-size: 13px;">LUNAS</div>
        @endif
        <div style="margin-top: 12px;">Terima Kasih<br>Atas Kunjungan Anda</div>
    </div>
</body>
</html>
