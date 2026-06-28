// pos-table.js

// =========================================================
// DENAH MEJA & PILIH MEJA SELECTION
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

                // Tentukan order_type & service_charge
                const orderType = cart.length > 0 ? (cart[0].order_type || 'dine-in') : 'dine-in';
                let scConfig = window.POS_CONFIG?.serviceCharges?.find(c => {
                    let typeKeyword = orderType === 'dine-in' ? 'dine' : orderType;
                    return c.name.toLowerCase().includes(typeKeyword);
                });
                const serviceChargeId = scConfig ? scConfig.service_charge_id : null;

                fetch('/pos/orders/save-bill', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.getAttribute('content') },
                    body: JSON.stringify({
                        table_id: selectedTable.id,
                        pax: paxCount,
                        waiter_id: selectedWaiter.id,
                        order_type: orderType,
                        cart: cart,
                        subtotal: subtotal,
                        discount_amount: discountAmount,
                        total_final: grandTotal,
                        tax_id: window.POS_CONFIG?.taxes?.[0]?.tax_id || null,
                        service_charge_id: serviceChargeId,
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
                        localStorage.removeItem('pos_customer');
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
// DAFTAR BILL
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
// FOLLOW-UP BILL (klik baris daftar bill → restore ke POS)
// =========================================================
    // Gunakan delegasi event karena baris di-render secara dinamis
    if (daftarBillRows) {
        daftarBillRows.addEventListener('click', (e) => {
            const row = e.target.closest('.daftar-bill-row');
            if (!row) return;
            const orderId = row.dataset.orderId;
            if (!orderId || orderId === 'null' || orderId === 'undefined') return;

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
    // CETAK BILL SEMENTARA
    // =========================================================
    const btnCetakBillLuar = document.getElementById('btnCetakBillLuar');
    if (btnCetakBillLuar) {
        btnCetakBillLuar.addEventListener('click', () => {
            const activeOrderId = localStorage.getItem('active_order_id');
            if (activeOrderId) {
                const bluetoothAvailable = navigator.bluetooth && window.isSecureContext;
                if (!bluetoothAvailable) {
                    window.open('/pos/order/' + activeOrderId + '/print', '_blank');
                    return;
                }

                // Ambil data struk JSON
                fetch('/pos/order/' + activeOrderId + '/struk-data')
                    .then(res => {
                        if (!res.ok) throw new Error("Gagal mengambil data struk.");
                        return res.json();
                    })
                    .then(data => {
                        if (typeof window.printBluetoothReceipt === 'function') {
                            window.printBluetoothReceipt(data).catch(err => {
                                console.error("Printer error:", err);
                                if (err.name !== 'NotFoundError') {
                                    showToast('Gagal cetak Bluetooth. Membuka PDF...', true);
                                    window.open('/pos/order/' + activeOrderId + '/print', '_blank');
                                } else {
                                    showToast('Pencarian printer dibatalkan.', true);
                                }
                            });
                        } else {
                            window.open('/pos/order/' + activeOrderId + '/print', '_blank');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        window.open('/pos/order/' + activeOrderId + '/print', '_blank');
                    });
            } else {
                showToast('Harap simpan bill terlebih dahulu sebelum mencetak.', true);
            }
        });
    }

// =========================================================
// TARIK KODE KIOSK / CRM
// =========================================================
const btnKodeKiosk = document.getElementById("btnKodeKiosk");
const modalKioskCode = document.getElementById("modalKioskCode");
const btnBatalKioskCode = document.getElementById("btnBatalKioskCode");
const btnProsesKioskCode = document.getElementById("btnProsesKioskCode");
const inputKioskCode = document.getElementById("inputKioskCode");
const kioskCodeError = document.getElementById("kioskCodeError");

if (btnKodeKiosk && modalKioskCode) {
    btnKodeKiosk.addEventListener("click", () => {
        modalKioskCode.style.display = "flex";
        inputKioskCode.value = "";
        kioskCodeError.innerText = "";
        inputKioskCode.focus();
    });
}

if (btnBatalKioskCode && modalKioskCode) {
    btnBatalKioskCode.addEventListener("click", () => {
        modalKioskCode.style.display = "none";
    });
}

if (modalKioskCode) {
    modalKioskCode.addEventListener("click", (e) => {
        if (e.target === modalKioskCode) modalKioskCode.style.display = "none";
    });
}

if (btnProsesKioskCode && inputKioskCode) {
    btnProsesKioskCode.addEventListener("click", processKioskCode);
    inputKioskCode.addEventListener("keypress", (e) => {
        if (e.key === "Enter") processKioskCode();
    });

    function processKioskCode() {
        const code = inputKioskCode.value.trim().toUpperCase();
        if (!code) {
            kioskCodeError.innerText = "Harap masukkan kode.";
            return;
        }

        kioskCodeError.innerText = "";
        btnProsesKioskCode.innerHTML = "Memproses...";
        btnProsesKioskCode.disabled = true;

        fetch(`/pos/orders/pickup/${code}`, {
            headers: {
                "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content"),
                "Accept": "application/json",
            }
        })
        .then(r => r.json())
        .then(res => {
            btnProsesKioskCode.innerHTML = "Proses Kode";
            btnProsesKioskCode.disabled = false;

            if (!res.success) {
                kioskCodeError.innerText = res.message || "Gagal menarik pesanan.";
                return;
            }

            const order = res.order;

            // 1. Simpan activeOrderId
            localStorage.setItem("active_order_id", order.order_id);

            // 2. Restore cart dari order items
            cart = res.cart.map((item, idx) => ({
                ...item,
                id: item.id || (Date.now() + idx),
            }));
            saveCart();

            // 3. Set active_table (biasanya walk-in/kiosk tidak ada meja, tapi kalau ada kita set)
            if (order.table_id) {
                const activeTableObj = {
                    id:           order.table_id,
                    name:         order.meja,
                    pax:          order.pax,
                    waiter_id:    order.waiter_id,
                    waiter_name:  order.waiter_name,
                };
                localStorage.setItem("active_table", JSON.stringify(activeTableObj));
            } else {
                localStorage.removeItem("active_table");
            }

            // 3. Set order type global sesuai order
            selectedOrderType = order.order_type || "takeaway";
            if (typeof labelOrderType !== "undefined" && labelOrderType) {
                labelOrderType.innerText = typeof orderTypeLabels !== "undefined" ? (orderTypeLabels[selectedOrderType] || selectedOrderType) : selectedOrderType;
            }

            // 4. Tutup modal
            modalKioskCode.style.display = "none";

            // 5. Re-render cart
            if (typeof renderCart === "function") renderCart();
            if (typeof showToast === "function") showToast(`Pesanan ${code} berhasil ditarik.`);
        })
        .catch(() => {
            btnProsesKioskCode.innerHTML = "Proses Kode";
            btnProsesKioskCode.disabled = false;
            kioskCodeError.innerText = "Gagal terhubung ke server.";
        });
    }
}
