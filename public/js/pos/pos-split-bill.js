// pos-split-bill.js

// =========================================================
// PISAH BILL (SPLIT BILL) ENGINE
// =========================================================
    const modalPisahBill = document.getElementById('modalPisahBill');
    const btnPisahBill = document.getElementById('btnPisahBill');
    const btnPisahBillTutup = document.getElementById('btnPisahBillTutup');
    const btnPisahBillPisahkan = document.getElementById('btnPisahBillPisahkan');
    const pisahBillAmountDisplay = document.getElementById('pisahBillAmountDisplay');
    const pisahBillProductList = document.getElementById('pisahBillProductList');
    const pisahBillBreakdown = document.getElementById('pisahBillBreakdown');

    window.selectedSplitItems = {};

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
            window.selectedSplitItems = {};
            // renderCart() sudah menghitung diskon + pajak + service charge
            // dan memperbarui tombol "Bayar" dengan total yang benar
            renderCart();
        });
    }

    if (btnPisahBillPisahkan && modalPisahBill && modalLoyalty) {
        btnPisahBillPisahkan.addEventListener('click', () => {
            const hasSelection = Object.values(window.selectedSplitItems).some(s => s.selected && s.qty > 0);
            if (!hasSelection) {
                showToast('Pilih minimal satu produk untuk dipisahkan', true);
                return;
            }

            window.splitBillActive = true;
            window.isCheckoutFlow = true;
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
            window.selectedSplitItems = {};
            // renderCart() menghitung ulang total termasuk diskon + pajak
            renderCart();
        });
    }

    function openPisahBillModal() {
        window.selectedSplitItems = {};
        cart.forEach(item => {
            window.selectedSplitItems[item.id] = {
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
                const splitState = window.selectedSplitItems[item.id] || { qty: 1, selected: false };
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
                    const activeState = window.selectedSplitItems[item.id];
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
                    window.selectedSplitItems[item.id].selected = !window.selectedSplitItems[item.id].selected;
                    updateRowVisualState();
                });

                minusBtn.addEventListener('click', () => {
                    const activeState = window.selectedSplitItems[item.id];
                    if (activeState.qty > 1) {
                        activeState.qty--;
                    } else if (activeState.qty === 1 && activeState.selected) {
                        activeState.selected = false;
                    }
                    updateRowVisualState();
                });

                plusBtn.addEventListener('click', () => {
                    const activeState = window.selectedSplitItems[item.id];
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
            const splitState = window.selectedSplitItems[item.id];
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
