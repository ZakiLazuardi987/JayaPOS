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
        if (!container) return;

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
            return;
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
            if (paymentTotalDisplay) paymentTotalDisplay.innerText = `Rp ${currentGrandTotal.toLocaleString('id-ID')}`;
            if (paymentServerName) paymentServerName.innerText = `| ${selectedStaffName}`;
            
            const btnUangPas = document.getElementById('btnUangPas');
            let next1k = Math.ceil(currentGrandTotal / 1000) * 1000;
            if (next1k === 0) next1k = currentGrandTotal;
            if (btnUangPas) {
                btnUangPas.innerText = `Rp ${next1k.toLocaleString('id-ID')}`;
                btnUangPas.dataset.val = next1k;
            }

            const inputTunai = document.getElementById('inputTunaiManual');
            if (inputTunai) {
                inputTunai.placeholder = `Rp ${currentGrandTotal.toLocaleString('id-ID')}`;
                inputTunai.value = ''; // Reset
            }

            const btnUang50k = document.getElementById('btnUang50k');
            if (btnUang50k) {
                let next50k = Math.ceil(currentGrandTotal / 50000) * 50000;
                if (next50k === next1k && currentGrandTotal > 0) next50k += 50000;
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
        if (e.target === modalLoyalty) modalLoyalty.style.display = 'none';
        if (e.target === modalStaff) modalStaff.style.display = 'none';
        if (e.target === modalPayment) modalPayment.style.display = 'none';
    });

    // =========================================================
    // 11. INIT
    // =========================================================
    renderCart();

}); // end DOMContentLoaded