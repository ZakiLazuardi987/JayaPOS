// pos-payment.js

// =========================================================
// MODAL STAFF (PILIH PELAYAN) & PEMBAYARAN
// =========================================================
    const modalStaff = document.getElementById('modalStaff');
    const btnBackToLoyalty = document.getElementById('btnBackToLoyalty');
    const btnLewatiStaff = document.getElementById('btnLewatiStaff');
    const modalPayment = document.getElementById('modalPayment');
    const btnBatalPayment = document.getElementById('btnBatalPayment');
    const paymentTotalDisplay = document.getElementById('paymentTotalDisplay');
    const paymentServerName = document.getElementById('paymentServerName');
    
    let selectedStaffName = '-';
    let selectedStaffId = null;

    function openPaymentModal(forcedTotal = null) {
        if (modalStaff) modalStaff.style.display = 'none';
        if (modalPayment) {
            window.currentSplitPaymentAmount = forcedTotal;
            const activeTotal = forcedTotal !== null ? forcedTotal : (window.splitBillActive ? window.splitBillSummaryData.grandTotal : currentGrandTotal);
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
        selectedStaffId = null;
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
            selectedStaffId = item.dataset.id;
            
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
            const activeTotal = window.currentSplitPaymentAmount !== null && window.currentSplitPaymentAmount !== undefined ? window.currentSplitPaymentAmount : (window.splitBillActive ? window.splitBillSummaryData.grandTotal : currentGrandTotal);

            // ---- PISAH BAYAR MODE: Intercept CASH only (QRIS needs actual scan flow) ----
            if (window.pisahBayarActive && window.pisahBayarCurrentRowIndex !== undefined && !isEwallet) {
                const rowIdx = window.pisahBayarCurrentRowIndex;

                // Cash: validate amount
                if (tunaiValue < activeTotal) {
                    showToast('Nominal pembayaran kurang dari total!', true);
                    return;
                }

                // Mark row as paid (Cash)
                splitPaymentRows[rowIdx].paid = true;
                splitPaymentRows[rowIdx].method = 'Tunai';
                splitPaymentRows[rowIdx].isEwallet = false;

                // Close payment modal, return to pisah bayar
                modalPayment.style.display = 'none';
                window.currentSplitPaymentAmount = null;
                window.pisahBayarCurrentRowIndex = undefined;

                // Check if all rows paid
                const allPaid = splitPaymentRows.every(r => r.paid);
                if (allPaid) {
                    finalizeSplitPayment();
                } else {
                    renderPisahBayarRows();
                    if (modalPisahBayar) modalPisahBayar.style.display = 'flex';
                }
                return; // Stop here — QRIS will go through normal flow below
            }

            // --- Kumpulkan Data Payload ---
            const activeTable = JSON.parse(localStorage.getItem('active_table') || 'null');
            const activeOrderId = localStorage.getItem('active_order_id') || null;
            const orderType = cart.length > 0 ? (cart[0].order_type || 'dine-in') : 'dine-in';
            const taxId = (window.POS_CONFIG?.taxes?.[0]?.tax_id) || null;
            let scConfig = window.POS_CONFIG?.serviceCharges?.find(c => {
                let typeKeyword = orderType === 'dine-in' ? 'dine' : orderType;
                return c.name.toLowerCase().includes(typeKeyword);
            });
            const serviceChargeId = scConfig ? scConfig.service_charge_id : null;
            const discountId = null;

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
            const isSplit = window.splitBillActive;
            const finalCart = isSplit ? cart.filter(item => {
                const splitState = window.selectedSplitItems[item.id];
                return splitState && splitState.selected && splitState.qty > 0;
            }).map(item => ({
                ...item,
                qty: window.selectedSplitItems[item.id].qty,
                total_price: window.selectedSplitItems[item.id].qty * item.unit_price
            })) : cart;
            const finalSubtotal = isSplit ? window.splitBillSummaryData.subtotal : rawSubtotal;
            const finalDiscount = isSplit ? window.splitBillSummaryData.discount : totalDiscAmt;

            const basePayload = {
                cart:               finalCart,
                subtotal:           finalSubtotal,
                discount_amount:    finalDiscount,
                total_final:        activeTotal,
                order_type:         orderType,
                table_id:           activeTable?.id || null,
                table_number:       activeTable?.name || '',
                pax:                1,
                waiter_id:          selectedStaffId,
                tax_id:             taxId,
                service_charge_id:  serviceChargeId,
                discount_id:        discountId,
                customer_id:        window.activeCustomer ? window.activeCustomer.id : null,
                active_order_id:    activeOrderId,
            };

            if (isEwallet) {
                btnProsesPayment.disabled = true;
                btnProsesPayment.innerText = 'Memproses QRIS...';

                fetch('/pos/checkout/qris', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify(basePayload),
                })
                .then(r => r.json())
                .then(resp => {
                    btnProsesPayment.disabled = false;
                    btnProsesPayment.innerText = 'Proses Pembayaran';

                    if (!resp.success) {
                        showToast(resp.message || 'Gagal membuat QRIS.', true);
                        return;
                    }

                    window.lastOrderId = resp.order_id;
                    
                    // Ganti gambar QR dengan yang asli dari Midtrans
                    const imgQr = document.getElementById('qrisMainImage');
                    if (imgQr && resp.qr_code_url) {
                        imgQr.src = resp.qr_code_url;
                    }

                    modalPayment.style.display = 'none';
                    document.getElementById('qrisTotalHargaDisplay').innerText = `Rp ${activeTotal.toLocaleString('id-ID')}`;
                    modalQris.style.display = 'flex';

                    // Ubah jadi 300 detik (5 menit)
                    let secondsLeft = 300;
                    const textEl = document.getElementById('qrisCountdownText');
                    const circle = document.getElementById('qrisProgressCircle');
                    if (textEl && circle) {
                        textEl.innerText = secondsLeft;
                        circle.style.strokeDashoffset = '0';
                        clearInterval(qrisInterval);
                        qrisInterval = setInterval(() => {
                            secondsLeft--;
                            if (secondsLeft <= 0) {
                                clearInterval(qrisInterval);
                                modalQris.style.display = 'none';
                                showToast('Waktu pembayaran QRIS habis. Silakan ulangi transaksi.', true);
                                return;
                            }
                            textEl.innerText = secondsLeft;
                            const offset = 226 - (secondsLeft / 300) * 226;
                            circle.style.strokeDashoffset = offset;

                            // Polling tiap 3 detik
                            if (secondsLeft % 3 === 0) {
                                fetch(`/pos/checkout/qris/${resp.order_id}/status`)
                                    .then(r => r.json())
                                    .then(res => {
                                        if (res.status === 'success') {
                                            clearInterval(qrisInterval);
                                            modalQris.style.display = 'none';

                                            // ---- PISAH BAYAR MODE: mark row paid, return to split modal ----
                                            if (window.pisahBayarActive && window.pisahBayarCurrentRowIndex !== undefined) {
                                                const rowIdx = window.pisahBayarCurrentRowIndex;
                                                splitPaymentRows[rowIdx].paid = true;
                                                splitPaymentRows[rowIdx].method = 'QRIS';
                                                splitPaymentRows[rowIdx].isEwallet = true;
                                                // Store temp order ID to cancel after finalize
                                                splitPaymentRows[rowIdx].tempOrderId = resp.order_id;
                                                window.pisahBayarCurrentRowIndex = undefined;
                                                window.currentSplitPaymentAmount = null;

                                                const allPaid = splitPaymentRows.every(r => r.paid);
                                                if (allPaid) {
                                                    finalizeSplitPayment();
                                                } else {
                                                    renderPisahBayarRows();
                                                    if (modalPisahBayar) modalPisahBayar.style.display = 'flex';
                                                }
                                                showToast('Pembayaran QRIS Berhasil!', false);
                                                return; // Don't show final receipt yet
                                            }
                                            
                                            // ---- NORMAL MODE: show receipt ----
                                            document.getElementById('tunaiSuccessTitle').innerText = 'QRIS / E-WALLET';
                                            document.getElementById('tunaiSuccessBayarContainer').style.display = 'none';
                                            document.getElementById('tunaiSuccessKembalianLabel').style.display = 'none';
                                            
                                            const lblKembalian = document.getElementById('tunaiSuccessKembalian');
                                            lblKembalian.innerText = 'Pembayaran Berhasil';
                                            lblKembalian.style.fontSize = '1.8rem';
                                            
                                            clearCartAfterPayment();

                                            modalTunaiSuccess.style.display = 'flex';
                                            showToast('Pembayaran QRIS Berhasil!', false);
                                        } else if (res.status === 'failed') {
                                            clearInterval(qrisInterval);
                                            modalQris.style.display = 'none';
                                            showToast('Pembayaran QRIS gagal atau dibatalkan.', true);
                                        }
                                    });
                            }
                        }, 1000);
                    }
                })
                .catch(err => {
                    btnProsesPayment.disabled = false;
                    btnProsesPayment.innerText = 'Proses Pembayaran';
                    showToast('Koneksi bermasalah. Coba lagi.', true);
                    console.error(err);
                });

            } else {
                // ---- CASH PAYMENT ----
                let bayar = tunaiValue;
                if (bayar < activeTotal) {
                    showToast('Nominal pembayaran kurang dari total!', true);
                    return;
                }

                btnProsesPayment.disabled = true;
                btnProsesPayment.innerText = 'Memproses...';

                const cashPayload = { ...basePayload, amount_paid: bayar };

                fetch('/pos/checkout/cash', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify(cashPayload),
                })
                .then(r => r.json())
                .then(resp => {
                    btnProsesPayment.disabled = false;
                    btnProsesPayment.innerText = 'Proses Pembayaran';

                    if (!resp.success) {
                        showToast(resp.message || 'Gagal menyimpan transaksi.', true);
                        return;
                    }

                    // Simpan order_id hasil checkout untuk referensi struk nanti
                    window.lastOrderId = resp.order_id;

                    // Tampilkan modal sukses
                    modalPayment.style.display = 'none';

                    // Pastikan elemen kembali seperti semula (karena bisa saja ter-hide oleh QRIS)
                    document.getElementById('tunaiSuccessTitle').innerText = 'TUNAI';
                    document.getElementById('tunaiSuccessBayarContainer').style.display = 'block';
                    document.getElementById('tunaiSuccessKembalianLabel').style.display = 'block';
                    const lblKembalianCash = document.getElementById('tunaiSuccessKembalian');
                    lblKembalianCash.style.fontSize = '2.5rem';

                    document.getElementById('tunaiSuccessBayar').innerText = `Rp ${bayar.toLocaleString('id-ID')}`;
                    const kembalian = bayar - activeTotal;
                    lblKembalianCash.innerText = `Rp ${kembalian.toLocaleString('id-ID')}`;
                    
                    clearCartAfterPayment();

                    modalTunaiSuccess.style.display = 'flex';
                })
                .catch(err => {
                    btnProsesPayment.disabled = false;
                    btnProsesPayment.innerText = 'Proses Pembayaran';
                    showToast('Koneksi bermasalah. Coba lagi.', true);
                    console.error(err);
                });
            }
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

    function clearCartAfterPayment() {
        if (window.splitBillActive) {
            cart = cart.map(item => {
                const splitState = window.selectedSplitItems[item.id];
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
                localStorage.removeItem('active_order_id');
                localStorage.removeItem('pos_customer');
            }
        } else {
            cart = [];
            saveCart();
            localStorage.removeItem('active_table');
            localStorage.removeItem('active_order_id');
            localStorage.removeItem('pos_customer');
        }
    }

    if (btnTransaksiBaru) {
        btnTransaksiBaru.addEventListener('click', () => {
            if(modalTunaiSuccess) modalTunaiSuccess.style.display = 'none';
            localStorage.removeItem('pos_customer');
            window.location.reload();
        });
    }

    const btnCetakStrukSuccess = document.getElementById('btnCetakStrukSuccess');
    if (btnCetakStrukSuccess) {
        btnCetakStrukSuccess.addEventListener('click', async () => {
            if (!window.lastOrderId) {
                showToast('ID Order tidak ditemukan, gagal mencetak.', true);
                return;
            }

            // Cek apakah Web Bluetooth tersedia (Chrome + HTTPS/localhost)
            const bluetoothAvailable = typeof navigator.bluetooth !== 'undefined';

            if (!bluetoothAvailable) {
                // Fallback: buka PDF di tab baru
                window.open('/pos/order/' + window.lastOrderId + '/print', '_blank');
                return;
            }

            // Bluetooth tersedia → fetch data order lalu cetak
            btnCetakStrukSuccess.disabled = true;
            btnCetakStrukSuccess.innerText = 'Menghubungkan...';

            try {
                const res = await fetch('/pos/order/' + window.lastOrderId + '/struk-data');
                if (!res.ok) throw new Error('Gagal ambil data struk.');
                const d = await res.json();

                btnCetakStrukSuccess.innerText = 'Mencetak...';
                await window.printBluetoothReceipt(d);
                showToast('Struk berhasil dicetak!');
            } catch (err) {
                console.error(err);
                // Jika user batalkan pairing atau error lain → fallback PDF
                if (err.message && err.message.includes('dibatalkan')) {
                    showToast('Pencarian printer dibatalkan.', true);
                } else {
                    showToast('Gagal cetak Bluetooth. Membuka PDF...', true);
                    window.open('/pos/order/' + window.lastOrderId + '/print', '_blank');
                }
            } finally {
                btnCetakStrukSuccess.disabled = false;
                btnCetakStrukSuccess.innerText = 'Cetak Struk';
            }
        });
    }
