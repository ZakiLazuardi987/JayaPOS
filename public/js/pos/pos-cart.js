// pos-cart.js

// =========================================================
// CART (localStorage) — dengan deteksi user ganti
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
                        localStorage.removeItem('active_table');
                        localStorage.removeItem('active_order_id');
                        localStorage.removeItem('pos_customer');
                        window.activeCustomer = null;
                        renderCustomerInfo();
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
                    localStorage.removeItem('active_table');
                    localStorage.removeItem('active_order_id');
                    localStorage.removeItem('pos_customer');
                    window.activeCustomer = null;
                    renderCustomerInfo();
                    renderCart();
                }
            }
        });
    }
