// pos-loyalty.js

// =========================================================
// MODAL LOYALTY (PISAH BILL / MEMBER)
// =========================================================
    const btnBayar = document.getElementById('totalBayar');
    const modalLoyalty = document.getElementById('modalLoyalty');
    const btnBatalLoyalty = document.getElementById('btnBatalLoyalty');
    const btnLewatiLoyalty = document.getElementById('btnLewatiLoyalty');

    // (Listeners dipindah ke bagian 13B agar tidak bentrok)

    

// =========================================================
// PROGRAM LOYALTY & MEMBER
// =========================================================
    const btnTambahPelanggan = document.getElementById('btnTambahPelanggan');
    const modalSearchMember = document.getElementById('modalSearchMember');
    const btnBatalSearchMember = document.getElementById('btnBatalSearchMember');
    const inputSearchMember = document.getElementById('inputSearchMember');
    const searchMemberList = document.getElementById('searchMemberList');
    const totalMembersSpan = document.getElementById('totalMembers');

    const btnCheckMember = document.getElementById('btnCheckMember');
    const loyaltyPhone = document.getElementById('loyaltyPhone');
    const loyaltySubtitleText = document.getElementById('loyaltySubtitleText');

    window.isCheckoutFlow = false;

    window.activeCustomer = JSON.parse(localStorage.getItem('pos_customer')) || null;

    if (btnBayar && modalLoyalty) {
        btnBayar.addEventListener('click', () => {
            if (cart.length === 0) {
                showToast('Keranjang masih kosong', true);
                return;
            }
            window.isCheckoutFlow = true;
            
            // Siapkan UI Modal Loyalty
            const pts = cart.reduce((sum, item) => sum + ((item.earning_points || 0) * item.qty), 0);
            if(loyaltySubtitleText) {
                loyaltySubtitleText.innerText = pts > 0 
                    ? `Dapatkan ${pts} poin dari pesanan ini`
                    : `Kumpulkan poin dari setiap pesanan`;
            }

            const resultContainer = document.getElementById('loyaltyCheckResult');
            if (resultContainer) resultContainer.innerHTML = '';

            if(window.activeCustomer && window.activeCustomer.phone) {
                loyaltyPhone.value = window.activeCustomer.phone.replace(/^(\+62|0)/, '');
                btnLewatiLoyalty.innerText = 'Selanjutnya';
                btnLewatiLoyalty.style.background = 'var(--primary)';
                btnLewatiLoyalty.style.color = '#fff';
                btnLewatiLoyalty.style.border = 'none';
            } else {
                loyaltyPhone.value = '';
                btnLewatiLoyalty.innerText = 'Lewati';
                btnLewatiLoyalty.style.background = '';
                btnLewatiLoyalty.style.color = '';
                btnLewatiLoyalty.style.border = '';
            }

            modalLoyalty.style.display = 'flex';
        });
    }

    function renderCustomerInfo() {
        if (!btnTambahPelanggan) return;
        if (window.activeCustomer) {
            btnTambahPelanggan.innerHTML = `
                <div style="text-align:center; line-height: 1.2;">
                    <div style="font-weight:600; font-size: 1.05rem;">${window.activeCustomer.name}</div>
                    <div style="font-size:0.8rem; opacity: 0.8;">${window.activeCustomer.points || 0} Poin</div>
                </div>
            `;
            btnTambahPelanggan.style.background = '';
            btnTambahPelanggan.style.border = '';
            btnTambahPelanggan.style.padding = '';
            btnTambahPelanggan.style.borderRadius = '';
        } else {
            btnTambahPelanggan.innerHTML = '+ Tambah Pelanggan';
            btnTambahPelanggan.style.background = '';
            btnTambahPelanggan.style.border = '';
            btnTambahPelanggan.style.padding = '';
            btnTambahPelanggan.style.borderRadius = '';
        }
    }

    let searchMemberDebounce;

    function fetchMembers(query = '') {
        if (!searchMemberList) return;
        searchMemberList.innerHTML = '<div style="padding: 24px; text-align: center; color: #6b7280;">Memuat data...</div>';
        
        fetch(`/pos/customers/search?q=${encodeURIComponent(query)}`)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    if (totalMembersSpan) totalMembersSpan.innerText = res.total;
                    
                    if (res.data.length === 0) {
                        searchMemberList.innerHTML = '<div style="padding: 24px; text-align: center; color: #6b7280;">Pelanggan tidak ditemukan.</div>';
                        return;
                    }
                    
                    let html = '';
                    res.data.forEach(m => {
                        html += `
                            <div class="member-item-row" style="display: flex; padding: 12px; border-bottom: 1px solid #e5e7eb; cursor: pointer; align-items: center;" onclick="selectMember(${m.id}, '${m.name}', '${m.phone || ''}', ${m.points || 0})">
                                <div style="flex: 1; display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>
                                    </div>
                                    <span style="font-weight: 500; color: #111;">${m.name}</span>
                                </div>
                                <div style="flex: 1; color: #4b5563;">${m.phone || '-'}</div>
                                <div style="flex: 1; color: #4b5563;">${m.email || '-'}</div>
                            </div>
                        `;
                    });
                    searchMemberList.innerHTML = html;
                }
            })
            .catch(() => {
                searchMemberList.innerHTML = '<div style="padding: 24px; text-align: center; color: #ef4444;">Gagal memuat data.</div>';
            });
    }

    window.selectMember = function(id, name, phone, points) {
        window.activeCustomer = { id, name, phone, points };
        localStorage.setItem('pos_customer', JSON.stringify(window.activeCustomer));
        renderCustomerInfo();
        if (modalSearchMember) modalSearchMember.style.display = 'none';
        showToast(`Pelanggan ${name} terpilih.`);
    };

    if (btnTambahPelanggan) {
        btnTambahPelanggan.addEventListener('click', () => {
            window.isCheckoutFlow = false;
            if (!window.activeCustomer) {
                // Open Search Member Modal
                if (modalSearchMember) {
                    if (inputSearchMember) inputSearchMember.value = '';
                    fetchMembers();
                    modalSearchMember.style.display = 'flex';
                }
            } else {
                // Open Loyalty Modal (Karena sudah ada pelanggan terpilih)
                let pts = 0;
                cart.forEach(item => {
                    pts += (item.earning_points || 0) * item.qty;
                });
                
                if(loyaltySubtitleText) {
                    loyaltySubtitleText.innerText = pts > 0 
                        ? `Dapatkan ${pts} poin dari pesanan ini`
                        : `Kumpulkan poin dari setiap pesanan`;
                }
                
                const resultContainer = document.getElementById('loyaltyCheckResult');
                if (resultContainer) resultContainer.innerHTML = '';
                
                if (loyaltyPhone) {
                    loyaltyPhone.value = window.activeCustomer.phone ? window.activeCustomer.phone.replace(/^(\+62|0)/, '') : '';
                }
                
                if (btnLewatiLoyalty) {
                    btnLewatiLoyalty.innerText = 'Hapus Member';
                    btnLewatiLoyalty.style.background = 'var(--primary)';
                    btnLewatiLoyalty.style.color = '#fff';
                    btnLewatiLoyalty.style.border = 'none';
                }
                
                if (modalLoyalty) modalLoyalty.style.display = 'flex';
            }
        });
    }

    if (inputSearchMember) {
        inputSearchMember.addEventListener('input', (e) => {
            clearTimeout(searchMemberDebounce);
            searchMemberDebounce = setTimeout(() => {
                fetchMembers(e.target.value);
            }, 300);
        });
    }

    if (btnBatalSearchMember && modalSearchMember) {
        btnBatalSearchMember.addEventListener('click', () => {
            modalSearchMember.style.display = 'none';
        });
    }

    const closeLoyaltyModal = () => {
        if(modalLoyalty) modalLoyalty.style.display = 'none';
    };

    if (btnBatalLoyalty) btnBatalLoyalty.addEventListener('click', closeLoyaltyModal);
    
    if (btnLewatiLoyalty) {
        btnLewatiLoyalty.addEventListener('click', () => {
            if (btnLewatiLoyalty.innerText === 'Hapus Member') {
                window.activeCustomer = null;
                localStorage.removeItem('pos_customer');
                renderCustomerInfo();
                closeLoyaltyModal();
                showToast('Member dihapus dari pesanan.');
            } else if (btnLewatiLoyalty.innerText === 'Selanjutnya' || btnLewatiLoyalty.innerText === 'Lewati') {
                if (btnLewatiLoyalty.innerText === 'Lewati') {
                    window.activeCustomer = null;
                    localStorage.removeItem('pos_customer');
                    renderCustomerInfo();
                }
                closeLoyaltyModal();
                
                if (window.isCheckoutFlow) {
                    const modalStaff = document.getElementById('modalStaff');
                    if (modalStaff) {
                        modalStaff.style.display = 'flex';
                    } else {
                        showToast('Lanjut ke Pembayaran...');
                    }
                }
            } else {
                window.activeCustomer = null;
                localStorage.removeItem('pos_customer');
                renderCustomerInfo();
                closeLoyaltyModal();
            }
        });
    }

    if (btnCheckMember) {
        btnCheckMember.addEventListener('click', () => {
            const phone = loyaltyPhone.value.trim();
            if(!phone) {
                showToast('Masukkan nomor HP terlebih dahulu', true);
                return;
            }
            
            showLoading();
            fetch(`/pos/customers/check?phone=${phone}`)
                .then(r => r.json())
                .then(res => {
                    hideLoading();
                    const resultContainer = document.getElementById('loyaltyCheckResult');
                    if(res.status === 'success') {
                        window.activeCustomer = res.data;
                        localStorage.setItem('pos_customer', JSON.stringify(window.activeCustomer));
                        renderCustomerInfo();
                        
                        if (resultContainer) {
                            resultContainer.innerHTML = `<span style="color: #10b981;">Member ditemukan: <b>${res.data.name}</b></span>`;
                        }
                        
                        if (btnLewatiLoyalty) {
                            btnLewatiLoyalty.innerText = 'Selanjutnya';
                            btnLewatiLoyalty.style.background = 'var(--primary)';
                            btnLewatiLoyalty.style.color = '#fff';
                            btnLewatiLoyalty.style.border = 'none';
                        }
                    } else {
                        if (resultContainer) {
                            resultContainer.innerHTML = `<span style="color: #ef4444;">Member tidak ditemukan. Daftarkan di Aplikasi CRM.</span>`;
                        }
                    }
                })
                .catch(() => {
                    hideLoading();
                    const resultContainer = document.getElementById('loyaltyCheckResult');
                    if (resultContainer) {
                        resultContainer.innerHTML = `<span style="color: #ef4444;">Gagal mengecek member. Terjadi kesalahan jaringan.</span>`;
                    }
                });
        });
    }
