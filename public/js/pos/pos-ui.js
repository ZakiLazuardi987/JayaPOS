// pos-ui.js

// =========================================================
// SIDEBAR
// =========================================================
    const btnMenus = document.querySelectorAll('.btn-hamburger');
    const sidebar  = document.getElementById('sidebar');
    const sideOverlay = document.getElementById('sidebarOverlay');

    if (btnMenus.length > 0) {
        btnMenus.forEach(btn => {
            btn.addEventListener('click', () => {
                sidebar.classList.add('open');
                sideOverlay.style.display = 'block';
            });
        });
    }
    if (sideOverlay) {
        sideOverlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            sideOverlay.style.display = 'none';
        });
    }

    

// =========================================================
// CAROUSEL / SLIDER (Favorit)
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
// MODE EDIT FAVORIT
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
// MODAL PILIH TIPE PENJUALAN (header "Dine In ⌄")
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
