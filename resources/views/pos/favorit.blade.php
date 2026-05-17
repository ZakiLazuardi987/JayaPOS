@extends('layouts.pos')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pos-favorit.css') }}">
@endpush

@section('content')
<div class="pos-container">
    
    <div class="left-panel" id="leftArea">
        <div class="tab-content active" id="tab-favorit">
            <div class="carousel-container" id="carousel-normal">
                @for ($page = 0; $page < $totalPages; $page++)
                    <div class="grid-page {{ $page == 0 ? 'active' : '' }}">
                        @for ($i = 0; $i < 16; $i++)
                            @php $index = ($page * 16) + $i; @endphp
                            
                            @if (isset($products[$index]))
                                <div class="product-card" 
                                     data-product-id="{{ $products[$index]->product_id }}"
                                     data-product-name="{{ $products[$index]->name }}"
                                     data-product-price="{{ $products[$index]->base_price }}"
                                     data-product-img="{{ $products[$index]->img_url ? asset($products[$index]->img_url) : asset('images/default.jpg') }}">
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
            
            @if($totalPages > 1)
            <div class="carousel-dots" id="dots-normal">
                @for ($page = 0; $page < $totalPages; $page++)
                    <button class="dot {{ $page == 0 ? 'active' : '' }}" data-page="{{ $page }}"></button>
                @endfor
            </div>
            @endif
        </div>

        <div class="tab-content" id="tab-edit-favorit" style="display:none;">
            <div class="carousel-container" id="carousel-edit">
                @for ($page = 0; $page < $totalPages; $page++)
                    <div class="grid-page {{ $page == 0 ? 'active' : '' }}">
                        @for ($i = 0; $i < 16; $i++)
                            @php $index = ($page * 16) + $i; @endphp
                            
                            @if (isset($products[$index]))
                                <div class="product-card edit-mode" data-id="{{ $products[$index]->product_id }}">
                                    <div class="btn-remove">&times;</div>
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
            
            @if($totalPages > 1)
            <div class="carousel-dots" id="dots-edit">
                @for ($page = 0; $page < $totalPages; $page++)
                    <button class="dot {{ $page == 0 ? 'active' : '' }}" data-page="{{ $page }}"></button>
                @endfor
            </div>
            @endif
        </div>
    </div>

    @include('partials.pos-billing')

    <div class="right-panel" id="right-panel-edit" style="display: none;">
        <div class="edit-instructions">
            Atur produk favorit dan terlaris Anda di halaman ini untuk transaksi cepat
        </div>
        <button class="btn-selesai-edit" id="btnSelesaiEdit">Selesai</button>
    </div>

    {{-- Modal Tambah Favorit --}}
    <div id="modalAddFavorite" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <button class="btn-batal" id="btnBatalFav">Batal</button>
                <h3>Tambahkan ke Favorit</h3>
                <div style="width: 70px;"></div>
            </div>
            <div class="modal-search-container">
                <div class="search-input-wrapper">
                    <input type="text" id="searchFavoriteProduct" placeholder="Cari">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <div class="modal-body list-view" id="favoriteProductList">
                @forelse($availableProducts as $av)
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

    {{-- Modal Detail Produk (shared) --}}
    @include('partials.product-detail-modal', ['discounts' => $discounts])

</div>

<div id="loadingOverlay" class="loading-overlay">
    <div class="spinner"></div>
    <p style="font-weight: 600; color: var(--primary);">Memproses...</p>
</div>
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