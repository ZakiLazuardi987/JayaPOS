// pos-split-payment.js

// =========================================================
// MODAL PISAH BAYAR
// =========================================================
    const btnPisahBayar = document.getElementById('btnPisahBayar');
    const modalPisahBayar = document.getElementById('modalPisahBayar');
    const btnBatalPisahBayar = document.getElementById('btnBatalPisahBayar');
    const pisahBayarTotalDisplay = document.getElementById('pisahBayarTotalDisplay');
    const pisahBayarCount = document.getElementById('pisahBayarCount');
    const pisahBayarInputsContainer = document.getElementById('pisahBayarInputsContainer');
    const btnTambahPisahBayar = document.getElementById('btnTambahPisahBayar');

    let splitPaymentRows = [];
    window.pisahBayarActive = false;
    window.pisahBayarCurrentRowIndex = undefined;

    if (btnPisahBayar) {
        btnPisahBayar.addEventListener('click', () => {
            if (modalPayment) modalPayment.style.display = 'none';

            const activeTotal = window.splitBillActive ? window.splitBillSummaryData.grandTotal : currentGrandTotal;
            pisahBayarTotalDisplay.innerText = `Rp ${activeTotal.toLocaleString('id-ID')}`;

            // Reset state
            window.pisahBayarActive = true;
            window.pisahBayarCurrentRowIndex = undefined;
            splitPaymentRows = [
                { amount: 0, paid: false, method: null },
                { amount: 0, paid: false, method: null }
            ];
            renderPisahBayarRows();

            if (modalPisahBayar) modalPisahBayar.style.display = 'flex';
        });
    }

    if (btnBatalPisahBayar) {
        btnBatalPisahBayar.addEventListener('click', () => {
            window.pisahBayarActive = false;
            window.pisahBayarCurrentRowIndex = undefined;
            window.currentSplitPaymentAmount = null;
            splitPaymentRows = [];
            if (modalPisahBayar) modalPisahBayar.style.display = 'none';
            openPaymentModal();
        });
    }

    if (btnTambahPisahBayar) {
        btnTambahPisahBayar.addEventListener('click', () => {
            splitPaymentRows.push({ amount: 0, paid: false, method: null });
            renderPisahBayarRows();
        });
    }

    function updatePisahBayarValidation() {
        const grandTotal = window.splitBillActive ? window.splitBillSummaryData.grandTotal : currentGrandTotal;
        const sumInputs = splitPaymentRows.reduce((s, r) => s + (r.amount || 0), 0);
        const isValid = sumInputs >= grandTotal; // kelebihan = kembalian, tetap boleh

        // Update hint text
        let hint = document.querySelector('.pisah-bayar-hint');
        if (hint) {
            if (sumInputs === 0) {
                hint.style.color = '#6b7280';
                hint.innerText = `Total: Rp 0 / Rp ${grandTotal.toLocaleString('id-ID')} — Isi semua nominal terlebih dahulu`;
            } else if (sumInputs < grandTotal) {
                hint.style.color = '#dc2626';
                hint.innerText = `Total: Rp ${sumInputs.toLocaleString('id-ID')} / Rp ${grandTotal.toLocaleString('id-ID')} — Kurang Rp ${(grandTotal - sumInputs).toLocaleString('id-ID')}`;
            } else if (sumInputs > grandTotal) {
                hint.style.color = '#b45309';
                hint.innerText = `Total: Rp ${sumInputs.toLocaleString('id-ID')} / Rp ${grandTotal.toLocaleString('id-ID')} — Kelebihan Rp ${(sumInputs - grandTotal).toLocaleString('id-ID')} (kembalian)`;
            } else {
                hint.style.color = '#16a34a';
                hint.innerText = `Total: Rp ${sumInputs.toLocaleString('id-ID')} ✓ Sesuai`;
            }
        }

        // Update button states only (no re-render)
        document.querySelectorAll('.btn-bayar-split').forEach(btn => {
            const idx = parseInt(btn.dataset.index);
            const rowAmount = splitPaymentRows[idx]?.amount || 0;
            const canPay = isValid && rowAmount > 0;
            btn.disabled = !canPay;
            btn.style.border = `1px solid ${canPay ? 'var(--primary)' : '#d1d5db'}`;
            btn.style.color = canPay ? 'var(--primary)' : '#9ca3af';
            btn.style.cursor = canPay ? 'pointer' : 'not-allowed';
        });
    }

    function renderPisahBayarRows() {
        if (!pisahBayarInputsContainer) return;
        pisahBayarCount.innerText = splitPaymentRows.length;

        const grandTotal = window.splitBillActive ? window.splitBillSummaryData.grandTotal : currentGrandTotal;
        const sumInputs = splitPaymentRows.reduce((s, r) => s + (r.amount || 0), 0);
        const isValid = sumInputs >= grandTotal;

        let html = '';
        splitPaymentRows.forEach((row, index) => {
            if (row.paid) {
                html += `
                    <div class="pisah-bayar-row" style="display: flex; gap: 16px; margin-bottom: 16px; align-items: center;">
                        <div style="flex: 1; position: relative;">
                            <input type="text" disabled value="${row.amount.toLocaleString('id-ID')}" style="width: 100%; padding: 14px 16px; border: 1px solid #d1fae5; background: #f0fdf4; border-radius: 4px; font-size: 1rem; font-family: inherit; color: #065f46;">
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; color:#16a34a; font-weight:600; min-width:120px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            ${row.method || 'Lunas'}
                        </div>
                    </div>
                `;
            } else {
                const canPay = isValid && row.amount > 0;
                html += `
                    <div class="pisah-bayar-row" style="display: flex; gap: 16px; margin-bottom: 16px; align-items: center;">
                        <div style="flex: 1; position: relative;">
                            <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 500; pointer-events:none;">Rp</span>
                            <input type="text" class="input-pisah-bayar" data-index="${index}" value="${row.amount ? row.amount.toLocaleString('id-ID') : ''}" style="width: 100%; padding: 14px 16px 14px 40px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 1rem; outline: none; font-family: inherit;" placeholder="0">
                        </div>
                        <button class="btn-bayar-split" data-index="${index}" ${!canPay ? 'disabled' : ''} style="background: white; border: 1px solid ${canPay ? 'var(--primary)' : '#d1d5db'}; color: ${canPay ? 'var(--primary)' : '#9ca3af'}; padding: 14px 24px; border-radius: 4px; font-weight: 600; cursor: ${canPay ? 'pointer' : 'not-allowed'}; white-space:nowrap;">Pembayaran</button>
                        ${splitPaymentRows.length > 2 && !row.paid
                            ? `<svg class="btn-remove-split" data-index="${index}" style="cursor: pointer; color: #ef4444; flex-shrink:0;" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>`
                            : '<div style="width:22px;"></div>'}
                    </div>
                `;
            }
        });

        pisahBayarInputsContainer.innerHTML = html;

        // Hint element — insert before container if not exists
        let hint = pisahBayarInputsContainer.parentElement.querySelector('.pisah-bayar-hint');
        if (!hint) {
            hint = document.createElement('div');
            hint.className = 'pisah-bayar-hint';
            hint.style.cssText = 'font-size:0.85rem; margin-bottom: 12px; text-align: right;';
            pisahBayarInputsContainer.parentElement.insertBefore(hint, pisahBayarInputsContainer);
        }

        // Attach input events — only update validation, don't re-render
        document.querySelectorAll('.input-pisah-bayar').forEach(input => {
            input.addEventListener('input', (e) => {
                const raw = e.target.value.replace(/\D/g, '');
                const idx = parseInt(e.target.dataset.index);
                splitPaymentRows[idx].amount = raw ? parseInt(raw) : 0;
                // Format in place: store cursor position, format, restore
                const cursorPos = e.target.selectionStart;
                const oldLen = e.target.value.length;
                const formatted = splitPaymentRows[idx].amount ? splitPaymentRows[idx].amount.toLocaleString('id-ID') : '';
                e.target.value = formatted;
                // Restore cursor
                const newLen = formatted.length;
                e.target.setSelectionRange(cursorPos + (newLen - oldLen), cursorPos + (newLen - oldLen));
                updatePisahBayarValidation();
            });
        });

        // Remove row
        document.querySelectorAll('.btn-remove-split').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const idx = parseInt(e.currentTarget.dataset.index);
                splitPaymentRows.splice(idx, 1);
                renderPisahBayarRows();
            });
        });

        // Pay row
        document.querySelectorAll('.btn-bayar-split').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (btn.disabled) return;
                const idx = parseInt(e.currentTarget.dataset.index);
                const amount = splitPaymentRows[idx].amount;
                if (amount <= 0) {
                    showToast('Masukkan nominal pembayaran terlebih dahulu', true);
                    return;
                }
                window.pisahBayarCurrentRowIndex = idx;
                window.currentSplitPaymentAmount = amount;
                if (modalPisahBayar) modalPisahBayar.style.display = 'none';
                openPaymentModal(amount);
            });
        });

        // Initial validation state
        updatePisahBayarValidation();
    }

    // ---- Finalize all split payments: send 1 order to backend ----
    function finalizeSplitPayment() {
        const grandTotal = window.splitBillActive ? window.splitBillSummaryData.grandTotal : currentGrandTotal;
        const activeTable = JSON.parse(localStorage.getItem('active_table') || 'null');
        const orderType = cart.length > 0 ? (cart[0].order_type || 'dine-in') : 'dine-in';
        const taxId = (window.POS_CONFIG?.taxes?.[0]?.tax_id) || null;
        let scConfig = window.POS_CONFIG?.serviceCharges?.find(c => {
            let typeKeyword = orderType === 'dine-in' ? 'dine' : orderType;
            return c.name.toLowerCase().includes(typeKeyword);
        });
        const serviceChargeId = scConfig ? scConfig.service_charge_id : null;
        let rawSubtotal = cart.reduce((s, i) => s + i.total_price, 0);
        let totalDiscAmt = 0;
        cart.forEach(i => {
            if (i.discounts && i.discounts.length > 0) {
                i.discounts.forEach(d => {
                    if (d.type === 'percentage') totalDiscAmt += i.total_price * (d.value / 100);
                    else totalDiscAmt += parseFloat(d.value);
                });
            }
        });
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const payload = {
            cart:               cart,
            subtotal:           rawSubtotal,
            discount_amount:    totalDiscAmt,
            total_final:        grandTotal,
            order_type:         orderType,
            table_id:           activeTable?.id || null,
            table_number:       activeTable?.name || '',
            pax:                1,
            waiter_id:          selectedStaffId,
            tax_id:             taxId,
            service_charge_id:  serviceChargeId,
            discount_id:        null,
            customer_id:        window.activeCustomer ? window.activeCustomer.id : null,
            split_payments:     splitPaymentRows.map(r => ({
                method: r.isEwallet ? 'QRIS' : 'Cash',
                amount: r.amount
            }))
        };

        fetch('/pos/checkout/split', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(resp => {
            window.pisahBayarActive = false;
            window.pisahBayarCurrentRowIndex = undefined;
            window.currentSplitPaymentAmount = null;

            if (!resp.success) {
                showToast(resp.message || 'Gagal menyimpan transaksi.', true);
                // Return to pisah bayar modal to retry
                renderPisahBayarRows();
                if (modalPisahBayar) modalPisahBayar.style.display = 'flex';
                return;
            }

            window.lastOrderId = resp.order_id;
            clearCartAfterPayment();

            // Cancel any temp QRIS orders created during split payment flow
            const tempOrderIds = splitPaymentRows
                .filter(r => r.tempOrderId)
                .map(r => r.tempOrderId);

            if (tempOrderIds.length > 0) {
                const csrf2 = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                tempOrderIds.forEach(tempId => {
                    fetch(`/pos/order/${tempId}/cancel-temp`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf2 },
                    }).catch(() => {}); // silently fail — cleanup only
                });
            }

            // Show success screen with split payment label
            document.getElementById('tunaiSuccessTitle').innerText = 'PISAH BAYAR';
            document.getElementById('tunaiSuccessBayarContainer').style.display = 'none';
            document.getElementById('tunaiSuccessKembalianLabel').style.display = 'none';
            const lblKembalian = document.getElementById('tunaiSuccessKembalian');
            lblKembalian.innerText = 'Pembayaran Berhasil';
            lblKembalian.style.fontSize = '1.8rem';

            if (modalTunaiSuccess) modalTunaiSuccess.style.display = 'flex';
            showToast('Pisah Bayar Berhasil!', false);
        })
        .catch(() => {
            showToast('Koneksi bermasalah. Coba lagi.', true);
        });
    }
