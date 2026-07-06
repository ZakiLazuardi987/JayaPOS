@extends('layouts.pos')

@section('content')
<div class="pos-container" style="flex-direction:column; background:#f3f4f6; height:100vh;">
    
    {{-- Header --}}
    <div style="background:#fff; padding:14px 28px; border-bottom:1px solid #e5e7eb; flex-shrink:0; display:flex; align-items:center; justify-content:space-between;">
        <div>
            <h1 style="margin:0; font-size:1.2rem; font-weight:700; color:#1A1A1A; font-family:'Inter',sans-serif;">Pesanan Online (Simulasi)</h1>
            <span style="font-size:0.8rem; color:#9ca3af;">Integrasi GrabFood, GoFood, & ShopeeFood</span>
        </div>
        
        <div style="display:flex; gap:10px; align-items:center;">
            <button onclick="simulasikanPesanan('GrabFood')" 
                    style="background:#00B14F; color:#fff; padding:8px 16px; border:none; border-radius:6px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                Terima Simulasi GrabFood
            </button>
            <button onclick="simulasikanPesanan('ShopeeFood')" 
                    style="background:#ee4d2d; color:#fff; padding:8px 16px; border:none; border-radius:6px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                Terima Simulasi ShopeeFood
            </button>
        </div>
    </div>

    {{-- Main Content --}}
    <div style="display:flex; flex:1; overflow:hidden;">
        
        {{-- List --}}
        <div style="width:400px; background:#fff; border-right:1px solid #e5e7eb; display:flex; flex-direction:column;">
            <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                <div style="font-weight:700; color:#1f2937; font-size:1rem;">Daftar Pesanan Baru</div>
                <div style="font-size:0.8rem; color:#6b7280; font-weight:600;"><span id="orderCount">0</span> Pesanan</div>
            </div>
            
            <div style="flex:1; overflow-y:auto; padding:10px;" id="orderList">
                <div id="emptyState" style="padding:40px 20px; text-align:center; color:#9ca3af;">
                    <svg style="width:48px; height:48px; margin:0 auto 10px; opacity:0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <p style="margin:0; font-size:0.9rem;">Belum ada pesanan online baru yang masuk.</p>
                </div>
            </div>
        </div>

        {{-- Right: Detail View --}}
        <div id="detailEmpty" style="flex:1; background:#f9fafb; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:20px;">
            <svg style="width:64px; height:64px; color:#d1d5db; margin-bottom:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            <p style="color:#9ca3af; font-size:1rem; margin:0;">Pilih pesanan online untuk melihat detail</p>
            <p style="color:#9ca3af; font-size:0.8rem; margin-top:10px;">(Halaman ini adalah mockup UI untuk presentasi skripsi)</p>
        </div>
        
        <div id="detailContent" style="flex:1; background:#fff; display:none; flex-direction:column; overflow-y:auto;">
            <div style="padding:24px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="font-size:0.85rem; font-weight:700; color:#374151; margin-bottom:4px;">ID PESANAN</div>
                    <div style="font-size:1.2rem; font-weight:700; color:#111827;" id="detId"></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:0.85rem; font-weight:700; color:#374151; margin-bottom:4px;">SUMBER</div>
                    <div style="font-size:1rem; font-weight:700;" id="detSumber"></div>
                </div>
            </div>
            
            <div style="padding:24px;">
                <div style="font-size:0.85rem; font-weight:700; color:#374151; margin-bottom:16px;">ITEM PESANAN (SIMULASI)</div>
                
                <div style="display:flex; justify-content:space-between; margin-bottom:12px; border-bottom:1px dashed #e5e7eb; padding-bottom:12px;">
                    <div>
                        <div style="font-weight:600; color:#111827;">Kopi Susu Gula Aren (Iced)</div>
                        <div style="font-size:0.8rem; color:#6b7280;">Normal Sugar, Less Ice</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-weight:600; color:#111827;">x2</div>
                        <div style="font-size:0.85rem; color:#6b7280;">Rp 36.000</div>
                    </div>
                </div>
                
                <div style="display:flex; justify-content:space-between; margin-bottom:12px; border-bottom:1px dashed #e5e7eb; padding-bottom:12px;">
                    <div>
                        <div style="font-weight:600; color:#111827;">Caffe Latte (Hot)</div>
                        <div style="font-size:0.8rem; color:#6b7280;">Oat Milk</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-weight:600; color:#111827;">x1</div>
                        <div style="font-size:0.85rem; color:#6b7280;">Rp 25.000</div>
                    </div>
                </div>

                <div style="margin-top:24px; border-top:1px solid #f3f4f6; padding-top:16px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-weight:600; color:#6b7280;">Subtotal :</span>
                        <span style="font-weight:600; color:#111827;" id="detSubtotal"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:16px;">
                        <span style="font-weight:600; color:#6b7280;">Biaya Aplikasi :</span>
                        <span style="font-weight:600; color:#111827;">Rp 3.000</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; background:#f9fafb; padding:12px; border-radius:8px;">
                        <span style="font-weight:700; color:#111827;">Total Pembayaran :</span>
                        <span style="font-weight:700; color:#C43626;" id="detTotal"></span>
                    </div>
                </div>
                
                <div style="margin-top:32px; display:flex; gap:12px;">
                    <button style="flex:1; background:#fff; color:#374151; padding:12px; border:1px solid #d1d5db; border-radius:8px; font-weight:600; cursor:pointer;" onclick="Swal.fire('Tolak Pesanan', 'Fitur simulasi penolakan', 'info')">Tolak Pesanan</button>
                    <button style="flex:1; background:#C43626; color:#fff; padding:12px; border:none; border-radius:8px; font-weight:600; cursor:pointer;" onclick="terimaPesananAsli()">Terima & Cetak Struk</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Audio for notification --}}
<audio id="notifSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

@endsection

@push('scripts')
<script>
    let orderCount = 0;

    function simulasikanPesanan(sumber) {
        // Mainkan suara ting!
        const audio = document.getElementById('notifSound');
        audio.play().catch(e => console.log('Audio autoplay blocked'));

        // Buat alert
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Pesanan Baru dari ' + sumber + '!',
            text: 'Menerima pesanan secara otomatis...',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });

        // Hapus empty state
        const emptyState = document.getElementById('emptyState');
        if (emptyState) {
            emptyState.style.display = 'none';
        }

        orderCount++;
        document.getElementById('orderCount').innerText = orderCount;

        // Data dummy
        const idPesanan = Math.floor(100000 + Math.random() * 900000);
        const harga = formatRupiah(25000 + (Math.floor(Math.random() * 50) * 1000));
        const waktu = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        
        let warnaSumber = sumber === 'GrabFood' ? '#00B14F' : '#ee4d2d';

        const card = document.createElement('div');
        card.innerHTML = `
            <div class="online-card" onclick="tampilkanDetail('${sumber}', '${idPesanan}', '${harga}', '${warnaSumber}')" style="padding:14px; border:1px solid #e5e7eb; border-radius:8px; margin-bottom:10px; cursor:pointer; background:#fff; transition:all 0.2s;"
                 onmouseover="this.style.borderColor='${warnaSumber}'"
                 onmouseout="if(this.dataset.active !== 'true') this.style.borderColor='#e5e7eb'">
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span style="font-weight:600; font-size:0.95rem; color:#111827;">${sumber}-${idPesanan}</span>
                    <span style="font-size:0.7rem; font-weight:700; background:${warnaSumber}15; color:${warnaSumber}; padding:2px 8px; border-radius:4px;">BARU</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:0.85rem; color:#6b7280;">Hari ini • ${waktu}</span>
                    <span style="font-weight:700; color:#111827;">Rp ${harga}</span>
                </div>
            </div>
        `;

        // Masukkan ke atas
        const list = document.getElementById('orderList');
        list.insertBefore(card, list.firstChild);
    }
    
    function tampilkanDetail(sumber, id, harga, warna) {
        // Reset border all cards
        document.querySelectorAll('.online-card').forEach(el => {
            el.dataset.active = 'false';
            el.style.borderColor = '#e5e7eb';
        });
        
        // Highlight clicked card
        const currentCard = event.currentTarget;
        currentCard.dataset.active = 'true';
        currentCard.style.borderColor = warna;
        
        document.getElementById('detailEmpty').style.display = 'none';
        document.getElementById('detailContent').style.display = 'flex';
        
        document.getElementById('detId').innerText = sumber + '-' + id;
        document.getElementById('detSumber').innerText = sumber;
        document.getElementById('detSumber').style.color = warna;
        
        document.getElementById('detSubtotal').innerText = 'Rp ' + harga;
        // Total = Harga + 3000
        let totalVal = parseInt(harga.replace(/\./g, '')) + 3000;
        document.getElementById('detTotal').innerText = 'Rp ' + formatRupiah(totalVal);
    }
    
    function terimaPesananAsli() {
        Swal.fire({
            icon: 'success',
            title: 'Pesanan Diterima!',
            text: 'Pesanan telah masuk ke antrean dapur.',
            showConfirmButton: false,
            timer: 2000
        });
    }

    function formatRupiah(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
</script>
@endpush
