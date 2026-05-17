@extends('layouts.pos')

@section('content')
<div class="pos-container">
    
    <div class="left-panel" id="leftArea">
        
        <!-- TAB FAVORIT NORMAL -->
        <div class="tab-content active" id="tab-favorit">
            <div class="carousel-container" id="carousel-normal">
                @for ($page = 0; $page < $totalPages; $page++)
                    <!-- PERUBAHAN DI SINI: Tambahkan class active untuk page pertama -->
                    <div class="grid-page {{ $page == 0 ? 'active' : '' }}">
                        @for ($i = 0; $i < 16; $i++)
                            @php $index = ($page * 16) + $i; @endphp
                            
                            @if (isset($products[$index]))
                                <div class="product-card">
                                    <img src="{{ $products[$index]->img_url ? asset($products[$index]->img_url) : asset('images/default.jpg') }}" alt="">
                                    <div class="product-name">{{ $products[$index]->name }}</div>
                                </div>
                            @else
                                <div class="product-card empty-card"></div>
                            @endif
                        @endfor
                    </div>
                @endfor
            </div>
            
            <!-- DOTS INDIKATOR NORMAL -->
            @if($totalPages > 1)
            <div class="carousel-dots" id="dots-normal">
                @for ($page = 0; $page < $totalPages; $page++)
                    <button class="dot {{ $page == 0 ? 'active' : '' }}" data-page="{{ $page }}"></button>
                @endfor
            </div>
            @endif
        </div>

        <!-- TAB FAVORIT EDIT MODE -->
        <div class="tab-content" id="tab-edit-favorit">
            <div class="carousel-container" id="carousel-edit">
                @for ($page = 0; $page < $totalPages; $page++)
                    <!-- PERUBAHAN DI SINI JUGA -->
                    <div class="grid-page {{ $page == 0 ? 'active' : '' }}">
                        @for ($i = 0; $i < 16; $i++)
                            @php $index = ($page * 16) + $i; @endphp
                            
                            @if (isset($products[$index]))
                                <!-- Bagian TAB FAVORIT EDIT MODE di index.blade.php -->
                                <div class="product-card edit-mode" data-id="{{ $products[$index]->product_id }}">
                                    <div class="btn-remove">&times;</div> <!-- Tombol X merah -->
                                    <img src="{{ $products[$index]->img_url ? asset($products[$index]->img_url) : asset('images/default.jpg') }}" alt="" draggable="false">
                                    <div class="product-name">{{ $products[$index]->name }}</div>
                                </div>
                            @else
                                <div class="empty-slot-btn">+</div>
                            @endif
                        @endfor
                    </div>
                @endfor
            </div>
            
            <!-- DOTS INDIKATOR EDIT -->
            @if($totalPages > 1)
            <div class="carousel-dots" id="dots-edit">
                @for ($page = 0; $page < $totalPages; $page++)
                    <button class="dot {{ $page == 0 ? 'active' : '' }}" data-page="{{ $page }}"></button>
                @endfor
            </div>
            @endif
        </div>

        <!-- TAB LIBRARY -->
        <div class="tab-content" id="tab-library">
            
            <!-- Area Search -->
            <div class="library-search-wrapper">
                <input type="text" id="searchLibraryProduct" class="library-search-bar" placeholder="Cari">
                <img src="{{ asset('assets/search_icon.png') }}" alt="Search" class="library-search-icon">
            </div>

            <!-- Area Kotak Putih (Header & List) -->
            <div class="library-container">
                <div class="library-header">
                    <div style="width: 24px;"></div> <!-- Spacer biar teks pas di tengah -->
                    <h3>Library</h3>
                    <!-- Icon Edit (menggunakan SVG agar lebih fleksibel) -->
                    <button class="btn-edit-library" aria-label="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                </div>
                
                <!-- Daftar Produk -->
                <div class="library-list" id="libraryListContent">
                    @foreach($allProducts as $product)
                    <div class="list-item">
                        <img src="{{ $product->img_url ? asset($product->img_url) : asset('images/default.jpg') }}" class="item-img" alt="" draggable="false">
                        <div class="list-item-name">{{ $product->name }}</div>
                        <img src="{{ asset('assets/chevron_right.png') }}" class="item-chevron" alt=">" style="bg: white;">
                    </div>
                    @endforeach
                </div>
            </div>
            
        </div>

        <div class="tab-content" id="tab-custom">
            <div class="calc-display">Rp 0</div>
            <div class="calc-grid">
                <button class="calc-btn">1</button><button class="calc-btn">2</button><button class="calc-btn">3</button><button class="calc-btn">0</button>
                <button class="calc-btn">4</button><button class="calc-btn">5</button><button class="calc-btn">6</button><button class="calc-btn">00</button>
                <button class="calc-btn">7</button><button class="calc-btn">8</button><button class="calc-btn">9</button><button class="calc-btn span-row">+</button>
                <button class="calc-btn span-2">C</button><button class="calc-btn">Del</button>
            </div>
        </div>

    </div>

    <div class="right-panel" id="right-panel-main">
        <div class="bill-header">
            <button class="btn-daftar-bill">Daftar Bill</button>
            <button class="btn-tambah-pelanggan">+ Tambah Pelanggan</button>
        </div>
        <div class="dine-in-selector">Dine In <span style="color:var(--primary);">&#8964;</span></div>
        <div class="bill-content">Tidak Ada Produk</div>
        
        <div class="bill-footer">
            <div class="bill-actions-row">
                <button class="btn-simpan">Simpan Bill</button>
                <button class="btn-cetak">Cetak Bill</button>
            </div>
            <div class="bill-pay-row">
                <button class="btn-pisah">Pisah Bill</button>
                <button class="btn-bayar">Bayar Rp 0</button>
            </div>
        </div>
    </div>

    <div class="right-panel" id="right-panel-edit">
        <div class="edit-instructions">
            Atur produk favorit dan terlaris Anda di halaman ini untuk transaksi cepat
        </div>
        <button class="btn-selesai-edit" id="btnSelesaiEdit">Selesai</button>
    </div>

    <!-- MODAL TAMBAH FAVORIT -->
    <div id="modalAddFavorite" class="modal-overlay">
        <div class="modal-content">
            
            <div class="modal-header">
                <button class="btn-batal" id="btnBatalFav">Batal</button>
                <h3>Tambahkan ke Favorit</h3>
                <div style="width: 70px;"></div> <!-- Spacer agar judul pas di tengah -->
            </div>
            
            <div class="modal-search-container">
                <div class="search-input-wrapper">
                    <input type="text" id="searchFavoriteProduct" placeholder="Cari">
                    <!-- Icon Search SVG -->
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            
            <div class="modal-body list-view" id="favoriteProductList">
                @forelse($availableProducts as $av)
                    <!-- Tiap baris list produk -->
                    <div class="product-list-item select-product-btn" data-id="{{ $av->product_id }}">
                        <img src="{{ $av->img_url ? asset($av->img_url) : asset('images/default.jpg') }}" alt="" draggable="false">
                        <span class="product-name">{{ $av->name }}</span>
                    </div>
                @empty
                    <div class="empty-state">Semua produk sudah ada di daftar favorit.</div>
                @endforelse
            </div>
            
        </div>
    </div>
</div>
<!-- LOADING OVERLAY -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="spinner"></div>
    <p style="font-weight: 600; color: var(--primary);">Memproses...</p>
</div>

<!-- TOAST CONTAINER -->
<div id="toast-container">
    @if(session('success'))
        <div class="toast" id="mainToast">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            {{ session('success') }}
        </div>
    @endif
</div>
@endsection