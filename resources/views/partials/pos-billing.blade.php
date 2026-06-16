{{-- partials/pos-billing.blade.php --}}
<script>
    window.POS_CONFIG = {
        serviceCharges: @json($serviceCharges ?? []),
        taxes: @json($taxes ?? [])
    };
</script>
<div class="right-panel" id="right-panel-main">

    {{-- HEADER: Daftar Bill + Tambah Pelanggan --}}
    <div class="bill-header" style="display: flex;">
        <button class="btn-daftar-bill" id="btnDaftarBill" style="flex: 1;">
            <img src="{{ asset('assets/listbill_icon.png') }}" alt="Daftar Bill" style="width: 32px; height: 32px; object-fit: contain;">
            Daftar Bill
        </button>
        <button class="btn-tambah-pelanggan" id="btnTambahPelanggan" style="flex: 2;">+ Tambah Pelanggan</button>
        <button class="btn-tambah-pelanggan" id="btnKodeKiosk" style="flex: 1; background-color: var(--primary); color: white;">Kode</button>
    </div>

    {{-- ORDER TYPE SELECTOR — klik untuk pilih tipe penjualan --}}
    <button class="bill-order-type" id="btnOrderTypeSelector">
        <span class="order-type-label" id="labelOrderType">Dine In</span>
        <img src="{{ asset('assets/chevron_down.png') }}" alt="▾" class="order-type-chevron-img">
    </button>

    {{-- ACTIVE TABLE ROW (Tabel Terpilih - Lanjutkan) --}}
    <div class="active-table-row" id="activeTableRow" style="display: none; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid #fca5a5; background: #fff1f2;">
        <span class="active-table-name" id="activeTableName" style="color: var(--primary); font-weight: 600; font-size: 0.95rem;">Table VIP</span>
        <button class="btn-lihat-meja" id="btnLihatMeja" style="background: none; border: none; color: var(--primary); font-weight: 600; font-size: 0.95rem; cursor: pointer; padding: 0;">Lihat Meja</button>
    </div>

    {{-- SCROLL AREA: cart items + summary (keduanya ikut scroll) --}}
    <div class="bill-scroll-area">

        {{-- CART ITEMS --}}
        <div id="cartItems">
            <div class="cart-empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                </svg>
                <p>Tidak Ada Produk</p>
            </div>
        </div>

        {{-- SUMMARY (muncul saat cart ada isi) --}}
        <div class="bill-summary" id="billSummary" style="display:none;">
            <div class="summary-row" id="summaryDiscountRow" style="display:none;">
                <span>Diskon</span>
                <span id="summaryDiscount" class="discount-value">(Rp 0)</span>
            </div>
            <div class="summary-row summary-subtotal">
                <span>Subtotal :</span>
                <span id="summarySubtotal">Rp 0</span>
            </div>
            <div class="summary-row summary-charge" id="takeawayChargeRow" style="display:none;">
                <span class="summary-label-secondary" id="serviceChargeLabel">Service Charge</span>
                <span class="summary-label-secondary" id="summaryTakeawayCharge">Rp 0</span>
            </div>
            <div class="summary-row summary-charge">
                <span class="summary-label-secondary" id="taxLabel">Pajak</span>
                <span class="summary-label-secondary" id="summaryTax">Rp 0</span>
            </div>
            <div class="summary-row summary-total">
                <span>Total :</span>
                <span id="summaryTotal">Rp 0</span>
            </div>
            <button class="btn-kosongkan" id="btnKosongkanKeranjang">Kosongkan Keranjang Belanja</button>
        </div>

    </div>{{-- /bill-scroll-area --}}


    {{-- FOOTER ACTIONS --}}
    <div class="bill-footer">
        <div class="bill-actions-row">
            <button class="btn-simpan" id="btnSimpanBillTrigger">Simpan Bill</button>
            <button class="btn-cetak" id="btnCetakBillLuar">Cetak Bill</button>
        </div>
        <div class="bill-pay-row">
            <button class="btn-pisah" id="btnPisahBill" disabled style="opacity: 1; pointer-events: none;">
                <img src="{{ asset('assets/splitbill_icon.png') }}" alt="Pisah Bill" style="width: 32px; height: 32px; object-fit: contain;">
                Pisah Bill
            </button>
            <button class="btn-bayar" id="totalBayar">Bayar Rp 0</button>
        </div>
    </div>
</div>

{{-- MODAL: Pilih Tipe Penjualan --}}
<div class="modal-overlay" id="modalOrderType" style="display:none;">
    <div class="order-type-modal">
        {{-- Header Modal --}}
        <div class="order-type-modal-header">
            <button class="btn-detail-action btn-batal-detail" id="btnOrderTypeBatal">Batal</button>
            <h3 class="order-type-modal-title">Pilih Tipe Penjualan</h3>
            <button class="btn-detail-action btn-simpan-detail" id="btnOrderTypeSelesai">Selesai</button>
        </div>
        <div class="order-type-modal-divider"></div>

        {{-- Body: pilihan tipe --}}
        <div class="order-type-modal-body">
            <div class="order-type-modal-label">TIPE PENJUALAN <span>| PILIH SATU</span></div>
            <div class="order-type-option-grid">
                <button class="option-btn active" data-type="dine-in" id="optTypeDineIn">Dine In</button>
                <button class="option-btn" data-type="takeaway" id="optTypeTakeaway">Takeaway</button>
                <button class="option-btn" data-type="gofood" id="optTypeGoFood">GoFood</button>
                <button class="option-btn" data-type="grabfood" id="optTypeGrabFood">GrabFood</button>
                <button class="option-btn" data-type="shopeefood" id="optTypeShopeeFood">ShopeeFood</button>
                <button class="option-btn option-disabled" disabled>Maxim Food</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: Search Member --}}
<div class="modal-overlay" id="modalSearchMember" style="display:none;">
    <div class="loyalty-modal">
        {{-- Header --}}
        <div class="loyalty-modal-header" style="justify-content: space-between;">
            <button class="btn-loyalty-outline" id="btnBatalSearchMember">Batal</button>
            <h3 class="loyalty-modal-title" style="flex: 1; text-align: center;"><span id="totalMembers">0</span> Pelanggan</h3>
            <div style="width: 70px;"></div> {{-- Spacer --}}
        </div>
        <div class="loyalty-modal-divider"></div>

        {{-- Body --}}
        <div class="loyalty-modal-body" style="padding: 24px;">
            <div style="position: relative; margin-bottom: 16px;">
                <input type="text" id="inputSearchMember" class="loyalty-input" placeholder="Cari dari Nama, Nomor Telepon, atau Email" style="width: 100%; padding-right: 40px; border: 1px solid var(--primary); border-radius: 4px; outline: none;">
                <img src="{{ asset('assets/search_icon.png') }}" alt="Search" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 24px; opacity: 0.5;">
            </div>

            <button class="btn-loyalty-check" style="width: 100%; margin-bottom: 24px; background: var(--primary); color: white; border: none; padding: 12px; border-radius: 4px; font-weight: 600; cursor: not-allowed; opacity: 0.8;" disabled>
                Buat Pelanggan Baru (via CRM)
            </button>

            <div class="loyalty-label" style="font-size: 0.85rem; color: #6b7280; margin-bottom: 8px;">DAFTAR PELANGGAN</div>

            {{-- Table header --}}
            <div style="display: flex; background: #f3f4f6; padding: 12px; font-weight: 600; font-size: 0.9rem; color: #374151;">
                <div style="flex: 1;">Nama</div>
                <div style="flex: 1;">Nomor Telepon</div>
                <div style="flex: 1;">Email</div>
            </div>

            {{-- Member List --}}
            <div id="searchMemberList" style="max-height: 300px; overflow-y: auto;">
                <div style="padding: 24px; text-align: center; color: #6b7280;">Memuat data...</div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: Loyalty Program / Cek Member --}}
<div class="modal-overlay" id="modalLoyalty" style="display:none;">
    <div class="loyalty-modal">
        {{-- Header --}}
        <div class="loyalty-modal-header">
            <button class="btn-loyalty-outline" id="btnBatalLoyalty">Batal</button>
            <h3 class="loyalty-modal-title">Program Loyalty</h3>
            <button class="btn-loyalty-outline" id="btnLewatiLoyalty">Lewati</button>
        </div>
        <div class="loyalty-modal-divider"></div>

        {{-- Body --}}
        <div class="loyalty-modal-body">
            <div class="loyalty-icon-wrapper">
                <div class="loyalty-circle">
                    <img src="{{ asset('assets/trophy.png') }}" alt="Trophy">
                </div>
            </div>
            <p class="loyalty-subtitle" id="loyaltySubtitleText">Dapatkan poin dari pesanan ini</p>

            <div class="loyalty-section">
                <div class="loyalty-label">DAFTAR ATAU CARI MEMBER</div>
                <div class="loyalty-input-group">
                    <div class="loyalty-country-code">
                        <img src="https://flagcdn.com/w20/id.png" alt="ID">
                        <span>+62</span>
                    </div>
                    <input type="text" id="loyaltyPhone" class="loyalty-input" placeholder="812">
                    <button class="btn-loyalty-check" id="btnCheckMember">Check</button>
                </div>
                <div id="loyaltyCheckResult" style="font-size: 0.85rem; margin-top: 8px; text-align: left;"></div>
            </div>

            <!-- <div class="loyalty-section">
                <div class="loyalty-label-row">
                    <div class="loyalty-label">DAFTAR REWARD</div>
                    <div class="loyalty-toggle">Sembunyikan</div>
                </div>

                <div class="loyalty-reward-list">
                    <div class="reward-item">
                        <div class="reward-info">
                            <div class="reward-title">5% Discount of Total Sale</div>
                            <div class="reward-desc">*Minimum pembelian Rp 0</div>
                        </div>
                        <div class="reward-points">10</div>
                    </div>
                    <div class="reward-item">
                        <div class="reward-info">
                            <div class="reward-title">10% Discount of Total Sale</div>
                            <div class="reward-desc">*Minimum pembelian Rp 0</div>
                        </div>
                        <div class="reward-points">20</div>
                    </div>
                    <div class="reward-item">
                        <div class="reward-info">
                            <div class="reward-title">5% Discount of Total Sale</div>
                            <div class="reward-desc">*Minimum pembelian Rp 0</div>
                        </div>
                        <div class="reward-points">10</div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</div>

{{-- MODAL: Daftar Bill --}}
<div class="modal-overlay" id="modalDaftarBill" style="display:none;">
    <div class="loyalty-modal">
        {{-- Header --}}
        <div class="loyalty-modal-header">
            <button class="btn-loyalty-outline" id="btnDaftarBillTutup">Tutup</button>
            <h3 class="loyalty-modal-title">Daftar Bill</h3>
            <button class="btn-loyalty-outline" id="btnDaftarBillBaru">Bill Baru</button>
        </div>
        <div class="loyalty-modal-divider"></div>

        {{-- Tabs --}}
        <div class="daftar-bill-tabs">
            <button class="daftar-bill-tab active" data-tab="open">Open Bill</button>
            <button class="daftar-bill-tab" data-tab="pembatalan-bill" style="display: none;">Pembatalan Bill</button>
            <button class="daftar-bill-tab" data-tab="pembatalan-produk" style="display: none;">Pembatalan Produk</button>
        </div>

        {{-- Search --}}
        <div class="daftar-bill-search-wrap">
            <input type="text" id="inputSearchBill" class="daftar-bill-search" placeholder="Cari Open Bill">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="daftar-bill-search-icon">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </div>

        {{-- Table Header --}}
        <div class="daftar-bill-table-header">
            <span>MEJA</span>
            <span>GRUP MEJA</span>
            <span>PELAYAN</span>
            <span>WAKTU</span>
            <span>SYNC</span>
        </div>

        {{-- Body —— rows rendered by JS --}}
        <div class="loyalty-modal-body" id="daftarBillBody" style="padding: 0; overflow-y: auto; flex: 1;">
            <div class="daftar-bill-loading" id="daftarBillLoading" style="display:flex; align-items:center; justify-content:center; padding: 48px 0; color:#9ca3af; font-size:0.95rem;">
                <svg style="margin-right:10px; animation: spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                </svg>
                Memuat data...
            </div>
            <table class="daftar-bill-table" id="daftarBillTable" style="display:none; width:100%; border-collapse:collapse;">
                <tbody id="daftarBillRows"></tbody>
            </table>
            <div id="daftarBillEmpty" style="display:none; text-align:center; padding: 48px 0; color:#9ca3af; font-size:0.95rem;">
                Tidak ada open bill saat ini.
            </div>
        </div>
    </div>
</div>

{{-- MODAL: Pisah Bayar --}}
<div class="modal-overlay" id="modalPisahBayar" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    <div class="loyalty-modal" style="width: 90%; max-width: 650px; background: white; border-radius: 8px; display: flex; flex-direction: column;">
        {{-- Header --}}
        <div class="loyalty-modal-header" style="justify-content: space-between; padding: 16px 24px; border-bottom: 1px solid #e5e7eb;">
            <button class="btn-loyalty-outline" id="btnBatalPisahBayar" style="border-radius: 4px; padding: 10px 24px; border: 1px solid var(--primary); color: var(--primary); background: transparent; cursor: pointer; font-weight: 500;">Batal</button>
            <h3 class="loyalty-modal-title" id="pisahBayarTotalDisplay" style="flex: 1; text-align: center; font-size: 1.5rem; font-weight: 700; color: #111; margin: 0;">Rp 0</h3>
            <div style="width: 80px;"></div> {{-- Spacer --}}
        </div>

        <div style="background: #fef2f2; color: #dc2626; padding: 12px; font-size: 0.85rem; text-align: center; border-bottom: 1px solid #fee2e2;">
            Transaksi pisah bayar tidak dapat di-refund. Anda masih bisa membatalkan sebelum semua pembayaran diselesaikan.
        </div>

        {{-- Body --}}
        <div class="loyalty-modal-body" style="padding: 24px; padding-bottom: 32px; overflow-y: auto; max-height: 60vh;">
            <div style="font-weight: 600; color: #111; font-size: 1.1rem; margin-bottom: 16px;">
                Bagi pembayaran menjadi : <span id="pisahBayarCount">2</span>
            </div>

            <div id="pisahBayarInputsContainer">
                <!-- Rows will be injected here via JS -->
            </div>

            <button id="btnTambahPisahBayar" style="width: 100%; border: 1px solid var(--primary); background: transparent; color: var(--primary); padding: 14px; border-radius: 4px; font-weight: 600; font-size: 1rem; cursor: pointer; margin-top: 16px;">
                Tambah Pembayaran
            </button>
        </div>
    </div>
</div>

{{-- MODAL: Pisah Bill --}}
<div class="modal-overlay" id="modalPisahBill" style="display:none;">
    <div class="loyalty-modal">
        {{-- Header --}}
        <div class="loyalty-modal-header">
            <button class="btn-loyalty-outline" id="btnPisahBillTutup" style="border: 1px solid #c43626; color: #c43626;">Tutup</button>
            <h3 class="loyalty-modal-title">Pisah Bill</h3>
            <button class="btn-loyalty-outline" id="btnPisahBillPisahkan" style="background: #c43626; color: #fff; border: none; font-weight: 600;">Pisahkan</button>
        </div>
        <div class="loyalty-modal-divider"></div>

        {{-- Body --}}
        <div class="loyalty-modal-body" style="padding: 24px 32px; display: flex; flex-direction: column; overflow-y: auto;">
            {{-- Jumlah yang dipisahkan display --}}
            <div class="pisah-bill-box">
                <span class="pisah-bill-box-label">Jumlah yang dipisahkan</span>
                <span class="pisah-bill-box-val" id="pisahBillAmountDisplay">Rp 0</span>
            </div>

            {{-- Label --}}
            <div class="pisah-bill-section-title">Produk yang Dipisahkan</div>

            {{-- Grouped items list --}}
            <div id="pisahBillProductList" style="flex: 1; display: flex; flex-direction: column;">
                {{-- Dynamically populated by JS --}}
            </div>

            {{-- Summary details (discounts, taxes, etc.) --}}
            <div id="pisahBillBreakdown" style="border-top: 1px solid #e5e7eb; margin-top: 20px; padding-top: 12px; display: flex; flex-direction: column; gap: 4px;">
                {{-- Dynamically populated by JS based on selected items --}}
            </div>
        </div>
    </div>
</div>

{{-- MODAL: Pilih Pelayan --}}
<div class="modal-overlay" id="modalStaff" style="display:none;">
    <div class="loyalty-modal">
        {{-- Header --}}
        <div class="loyalty-modal-header">
            <button class="btn-staff-back" id="btnBackToLoyalty">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" style="stroke: var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </button>
            <h3 class="loyalty-modal-title">Pilih Pelayan</h3>
            <button class="btn-loyalty-outline" id="btnLewatiStaff">Lewati</button>
        </div>
        <div class="loyalty-modal-divider"></div>

        {{-- Body --}}
        <div class="staff-modal-body">
            @if(isset($staffs) && count($staffs) > 0)
            @foreach($staffs as $staff)
            <div class="staff-list-item" data-id="{{ $staff->staff_id }}" data-name="{{ $staff->name }}">
                <div class="staff-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" style="stroke: var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="staff-name">{{ $staff->name }}</div>
            </div>
            @endforeach
            @else
            <div style="padding: 32px; text-align: center; color: #9ca3af;">Belum ada data pelayan</div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL: Metode Pembayaran --}}
<div class="modal-overlay" id="modalPayment" style="display:none;">
    <div class="loyalty-modal">
        {{-- Header --}}
        <div class="loyalty-modal-header" style="border-bottom: 1px solid #e5e7eb;">
            <button class="btn-loyalty-outline" id="btnBatalPayment">Batal</button>
            <h3 class="loyalty-modal-title" id="paymentTotalDisplay" style="font-size: 1.6rem; font-weight: 700;">Rp 0</h3>
            <button class="btn-loyalty-check" id="btnProsesPayment" style="border-radius: 4px; padding: 10px 32px;">Bayar</button>
        </div>

        {{-- Body --}}
        <div class="payment-modal-body" style="overflow-y: auto; padding: 0 32px 32px 32px;">

            {{-- Server Info --}}
            <div class="payment-section" style="padding: 16px 0; border-bottom: 1px solid #e5e7eb;">
                <span style="color: #111; font-weight: 500; font-size: 0.9rem;">Server</span>
                <span style="color: #9ca3af; font-size: 0.9rem;" id="paymentServerName">| -</span>
            </div>

            {{-- Pisah Bayar --}}
            <div class="payment-section" style="padding: 16px 0; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 1.15rem; color: #111;">Bayar dengan beberapa metode pembayaran</div>
                <div id="btnPisahBayar" style="color: var(--primary); font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                        <circle cx="12" cy="12" r="2"></circle>
                        <path d="M6 12h.01M18 12h.01"></path>
                    </svg>
                    Pisah Bayar >
                </div>
            </div>

            {{-- Tunai --}}
            <div class="payment-section payment-grid-section">
                <div class="payment-label-col">
                    <div class="payment-label-title">Uang Tunai</div>
                </div>
                <div class="payment-content-col">
                    <div class="payment-btn-group">
                        <button class="btn-payment-outline" id="btnUangPas">Rp 0</button>
                        <button class="btn-payment-outline" id="btnUang50k">Rp 50.000</button>
                    </div>
                    <input type="text" id="inputTunaiManual" class="loyalty-input" style="width: 100%; border: 1px solid var(--primary); border-radius: 4px; margin-top: 12px;" placeholder="Rp 0">
                </div>
            </div>

            {{-- EWallet --}}
            <div class="payment-section payment-grid-section">
                <div class="payment-label-col">
                    <div class="payment-label-title">EWallet</div>
                    <div class="payment-label-desc">Aktifkan melalui<br>backoffice</div>
                </div>
                <div class="payment-content-col">
                    <div class="payment-btn-group">
                        <button class="btn-payment-outline" data-method="ewallet" style="padding: 6px 12px; display:flex; justify-content:center; align-items:center; height: 42px;">
                            <img src="{{ asset('assets/dana.png') }}" alt="DANA" style="height: 24px; object-fit: contain;">
                        </button>
                        <button class="btn-payment-outline" data-method="ewallet" style="padding: 6px 12px; display:flex; justify-content:center; align-items:center; height: 42px;">
                            <img src="{{ asset('assets/shopeepay.png') }}" alt="ShopeePay" style="max-height: 24px; object-fit: contain;">
                        </button>
                        <button class="btn-payment-outline" data-method="ewallet" style="padding: 6px 12px; display:flex; justify-content:center; align-items:center; height: 42px;">
                            <img src="{{ asset('assets/gopay.png') }}" alt="GoPay" style="height: 24px; object-fit: contain;">
                        </button>
                    </div>
                </div>
            </div>

            <!-- {{-- EDC --}}
            <div class="payment-section payment-grid-section">
                <div class="payment-label-col">
                    <div class="payment-label-title">EDC</div>
                </div>
                <div class="payment-content-col">
                    <div class="payment-btn-group">
                        <button class="btn-payment-outline">BCA</button>
                        <button class="btn-payment-outline">Mandiri</button>
                        <button class="btn-payment-outline">BRI</button>
                    </div>
                    <input type="text" class="loyalty-input" style="width: 100%; border: 1px solid var(--primary); border-radius: 4px; margin-top: 12px; font-size: 1rem; padding: 12px;" placeholder="Catatan Tambahan">
                </div>
            </div>

            {{-- Lainnya --}}
            <div class="payment-section payment-grid-section">
                <div class="payment-label-col">
                    <div class="payment-label-title">Lainnya</div>
                </div>
                <div class="payment-content-col">
                    <div class="payment-btn-group" style="grid-template-columns: 1fr;">
                        <button class="btn-payment-outline" style="max-width: 200px;">Pay Later</button>
                    </div>
                    <input type="text" class="loyalty-input" style="width: 100%; border: 1px solid var(--primary); border-radius: 4px; margin-top: 12px; font-size: 1rem; padding: 12px;" placeholder="Catatan Tambahan">
                </div>
            </div> -->

            <!-- {{-- Invoice --}}
            <div class="payment-section payment-grid-section" style="border-bottom: none;">
                <div class="payment-label-col">
                    <div class="payment-label-title">Invoice</div>
                </div>
                <div class="payment-content-col">
                    <button class="btn-payment-outline" style="width: 100%; display: flex; justify-content: flex-start; gap: 8px; color: #111;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        Invoice
                    </button>
                </div>
            </div> -->

        </div>
    </div>
</div>

{{-- MODAL: Pembayaran Tunai Sukses --}}
<div class="modal-full-overlay" id="modalTunaiSuccess" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:#fff; z-index:9999; flex-direction:column;">
    {{-- Header Full Width --}}
    <div style="width: 100%; padding: 32px 48px; display: flex; justify-content: space-between; align-items: center;">
        <div style="color:var(--primary); font-weight:700; font-size:1.4rem;" id="tunaiSuccessTitle">TUNAI</div>
    </div>

    {{-- Content Center --}}
    <div style="width:100%; max-width:500px; margin: 0 auto; display:flex; flex-direction:column; align-items:center;">
        <div style="font-size:1.2rem; color:#333;" id="tunaiSuccessBayarContainer">Bayar <span id="tunaiSuccessBayar">Rp 0</span></div>
        <div style="margin-top:24px; font-size:2rem; font-weight:600; color:var(--primary);" id="tunaiSuccessKembalianLabel">Kembalian</div>
        <div style="margin-top:8px; font-size:2rem; font-weight:600; color:var(--primary);" id="tunaiSuccessKembalian">Rp 0</div>

        <img src="{{ asset('assets/payment_check.png') }}" alt="Check" style="width:120px; margin-top:32px;">

        <div style="width:100%; margin-top:32px; display: none;">
            <div style="display:flex; margin-bottom:16px;">
                <input type="email" placeholder="Struk Email" style="flex:1; padding:12px; border:1px solid #9ca3af; border-radius:4px 0 0 4px; outline:none; font-family:inherit; font-size:1rem;">
                <button style="background:#f87171; color:white; border:none; padding:0 24px; border-radius:0 4px 4px 0; font-weight:600; cursor:pointer;">Kirim</button>
            </div>
            <div style="display:flex; margin-bottom:24px;">
                <input type="text" placeholder="+62" style="flex:1; padding:12px; border:1px solid #9ca3af; border-radius:4px 0 0 4px; outline:none; font-family:inherit; font-size:1rem;">
                <button style="background:var(--primary); color:white; border:none; padding:0 24px; border-radius:0 4px 4px 0; font-weight:600; cursor:pointer;">Kirim</button>
            </div>
        </div>

        <button id="btnCetakStrukSuccess" style="width:100%; background:var(--primary); color:white; border:none; padding:14px; border-radius:4px; font-weight:600; font-size:1.05rem; cursor:pointer; margin-bottom:16px; margin-top: 32px;">Cetak Struk</button>
        <button id="btnTransaksiBaru" style="width:100%; background:transparent; color:var(--primary); border:1px solid var(--primary); padding:14px; border-radius:4px; font-weight:600; font-size:1.05rem; cursor:pointer;">Transaksi Baru</button>
    </div>
</div>

{{-- MODAL: Pembayaran QRIS / E-Wallet --}}
<div class="modal-full-overlay" id="modalQris" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:#fff; z-index:9999; flex-direction:column;">
    {{-- Header Full Width --}}
    <div style="width: 100%; padding: 32px 48px; display: flex; justify-content: space-between; align-items: flex-start;">
        <div style="display:flex; align-items:center;">
            <img src="{{ asset('assets/qris.png') }}" alt="QRIS" style="height: 32px; object-fit: contain; margin-right: 12px;">
        </div>
        <button id="btnBatalQris" style="background:transparent; border:1px solid var(--primary); color:var(--primary); padding:8px 24px; border-radius:4px; font-weight:600; cursor:pointer;">Batal</button>
    </div>

    {{-- Content Center --}}
    <div style="width:100%; max-width:500px; margin: 0 auto; display:flex; flex-direction:column; align-items:center;">
        <div style="margin-top:24px; font-size:1.2rem; color:#333;">Total Harga</div>
        <div style="margin-top:8px; font-size:2rem; font-weight:600; color:var(--primary);" id="qrisTotalHargaDisplay">Rp 0</div>

        <div style="margin-top:32px; padding:16px; border:1px solid #e5e7eb; border-radius:8px;">
            <img id="qrisMainImage" src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=JayaPOS" alt="QR Code" style="width:200px; height:200px;">
        </div>

        <button style="width:100%; max-width:300px; background:transparent; color:var(--primary); border:1px solid var(--primary); padding:12px; border-radius:4px; font-weight:600; cursor:pointer; margin-top:32px;">Cetak QR Code</button>

        <div style="text-align:center; margin-top:32px;">
            <div style="font-weight:700; color:#111; margin-bottom:4px;">Toko Kopi Jaya Tenes</div>
            <div style="color:#111;">Jl. Tenes</div>
        </div>

        <div style="margin-top:32px; position:relative; width:80px; height:80px; display:flex; justify-content:center; align-items:center;">
            <svg width="80" height="80" style="position:absolute; transform:rotate(-90deg);">
                <circle cx="40" cy="40" r="36" fill="none" stroke="#e5e7eb" stroke-width="8"></circle>
                <circle cx="40" cy="40" r="36" fill="none" stroke-width="8" stroke-dasharray="226" stroke-dashoffset="0" id="qrisProgressCircle" style="stroke: var(--primary); transition: stroke-dashoffset 1s linear;"></circle>
            </svg>
            <div id="qrisCountdownText" style="font-size:1.2rem; font-weight:600; color:#111;">60</div>
        </div>

        <div style="margin-top:24px; font-size:0.85rem; color:#9ca3af;">Transaksi e-wallet tidak bisa di refund</div>
    </div>
</div>

{{-- MODAL: Konfirmasi Batal QRIS --}}
<div class="modal-overlay" id="modalBatalQrisConfirm" style="display:none; z-index: 10000;">
    <div class="loyalty-modal" style="width: 90%; max-width: 400px; height: auto; text-align: center; padding: 32px 24px;">
        <h3 style="margin-top: 0; margin-bottom: 24px; font-size: 1.25rem;">Batalkan Transaksi?</h3>
        <button class="btn-loyalty-check" id="btnConfirmBatalQris" style="width: 100%; padding: 12px; border-radius: 4px; border: none; font-size: 1rem; cursor: pointer; color: white; margin-bottom: 12px;">Iya</button>
        <button class="btn-loyalty-outline" id="btnCancelBatalQris" style="width: 100%;">Tidak</button>
    </div>
</div>

{{-- OVERLAY: Pilih Meja (Denah Meja) --}}
<div class="modal-full-overlay" id="overlayPilihMeja" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:#f3f4f6; z-index:999; flex-direction:column; font-family: 'Inter', sans-serif;">
    {{-- Header --}}
    <div style="width:100%; background:#fff; padding:16px 32px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <button id="btnPilihMejaBatal" style="background:#fff; border:1px solid #ef4444; color:#ef4444; padding:10px 24px; border-radius:6px; font-weight:600; cursor:pointer; font-size:0.95rem; transition: all 0.2s;">Batal</button>
        <h2 style="margin:0; font-size:1.4rem; font-weight:700; color:#111827;">Pilih Meja</h2>
        <div style="display:flex; gap:12px;">
            <button id="btnSimpanSebagaiBill" class="btn-meja-action" style="background:#fff; border:1px solid #c43626; color:#c43626; padding:10px 20px; border-radius:6px; font-weight:600; cursor:pointer; font-size:0.95rem; opacity: 0.6; pointer-events: none;">Simpan Sebagai Bill</button>
            <button id="btnMejaLanjutkan" class="btn-meja-action" style="background:#d1d5db; border:none; color:#9ca3af; padding:10px 24px; border-radius:6px; font-weight:600; cursor:pointer; font-size:0.95rem; pointer-events: none;">Lanjutkan</button>
        </div>
    </div>

    {{-- Main Area Container --}}
    <div id="pilihMejaContent" style="flex:1; overflow-y:auto; padding:40px 32px; display:flex; justify-content:center; align-items:center;">
        @if(isset($areas) && count($areas) > 0)
        @foreach($areas as $areaIdx => $area)
        <div class="area-grid-view" id="areaGridView_{{ $area->area_id }}" style="display: {{ $areaIdx === 0 ? 'grid' : 'none' }}; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 24px; width: 100%; max-width: 900px; justify-content: center; align-content: center;">
            @foreach($area->tables as $table)
            @if($table->status === 'occupied')
            {{-- Occupied Table --}}
            <div class="table-card table-occupied" style="background:#d1d5db; border-radius:50%; width:110px; height:110px; margin:0 auto; display:flex; flex-direction:column; justify-content:center; align-items:center; cursor:not-allowed; box-shadow:0 4px 6px rgba(0,0,0,0.05); color:#6b7280;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="margin-bottom:4px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <span style="font-size:0.75rem; font-weight:600; opacity:0.8;">Terisi</span>
            </div>
            @else
            {{-- Available Table --}}
            <div class="table-card table-available" data-id="{{ $table->table_id }}" data-name="{{ $table->name }}" data-capacity="{{ $table->capacity }}" data-area="{{ $area->name }}" style="background:#fff; border-radius:12px; height:100px; display:flex; flex-direction:column; justify-content:center; align-items:center; cursor:pointer; box-shadow:0 4px 6px rgba(0,0,0,0.05); transition:all 0.2s; border: 2px solid transparent;" onmouseover="this.style.transform='scale(1.03)';" onmouseout="this.style.transform='scale(1)';">
                <span style="font-weight:700; color:#374151; font-size:1rem;">{{ $table->name }}</span>
                <span style="font-size:0.8rem; color:#9ca3af; margin-top:4px;">0 / {{ $table->capacity }}</span>
            </div>
            @endif
            @endforeach
        </div>
        @endforeach
        @else
        <div style="color:#6b7280; font-size:1.1rem;">Belum ada denah meja yang di-setup.</div>
        @endif
    </div>

    {{-- Bottom Tab Bar --}}
    @include('partials.pos-bottom-nav', ['isFloorPlan' => true])
</div>

{{-- MODAL: Bill Baru (Jumlah Pelanggan & Pelayan) --}}
<div class="modal-overlay" id="modalBillBaru" style="display:none; z-index: 1000; font-family: 'Inter', sans-serif;">
    <div class="loyalty-modal">
        {{-- Header --}}
        <div class="loyalty-modal-header" style="align-items: center;">
            <button id="btnBillBaruBatal" class="btn-loyalty-outline">Batal</button>
            <div style="display:flex; flex-direction:column; align-items:center; text-align:center; flex:1; padding: 0 16px;">
                <span class="loyalty-modal-title" style="font-size:1.3rem; font-weight:700; color: #111;">Bill Baru</span>
                <span id="billBaruSubTitle" style="font-size:0.95rem; color:#4b5563; margin-top:4px; font-weight:500;">Table VIP - Outdoor 1</span>
            </div>
            <button id="btnBillBaruKonfirmasi" class="btn-loyalty-check" style="height: 44px; border-radius: 4px; opacity: 0.5; pointer-events: none;">Konfirmasi</button>
        </div>

        <div class="loyalty-modal-divider"></div>

        {{-- Body --}}
        <div class="loyalty-modal-body" style="padding: 24px 32px 32px 32px; background: #fff;">
            {{-- Section 1: Pax Input (Mockup style) --}}
            <div style="border: 1px solid #e5e7eb; border-radius: 6px; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; background: #fff; transition: border-color 0.2s;" class="pax-input-wrapper">
                <span style="font-weight: 700; color: #111827; font-size: 1.05rem;">Pax</span>
                <input type="number" id="inputPax" placeholder="Masukkan jumlah pax" style="border: none; outline: none; text-align: right; font-size: 1.05rem; color: #111827; width: 60%; font-family: inherit; font-weight: 500;" value="1" min="1" max="99">
            </div>

            {{-- Section 2: Waiter Selector (Checkout list style) --}}
            <div style="flex: 1; display: flex; flex-direction: column; min-height: 0;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#111827; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">PILIH PELAYAN</label>
                <div id="waiterSelectionList" class="staff-modal-body" style="flex:1; gap:0; overflow-y:auto; border-top: 1px solid #e5e7eb;">
                    @if(isset($staffs) && count($staffs) > 0)
                    @foreach($staffs as $staff)
                    <div class="staff-list-item waiter-item" data-id="{{ $staff->staff_id }}" data-name="{{ $staff->name }}" style="display:flex; align-items:center; justify-content:space-between; padding:16px 32px; border-bottom:1px solid #e5e7eb; cursor:pointer;">
                        <div style="display:flex; align-items:center;">
                            <div class="staff-icon" style="margin-right:16px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <div class="staff-name">{{ $staff->name }}</div>
                        </div>
                        <div style="font-size:1.05rem; color:#4b5563; font-weight:400;">
                            {{ ucfirst($staff->role === 'cashier' ? 'Kasir' : $staff->role) }}
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div style="text-align:center; color:#9ca3af; padding:32px 0;">Belum ada pelayan aktif.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
{{-- MODAL: Tarik Pesanan Kiosk/CRM --}}
<div class="modal-overlay" id="modalKioskCode" style="display:none;">
    <div class="loyalty-modal">
        {{-- Header --}}
        <div class="loyalty-modal-header" style="justify-content: space-between;">
            <button class="btn-loyalty-outline" id="btnBatalKioskCode">Batal</button>
            <h3 class="loyalty-modal-title" style="flex: 1; text-align: center;">Tarik Pesanan</h3>
            <div style="width: 70px;"></div> {{-- Spacer --}}
        </div>
        <div class="loyalty-modal-divider"></div>

        {{-- Body --}}
        <div class="loyalty-modal-body" style="padding: 24px;">
            <div style="text-align: center; margin-bottom: 16px;">
                <p style="color: #6b7280; font-size: 0.95rem;">Masukkan kode pesanan dari Kiosk atau CRM.</p>
            </div>
            <div style="position: relative; margin-bottom: 16px;">
                <input type="text" id="inputKioskCode" class="loyalty-input" placeholder="Misal: APP001" style="width: 100%; padding: 12px; font-size: 1.25rem; font-weight: bold; text-align: center; text-transform: uppercase; letter-spacing: 4px; border: 2px solid var(--primary); border-radius: 4px; outline: none;">
            </div>

            <button class="btn-loyalty-check" id="btnProsesKioskCode" style="width: 100%; margin-bottom: 8px; background: var(--primary); color: white; border: none; padding: 12px; border-radius: 4px; font-weight: 600; cursor: pointer;">
                Proses Kode
            </button>
            <div id="kioskCodeError" style="color: #ef4444; font-size: 0.85rem; text-align: center; min-height: 20px;"></div>
        </div>
    </div>
</div>
