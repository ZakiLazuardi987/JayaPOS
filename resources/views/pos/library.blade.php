@extends('layouts.pos')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pos-library.css') }}">
@endpush

@section('content')
<div class="pos-container">
    
    <div class="left-panel" id="leftArea">
        <div class="tab-content active" id="tab-library" style="display: flex;">
            
            <div class="library-search-wrapper">
                <input type="text" id="searchLibraryProduct" class="library-search-bar" placeholder="Cari">
                <img src="{{ asset('assets/search_icon.png') }}" alt="Search" class="library-search-icon">
            </div>

            <div class="library-container">
                <div class="library-header">
                    <div style="width: 24px;"></div>
                    <h3>Library</h3>
                    <button class="btn-edit-library" aria-label="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                </div>
                
                <div class="library-list" id="libraryListContent">
                    @foreach($allProducts as $product)
                    <div class="list-item"
                         data-product-id="{{ $product->product_id }}"
                         data-product-name="{{ $product->name }}"
                         data-product-price="{{ $product->base_price }}"
                         data-product-img="{{ $product->img_url ? asset($product->img_url) : asset('images/default.jpg') }}">
                        <img src="{{ $product->img_url ? asset($product->img_url) : asset('images/default.jpg') }}" class="item-img" alt="" draggable="false">
                        <div class="list-item-name">{{ $product->name }}</div>
                        <img src="{{ asset('assets/chevron_right.png') }}" class="item-chevron" alt=">">
                    </div>
                    @endforeach
                </div>
            </div>
            
        </div>
    </div>

    @include('partials.pos-billing')

    {{-- Modal Detail Produk (shared) --}}
    @include('partials.product-detail-modal', ['discounts' => $discounts])

</div>

<div id="loadingOverlay" class="loading-overlay">
    <div class="spinner"></div>
    <p style="font-weight: 600; color: var(--primary);">Memproses...</p>
</div>
<div id="toast-container"></div>
@endsection