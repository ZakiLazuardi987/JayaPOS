{{-- partials/product-detail-modal.blade.php --}}
{{-- Digunakan di favorit.blade.php & library.blade.php --}}
{{-- Pass $discounts dari controller --}}

<div id="modalProductDetail" class="modal-overlay">
    <div class="modal-content detail-modal">

        {{-- FIXED HEADER --}}
        <div class="detail-header">
            <button class="btn-detail-action btn-batal-detail" id="btnBatalDetail">Batal</button>
            <div class="detail-header-title">
                <h3 id="detailProductName">Nama Produk</h3>
                <span id="detailProductPrice" class="detail-price">Rp 0</span>
            </div>
            <button class="btn-detail-action btn-simpan-detail" id="btnSimpanDetail">Simpan</button>
        </div>

        {{-- SCROLLABLE BODY --}}
        <div class="detail-body" id="detailBody">

            {{-- Modifier groups akan di-render JS di sini --}}
            <div id="modifierGroupsContainer"></div>

            {{-- JUMLAH --}}
            <div class="detail-section qty-section">
                <div class="qty-row">
                    <div class="qty-value-box">
                        <input type="number" id="inputQty" value="1" min="1" max="99">
                    </div>
                    <div class="qty-btn-group">
                        <button id="btnMinus" class="qty-btn">&#8722;</button>
                        <button id="btnPlus" class="qty-btn">&#43;</button>
                    </div>
                </div>
            </div>

            {{-- DISKON (toggle) --}}
            @if(isset($discounts) && $discounts->count() > 0)
            <div class="detail-section discount-section">
                <div class="discount-grid">
                    @foreach($discounts as $disc)
                    <label class="discount-toggle-item">
                        <div class="discount-info">
                            <span class="discount-name">{{ $disc->name }}
                                ({{ $disc->type === 'percentage' ? $disc->value.'%' : 'Rp '.number_format($disc->value,0,',','.') }})
                            </span>
                        </div>
                        <div class="toggle-switch">
                            <input type="checkbox" class="discount-cb" 
                                   data-id="{{ $disc->discount_id }}"
                                   data-name="{{ $disc->name }}"
                                   data-type="{{ $disc->type }}"
                                   data-value="{{ $disc->value }}">
                            <span class="toggle-slider"></span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- TIPE PESANAN --}}
            <div class="detail-section order-type-section">
                <div class="order-type-label-hint">PILIH SATU</div>
                <div class="option-grid">
                    <label class="option-btn active" id="optDineIn">
                        <input type="radio" name="modal_order_type" value="dine-in" checked hidden>
                        Dine In
                    </label>
                    <label class="option-btn" id="optTakeaway">
                        <input type="radio" name="modal_order_type" value="takeaway" hidden>
                        Takeaway
                    </label>
                    <label class="option-btn" id="optGofood">
                        <input type="radio" name="modal_order_type" value="gofood" hidden>
                        GoFood
                    </label>
                    <label class="option-btn" id="optGrabfood">
                        <input type="radio" name="modal_order_type" value="grabfood" hidden>
                        GrabFood
                    </label>
                    <label class="option-btn" id="optShopeefood">
                        <input type="radio" name="modal_order_type" value="shopeefood" hidden>
                        ShopeeFood
                    </label>
                    <!-- <label class="option-btn option-disabled" id="optMaximfood">
                        <input type="radio" name="modal_order_type" value="maximfood" hidden disabled>
                        Maxim Food
                    </label> -->
                </div>
                
                <div class="order-type-hint-text">
                    Anda tidak dapat memilih tipe penjualan tertentu, karena tipe penjualan tersebut tidak memiliki harga. Silakan melihat backoffice kembali untuk mendapatkan detil lebih lanjut.
                </div>
            </div>

            <hr class="detail-divider">

            {{-- CATATAN --}}
            <div class="notes-section">
                <div class="notes-label">CATATAN</div>
                <textarea id="inputCatatan" class="notes-textarea" placeholder="Deskripsi" rows="3"></textarea>
            </div>

        </div>{{-- /detail-body --}}
    </div>
</div>
