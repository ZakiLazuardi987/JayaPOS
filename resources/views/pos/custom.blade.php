@extends('layouts.pos')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pos-custom.css') }}">
@endpush

@section('content')
<div class="pos-container">
    
    <div class="left-panel" id="leftArea">
        <div class="tab-content active" id="tab-custom" style="display: flex;">

            {{-- Display angka --}}
            <div class="calc-display" id="calcDisplay">Rp 0</div>

            {{-- Grid kalkulator --}}
            <div class="calc-grid">
                {{-- Baris 1 --}}
                <button class="calc-btn" data-val="1">1</button>
                <button class="calc-btn" data-val="2">2</button>
                <button class="calc-btn" data-val="3">3</button>
                <button class="calc-btn" data-val="0">0</button>

                {{-- Baris 2 --}}
                <button class="calc-btn" data-val="4">4</button>
                <button class="calc-btn" data-val="5">5</button>
                <button class="calc-btn" data-val="6">6</button>
                <button class="calc-btn" data-val="00">00</button>

                {{-- Baris 3 --}}
                <button class="calc-btn" data-val="7">7</button>
                <button class="calc-btn" data-val="8">8</button>
                <button class="calc-btn" data-val="9">9</button>

                {{-- Tombol + span 2 baris --}}
                <button class="calc-btn span-row" id="btnAddCustom">+</button>

                {{-- Baris 4 --}}
                <button class="calc-btn span-2" id="btnClear">C</button>
                <button class="calc-btn btn-del" id="btnDel">Del</button>
            </div>

        </div>
    </div>

    @include('partials.pos-billing')

    {{-- Modal Detail Produk (shared) — agar edit keranjang bisa dari halaman custom --}}
    @include('partials.product-detail-modal', ['discounts' => $discounts])

</div>

<div id="loadingOverlay" class="loading-overlay">
    <div class="spinner"></div>
    <p style="font-weight: 600; color: var(--primary);">Memproses...</p>
</div>
<div id="toast-container"></div>
@endsection

@push('scripts')
<script>
(function () {
    let rawValue = '';

    const display  = document.getElementById('calcDisplay');
    const btnClear = document.getElementById('btnClear');
    const btnDel   = document.getElementById('btnDel');
    const btnAdd   = document.getElementById('btnAddCustom');

    function formatRupiah(num) {
        if (!num || num === 0) return 'Rp 0';
        return 'Rp ' + parseInt(num, 10).toLocaleString('id-ID');
    }

    function updateDisplay() {
        display.textContent = formatRupiah(parseInt(rawValue || '0', 10));
    }

    // Klik digit
    document.querySelectorAll('.calc-btn[data-val]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var val = this.getAttribute('data-val');
            if ((val === '00' || val === '0') && (rawValue === '' || rawValue === '0')) return;
            rawValue += val;
            if (rawValue.length > 13) rawValue = rawValue.slice(0, 13);
            updateDisplay();
        });
    });

    // Clear — hanya reset input kalkulator, tidak hapus cart
    btnClear.addEventListener('click', function () {
        rawValue = '';
        updateDisplay();
    });

    // Del
    btnDel.addEventListener('click', function () {
        rawValue = rawValue.slice(0, -1);
        updateDisplay();
    });

    // Tambah ke cart (pakai sistem cart global dari pos.js)
    btnAdd.addEventListener('click', function () {
        var amount = parseInt(rawValue, 10);
        if (!rawValue || amount === 0) return;

        if (typeof window.addToCart !== 'function') {
            alert('Sistem cart belum siap, coba refresh halaman.');
            return;
        }

        window.addToCart({
            id:          Date.now(),
            product_id:  'custom_' + Date.now(),
            name:        'Custom Amount',
            base_price:  amount,
            modifiers:   [],
            discounts:   [],
            order_type:  'dine-in',
            qty:         1,
            unit_price:  amount,
            total_price: amount,
        });

        // Reset kalkulator setelah tambah
        rawValue = '';
        updateDisplay();
    });

})();
</script>
@endpush