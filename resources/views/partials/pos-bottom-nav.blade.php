<div class="bottom-nav">
    <button class="nav-btn btn-hamburger" id="btnMenu">
        <img src="{{ asset('assets/hamburger_icon.png') }}" alt="Menu" style="width: 32px; height: 32px; object-fit: contain;">
    </button>

    @if(isset($isFloorPlan) && $isFloorPlan)
        {{-- Floor Plan Area Tabs --}}
        <style>
            .bottom-nav-tabs::-webkit-scrollbar { display: none; }
        </style>
        <div class="bottom-nav-tabs" style="display:flex; flex:1; overflow-x:auto; align-items:stretch; scrollbar-width: none; -ms-overflow-style: none;">
            @if(isset($areas) && count($areas) > 0)
                @foreach($areas as $areaIdx => $area)
                    @php
                        $occupiedCount = $area->tables->where('status', 'occupied')->count();
                        $totalCount = $area->tables->count();
                    @endphp
                    <button class="nav-btn area-tab-btn {{ $areaIdx === 0 ? 'active' : '' }}" data-id="{{ $area->area_id }}" style="border-right:1px solid rgba(255,255,255,0.15);">
                        {{ $area->name }} - {{ $occupiedCount }}/{{ $totalCount }}
                    </button>
                @endforeach
            @endif
        </div>
    @elseif(request()->routeIs('pos.favorit', 'pos.library', 'pos.custom'))
        {{-- Standard POS Tabs --}}
        <a href="{{ route('pos.favorit') }}" class="nav-btn {{ request()->routeIs('pos.favorit') ? 'active' : '' }}">
            <img src="{{ asset('assets/favorite_icon.png') }}" alt="Favorit" style="width: 24px; height: 24px; object-fit: contain;">
            Favorit
        </a>

        <a href="{{ route('pos.library') }}" class="nav-btn {{ request()->routeIs('pos.library') ? 'active' : '' }}">
            <img src="{{ asset('assets/library_icon.png') }}" alt="Library" style="width: 24px; height: 24px; object-fit: contain;">
            Library
        </a>

        <a href="{{ route('pos.custom') }}" class="nav-btn {{ request()->routeIs('pos.custom') ? 'active' : '' }}">
            <img src="{{ asset('assets/custom_icon.png') }}" alt="Custom" style="width: 24px; height: 24px; object-fit: contain;">
            Custom
        </a>
    @endif
</div>