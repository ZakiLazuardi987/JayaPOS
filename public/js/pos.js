/**
 * pos.js — JayaPOS
 * Handles: sidebar, carousel/slider, edit mode favorit,
 *          modal detail produk (modifier grouped), cart, billing panel
 */

document.addEventListener('DOMContentLoaded', () => {

    // =========================================================
    // 1. INISIALISASI & TOAST
    // =========================================================
    const loadingOverlay = document.getElementById('loadingOverlay');
    const mainToast = document.getElementById('mainToast');

    function showLoading() {
        if (loadingOverlay) loadingOverlay.style.display = 'flex';
    }
    function hideLoading() {
        if (loadingOverlay) loadingOverlay.style.display = 'none';
    }

    function showToast(msg, isError = false) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const t = document.createElement('div');
        t.className = 'toast';
        t.style.background = isError ? '#ef4444' : '#10b981';
        t.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>${msg}`;
        container.appendChild(t);
        setTimeout(() => {
            t.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            t.style.opacity = '0';
            t.style.transform = 'translateX(100%)';
            setTimeout(() => t.remove(), 500);
        }, 3500);
    }

    // Auto-dismiss server-rendered toast
    if (mainToast) {
        setTimeout(() => {
            mainToast.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            mainToast.style.opacity = '0';
            mainToast.style.transform = 'translateX(100%)';
            setTimeout(() => mainToast.remove(), 500);
        }, 4000);
    }

    // =========================================================
    // 2. SIDEBAR
    // =========================================================
    const btnMenu  = document.getElementById('btnMenu');
    const sidebar  = document.getElementById('sidebar');
    const sideOverlay = document.getElementById('sidebarOverlay');

    if (btnMenu) {
        btnMenu.addEventListener('click', () => {
            sidebar.classList.add('open');
            sideOverlay.style.display = 'block';
        });
    }
    if (sideOverlay) {
        sideOverlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            sideOverlay.style.display = 'none';
        });
    }

    // =========================================================
    // 3. CAROUSEL / SLIDER (Favorit)
    // =========================================================
    let currentPage = 0;
    const normalPages = document.querySelectorAll('#carousel-normal .grid-page');
    let maxPages = normalPages.length;

    function goToPage(index) {
        if (index < 0 || index >= maxPages) return;
        currentPage = index;
        updateSliderView('carousel-normal', 'dots-normal');
        updateSliderView('carousel-edit', 'dots-edit');
    }

    function updateSliderView(containerId, dotsId) {
        const container = document.getElementById(containerId);
        const dotsContainer = document.getElementById(dotsId);
        if (container) {
            container.querySelectorAll('.grid-page').forEach((page, idx) => {
                page.classList.toggle('active', idx === currentPage);
            });
        }
        if (dotsContainer) {
            dotsContainer.querySelectorAll('.dot').forEach((dot, idx) => {
                dot.classList.toggle('active', idx === currentPage);
            });
        }
    }

    document.querySelectorAll('.carousel-dots').forEach(dotsContainer => {
        dotsContainer.querySelectorAll('.dot').forEach(dot => {
            dot.addEventListener('click', (e) => {
                e.stopPropagation();
                goToPage(parseInt(dot.dataset.page));
            });
        });
    });

    // Touch swipe
    const leftArea = document.getElementById('leftArea');
    let touchStartX = 0;
    if (leftArea) {
        leftArea.addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].screenX; });
        leftArea.addEventListener('touchend', e => {
            let touchEndX = e.changedTouches[0].screenX;
            if (touchEndX < touchStartX - 50) goToPage(currentPage + 1);
            if (touchEndX > touchStartX + 50) goToPage(currentPage - 1);
        });
    }

    // =========================================================
    // 4. MODE EDIT FAVORIT
    // =========================================================
    const tabFavorit     = document.getElementById('tab-favorit');
    const tabEditFavorit = document.getElementById('tab-edit-favorit');
    const rightPanelMain = document.getElementById('right-panel-main');
    const rightPanelEdit = document.getElementById('right-panel-edit');
    const btnSelesaiEdit = document.getElementById('btnSelesaiEdit');
    let isEditMode = false;

    function toggleEditMode(activate) {
        if (!tabFavorit || !tabEditFavorit) return;
        isEditMode = activate;
        if (activate) {
            tabFavorit.style.display = 'none';
            tabEditFavorit.style.display = 'block';
            tabEditFavorit.classList.add('active');
            if (rightPanelMain) rightPanelMain.style.display = 'none';
            if (rightPanelEdit) rightPanelEdit.style.display = 'flex';
        } else {
            tabEditFavorit.style.display = 'none';
            tabEditFavorit.classList.remove('active');
            tabFavorit.style.display = 'block';
            if (rightPanelEdit) rightPanelEdit.style.display = 'none';
            if (rightPanelMain) rightPanelMain.style.display = 'flex';
        }
    }

    if (leftArea) {
        leftArea.addEventListener('click', (e) => {
            if (tabFavorit && tabFavorit.style.display !== 'none' && !isEditMode) {
                const isFilledCard = e.target.closest('.product-card:not(.empty-card)');
                const isDot = e.target.closest('.dot');
                if (!isFilledCard && !isDot) {
                    toggleEditMode(true);
                }
            }
        });
    }

    if (btnSelesaiEdit) btnSelesaiEdit.addEventListener('click', () => toggleEditMode(false));

    // =========================================================
    // 5. MODAL TAMBAH FAVORIT & SEARCH
    // =========================================================
    const modalFav       = document.getElementById('modalAddFavorite');
    const btnBatalFav    = document.getElementById('btnBatalFav');
    const emptySlotBtns  = document.querySelectorAll('.empty-slot-btn');
    const selectProdBtns = document.querySelectorAll('.select-product-btn');
    const searchInput    = document.getElementById('searchFavoriteProduct');
    const productItems   = document.querySelectorAll('.product-list-item');

    if (modalFav) {
        emptySlotBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                modalFav.style.display = 'flex';
                if (searchInput) {
                    searchInput.value = '';
                    productItems.forEach(item => item.style.display = 'flex');
                }
            });
        });
        if (btnBatalFav) btnBatalFav.addEventListener('click', () => modalFav.style.display = 'none');
        window.addEventListener('click', (e) => { if (e.target === modalFav) modalFav.style.display = 'none'; });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const kw = this.value.toLowerCase();
            productItems.forEach(item => {
                const name = item.querySelector('.product-name').innerText.toLowerCase();
                item.style.display = name.includes(kw) ? 'flex' : 'none';
            });
        });
    }

    // AJAX Tambah Favorit
    selectProdBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const productId = this.dataset.id;
            const csrf = document.querySelector('meta[name="csrf-token"]');
            if (!csrf) return;
            this.style.opacity = '0.5';
            this.style.pointerEvents = 'none';
            modalFav.style.display = 'none';
            showLoading();
            fetch('/pos/favorite/add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.getAttribute('content') },
                body: JSON.stringify({ product_id: productId })
            }).then(r => r.json()).then(data => {
                if (data.success) window.location.reload();
                else { this.style.opacity = '1'; this.style.pointerEvents = 'auto'; hideLoading(); }
            });
        });
    });

    // AJAX Hapus Favorit
    document.querySelectorAll('.btn-remove').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const card = this.closest('.product-card');
            const csrf = document.querySelector('meta[name="csrf-token"]');
            if (confirm('Hapus produk ini dari daftar favorit?')) {
                showLoading();
                fetch('/pos/favorite/remove', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.getAttribute('content') },
                    body: JSON.stringify({ product_id: card.dataset.id })
                }).then(r => r.json()).then(data => {
                    if (data.success) window.location.reload();
                    else hideLoading();
                });
            }
        });
    });

    // =========================================================
    // 6. LIBRARY SEARCH
    // =========================================================
    const searchLibInput = document.getElementById('searchLibraryProduct');
    const libraryItems   = document.querySelectorAll('#libraryListContent .list-item');

    if (searchLibInput) {
        searchLibInput.addEventListener('input', function () {
            const kw = this.value.toLowerCase();
            libraryItems.forEach(item => {
                const name = item.querySelector('.list-item-name').innerText.toLowerCase();
                item.style.display = name.includes(kw) ? 'flex' : 'none';
            });
        });
    }

    // =========================================================
    // 7. CART (localStorage) — dengan deteksi user ganti
    // =========================================================
    
    // Cek apakah cart milik user yang sama
    // Kalau beda (logout → login user lain), keranjang dikosongkan otomatis
    const currentStaffId = document.querySelector('meta[name="staff-id"]')?.getAttribute('content') || '';
    const cartOwner = localStorage.getItem('pos_cart_owner') || '';

    if (currentStaffId && cartOwner && cartOwner !== currentStaffId) {
        // User berbeda → buang cart lama
        localStorage.removeItem('pos_cart');
        localStorage.removeItem('pos_cart_owner');
    }

    // Simpan owner sekarang
    if (currentStaffId) {
        localStorage.setItem('pos_cart_owner', currentStaffId);
    }

    let cart = JSON.parse(localStorage.getItem('pos_cart')) || [];
    let currentGrandTotal = 0;

    function saveCart() {
        localStorage.setItem('pos_cart', JSON.stringify(cart));
    }

    window.removeFromCart = function (id) {
        cart = cart.filter(item => item.id !== id);
        saveCart();
        renderCart();
    };

    // Global: dipakai oleh halaman custom (kalkulator)
    window.addToCart = function (item) {
        cart.push(item);
        saveCart();
        renderCart();
    };

    function renderCart() {
        const container    = document.getElementById('cartItems');
        const billSummary  = document.getElementById('billSummary');
        const totalBayar   = document.getElementById('totalBayar');
        const btnBayar     = document.getElementById('totalBayar');
        const activeTableRow = document.getElementById('activeTableRow');
        const activeTableName = document.getElementById('activeTableName');
        if (!container) return;

        // Dynamic enable/disable of Pisah Bill button
        const btnPisahBill = document.getElementById('btnPisahBill');
        if (btnPisahBill) {
            const totalQty = cart.reduce((sum, item) => sum + (parseInt(item.qty) || 0), 0);
            if (totalQty > 1) {
                btnPisahBill.removeAttribute('disabled');
                btnPisahBill.style.opacity = '1';
                btnPisahBill.style.pointerEvents = 'auto';
                btnPisahBill.classList.add('has-items');
            } else {
                btnPisahBill.setAttribute('disabled', 'true');
                btnPisahBill.style.opacity = '1';
                btnPisahBill.style.pointerEvents = 'none';
                btnPisahBill.classList.remove('has-items');
            }
        }

        if (cart.length === 0) {
            container.innerHTML = `
                <div class="cart-empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                    <p>Tidak Ada Produk</p>
                </div>`;
            if (billSummary) billSummary.style.display = 'none';
            if (totalBayar) { totalBayar.innerText = 'Bayar Rp 0'; totalBayar.classList.remove('has-items'); }
            
            // Hapus table terpilih jika keranjang kosong
            localStorage.removeItem('active_table');
            if (activeTableRow) activeTableRow.style.display = 'none';
            return;
        }

        // Tampilkan info meja jika aktif
        const activeTable = JSON.parse(localStorage.getItem('active_table'));
        if (activeTable && activeTableRow && activeTableName) {
            activeTableName.innerText = activeTable.name;
            activeTableRow.style.display = 'flex';
        } else if (activeTableRow) {
            activeTableRow.style.display = 'none';
        }

        container.innerHTML = '';
        let subtotal = 0;
        let totalDiscount = 0;

        cart.forEach(item => {
            subtotal += item.total_price;

            // Hitung diskon per item
            if (item.discounts && item.discounts.length > 0) {
                item.discounts.forEach(d => {
                    if (d.type === 'percentage') {
                        totalDiscount += item.total_price * (d.value / 100);
                    } else {
                        totalDiscount += parseFloat(d.value);
                    }
                });
            }

            const modText = item.modifiers && item.modifiers.length > 0
                ? item.modifiers.map(m => m.name).join(', ')
                : '';

            const isCustom = String(item.product_id).startsWith('custom_');
            const itemEl   = document.createElement('div');
            itemEl.className  = 'cart-item';
            itemEl.dataset.cartId = item.id;

            if (isCustom) {
                // === Custom Amount: tidak bisa diedit, tidak ada qty ===
                itemEl.innerHTML = `
                    <div class="cart-item-body">
                        <div class="cart-item-name">${item.name}</div>
                    </div>
                    <div class="cart-item-right">
                        <div class="cart-item-qty-price">
                            <span>Rp ${item.total_price.toLocaleString('id-ID')}</span>
                            <button class="btn-remove-cart" onclick="removeFromCart(${item.id})">&times;</button>
                        </div>
                    </div>
                `;
                // Tidak ada click handler edit — custom amount tidak bisa diedit
            } else {
                // === Item biasa: bisa diklik untuk edit ===
                itemEl.title = 'Klik untuk edit pesanan';
                itemEl.innerHTML = `
                    <div class="cart-item-body">
                        <div class="cart-item-name">${item.name}</div>
                        ${modText ? `<div class="cart-item-mods">${modText}</div>` : ''}
                    </div>
                    <div class="cart-item-right">
                        <div class="cart-item-qty-price">
                            <span class="cart-item-qty">x${item.qty}</span>
                            <span>Rp ${item.total_price.toLocaleString('id-ID')}</span>
                            <button class="btn-remove-cart" onclick="removeFromCart(${item.id})">&times;</button>
                        </div>
                    </div>
                `;
                itemEl.addEventListener('click', (e) => {
                    if (e.target.closest('.btn-remove-cart')) return;
                    openDetailModal(item.product_id, item);
                });
            }

            container.appendChild(itemEl);
        });

        // -----------------------------------------------------
        // Hitung Service Charge (Per Item Net) & Pajak (Global)
        // -----------------------------------------------------
        const afterDiscount = subtotal - totalDiscount;

        let extraCharge = 0;
        let activeChargeLabel = '';

        if (window.POS_CONFIG && window.POS_CONFIG.serviceCharges) {
            cart.forEach(item => {
                let itemTotalDiscount = 0;
                if (item.discounts && item.discounts.length > 0) {
                    item.discounts.forEach(d => {
                        if (d.type === 'percentage') itemTotalDiscount += item.total_price * (d.value / 100);
                        else itemTotalDiscount += parseFloat(d.value);
                    });
                }
                let itemNet = item.total_price - itemTotalDiscount;

                // Cari service charge yang cocok dengan order_type item ini
                let chargeConfig = window.POS_CONFIG.serviceCharges.find(c => {
                    let typeKeyword = item.order_type === 'dine-in' ? 'dine' : item.order_type;
                    return c.name.toLowerCase().includes(typeKeyword);
                });

                if (chargeConfig) {
                    if (chargeConfig.type === 'percentage') {
                        extraCharge += itemNet * (parseFloat(chargeConfig.value) / 100);
                    } else {
                        // Karena logic nominal per item bisa salah jika nominal harusnya per order, 
                        // kita asumsikan di DB biasanya persentase. Jika nominal, tambahkan per item.
                        extraCharge += parseFloat(chargeConfig.value);
                    }
                    
                    // Set nama label (jika beda-beda ambil yang terakhir atau generic)
                    let currentLabel = `${chargeConfig.name} (${chargeConfig.type === 'percentage' ? parseFloat(chargeConfig.value) + '%' : 'Rp ' + parseFloat(chargeConfig.value).toLocaleString('id-ID')})`;
                    if (!activeChargeLabel) activeChargeLabel = currentLabel;
                    else if (activeChargeLabel !== currentLabel) activeChargeLabel = 'Service Charges';
                }
            });
        }

        let ppn = 0;
        let taxLabelText = 'Pajak';
        if (window.POS_CONFIG && window.POS_CONFIG.taxes && window.POS_CONFIG.taxes.length > 0) {
            let taxConfig = window.POS_CONFIG.taxes[0]; // Ambil pajak aktif pertama
            if (taxConfig.type === 'percentage') {
                ppn = afterDiscount * (parseFloat(taxConfig.value) / 100);
                taxLabelText = `${taxConfig.name} (${parseFloat(taxConfig.value)}%)`;
            } else {
                ppn = parseFloat(taxConfig.value);
                taxLabelText = taxConfig.name;
            }
        } else {
            // Fallback
            ppn = afterDiscount * 0.10;
            taxLabelText = 'PPN Resto (10%)';
        }

        const grandTotal = afterDiscount + extraCharge + ppn;
        currentGrandTotal = grandTotal;

        // Update Summary
        if (billSummary) {
            billSummary.style.display = 'block';

            const discRow     = document.getElementById('summaryDiscountRow');
            const discVal     = document.getElementById('summaryDiscount');
            const subtotalEl  = document.getElementById('summarySubtotal');
            const chargeRow   = document.getElementById('takeawayChargeRow');
            const chargeVal   = document.getElementById('summaryTakeawayCharge');
            const chargeLabel = document.getElementById('serviceChargeLabel');
            const taxRow      = document.getElementById('summaryTax');
            const taxLabel    = document.getElementById('taxLabel');
            const totalEl     = document.getElementById('summaryTotal');

            if (totalDiscount > 0) {
                if (discRow) discRow.style.display = 'flex';
                if (discVal) discVal.innerText = `(Rp ${totalDiscount.toLocaleString('id-ID')})`;
            } else {
                if (discRow) discRow.style.display = 'none';
            }

            if (subtotalEl) subtotalEl.innerText = `Rp ${afterDiscount.toLocaleString('id-ID')}`;

            if (extraCharge > 0) {
                if (chargeRow) chargeRow.style.display = 'flex';
                if (chargeVal) chargeVal.innerText = `Rp ${extraCharge.toLocaleString('id-ID')}`;
                if (chargeLabel) chargeLabel.innerText = activeChargeLabel;
            } else {
                if (chargeRow) chargeRow.style.display = 'none';
            }

            if (taxLabel) taxLabel.innerText = taxLabelText;
            if (taxRow)   taxRow.innerText   = `Rp ${ppn.toLocaleString('id-ID')}`;
            if (totalEl)  totalEl.innerText  = `Rp ${grandTotal.toLocaleString('id-ID')}`;
        }

        if (totalBayar) {
            totalBayar.innerText = `Bayar Rp ${grandTotal.toLocaleString('id-ID')}`;
            totalBayar.classList.add('has-items');
        }
    }

    // Kosongkan keranjang
    const btnKosongkan = document.getElementById('btnKosongkanKeranjang');
    if (btnKosongkan) {
        btnKosongkan.addEventListener('click', () => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Kosongkan Keranjang?',
                    text: "Semua produk yang telah dipilih akan dihapus.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#c43626',
                    cancelButtonColor: '#9ca3af',
                    confirmButtonText: 'Ya, Kosongkan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    heightAuto: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        cart = [];
                        saveCart();
                        renderCart();
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Keranjang telah dikosongkan.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false,
                            heightAuto: false
                        });
                    }
                });
            } else {
                if (confirm('Kosongkan seluruh keranjang belanja?')) {
                    cart = [];
                    saveCart();
                    renderCart();
                }
            }
        });
    }

    // =========================================================
    // 8. MODAL PILIH TIPE PENJUALAN (header "Dine In ⌄")
    // =========================================================
    const modalOrderType     = document.getElementById('modalOrderType');
    const btnOrderTypeSel    = document.getElementById('btnOrderTypeSelector');
    const btnOrderTypeBatal  = document.getElementById('btnOrderTypeBatal');
    const btnOrderTypeSelesai= document.getElementById('btnOrderTypeSelesai');
    const labelOrderType     = document.getElementById('labelOrderType');

    // Label mapping untuk display
    const orderTypeLabels = {
        'dine-in':    'Dine In',
        'takeaway':   'Takeaway',
        'gofood':     'GoFood',
        'grabfood':   'GrabFood',
        'shopeefood': 'ShopeeFood',
    };

    let selectedOrderType = 'dine-in'; // default global

    function openOrderTypeModal() {
        if (!modalOrderType) return;
        modalOrderType.querySelectorAll('.option-btn[data-type]').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.type === selectedOrderType);
        });
        modalOrderType.style.display = 'flex';
    }

    function closeOrderTypeModal() {
        if (modalOrderType) modalOrderType.style.display = 'none';
    }

    // Buka modal saat klik selector
    if (btnOrderTypeSel) btnOrderTypeSel.addEventListener('click', openOrderTypeModal);

    // Pilih tipe di dalam modal (single select)
    if (modalOrderType) {
        modalOrderType.querySelectorAll('.option-btn[data-type]').forEach(btn => {
            btn.addEventListener('click', () => {
                modalOrderType.querySelectorAll('.option-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedOrderType = btn.dataset.type;
            });
        });
    }

    // Batal — tutup tanpa simpan
    if (btnOrderTypeBatal) btnOrderTypeBatal.addEventListener('click', closeOrderTypeModal);

    // Selesai — terapkan ke semua item di cart
    if (btnOrderTypeSelesai) {
        btnOrderTypeSelesai.addEventListener('click', () => {
            // Update semua cart item (kecuali custom amount)
            cart = cart.map(item => ({
                ...item,
                order_type: String(item.product_id).startsWith('custom_')
                    ? item.order_type  // custom amount tidak ikut diubah
                    : selectedOrderType,
            }));
            saveCart();
            renderCart();
            // Update label header
            if (labelOrderType) {
                labelOrderType.innerText = orderTypeLabels[selectedOrderType] || selectedOrderType;
            }
            closeOrderTypeModal();
        });
    }

    // Tutup modal jika klik backdrop
    if (modalOrderType) {
        modalOrderType.addEventListener('click', e => {
            if (e.target === modalOrderType) closeOrderTypeModal();
        });
    }



    // =========================================================
    // 8. MODAL DETAIL PRODUK
    // =========================================================
    const modalDetail    = document.getElementById('modalProductDetail');
    const btnBatalDetail = document.getElementById('btnBatalDetail');
    const btnSimpan      = document.getElementById('btnSimpanDetail');
    let currentProduct   = null;
    let editingCartId    = null; // null = mode tambah, diisi = mode edit

    function closeDetailModal() {
        if (modalDetail) modalDetail.style.display = 'none';
        currentProduct = null; 
        editingCartId  = null;
        // Reset label tombol Simpan ke default
        if (btnSimpan) btnSimpan.innerText = 'Simpan';
    }

    // openDetailModal — bisa dipanggil tanpa cartItem (mode tambah)
    //                   atau dengan cartItem (mode edit, pre-fill state)
    function openDetailModal(productId, cartItemToEdit = null) {
        fetch(`/pos/product-detail/${productId}`)
            .then(r => r.json())
            .then(product => {
                currentProduct = product;
                editingCartId  = cartItemToEdit ? cartItemToEdit.id : null;

                // Set header
                document.getElementById('detailProductName').innerText  = product.name;
                document.getElementById('detailProductPrice').innerText = `Rp ${parseInt(product.price).toLocaleString('id-ID')}`;

                // Label tombol Simpan
                if (btnSimpan) btnSimpan.innerText = cartItemToEdit ? 'Update' : 'Simpan';

                // === QTY ===
                document.getElementById('inputQty').value = cartItemToEdit ? cartItemToEdit.qty : 1;

                // === ORDER TYPE ===
                const targetOrderType = cartItemToEdit ? cartItemToEdit.order_type : 'dine-in';
                document.querySelectorAll('input[name="modal_order_type"]').forEach(r => r.checked = (r.value === targetOrderType));
                document.querySelectorAll('.order-type-section .option-btn').forEach(b => b.classList.remove('active'));
                const activeOrderBtn = document.querySelector(`.order-type-section .option-btn input[value="${targetOrderType}"]`);
                if (activeOrderBtn) activeOrderBtn.closest('.option-btn').classList.add('active');

                // === DISKON — pre-fill jika mode edit ===
                document.querySelectorAll('.discount-cb').forEach(cb => {
                    if (cartItemToEdit && cartItemToEdit.discounts) {
                        cb.checked = cartItemToEdit.discounts.some(d => d.id == cb.dataset.id);
                    } else {
                        cb.checked = false;
                    }
                });

                // === MODIFIER GROUPS ===
                const container = document.getElementById('modifierGroupsContainer');
                container.innerHTML = '';

                if (product.groups && product.groups.length > 0) {
                    product.groups.forEach(group => {
                        const isSingle   = group.selection_type === 'single';
                        const hintText   = isSingle ? 'PILIH SATU' : 'PILIH BANYAK';
                        const inputType  = isSingle ? 'radio' : 'checkbox';
                        const groupName  = `mod_${group.group_name.replace(/\s+/g, '_')}`;

                        const groupEl = document.createElement('div');
                        groupEl.className = 'modifier-group';
                        groupEl.innerHTML = `
                            <div class="modifier-group-label">
                                ${group.group_name}
                                <span class="modifier-group-hint">| ${hintText}</span>
                            </div>
                            <div class="option-grid" data-group="${group.group_name}" data-type="${group.selection_type}">
                                ${group.options.map(opt => `
                                    <label class="option-btn" data-modifier-id="${opt.modifier_id}" data-name="${opt.name}" data-price="${opt.extra_price}">
                                        <input type="${inputType}" name="${groupName}" value="${opt.modifier_id}" hidden>
                                        ${opt.name}${opt.extra_price > 0 ? ` <small style="font-size:0.7rem;opacity:0.7">(+Rp ${parseInt(opt.extra_price).toLocaleString('id-ID')})</small>` : ''}
                                    </label>
                                `).join('')}
                            </div>
                        `;
                        container.appendChild(groupEl);

                        // Pre-select modifier jika mode edit
                        const grid = groupEl.querySelector('.option-grid');
                        grid.querySelectorAll('.option-btn').forEach(btn => {
                            const modId = btn.dataset.modifierId;
                            if (cartItemToEdit && cartItemToEdit.modifiers) {
                                const isSelected = cartItemToEdit.modifiers.some(m => m.id == modId);
                                if (isSelected) {
                                    btn.classList.add('active');
                                    btn.querySelector('input').checked = true;
                                }
                            }
                            // Click handler
                            btn.addEventListener('click', () => {
                                if (isSingle) {
                                    grid.querySelectorAll('.option-btn').forEach(b => b.classList.remove('active'));
                                    btn.classList.add('active');
                                    btn.querySelector('input').checked = true;
                                } else {
                                    btn.classList.toggle('active');
                                    btn.querySelector('input').checked = btn.classList.contains('active');
                                }
                            });
                        });
                    });
                } else {
                    container.innerHTML = '<p style="padding:16px 20px;color:#9ca3af;font-size:0.85rem;">Tidak ada pilihan modifier untuk produk ini.</p>';
                }

                modalDetail.style.display = 'flex';
            })
            .catch(() => showToast('Gagal memuat detail produk', true));
    }

    // Klik produk card (Favorit)
    document.querySelectorAll('.product-card:not(.empty-card):not(.edit-mode)').forEach(card => {
        card.addEventListener('click', (e) => {
            if (isEditMode) return;
            const id = card.dataset.productId;
            if (id) openDetailModal(id);
        });
    });

    // Klik list item (Library)
    document.querySelectorAll('#libraryListContent .list-item').forEach(item => {
        item.addEventListener('click', () => {
            const id = item.dataset.productId;
            if (id) openDetailModal(id);
        });
    });

    // Tutup modal
    if (btnBatalDetail) btnBatalDetail.addEventListener('click', closeDetailModal);
    window.addEventListener('click', e => { if (e.target === modalDetail) closeDetailModal(); });

    // Qty controls
    const inputQty = document.getElementById('inputQty');
    document.getElementById('btnMinus')?.addEventListener('click', () => {
        const v = parseInt(inputQty.value);
        if (v > 1) inputQty.value = v - 1;
    });
    document.getElementById('btnPlus')?.addEventListener('click', () => {
        const v = parseInt(inputQty.value);
        if (v < 99) inputQty.value = v + 1;
    });

    // Order type buttons dalam modal
    document.querySelectorAll('.order-type-section .option-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.order-type-section .option-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            btn.querySelector('input').checked = true;
        });
    });

    // SIMPAN / UPDATE ke keranjang
    if (btnSimpan) {
        btnSimpan.addEventListener('click', () => {
            if (!currentProduct) return;

            const qty = parseInt(inputQty.value) || 1;

            // Kumpulkan modifier yang dipilih
            const selectedMods = [];
            let extraPrice = 0;
            document.querySelectorAll('#modifierGroupsContainer .option-btn.active').forEach(btn => {
                selectedMods.push({
                    id:    btn.dataset.modifierId,
                    name:  btn.dataset.name,
                    price: parseFloat(btn.dataset.price) || 0
                });
                extraPrice += parseFloat(btn.dataset.price) || 0;
            });

            // Kumpulkan diskon yang dipilih
            const selectedDiscounts = [];
            document.querySelectorAll('.discount-cb:checked').forEach(cb => {
                selectedDiscounts.push({
                    id:    cb.dataset.id,
                    name:  cb.dataset.name,
                    type:  cb.dataset.type,
                    value: parseFloat(cb.dataset.value)
                });
            });

            // Order type
            const orderTypeInput = document.querySelector('input[name="modal_order_type"]:checked');
            const orderType = orderTypeInput ? orderTypeInput.value : 'dine-in';

            const unitPrice  = parseFloat(currentProduct.price) + extraPrice;
            const totalPrice = unitPrice * qty;

            if (editingCartId !== null) {
                // ===== MODE EDIT: update item yang sudah ada =====
                const idx = cart.findIndex(i => i.id === editingCartId);
                if (idx !== -1) {
                    cart[idx] = {
                        ...cart[idx],           // pertahankan id & product_id
                        img_url:     currentProduct.img_url,
                        modifiers:   selectedMods,
                        discounts:   selectedDiscounts,
                        order_type:  orderType,
                        qty:         qty,
                        unit_price:  unitPrice,
                        total_price: totalPrice,
                    };
                }
                saveCart();
                renderCart();
                closeDetailModal();
                showToast(`${currentProduct.name} berhasil diupdate`);
            } else {
                // ===== MODE TAMBAH: push item baru =====
                const cartItem = {
                    id:          Date.now(),
                    product_id:  currentProduct.product_id,
                    name:        currentProduct.name,
                    img_url:     currentProduct.img_url,
                    base_price:  currentProduct.price,
                    modifiers:   selectedMods,
                    discounts:   selectedDiscounts,
                    order_type:  orderType,
                    qty:         qty,
                    unit_price:  unitPrice,
                    total_price: totalPrice,
                };
                cart.push(cartItem);
                saveCart();
                renderCart();
                closeDetailModal();
                showToast(`${currentProduct.name} ditambahkan ke keranjang`);
            }
        });
    }

    // =========================================================
    // 9. MODAL LOYALTY (PISAH BILL / MEMBER)
    // =========================================================
    const btnBayar = document.getElementById('totalBayar');
    const modalLoyalty = document.getElementById('modalLoyalty');
    const btnBatalLoyalty = document.getElementById('btnBatalLoyalty');
    const btnLewatiLoyalty = document.getElementById('btnLewatiLoyalty');
    
    if (btnBayar && modalLoyalty) {
        btnBayar.addEventListener('click', () => {
            if (cart.length === 0) {
                showToast('Keranjang masih kosong', true);
                return;
            }
            modalLoyalty.style.display = 'flex';
        });
    }

    if (btnBatalLoyalty && modalLoyalty) {
        btnBatalLoyalty.addEventListener('click', () => {
            modalLoyalty.style.display = 'none';
        });
    }

    if (btnLewatiLoyalty && modalLoyalty) {
        btnLewatiLoyalty.addEventListener('click', () => {
            modalLoyalty.style.display = 'none';
            // Tampilkan modal pilih pelayan
            const modalStaff = document.getElementById('modalStaff');
            if (modalStaff) {
                modalStaff.style.display = 'flex';
            } else {
                showToast('Lanjut ke Pembayaran...');
            }
        });
    }

    // =========================================================
    // 10. MODAL STAFF (PILIH PELAYAN) & PEMBAYARAN
    // =========================================================
    const modalStaff = document.getElementById('modalStaff');
    const btnBackToLoyalty = document.getElementById('btnBackToLoyalty');
    const btnLewatiStaff = document.getElementById('btnLewatiStaff');
    const modalPayment = document.getElementById('modalPayment');
    const btnBatalPayment = document.getElementById('btnBatalPayment');
    const paymentTotalDisplay = document.getElementById('paymentTotalDisplay');
    const paymentServerName = document.getElementById('paymentServerName');
    
    let selectedStaffName = '-';

    function openPaymentModal() {
        if (modalStaff) modalStaff.style.display = 'none';
        if (modalPayment) {
            const activeTotal = window.splitBillActive ? window.splitBillSummaryData.grandTotal : currentGrandTotal;
            if (paymentTotalDisplay) paymentTotalDisplay.innerText = `Rp ${activeTotal.toLocaleString('id-ID')}`;
            if (paymentServerName) paymentServerName.innerText = `| ${selectedStaffName}`;
            
            const btnUangPas = document.getElementById('btnUangPas');
            let next1k = Math.ceil(activeTotal / 1000) * 1000;
            if (next1k === 0) next1k = activeTotal;
            if (btnUangPas) {
                btnUangPas.innerText = `Rp ${next1k.toLocaleString('id-ID')}`;
                btnUangPas.dataset.val = next1k;
            }

            const inputTunai = document.getElementById('inputTunaiManual');
            if (inputTunai) {
                inputTunai.placeholder = `Rp ${activeTotal.toLocaleString('id-ID')}`;
                inputTunai.value = ''; // Reset
            }

            const btnUang50k = document.getElementById('btnUang50k');
            if (btnUang50k) {
                let next50k = Math.ceil(activeTotal / 50000) * 50000;
                if (next50k === next1k && activeTotal > 0) next50k += 50000;
                if (next50k === 0) next50k = 50000;
                btnUang50k.innerText = `Rp ${next50k.toLocaleString('id-ID')}`;
                btnUang50k.dataset.val = next50k;
            }

            // Hapus seleksi sebelumnya
            document.querySelectorAll('.payment-modal-body .btn-payment-outline').forEach(b => b.classList.remove('active'));
            
            modalPayment.style.display = 'flex';
        }
    }

    // Interaksi Tombol Pembayaran
    const paymentButtons = document.querySelectorAll('.payment-modal-body .btn-payment-outline');
    paymentButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            // Abaikan tombol Invoice jika memang itu aksi khusus (sementara ikut active gapapa)
            paymentButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const inputTunai = document.getElementById('inputTunaiManual');
            if (btn.id === 'btnUangPas') {
                if (inputTunai) inputTunai.value = btn.dataset.val;
            } else if (btn.id === 'btnUang50k') {
                if (inputTunai) inputTunai.value = btn.dataset.val;
            } else {
                if (inputTunai) inputTunai.value = ''; // Reset tunai jika milih e-wallet/lainnya
            }
        });
    });

    const paymentInputs = document.querySelectorAll('.payment-modal-body input');
    paymentInputs.forEach(input => {
        input.addEventListener('focus', () => {
            paymentButtons.forEach(b => b.classList.remove('active'));
            if (input.id === 'inputTunaiManual') {
                // Mungkin opsional: tidak harus melakukan sesuatu
            }
        });
    });

    function resetStaffSelection() {
        selectedStaffName = '-';
        const staffItems = document.querySelectorAll('.staff-list-item');
        if(staffItems) staffItems.forEach(i => i.style.background = '');
        
        if (btnLewatiStaff) {
            btnLewatiStaff.innerText = 'Lewati';
            btnLewatiStaff.style.backgroundColor = 'transparent';
            btnLewatiStaff.style.color = 'var(--primary)';
            btnLewatiStaff.style.border = '1px solid var(--primary)';
        }
    }

    if (btnBackToLoyalty && modalStaff) {
        btnBackToLoyalty.addEventListener('click', () => {
            modalStaff.style.display = 'none';
            resetStaffSelection();
            if (modalLoyalty) modalLoyalty.style.display = 'flex';
        });
    }

    if (btnLewatiStaff && modalStaff) {
        btnLewatiStaff.addEventListener('click', () => {
            openPaymentModal();
        });
    }

    // Klik pada salah satu staff
    const staffItems = document.querySelectorAll('.staff-list-item');
    staffItems.forEach(item => {
        item.addEventListener('click', () => {
            // Hapus style active dari yang lain (opsional)
            staffItems.forEach(i => i.style.background = '');
            item.style.background = '#f3f4f6';

            selectedStaffName = item.dataset.name;
            
            // Ubah tombol "Lewati" menjadi "Konfirmasi"
            if (btnLewatiStaff) {
                btnLewatiStaff.innerText = 'Konfirmasi';
                btnLewatiStaff.style.backgroundColor = 'var(--primary)';
                btnLewatiStaff.style.color = 'white';
                btnLewatiStaff.style.border = 'none';
            }
        });
    });

    if (btnBatalPayment && modalPayment) {
        btnBatalPayment.addEventListener('click', () => {
            modalPayment.style.display = 'none';
        });
    }

    window.addEventListener('click', (e) => {
        if (e.target === modalLoyalty) {
            modalLoyalty.style.display = 'none';
            window.splitBillActive = false;
        }
        if (e.target === modalStaff) modalStaff.style.display = 'none';
        if (e.target === modalPayment) modalPayment.style.display = 'none';
    });

    const btnProsesPayment = document.getElementById('btnProsesPayment');
    const modalTunaiSuccess = document.getElementById('modalTunaiSuccess');
    const modalQris = document.getElementById('modalQris');
    const btnBatalQris = document.getElementById('btnBatalQris');
    const modalBatalQrisConfirm = document.getElementById('modalBatalQrisConfirm');
    const btnCancelBatalQris = document.getElementById('btnCancelBatalQris');
    const btnConfirmBatalQris = document.getElementById('btnConfirmBatalQris');
    const btnTransaksiBaru = document.getElementById('btnTransaksiBaru');
    let qrisInterval = null;

    if (btnProsesPayment) {
        btnProsesPayment.addEventListener('click', () => {
            const activeBtn = document.querySelector('.payment-modal-body .btn-payment-outline.active');
            const inputTunaiManual = document.getElementById('inputTunaiManual');
            
            let tunaiValue = 0;
            if (inputTunaiManual && inputTunaiManual.value) {
                tunaiValue = parseInt(inputTunaiManual.value.toString().replace(/\D/g, '')) || 0;
            }

            let isEwallet = activeBtn && activeBtn.dataset.method === 'ewallet';
            const activeTotal = window.splitBillActive ? window.splitBillSummaryData.grandTotal : currentGrandTotal;

            if (isEwallet) {
                modalPayment.style.display = 'none';
                document.getElementById('qrisTotalHargaDisplay').innerText = `Rp ${activeTotal.toLocaleString('id-ID')}`;
                modalQris.style.display = 'flex';

                let secondsLeft = 60;
                const textEl = document.getElementById('qrisCountdownText');
                const circle = document.getElementById('qrisProgressCircle');
                if (textEl && circle) {
                    textEl.innerText = secondsLeft;
                    circle.style.strokeDashoffset = '0';
                    clearInterval(qrisInterval);
                    qrisInterval = setInterval(() => {
                        secondsLeft--;
                        if (secondsLeft < 0) {
                            clearInterval(qrisInterval);
                            return;
                        }
                        textEl.innerText = secondsLeft;
                        const offset = 226 - (secondsLeft / 60) * 226;
                        circle.style.strokeDashoffset = offset;
                    }, 1000);
                }
            } else {
                let bayar = tunaiValue;
                if (bayar < activeTotal) {
                    showToast('Nominal pembayaran kurang dari total!', true);
                    return;
                }
                
                modalPayment.style.display = 'none';
                document.getElementById('tunaiSuccessBayar').innerText = `Rp ${bayar.toLocaleString('id-ID')}`;
                const kembalian = bayar - activeTotal;
                document.getElementById('tunaiSuccessKembalian').innerText = `Rp ${kembalian.toLocaleString('id-ID')}`;
                modalTunaiSuccess.style.display = 'flex';
            }
        });
    }

    const btnBatalTunai = document.getElementById('btnBatalTunai');
    
    if (btnBatalTunai) {
        btnBatalTunai.addEventListener('click', () => {
            if(modalBatalQrisConfirm) modalBatalQrisConfirm.style.display = 'flex';
        });
    }

    if (btnBatalQris) {
        btnBatalQris.addEventListener('click', () => {
            if(modalBatalQrisConfirm) modalBatalQrisConfirm.style.display = 'flex';
        });
    }

    if (btnCancelBatalQris) {
        btnCancelBatalQris.addEventListener('click', () => {
            if(modalBatalQrisConfirm) modalBatalQrisConfirm.style.display = 'none';
        });
    }

    if (btnConfirmBatalQris) {
        btnConfirmBatalQris.addEventListener('click', () => {
            clearInterval(qrisInterval);
            if(modalBatalQrisConfirm) modalBatalQrisConfirm.style.display = 'none';
            if(modalQris) modalQris.style.display = 'none';
            if(modalTunaiSuccess) modalTunaiSuccess.style.display = 'none';
            if(modalPayment) modalPayment.style.display = 'flex';
        });
    }

    if (btnTransaksiBaru) {
        btnTransaksiBaru.addEventListener('click', () => {
            if(modalTunaiSuccess) modalTunaiSuccess.style.display = 'none';
            
            if (window.splitBillActive) {
                // Deduct selected split quantities from main cart!
                cart = cart.map(item => {
                    const splitState = selectedSplitItems[item.id];
                    if (splitState && splitState.selected) {
                        item.qty -= splitState.qty;
                        item.total_price = item.qty * item.unit_price;
                    }
                    return item;
                }).filter(item => item.qty > 0);
                
                saveCart();
                window.splitBillActive = false;
                
                if (cart.length === 0) {
                    localStorage.removeItem('active_table');
                }
                window.location.reload();
            } else {
                cart = [];
                saveCart();
                localStorage.removeItem('active_table');
                window.location.reload();
            }
        });
    }

    // =========================================================
    // 10A. PISAH BILL (SPLIT BILL) ENGINE
    // =========================================================
    const modalPisahBill = document.getElementById('modalPisahBill');
    const btnPisahBill = document.getElementById('btnPisahBill');
    const btnPisahBillTutup = document.getElementById('btnPisahBillTutup');
    const btnPisahBillPisahkan = document.getElementById('btnPisahBillPisahkan');
    const pisahBillAmountDisplay = document.getElementById('pisahBillAmountDisplay');
    const pisahBillProductList = document.getElementById('pisahBillProductList');
    const pisahBillBreakdown = document.getElementById('pisahBillBreakdown');

    let selectedSplitItems = {};

    if (btnPisahBill && modalPisahBill) {
        btnPisahBill.addEventListener('click', () => {
            if (cart.length === 0) {
                showToast('Keranjang masih kosong', true);
                return;
            }
            openPisahBillModal();
        });
    }

    if (btnPisahBillTutup && modalPisahBill) {
        btnPisahBillTutup.addEventListener('click', () => {
            modalPisahBill.style.display = 'none';
            // Bersihkan state split
            window.splitBillActive = false;
            window.splitBillSummaryData = null;
            selectedSplitItems = {};
            // renderCart() sudah menghitung diskon + pajak + service charge
            // dan memperbarui tombol "Bayar" dengan total yang benar
            renderCart();
        });
    }

    if (btnPisahBillPisahkan && modalPisahBill && modalLoyalty) {
        btnPisahBillPisahkan.addEventListener('click', () => {
            const hasSelection = Object.values(selectedSplitItems).some(s => s.selected && s.qty > 0);
            if (!hasSelection) {
                showToast('Pilih minimal satu produk untuk dipisahkan', true);
                return;
            }

            window.splitBillActive = true;
            modalPisahBill.style.display = 'none';

            const titleEl = modalLoyalty.querySelector('.loyalty-modal-title');
            if (titleEl) titleEl.innerText = 'Pisah Bill';
            modalLoyalty.style.display = 'flex';
        });
    }

    if (btnBatalLoyalty && modalLoyalty) {
        btnBatalLoyalty.addEventListener('click', () => {
            modalLoyalty.style.display = 'none';
            window.splitBillActive = false;
            window.splitBillSummaryData = null;
            selectedSplitItems = {};
            // renderCart() menghitung ulang total termasuk diskon + pajak
            renderCart();
        });
    }

    function openPisahBillModal() {
        selectedSplitItems = {};
        cart.forEach(item => {
            selectedSplitItems[item.id] = {
                qty: 1,
                selected: false,
                maxQty: item.qty
            };
        });

        renderPisahBillList();
        calculateSplitBill();
        modalPisahBill.style.display = 'flex';
    }

    function renderPisahBillList() {
        if (!pisahBillProductList) return;
        pisahBillProductList.innerHTML = '';

        const typeLabels = {
            'dine-in': 'Dine In',
            'takeaway': 'Take Away',
            'gofood': 'GoFood',
            'grabfood': 'GrabFood',
            'shopeefood': 'ShopeeFood'
        };

        const groups = {};
        cart.forEach(item => {
            const type = item.order_type || 'dine-in';
            if (!groups[type]) groups[type] = [];
            groups[type].push(item);
        });

        for (const [type, items] of Object.entries(groups)) {
            const header = document.createElement('div');
            header.className = 'pisah-bill-group-header';
            header.innerText = typeLabels[type] || type.toUpperCase();
            pisahBillProductList.appendChild(header);

            items.forEach(item => {
                const splitState = selectedSplitItems[item.id] || { qty: 1, selected: false };
                let imgSrc = '/images/default.jpg';
                if (item.img_url) {
                    imgSrc = item.img_url.startsWith('http') || item.img_url.startsWith('/') ? item.img_url : '/' + item.img_url;
                }

                const row = document.createElement('div');
                row.className = 'pisah-bill-item-row';
                row.innerHTML = `
                    <div class="pisah-bill-item-left">
                        <img src="${imgSrc}" class="pisah-bill-item-img" alt="${item.name}">
                        <div class="pisah-bill-item-info">
                            <span class="pisah-bill-item-name">${item.name}</span>
                            <span class="pisah-bill-item-price">Rp ${item.unit_price.toLocaleString('id-ID')}</span>
                        </div>
                    </div>
                    <div class="pisah-bill-item-right">
                        <div class="pisah-bill-qty-pill">
                            <button class="pisah-bill-qty-btn minus-btn ${splitState.selected ? 'active' : ''}">-</button>
                            <span class="pisah-bill-qty-val">${splitState.qty}</span>
                            <button class="pisah-bill-qty-btn plus-btn ${splitState.selected ? 'active' : ''}">+</button>
                        </div>
                        <div class="pisah-bill-checkbox-circle ${splitState.selected ? 'selected' : ''}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </div>
                `;

                const qtyValSpan = row.querySelector('.pisah-bill-qty-val');
                const minusBtn = row.querySelector('.minus-btn');
                const plusBtn = row.querySelector('.plus-btn');
                const checkboxCircle = row.querySelector('.pisah-bill-checkbox-circle');

                const updateRowVisualState = () => {
                    const activeState = selectedSplitItems[item.id];
                    if (activeState.selected) {
                        checkboxCircle.classList.add('selected');
                        minusBtn.classList.add('active');
                        plusBtn.classList.add('active');
                    } else {
                        checkboxCircle.classList.remove('selected');
                        minusBtn.classList.remove('active');
                        plusBtn.classList.remove('active');
                    }
                    qtyValSpan.innerText = activeState.qty;
                    calculateSplitBill();
                };

                checkboxCircle.addEventListener('click', () => {
                    selectedSplitItems[item.id].selected = !selectedSplitItems[item.id].selected;
                    updateRowVisualState();
                });

                minusBtn.addEventListener('click', () => {
                    const activeState = selectedSplitItems[item.id];
                    if (activeState.qty > 1) {
                        activeState.qty--;
                    } else if (activeState.qty === 1 && activeState.selected) {
                        activeState.selected = false;
                    }
                    updateRowVisualState();
                });

                plusBtn.addEventListener('click', () => {
                    const activeState = selectedSplitItems[item.id];
                    if (activeState.qty < activeState.maxQty) {
                        activeState.qty++;
                        activeState.selected = true;
                    } else {
                        showToast(`Jumlah maksimal adalah ${activeState.maxQty}`);
                    }
                    updateRowVisualState();
                });

                pisahBillProductList.appendChild(row);
            });
        }
    }

    function calculateSplitBill() {
        let splitSubtotal = 0;
        let splitDiscount = 0;
        let splitCharge = 0;
        let activeChargeLabel = 'Biaya Tambahan';
        
        cart.forEach(item => {
            const splitState = selectedSplitItems[item.id];
            if (splitState && splitState.selected && splitState.qty > 0) {
                const itemQty = splitState.qty;
                const itemSubtotal = item.unit_price * itemQty;
                splitSubtotal += itemSubtotal;
                
                let itemTotalDiscount = 0;
                if (item.discounts && item.discounts.length > 0) {
                    item.discounts.forEach(d => {
                        if (d.type === 'percentage') {
                            itemTotalDiscount += itemSubtotal * (d.value / 100);
                        } else {
                            itemTotalDiscount += (parseFloat(d.value) / item.qty) * itemQty;
                        }
                    });
                }
                splitDiscount += itemTotalDiscount;
                
                let itemNet = itemSubtotal - itemTotalDiscount;
                if (window.POS_CONFIG && window.POS_CONFIG.serviceCharges) {
                    let chargeConfig = window.POS_CONFIG.serviceCharges.find(c => {
                        let typeKeyword = item.order_type === 'dine-in' ? 'dine' : item.order_type;
                        return c.name.toLowerCase().includes(typeKeyword);
                    });

                    if (chargeConfig) {
                        let chargeVal = 0;
                        if (chargeConfig.type === 'percentage') {
                            chargeVal = itemNet * (parseFloat(chargeConfig.value) / 100);
                        } else {
                            chargeVal = (parseFloat(chargeConfig.value) / item.qty) * itemQty;
                        }
                        splitCharge += chargeVal;
                        
                        let currentLabel = `${chargeConfig.name} (${chargeConfig.type === 'percentage' ? parseFloat(chargeConfig.value) + '%' : 'Rp ' + parseFloat(chargeConfig.value).toLocaleString('id-ID')})`;
                        if (!activeChargeLabel || activeChargeLabel === 'Biaya Tambahan') activeChargeLabel = currentLabel;
                        else if (activeChargeLabel !== currentLabel) activeChargeLabel = 'Service Charges';
                    }
                }
            }
        });
        
        const splitAfterDiscount = splitSubtotal - splitDiscount;
        
        let splitTax = 0;
        let taxLabelText = 'Pajak';
        if (window.POS_CONFIG && window.POS_CONFIG.taxes && window.POS_CONFIG.taxes.length > 0) {
            let taxConfig = window.POS_CONFIG.taxes[0];
            if (taxConfig.type === 'percentage') {
                splitTax = splitAfterDiscount * (parseFloat(taxConfig.value) / 100);
                taxLabelText = `${taxConfig.name} (${parseFloat(taxConfig.value)}%)`;
            } else {
                const mainAfterDiscount = cart.reduce((sum, item) => sum + (item.total_price), 0);
                splitTax = mainAfterDiscount > 0 ? parseFloat(taxConfig.value) * (splitAfterDiscount / mainAfterDiscount) : 0;
                taxLabelText = taxConfig.name;
            }
        } else {
            splitTax = splitAfterDiscount * 0.10;
            taxLabelText = 'PPN Resto (10%)';
        }
        
        const splitGrandTotal = splitAfterDiscount + splitCharge + splitTax;
        
        pisahBillAmountDisplay.innerText = `Rp ${Math.round(splitGrandTotal).toLocaleString('id-ID')}`;
        
        pisahBillBreakdown.innerHTML = `
            <div class="pisah-bill-summary-row">
                <div>
                    <div class="pisah-bill-summary-label">Diskon</div>
                    <div class="pisah-bill-summary-sublabel">Diskon VIP</div>
                </div>
                <span class="pisah-bill-summary-val" style="color: #c43626;">-Rp ${Math.round(splitDiscount).toLocaleString('id-ID')}</span>
            </div>
            <div class="pisah-bill-summary-row">
                <div>
                    <div class="pisah-bill-summary-label">Biaya Tambahan</div>
                    <div class="pisah-bill-summary-sublabel">${activeChargeLabel}</div>
                </div>
                <span class="pisah-bill-summary-val">Rp ${Math.round(splitCharge).toLocaleString('id-ID')}</span>
            </div>
            <div class="pisah-bill-summary-row">
                <div>
                    <div class="pisah-bill-summary-label">Pajak</div>
                    <div class="pisah-bill-summary-sublabel">${taxLabelText}</div>
                </div>
                <span class="pisah-bill-summary-val">Rp ${Math.round(splitTax).toLocaleString('id-ID')}</span>
            </div>
        `;

        window.splitBillSummaryData = {
            subtotal: splitSubtotal,
            discount: splitDiscount,
            charge: splitCharge,
            tax: splitTax,
            grandTotal: splitGrandTotal,
            chargeLabel: activeChargeLabel,
            taxLabel: taxLabelText
        };
    }

    // =========================================================
    // 10B. DENAH MEJA & PILIH MEJA SELECTION
    // =========================================================
    const btnSimpanBillTrigger = document.getElementById('btnSimpanBillTrigger');
    const overlayPilihMeja      = document.getElementById('overlayPilihMeja');
    const btnPilihMejaBatal     = document.getElementById('btnPilihMejaBatal');
    const areaTabBtns           = document.querySelectorAll('.area-tab-btn');
    const tableAvailableCards   = document.querySelectorAll('.table-card.table-available');
    const btnSimpanSebagaiBill  = document.getElementById('btnSimpanSebagaiBill');
    const btnMejaLanjutkan       = document.getElementById('btnMejaLanjutkan');

    const modalBillBaru         = document.getElementById('modalBillBaru');
    const btnBillBaruBatal      = document.getElementById('btnBillBaruBatal');
    const btnBillBaruKonfirmasi = document.getElementById('btnBillBaruKonfirmasi');
    const billBaruSubTitle      = document.getElementById('billBaruSubTitle');
    const inputPax              = document.getElementById('inputPax');
    const btnPaxMinus           = document.getElementById('btnPaxMinus');
    const btnPaxPlus            = document.getElementById('btnPaxPlus');
    const waiterSelectionList   = document.getElementById('waiterSelectionList');
    const waiterItems           = document.querySelectorAll('.waiter-item');

    const activeTableRow        = document.getElementById('activeTableRow');
    const activeTableName       = document.getElementById('activeTableName');
    const btnLihatMeja          = document.getElementById('btnLihatMeja');

    let selectedTable = null; // { id, name, capacity, area }
    let selectedWaiter = null; // { id, name }
    let pilihMejaMode = ''; // 'save' or 'continue'

    // Open Pilih Meja Overlay
    if (btnSimpanBillTrigger && overlayPilihMeja) {
        btnSimpanBillTrigger.addEventListener('click', () => {
            if (cart.length === 0) {
                showToast('Keranjang belanja kosong!', true);
                return;
            }
            // Reset state
            selectedTable = null;
            selectedWaiter = null;
            tableAvailableCards.forEach(c => c.classList.remove('active-table-card'));
            
            // Disable actions
            btnSimpanSebagaiBill.style.opacity = '0.6';
            btnSimpanSebagaiBill.style.pointerEvents = 'none';
            btnMejaLanjutkan.style.background = '#d1d5db';
            btnMejaLanjutkan.style.color = '#9ca3af';
            btnMejaLanjutkan.style.pointerEvents = 'none';

            overlayPilihMeja.style.display = 'flex';
        });
    }

    // Batal Pilih Meja
    if (btnPilihMejaBatal && overlayPilihMeja) {
        btnPilihMejaBatal.addEventListener('click', () => {
            overlayPilihMeja.style.display = 'none';
        });
    }

    // Switch Area Grid Tabs
    areaTabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            areaTabBtns.forEach(b => {
                b.classList.remove('active');
                b.style.background = 'transparent';
            });
            btn.classList.add('active');
            btn.style.background = '#922014';

            const areaId = btn.dataset.id;
            document.querySelectorAll('.area-grid-view').forEach(view => {
                view.style.display = view.id === `areaGridView_${areaId}` ? 'grid' : 'none';
            });
        });
    });

    // Table Selection inside Grid
    tableAvailableCards.forEach(card => {
        card.addEventListener('click', () => {
            tableAvailableCards.forEach(c => c.classList.remove('active-table-card'));
            card.classList.add('active-table-card');

            selectedTable = {
                id: card.dataset.id,
                name: card.dataset.name,
                capacity: card.dataset.capacity,
                area: card.dataset.area
            };

            // Enable action buttons
            btnSimpanSebagaiBill.style.opacity = '1';
            btnSimpanSebagaiBill.style.pointerEvents = 'auto';
            
            btnMejaLanjutkan.style.background = 'var(--primary)';
            btnMejaLanjutkan.style.color = '#fff';
            btnMejaLanjutkan.style.pointerEvents = 'auto';
        });
    });

    // Handle "Simpan Sebagai Bill" and "Lanjutkan"
    function openBillBaruModal(mode) {
        if (!selectedTable) return;
        pilihMejaMode = mode;
        if (billBaruSubTitle) {
            billBaruSubTitle.innerText = `${selectedTable.name} - ${selectedTable.area}`;
        }
        if (inputPax) inputPax.value = '';
        
        // Reset Waiter List
        selectedWaiter = null;
        waiterItems.forEach(item => {
            item.classList.remove('active-waiter');
            item.style.background = ''; // reset backup style inline if any
        });
        
        // Disable Konfirmasi Button
        btnBillBaruKonfirmasi.style.opacity = '0.5';
        btnBillBaruKonfirmasi.style.pointerEvents = 'none';

        modalBillBaru.style.display = 'flex';
    }

    if (btnSimpanSebagaiBill) {
        btnSimpanSebagaiBill.addEventListener('click', () => openBillBaruModal('save'));
    }
    if (btnMejaLanjutkan) {
        btnMejaLanjutkan.addEventListener('click', () => openBillBaruModal('continue'));
    }

    // Modal Bill Baru Batal
    if (btnBillBaruBatal && modalBillBaru) {
        btnBillBaruBatal.addEventListener('click', () => {
            modalBillBaru.style.display = 'none';
        });
    }

    // Pax Minus / Plus Controls
    if (btnPaxMinus && inputPax) {
        btnPaxMinus.addEventListener('click', () => {
            let val = parseInt(inputPax.value) || 1;
            if (val > 1) inputPax.value = val - 1;
        });
    }
    if (btnPaxPlus && inputPax) {
        btnPaxPlus.addEventListener('click', () => {
            let val = parseInt(inputPax.value) || 1;
            if (val < 99) inputPax.value = val + 1;
        });
    }

    // Waiter selection inside modal
    waiterItems.forEach(item => {
        item.addEventListener('click', () => {
            waiterItems.forEach(i => i.classList.remove('active-waiter'));
            item.classList.add('active-waiter');

            selectedWaiter = {
                id: item.dataset.id,
                name: item.dataset.name
            };

            // Enable Konfirmasi Button
            btnBillBaruKonfirmasi.style.opacity = '1';
            btnBillBaruKonfirmasi.style.pointerEvents = 'auto';
        });
    });

    // Konfirmasi button click
    if (btnBillBaruKonfirmasi) {
        btnBillBaruKonfirmasi.addEventListener('click', () => {
            if (!selectedTable || !selectedWaiter) return;

            const paxCount = parseInt(inputPax.value) || 1;

            if (pilihMejaMode === 'save') {
                const csrf = document.querySelector('meta[name="csrf-token"]');
                if (!csrf) return;

                // Cek apakah ini follow-up dari order yang sudah ada di DB
                const existingOrderId = localStorage.getItem('active_order_id');
                if (existingOrderId) {
                    // UPDATE meja pada order pending yang sudah ada
                    showLoading();
                    modalBillBaru.style.display = 'none';
                    overlayPilihMeja.style.display = 'none';

                    fetch(`/pos/orders/${existingOrderId}/update-table`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.getAttribute('content') },
                        body: JSON.stringify({
                            table_id:  selectedTable.id,
                            pax:       paxCount,
                            waiter_id: selectedWaiter.id,
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        hideLoading();
                        if (data.success) {
                            // Update active_table di localStorage juga
                            localStorage.setItem('active_table', JSON.stringify({
                                id: selectedTable.id,
                                name: selectedTable.name,
                                pax: paxCount,
                                waiter_id: selectedWaiter.id,
                                waiter_name: selectedWaiter.name,
                            }));
                            
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: `Meja berhasil diubah ke ${selectedTable.name}.`,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false,
                                    heightAuto: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                showToast(`Meja berhasil diubah ke ${selectedTable.name}.`);
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            }
                        } else {
                            showToast(data.message || 'Gagal mengubah meja.', true);
                        }
                    })
                    .catch(() => {
                        hideLoading();
                        showToast('Gagal terhubung ke server.', true);
                    });
                    return; // jangan lanjut ke flow save-bill baru
                }

                // AJAX: Buat Order baru (Pending)

                // Calculate subtotal, discount, grand total
                let subtotal = 0;
                let discountAmount = 0;
                cart.forEach(item => {
                    subtotal += item.total_price;
                    if (item.discounts && item.discounts.length > 0) {
                        item.discounts.forEach(d => {
                            if (d.type === 'percentage') discountAmount += item.total_price * (d.value / 100);
                            else discountAmount += parseFloat(d.value);
                        });
                    }
                });

                const afterDiscount = subtotal - discountAmount;
                let serviceCharge = 0;
                if (window.POS_CONFIG && window.POS_CONFIG.serviceCharges) {
                    cart.forEach(item => {
                        let itemTotalDiscount = 0;
                        if (item.discounts && item.discounts.length > 0) {
                            item.discounts.forEach(d => {
                                if (d.type === 'percentage') itemTotalDiscount += item.total_price * (d.value / 100);
                                else itemTotalDiscount += parseFloat(d.value);
                            });
                        }
                        let itemNet = item.total_price - itemTotalDiscount;
                        let chargeConfig = window.POS_CONFIG.serviceCharges.find(c => {
                            let typeKeyword = item.order_type === 'dine-in' ? 'dine' : item.order_type;
                            return c.name.toLowerCase().includes(typeKeyword);
                        });
                        if (chargeConfig) {
                            if (chargeConfig.type === 'percentage') serviceCharge += itemNet * (parseFloat(chargeConfig.value) / 100);
                            else serviceCharge += parseFloat(chargeConfig.value);
                        }
                    });
                }

                let tax = 0;
                if (window.POS_CONFIG && window.POS_CONFIG.taxes && window.POS_CONFIG.taxes.length > 0) {
                    let taxConfig = window.POS_CONFIG.taxes[0];
                    if (taxConfig.type === 'percentage') tax = afterDiscount * (parseFloat(taxConfig.value) / 100);
                    else tax = parseFloat(taxConfig.value);
                } else {
                    tax = afterDiscount * 0.10;
                }

                const grandTotal = afterDiscount + serviceCharge + tax;

                showLoading();
                modalBillBaru.style.display = 'none';
                overlayPilihMeja.style.display = 'none';

                fetch('/pos/orders/save-bill', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.getAttribute('content') },
                    body: JSON.stringify({
                        table_id: selectedTable.id,
                        pax: paxCount,
                        waiter_id: selectedWaiter.id,
                        cart: cart,
                        subtotal: subtotal,
                        discount_amount: discountAmount,
                        total_final: grandTotal,
                        tax_id: window.POS_CONFIG?.taxes?.[0]?.tax_id || null,
                        service_charge_id: window.POS_CONFIG?.serviceCharges?.[0]?.service_charge_id || null,
                        discount_id: cart[0]?.discounts?.[0]?.id || null // standard discount fallback
                    })
                })
                .then(r => r.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        cart = [];
                        saveCart();
                        localStorage.removeItem('active_table');
                        localStorage.removeItem('active_order_id');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Transaksi disimpan sebagai Bill.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false,
                                heightAuto: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            alert('Transaksi disimpan sebagai Bill.');
                            window.location.reload();
                        }
                    } else {
                        showToast(data.message || 'Gagal menyimpan transaksi.', true);
                    }
                })
                .catch(() => {
                    hideLoading();
                    showToast('Gagal terhubung ke server.', true);
                });

            } else if (pilihMejaMode === 'continue') {
                // Simpan ke in-memory session (localStorage) untuk ditampilkan di POS
                const activeTableObj = {
                    id: selectedTable.id,
                    name: selectedTable.name,
                    pax: paxCount,
                    waiter_id: selectedWaiter.id,
                    waiter_name: selectedWaiter.name
                };

                localStorage.setItem('active_table', JSON.stringify(activeTableObj));

                // Close modals
                modalBillBaru.style.display = 'none';
                overlayPilihMeja.style.display = 'none';

                // Render cart to reflect active table row
                renderCart();
                showToast(`Meja ${selectedTable.name} berhasil dipilih.`);
            }
        });
    }

    // Active Table Row triggers
    if (btnLihatMeja && overlayPilihMeja) {
        btnLihatMeja.addEventListener('click', () => {
            // Open layout again
            btnSimpanBillTrigger.click();
        });
    }

    // =========================================================
    // 12. DAFTAR BILL
    // =========================================================
    const modalDaftarBill   = document.getElementById('modalDaftarBill');
    const btnDaftarBill     = document.getElementById('btnDaftarBill');
    const btnDaftarBillTutup= document.getElementById('btnDaftarBillTutup');
    const btnDaftarBillBaru = document.getElementById('btnDaftarBillBaru');
    const daftarBillLoading = document.getElementById('daftarBillLoading');
    const daftarBillTable   = document.getElementById('daftarBillTable');
    const daftarBillRows    = document.getElementById('daftarBillRows');
    const daftarBillEmpty   = document.getElementById('daftarBillEmpty');
    const inputSearchBill   = document.getElementById('inputSearchBill');

    let allBillsData = []; // cache for search

    function renderBillRows(data) {
        daftarBillRows.innerHTML = '';
        if (!data || data.length === 0) {
            daftarBillTable.style.display = 'none';
            daftarBillEmpty.style.display = 'block';
            return;
        }
        daftarBillEmpty.style.display = 'none';
        daftarBillTable.style.display = 'table';

        data.forEach(bill => {
            const tr = document.createElement('tr');
            tr.className = 'daftar-bill-row';
            tr.dataset.orderId = bill.order_id;
            tr.innerHTML = `
                <td>${bill.meja}</td>
                <td>${bill.grup_meja}</td>
                <td>${bill.pelayan}</td>
                <td>${bill.waktu}</td>
                <td>
                    <span class="daftar-bill-sync-icon" title="Sudah tersinkron">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="9 12 11 14 15 10"/>
                        </svg>
                    </span>
                </td>
            `;
            daftarBillRows.appendChild(tr);
        });
    }

    function loadPendingBills() {
        daftarBillLoading.style.display = 'flex';
        daftarBillTable.style.display   = 'none';
        daftarBillEmpty.style.display   = 'none';

        fetch('/pos/orders/pending-bills', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(res => {
            daftarBillLoading.style.display = 'none';
            if (res.success) {
                allBillsData = res.data;
                renderBillRows(allBillsData);
            } else {
                daftarBillEmpty.style.display = 'block';
            }
        })
        .catch(() => {
            daftarBillLoading.style.display = 'none';
            daftarBillEmpty.style.display = 'block';
            daftarBillEmpty.textContent = 'Gagal memuat data bill.';
        });
    }

    // Open modal
    if (btnDaftarBill && modalDaftarBill) {
        btnDaftarBill.addEventListener('click', () => {
            modalDaftarBill.style.display = 'flex';
            if (inputSearchBill) inputSearchBill.value = '';
            loadPendingBills();
        });
    }

    // Close modal
    if (btnDaftarBillTutup && modalDaftarBill) {
        btnDaftarBillTutup.addEventListener('click', () => {
            modalDaftarBill.style.display = 'none';
        });
    }
    if (modalDaftarBill) {
        modalDaftarBill.addEventListener('click', e => {
            if (e.target === modalDaftarBill) modalDaftarBill.style.display = 'none';
        });
    }

    // "Bill Baru" button inside modal — close modal and open floor plan
    if (btnDaftarBillBaru && modalDaftarBill && btnSimpanBillTrigger) {
        btnDaftarBillBaru.addEventListener('click', () => {
            modalDaftarBill.style.display = 'none';
            btnSimpanBillTrigger.click();
        });
    }

    // Tab switching (Open Bill only active for now, others show placeholder)
    if (modalDaftarBill) {
        modalDaftarBill.querySelectorAll('.daftar-bill-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                modalDaftarBill.querySelectorAll('.daftar-bill-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const tabName = tab.dataset.tab;
                if (tabName === 'open') {
                    loadPendingBills();
                } else {
                    // Placeholder for other tabs
                    daftarBillLoading.style.display = 'none';
                    daftarBillTable.style.display   = 'none';
                    daftarBillEmpty.style.display   = 'block';
                    daftarBillEmpty.textContent     = 'Fitur ini akan segera hadir.';
                }
            });
        });
    }

    // Live search filter
    if (inputSearchBill) {
        inputSearchBill.addEventListener('input', () => {
            const q = inputSearchBill.value.trim().toLowerCase();
            if (!q) {
                renderBillRows(allBillsData);
                return;
            }
            const filtered = allBillsData.filter(b =>
                b.meja.toLowerCase().includes(q) ||
                b.grup_meja.toLowerCase().includes(q) ||
                b.pelayan.toLowerCase().includes(q)
            );
            renderBillRows(filtered);
        });
    }

    // =========================================================
    // 13. FOLLOW-UP BILL (klik baris daftar bill → restore ke POS)
    // =========================================================
    // Gunakan delegasi event karena baris di-render secara dinamis
    if (daftarBillRows) {
        daftarBillRows.addEventListener('click', (e) => {
            const row = e.target.closest('.daftar-bill-row');
            if (!row) return;
            const orderId = row.dataset.orderId;
            if (!orderId) return;

            showLoading();
            fetch(`/pos/orders/${orderId}/detail`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(r => r.json())
            .then(res => {
                hideLoading();
                if (!res.success) {
                    showToast('Gagal memuat detail bill.', true);
                    return;
                }

                const order = res.order;

                // 1. Simpan activeOrderId agar saat ganti meja bisa UPDATE, bukan INSERT baru
                localStorage.setItem('active_order_id', order.order_id);

                // 2. Restore cart dari order items
                cart = res.cart.map((item, idx) => ({
                    ...item,
                    id: item.id || (Date.now() + idx),
                }));
                saveCart();

                // 3. Set active_table seperti alur "Lanjutkan"
                if (order.table_id) {
                    const activeTableObj = {
                        id:           order.table_id,
                        name:         order.meja,
                        pax:          order.pax,
                        waiter_id:    order.waiter_id,
                        waiter_name:  order.waiter_name,
                    };
                    localStorage.setItem('active_table', JSON.stringify(activeTableObj));
                } else {
                    localStorage.removeItem('active_table');
                }

                // 3. Set order type global sesuai order
                selectedOrderType = order.order_type || 'dine-in';
                if (labelOrderType) {
                    labelOrderType.innerText = orderTypeLabels[selectedOrderType] || selectedOrderType;
                }

                // 4. Tutup modal daftar bill
                if (modalDaftarBill) modalDaftarBill.style.display = 'none';

                // 5. Re-render cart (tampilkan item + active table row)
                renderCart();
                showToast(`Bill meja ${order.meja} berhasil dibuka.`);
            })
            .catch(() => {
                hideLoading();
                showToast('Gagal terhubung ke server.', true);
            });
        });
    }

    // =========================================================
    // 14. INIT
    // =========================================================
    renderCart();

}); // end DOMContentLoaded