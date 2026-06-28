// pos-product.js

// =========================================================
// MODAL TAMBAH FAVORIT & SEARCH
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
// LIBRARY SEARCH
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
// MODAL DETAIL PRODUK
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

            let existingQty = 0;
            // Jika mode edit, kita hitung qty di luar item yang sedang di-edit
            cart.forEach(item => {
                if (item.product_id === currentProduct.product_id && item.id !== editingCartId) {
                    existingQty += parseInt(item.qty) || 0;
                }
            });

            if (currentProduct.stock !== undefined && (qty + existingQty) > currentProduct.stock) {
                const sisa = currentProduct.stock - existingQty;
                Swal.fire({
                    icon: 'warning',
                    title: 'Stok Tidak Mencukupi',
                    text: `Stok ${currentProduct.name} sisa ${currentProduct.stock}. Di keranjang sudah ada ${existingQty}, maksimal penambahan: ${Math.max(0, sisa)}.`
                });
                return;
            }

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
                        earning_points: currentProduct.earning_points || 0,
                    };
                }
                saveCart();
                renderCart();
                const productName = currentProduct.name;
                closeDetailModal();
                showToast(`${productName} berhasil diupdate`);
            } else {
                // ===== MODE TAMBAH: push item baru =====
                const cartItem = {
                    id:          Date.now(),
                    product_id:  currentProduct.product_id,
                    name:        currentProduct.name,
                    img_url:     currentProduct.img_url,
                    base_price:  currentProduct.price,
                    earning_points: currentProduct.earning_points || 0,
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
                const productName = currentProduct.name;
                closeDetailModal();
                showToast(`${productName} ditambahkan ke keranjang`);
            }
        });
    }
