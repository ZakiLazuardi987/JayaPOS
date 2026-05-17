{{-- partials/pos-billing.blade.php --}}
<script>
    window.POS_CONFIG = {
        serviceCharges: @json($serviceCharges ?? []),
        taxes: @json($taxes ?? [])
    };
</script>
<div class="right-panel" id="right-panel-main">

    {{-- HEADER: Daftar Bill + Tambah Pelanggan --}}
    <div class="bill-header">
        <button class="btn-daftar-bill" id="btnDaftarBill">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Daftar Bill
        </button>
        <button class="btn-tambah-pelanggan">+ Tambah Pelanggan</button>
    </div>

    {{-- ORDER TYPE SELECTOR — klik untuk pilih tipe penjualan --}}
    <button class="bill-order-type" id="btnOrderTypeSelector">
        <span class="order-type-label" id="labelOrderType">Dine In</span>
        <img src="{{ asset('assets/chevron_down.png') }}" alt="▾" class="order-type-chevron-img">
    </button>

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
            <button class="btn-simpan">Simpan Bill</button>
            <button class="btn-cetak">Cetak Bill</button>
        </div>
        <div class="bill-pay-row">
            <button class="btn-pisah">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 6h18M3 14h9m-9 4h9" />
                </svg>
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

{{-- MODAL: Loyalty Program / Cek Member --}}
<div class="modal-overlay" id="modalLoyalty" style="display:none;">
    <div class="loyalty-modal">
        {{-- Header --}}
        <div class="loyalty-modal-header">
            <button class="btn-loyalty-outline" id="btnBatalLoyalty">Batal</button>
            <h3 class="loyalty-modal-title">Pisah Bill</h3>
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
            <p class="loyalty-subtitle">Dapatkan 10 poin untuk registrasi Member Baru</p>

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
            </div>

            <div class="loyalty-section">
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
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                <div style="color: var(--primary); font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
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
                        <button class="btn-payment-outline" style="padding: 6px 12px; display:flex; justify-content:center; align-items:center; height: 42px;">
                            <img src="{{ asset('assets/dana.png') }}" alt="DANA" style="height: 24px; object-fit: contain;">
                        </button>
                        <button class="btn-payment-outline" style="padding: 6px 12px; display:flex; justify-content:center; align-items:center; height: 42px;">
                            <img src="{{ asset('assets/shopeepay.png') }}" alt="ShopeePay" style="max-height: 24px; object-fit: contain;">
                        </button>
                        <button class="btn-payment-outline" style="padding: 6px 12px; display:flex; justify-content:center; align-items:center; height: 42px;">
                            <img src="{{ asset('assets/gopay.png') }}" alt="GoPay" style="height: 24px; object-fit: contain;">
                        </button>
                    </div>
                </div>
            </div>

            {{-- EDC --}}
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
            </div>

            {{-- Invoice --}}
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
            </div>

        </div>
    </div>
</div>