@extends('layouts.pos')

@section('content')
<div class="pos-container" style="flex-direction:column; background:#f3f4f6;">

    {{-- Header --}}
    <div style="background:#fff; padding:14px 28px; border-bottom:1px solid #e5e7eb; flex-shrink:0; display:flex; align-items:center; justify-content:space-between;">
        <h1 style="margin:0; font-size:1.2rem; font-weight:700; color:#1A1A1A; font-family:'Inter',sans-serif;">Denah Meja</h1>
        <span style="font-size:0.8rem; color:#9ca3af;">Klik meja untuk melihat detail</span>
    </div>

    {{-- Grid Area (scrollable) --}}
    <div id="denahMejaContent" style="flex:1; overflow-y:auto; padding:24px 28px 100px;">
        @if(isset($areas) && count($areas) > 0)
            @foreach($areas as $areaIdx => $area)
                <div class="area-grid-view" id="areaTabContent_{{ $area->area_id }}"
                     style="display: {{ $areaIdx === 0 ? 'grid' : 'none' }};
                            grid-template-columns: repeat(auto-fill, minmax(145px, 1fr));
                            gap: 18px; width: 100%; align-content: start;">
                    @foreach($area->tables as $table)
                        @if($table->status === 'occupied')
                            @php
                                $order       = $tableOrders[$table->table_id] ?? null;
                                $timeElapsed = '';
                                $totalAmount = 0;
                                $pax         = $order ? $order->pax : 0;
                                $hasOrder    = !is_null($order);
                                if ($order) {
                                    $createdAt     = \Carbon\Carbon::parse($order->created_at);
                                    $diffInMinutes = $createdAt->diffInMinutes(\Carbon\Carbon::now());
                                    $hours         = floor($diffInMinutes / 60);
                                    $minutes       = $diffInMinutes % 60;
                                    $timeElapsed   = $hours > 0 ? "{$hours} j {$minutes} m" : "{$minutes} m";
                                    $totalAmount   = $order->total_final;
                                }
                            @endphp

                            {{-- Meja Terisi --}}
                            <div onclick="showTableDetail({{ $table->table_id }})"
                                 style="background:#C43626; border-radius:10px; height:125px;
                                        display:flex; flex-direction:column; justify-content:space-between;
                                        align-items:center; cursor:pointer; color:#fff; padding:14px 10px;
                                        position:relative;
                                        box-shadow:0 3px 10px rgba(196,54,38,0.25);
                                        transition:transform 0.15s, box-shadow 0.15s;"
                                 onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 16px rgba(196,54,38,0.35)';"
                                 onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 3px 10px rgba(196,54,38,0.25)';">

                                <div style="font-weight:700; font-size:0.95rem; text-align:center; line-height:1.2;">{{ $table->name }}</div>

                                @if($hasOrder)
                                    <div style="font-size:0.8rem; font-weight:600; margin:0;">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
                                    <div style="font-size:0.7rem; opacity:0.85;">{{ $pax }} / {{ $table->capacity }} pax</div>

                                    {{-- Timer badge --}}
                                    @if($timeElapsed)
                                    <div style="position:absolute; top:-9px; right:-6px;
                                                background:#f59e0b; color:#fff;
                                                font-size:0.65rem; font-weight:700;
                                                padding:3px 7px; border-radius:20px;
                                                box-shadow:0 1px 4px rgba(0,0,0,0.2);">
                                        {{ $timeElapsed }}
                                    </div>
                                    @endif
                                @else
                                    <div style="font-size:0.75rem; opacity:0.75; text-align:center;">Data tidak tersedia</div>
                                    <div style="font-size:0.7rem; opacity:0.7;">- / {{ $table->capacity }} pax</div>
                                @endif

                                {{-- Hidden data for JS --}}
                                <div id="table_data_{{ $table->table_id }}" style="display:none;"
                                     data-name="{{ $table->name }}"
                                     data-area="{{ $area->name }}"
                                     data-time="{{ $timeElapsed }}"
                                     data-total="{{ number_format($totalAmount, 0, ',', '.') }}"
                                     data-pax="{{ $pax }}"
                                     data-capacity="{{ $table->capacity }}"
                                     data-has-order="{{ $hasOrder ? '1' : '0' }}"
                                     data-order="{{ $order ? $order->order_id : '' }}">
                                </div>
                            </div>

                        @else
                            {{-- Meja Kosong --}}
                            <div style="background:#fff; border-radius:10px; height:125px;
                                        display:flex; flex-direction:column; justify-content:center;
                                        align-items:center; color:#374151;
                                        border:1.5px solid #e5e7eb;
                                        box-shadow:0 1px 4px rgba(0,0,0,0.04);
                                        cursor:pointer;"
                                 onclick="showTableEmpty('{{ $table->name }}', {{ $table->capacity }})">
                                <span style="font-weight:700; font-size:0.95rem; text-align:center; color:#374151;">{{ $table->name }}</span>
                                <span style="font-size:0.75rem; color:#9ca3af; margin-top:6px;">0 / {{ $table->capacity }} pax</span>
                                <span style="font-size:0.7rem; color:#C43626; margin-top:4px; font-weight:500;">Kosong</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        @else
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center;
                        height:50vh; color:#9ca3af;">
                <p style="font-size:1rem; font-weight:500; margin:0;">Belum ada data area atau meja.</p>
            </div>
        @endif
    </div>
</div>

{{-- ============ MODAL: Meja Terisi ============ --}}
<div id="modalTableDetail"
     style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh;
            background:rgba(0,0,0,0.4); z-index:9999;
            justify-content:center; align-items:center;
            font-family:'Inter',sans-serif;">
    <div style="background:#fff; width:100%; max-width:340px; border-radius:12px;
                overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.15);">
        {{-- Header Modal --}}
        <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6;
                    display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h3 id="tdTitle" style="margin:0; font-size:1rem; font-weight:700; color:#1A1A1A;"></h3>
                <p id="tdSubtitle" style="margin:5px 0 0; font-size:0.8rem; color:#6b7280;"></p>
            </div>
            <button onclick="closeTableDetail()"
                    style="background:none; border:none; font-size:1.2rem; color:#9ca3af;
                           cursor:pointer; line-height:1; padding:0; margin-left:12px;">&#x2715;</button>
        </div>

        {{-- Total Box --}}
        <div style="padding:16px 20px 0;">
            <div style="background:#fef2f2; border-radius:8px; padding:14px; text-align:center;">
                <p style="margin:0; font-size:0.72rem; color:#6b7280; font-weight:600;
                           text-transform:uppercase; letter-spacing:0.05em;">Total Tagihan</p>
                <h2 id="tdTotal" style="margin:6px 0 0; font-size:1.7rem; font-weight:800; color:#C43626;"></h2>
            </div>
        </div>

        {{-- Action Buttons (saat ada pending order) --}}
        <div id="tdActions" style="padding:16px 20px 20px; display:flex; flex-direction:column; gap:10px;">
            <button id="btnLihatPesanan"
                    style="background:#C43626; color:#fff; border:none; padding:12px;
                           border-radius:8px; font-weight:600; font-size:0.88rem;
                           cursor:pointer; width:100%;
                           transition:background 0.2s;"
                    onmouseover="this.style.background='#A93226';"
                    onmouseout="this.style.background='#C43626';">
                Lihat Pesanan (Buka Bill)
            </button>
            <button id="btnCetakBillMeja"
                    style="background:#fff; color:#374151; border:1.5px solid #d1d5db;
                           padding:12px; border-radius:8px; font-weight:600; font-size:0.88rem;
                           cursor:pointer; width:100%;
                           transition:border-color 0.2s, color 0.2s;"
                    onmouseover="this.style.borderColor='#C43626';this.style.color='#C43626';"
                    onmouseout="this.style.borderColor='#d1d5db';this.style.color='#374151';">
                Cetak Bill Sementara
            </button>
        </div>

        {{-- Warning: meja occupied tapi tidak ada pending order --}}
        <div id="tdNoOrder" style="display:none; padding:16px 20px 20px;">
            <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:14px;">
                <p style="margin:0; font-size:0.83rem; color:#92400e; line-height:1.5;">
                    Meja ini tercatat terisi namun tidak ada pesanan aktif yang ditemukan.
                    Mungkin pesanan sudah selesai atau belum tersinkron.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- ============ MODAL: Meja Kosong ============ --}}
<div id="modalTableEmpty"
     style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh;
            background:rgba(0,0,0,0.4); z-index:9999;
            justify-content:center; align-items:center;
            font-family:'Inter',sans-serif;">
    <div style="background:#fff; width:100%; max-width:320px; border-radius:12px;
                overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.15);">
        {{-- Header Modal --}}
        <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6;
                    display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h3 id="teTitle" style="margin:0; font-size:1rem; font-weight:700; color:#1A1A1A;"></h3>
                <p id="teSubtitle" style="margin:5px 0 0; font-size:0.8rem; color:#6b7280;"></p>
            </div>
            <button onclick="closeTableEmpty()"
                    style="background:none; border:none; font-size:1.2rem; color:#9ca3af;
                           cursor:pointer; line-height:1; padding:0; margin-left:12px;">&#x2715;</button>
        </div>

        <div style="padding:16px 20px 20px;">
            <div style="background:#f3f4f6; border-radius:8px; padding:14px; text-align:center; margin-bottom:16px;">
                <p style="margin:0; font-size:0.85rem; color:#374151; font-weight:500;">
                    Meja ini masih kosong.
                </p>
            </div>
            <button onclick="closeTableEmpty()"
                    style="background:#C43626; color:#fff; border:none; padding:12px;
                           border-radius:8px; font-weight:600; font-size:0.88rem;
                           cursor:pointer; width:100%;
                           transition:background 0.2s;"
                    onmouseover="this.style.background='#A93226';"
                    onmouseout="this.style.background='#C43626';">
                Kembali
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ======================================================
// TAB AREA SWITCHING
// ======================================================
document.querySelectorAll('.area-tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.area-tab-btn').forEach(b => {
            b.classList.remove('active');
        });
        this.classList.add('active');

        document.querySelectorAll('.area-grid-view').forEach(a => a.style.display = 'none');
        const target = document.getElementById('areaTabContent_' + this.getAttribute('data-id'));
        if (target) target.style.display = 'grid';
    });
});

// ======================================================
// MODAL: MEJA TERISI
// ======================================================
let activeOrderId = null;

window.showTableDetail = function (tableId) {
    const dataDiv = document.getElementById('table_data_' + tableId);
    if (!dataDiv) return;

    const name     = dataDiv.getAttribute('data-name');
    const area     = dataDiv.getAttribute('data-area');
    const time     = dataDiv.getAttribute('data-time');
    const total    = dataDiv.getAttribute('data-total');
    const pax      = dataDiv.getAttribute('data-pax');
    const capacity = dataDiv.getAttribute('data-capacity');
    const hasOrder = dataDiv.getAttribute('data-has-order') === '1';
    activeOrderId  = dataDiv.getAttribute('data-order') || null;

    document.getElementById('tdTitle').innerText    = name + '  —  ' + area;
    document.getElementById('tdSubtitle').innerText =
        (time ? time + '  •  ' : '') + pax + ' / ' + capacity + ' pax';
    document.getElementById('tdTotal').innerText    = 'Rp ' + total;

    document.getElementById('tdActions').style.display  = hasOrder ? 'flex'  : 'none';
    document.getElementById('tdNoOrder').style.display  = hasOrder ? 'none'  : 'block';

    document.getElementById('modalTableDetail').style.display = 'flex';
};

window.closeTableDetail = function () {
    document.getElementById('modalTableDetail').style.display = 'none';
    activeOrderId = null;
};

// Tutup modal klik backdrop
document.getElementById('modalTableDetail').addEventListener('click', function (e) {
    if (e.target === this) closeTableDetail();
});

// ======================================================
// MODAL: MEJA KOSONG
// ======================================================
window.showTableEmpty = function(name, capacity) {
    document.getElementById('teTitle').innerText = name;
    document.getElementById('teSubtitle').innerText = 'Kapasitas: ' + capacity + ' pax';
    document.getElementById('modalTableEmpty').style.display = 'flex';
};

window.closeTableEmpty = function() {
    document.getElementById('modalTableEmpty').style.display = 'none';
};

document.getElementById('modalTableEmpty').addEventListener('click', function(e) {
    if (e.target === this) closeTableEmpty();
});

// ======================================================
// LIHAT PESANAN — redirect ke POS dengan bill di-load
// ======================================================
document.getElementById('btnLihatPesanan').addEventListener('click', function () {
    if (!activeOrderId) return;
    closeTableDetail();

    fetch('/pos/orders/' + activeOrderId + '/detail', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(res => {
        if (res.success && res.order) {
            // Simpan ke localStorage; POS page akan auto-load saat terbuka
            localStorage.setItem('pos_pending_order_id', res.order.order_id);
            localStorage.setItem('pos_pending_cart',     JSON.stringify(res.cart));
            localStorage.setItem('pos_pending_order',    JSON.stringify(res.order));
            window.location.href = '/pos/library';
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Pesanan tidak ditemukan.', confirmButtonColor: '#C43626' });
        }
    })
    .catch(() => {
        Swal.fire({ icon: 'error', title: 'Koneksi error', text: 'Terjadi kesalahan jaringan.', confirmButtonColor: '#C43626' });
    });
});

// ======================================================
// CETAK BILL SEMENTARA
// ======================================================
document.getElementById('btnCetakBillMeja').addEventListener('click', function () {
    if (!activeOrderId) return;
    const pdfUrl = '/pos/order/' + activeOrderId + '/print';

    fetch('/pos/order/' + activeOrderId + '/struk-data', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(async data => {
        let printed = false;
        if (typeof window.printBluetoothReceipt === 'function') {
            try { printed = await window.printBluetoothReceipt(data); } catch (e) { printed = false; }
        }
        if (!printed) window.open(pdfUrl, '_blank');
    })
    .catch(() => window.open(pdfUrl, '_blank'));
});
</script>
@endpush
