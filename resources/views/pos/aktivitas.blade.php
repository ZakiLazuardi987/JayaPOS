@extends('layouts.pos')

@section('content')
<div class="pos-container" style="flex-direction:column; background:#f3f4f6; height:100vh;">
    
    {{-- Header --}}
    <div style="background:#fff; padding:14px 28px; border-bottom:1px solid #e5e7eb; flex-shrink:0; display:flex; align-items:center; justify-content:space-between;">
        <div>
            <h1 style="margin:0; font-size:1.2rem; font-weight:700; color:#1A1A1A; font-family:'Inter',sans-serif;">Kelola Aktivitas</h1>
            <span style="font-size:0.8rem; color:#9ca3af;">Histori transaksi harian</span>
        </div>
        
        <form method="GET" action="{{ route('pos.aktivitas') }}" style="display:flex; gap:10px; align-items:center;">
            <input type="date" name="date" value="{{ $date }}" 
                   style="padding:8px 12px; border:1px solid #d1d5db; border-radius:6px; outline:none; font-family:'Inter',sans-serif;"
                   onchange="this.form.submit()">
        </form>
    </div>

    {{-- Main Split View --}}
    <div style="display:flex; flex:1; overflow:hidden;">
        
        {{-- Left: Transaction List --}}
        <div style="width:380px; background:#fff; border-right:1px solid #e5e7eb; display:flex; flex-direction:column;">
            <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="font-size:0.8rem; color:#6b7280;">Total Penjualan</div>
                    <div style="font-weight:700; color:#C43626; font-size:1.1rem;">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:0.8rem; color:#6b7280;">Transaksi</div>
                    <div style="font-weight:600; color:#1f2937; font-size:1rem;">{{ $totalTransactions }}</div>
                </div>
            </div>
            
            <div style="flex:1; overflow-y:auto; padding:10px;">
                @if($orders->count() > 0)
                    @foreach($orders as $order)
                        @php
                            $isCancelled = $order->status === 'cancelled' || $order->status === 'refunded';
                            $statusColor = $isCancelled ? '#ef4444' : '#10b981';
                            $statusText  = $isCancelled ? 'Dibatalkan' : 'Sukses';
                        @endphp
                        <div class="tx-card" 
                             onclick="selectTransaction({{ $order->toJson() }})"
                             style="padding:14px; border:1px solid #e5e7eb; border-radius:8px; margin-bottom:10px; cursor:pointer; background:#fff; transition:all 0.2s;"
                             onmouseover="this.style.borderColor='#C43626'"
                             onmouseout="this.style.borderColor='#e5e7eb'">
                            <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                <span style="font-weight:600; font-size:0.95rem; color:#111827;">#{{ $order->order_id }}</span>
                                <span style="font-size:0.8rem; font-weight:600; color:{{ $statusColor }};">{{ $statusText }}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:0.85rem; color:#6b7280;">
                                    {{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }} • 
                                    {{ $order->payment ? $order->payment->payment_method : 'N/A' }}
                                </span>
                                <span style="font-weight:700; color:#111827;">Rp {{ number_format($order->total_final, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="padding:40px 20px; text-align:center; color:#9ca3af;">
                        <svg style="width:48px; height:48px; margin:0 auto 10px; opacity:0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <p style="margin:0; font-size:0.9rem;">Tidak ada transaksi pada tanggal ini.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: Transaction Details --}}
        <div style="flex:1; background:#f9fafb; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px; position:relative;" id="txDetailEmpty">
            <svg style="width:64px; height:64px; color:#d1d5db; margin-bottom:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
            <p style="color:#9ca3af; font-size:1rem; margin:0;">Pilih transaksi di samping untuk melihat detail</p>
        </div>

        <div style="flex:1; background:#fff; display:none; flex-direction:column; overflow-y:auto;" id="txDetailContent">
            {{-- Toolbar --}}
            <div style="padding:24px; display:flex; gap:12px; border-bottom:1px solid #f3f4f6;">
                <button onclick="printCurrentTx()" style="flex:1; background:#fff; border:1px solid #d1d5db; padding:12px; border-radius:8px; font-weight:600; cursor:pointer; color:#374151; transition:background 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                    Cetak Struk
                </button>
                <button onclick="refundTx()" style="flex:1; background:#fff; border:1px solid #ef4444; padding:12px; border-radius:8px; font-weight:600; cursor:pointer; color:#ef4444; transition:background 0.2s;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">
                    Pilih Refund
                </button>
            </div>

            {{-- Detail Section --}}
            <div style="padding:24px;">
                <div style="font-size:0.85rem; font-weight:700; color:#374151; margin-bottom:16px;">DETAIL</div>
                
                <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:16px; border-bottom:1px solid #f3f4f6; margin-bottom:16px;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <svg style="width:24px; height:24px; color:#4b5563;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span style="font-weight:500; color:#374151;">Metode Pembayaran</span>
                    </div>
                    <div style="font-weight:500; color:#111827;" id="rPaymentMethod">Tunai</div>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:16px; border-bottom:1px solid #f3f4f6; margin-bottom:16px;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <svg style="width:24px; height:24px; color:#4b5563;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span style="font-weight:500; color:#374151;">Nomor Struk</span>
                    </div>
                    <div style="font-weight:500; color:#111827;" id="rOrder_id"></div>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:16px; border-bottom:1px solid #f3f4f6; margin-bottom:24px;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <svg style="width:24px; height:24px; color:#4b5563;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span style="font-weight:500; color:#374151;">Waktu Pembelian</span>
                    </div>
                    <div style="font-weight:500; color:#111827;" id="rDate"></div>
                </div>

                <div style="font-size:0.85rem; font-weight:700; color:#374151; margin-bottom:16px;">PRODUK</div>
                
                <div id="rItems">
                    <!-- Items rendered here -->
                </div>

                <div id="rDiscountRow" style="display:flex; justify-content:space-between; align-items:center; padding:16px 0; border-bottom:1px solid #f3f4f6; display:none;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <svg style="width:24px; height:24px; color:#4b5563;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        <span style="font-weight:600; color:#111827;">Diskon</span>
                    </div>
                    <div style="font-weight:600; color:#111827;" id="rDiscount"></div>
                </div>

                <div style="margin-top:24px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-weight:600; color:#111827;">Subtotal :</span>
                        <span style="font-weight:600; color:#111827;" id="rSubtotal"></span>
                    </div>
                    <div id="rExtraCharges">
                        <!-- Extra charges (Tax, Service) -->
                    </div>
                    
                    <div style="display:flex; justify-content:space-between; margin-top:16px; margin-bottom:8px;">
                        <span style="font-weight:700; color:#111827;">Total :</span>
                        <span style="font-weight:700; color:#111827;" id="rTotal"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-weight:600; color:#111827;">Pembayaran :</span>
                        <span style="font-weight:600; color:#111827;" id="rAmountPaid"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-weight:600; color:#111827;">Kembalian :</span>
                        <span style="font-weight:600; color:#111827;" id="rChange"></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    let activeTx = null;

    function formatRupiah(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    function selectTransaction(tx) {
        activeTx = tx;
        
        document.getElementById('txDetailEmpty').style.display = 'none';
        document.getElementById('txDetailContent').style.display = 'flex';

        // Render data to receipt
        document.getElementById('rOrder_id').innerText = '#' + tx.order_id;
        document.getElementById('rDate').innerText = new Date(tx.created_at).toLocaleString('id-ID', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }).replace(',', ' pukul');

        let itemsHtml = '';
        if (tx.items && tx.items.length > 0) {
            // Kita pisahkan item berdasarkan order_type jika ada, tapi karena ini simple kita kelompokkan manual
            let orderType = tx.order_type || 'Dine In';
            orderType = orderType.charAt(0).toUpperCase() + orderType.slice(1);
            
            itemsHtml += `
                <div style="background:#f9fafb; padding:8px 16px; margin:16px -24px 12px; font-weight:600; font-size:0.85rem; color:#6b7280; text-align:center;">
                    ${orderType}
                </div>
            `;

            tx.items.forEach(item => {
                let name = item.product ? item.product.name : 'Custom Amount';
                let price = item.price_at_purchase;
                
                // Base item
                itemsHtml += `
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                        <div style="display:flex; gap:12px; flex:1;">
                            <div style="width:40px; height:40px; background:#f3f4f6; border-radius:6px; overflow:hidden;">
                                ${item.product && item.product.img_url ? `<img src="/${item.product.img_url}" style="width:100%; height:100%; object-fit:cover;">` : ''}
                            </div>
                            <div>
                                <div style="font-weight:600; color:#111827;">${name}</div>
                            </div>
                        </div>
                        <div style="font-weight:500; color:#374151; width:40px; text-align:right;">x${item.quantity}</div>
                        <div style="font-weight:600; color:#111827; width:90px; text-align:right;">Rp ${formatRupiah(price * item.quantity)}</div>
                    </div>
                `;

                // Modifiers
                if (item.modifiers && item.modifiers.length > 0) {
                    item.modifiers.forEach(mod => {
                        let modPrice = mod.pivot ? mod.pivot.price_added : 0;
                        itemsHtml += `
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; margin-left:52px;">
                                <div style="font-size:0.85rem; color:#6b7280;">[Add On] ${mod.name}</div>
                                <div style="font-size:0.85rem; color:#6b7280;">Rp ${formatRupiah(modPrice * item.quantity)}</div>
                            </div>
                        `;
                    });
                }
                itemsHtml += `<div style="border-bottom:1px solid #f3f4f6; margin:12px 0;"></div>`;
            });
        }
        document.getElementById('rItems').innerHTML = itemsHtml;

        document.getElementById('rSubtotal').innerText = 'Rp ' + formatRupiah(tx.subtotal);
        
        if (tx.discount_amount > 0) {
            document.getElementById('rDiscountRow').style.display = 'flex';
            document.getElementById('rDiscount').innerText = '(Rp ' + formatRupiah(tx.discount_amount) + ')';
        } else {
            document.getElementById('rDiscountRow').style.display = 'none';
        }

        // Extra charges (Tax, Service Charge)
        let extraHtml = '';
        if (tx.service_charge) {
            let scAmount = tx.subtotal * (tx.service_charge.value / 100);
            extraHtml += `
                <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:0.85rem; color:#6b7280;">
                    <span>${tx.service_charge.name} (${parseFloat(tx.service_charge.value)}%)</span>
                    <span>Rp ${formatRupiah(scAmount)}</span>
                </div>
            `;
        }
        if (tx.tax) {
            // simple calculation, subtotal - discount + sc, or whatever the logic is
            // For now just use simple percentage of subtotal
            let taxAmount = tx.subtotal * (tx.tax.value / 100); 
            extraHtml += `
                <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:0.85rem; color:#6b7280;">
                    <span>${tx.tax.name} (${parseFloat(tx.tax.value)}%)</span>
                    <span>Rp ${formatRupiah(taxAmount)}</span>
                </div>
            `;
        }
        document.getElementById('rExtraCharges').innerHTML = extraHtml;

        document.getElementById('rTotal').innerText = 'Rp ' + formatRupiah(tx.total_final);

        if (tx.payment) {
            document.getElementById('rPaymentMethod').innerText = tx.payment.payment_method;
            document.getElementById('rAmountPaid').innerText = 'Rp ' + formatRupiah(tx.payment.amount_paid);
            document.getElementById('rChange').innerText = 'Rp ' + formatRupiah(tx.payment.change_amount);
        } else {
            document.getElementById('rPaymentMethod').innerText = '-';
            document.getElementById('rAmountPaid').innerText = 'Rp 0';
            document.getElementById('rChange').innerText = 'Rp 0';
        }
        
        // Highlight active card
        document.querySelectorAll('.tx-card').forEach(el => el.style.borderColor = '#e5e7eb');
        event.currentTarget.style.borderColor = '#C43626';
    }

    function printCurrentTx() {
        if (!activeTx) return;
        
        if (typeof window.printBluetoothReceipt !== 'function') {
            Swal.fire({ icon: 'warning', title: 'Printer Belum Siap', text: 'Script printer bluetooth belum dimuat atau didukung.'});
            return;
        }

        const itemsFormatted = activeTx.items.map(item => ({
            name: item.product ? item.product.name : 'Custom Amount',
            unit_price: item.price_at_purchase,
            qty: item.quantity,
            modifiers: (item.modifiers || []).map(m => ({
                name: m.name,
                price: m.pivot ? m.pivot.price_added : 0
            }))
        }));

        const taxAmount = activeTx.tax ? (activeTx.subtotal * (activeTx.tax.value / 100)) : 0;
        const scAmount = activeTx.service_charge ? (activeTx.subtotal * (activeTx.service_charge.value / 100)) : 0;

        const printData = {
            orderId: activeTx.order_id,
            tanggal: new Date(activeTx.created_at).toLocaleString('id-ID', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }).replace(',', ' pukul'),
            kasir: activeTx.waiter ? activeTx.waiter.name : '-',
            pickupCode: activeTx.pickup_code,
            source: activeTx.source,
            items: itemsFormatted,
            subtotal: activeTx.subtotal,
            discountAmount: activeTx.discount_amount,
            scAmount: scAmount,
            scName: activeTx.service_charge ? activeTx.service_charge.name : '',
            taxAmount: taxAmount,
            taxName: activeTx.tax ? activeTx.tax.name : '',
            total: activeTx.total_final,
            status: activeTx.status,
            orderType: activeTx.order_type || 'DINE-IN',
            metode: activeTx.payment ? activeTx.payment.payment_method : 'Tunai',
            nominal: activeTx.payment ? activeTx.payment.amount_paid : activeTx.total_final,
            kembali: activeTx.payment ? activeTx.payment.change_amount : 0,
            outletName: "{{ $outlet->name ?? 'Toko Kopi Jaya' }}",
            outletAddress: "{{ $outlet->address ?? 'Jalan Brigadir Jenderal Slamet Riyadi 53 65119 Klojen East Java' }}",
            outletPhone: "{{ $outlet->phone ?? '0811-3333-2323' }}"
        };

        window.printBluetoothReceipt(printData).catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Cetak',
                text: err.message
            });
        });
    }

    function refundTx() {
        if (!activeTx) return;

        if (activeTx.status === 'cancelled' || activeTx.status === 'refunded') {
            Swal.fire({ icon: 'info', title: 'Perhatian', text: 'Transaksi ini sudah dibatalkan/refund.', confirmButtonColor: '#C43626' });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Refund',
            text: 'Apakah Anda yakin ingin membatalkan/refund transaksi #' + activeTx.order_id + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'Ya, Refund',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                
                fetch(`/pos/orders/${activeTx.order_id}/refund`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, confirmButtonColor: '#C43626' })
                        .then(() => { window.location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message, confirmButtonColor: '#C43626' });
                    }
                })
                .catch(err => {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan sistem.', confirmButtonColor: '#C43626' });
                });
            }
        });
    }
</script>
@endpush
