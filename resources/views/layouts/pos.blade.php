<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="staff-id" content="{{ auth()->id() ?? '' }}">
    <title>Menu POS - JAYA POS</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/jaya_square.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- <link rel="stylesheet" href="{{ asset('css/pos.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('css/pos-global.css') }}">

    @stack('styles')
</head>
<body>

    @include('partials.pos-sidebar')

    @yield('content')

    @include('partials.pos-bottom-nav')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/pos/pos-core.js') }}"></script>
    <script src="{{ asset('js/pos/pos-ui.js') }}"></script>
    <script src="{{ asset('js/pos/pos-cart.js') }}"></script>
    <script src="{{ asset('js/pos/pos-product.js') }}"></script>
    <script src="{{ asset('js/pos/pos-printer.js') }}"></script>
    <script src="{{ asset('js/pos/pos-payment.js') }}"></script>
    <script src="{{ asset('js/pos/pos-split-payment.js') }}"></script>
    <script src="{{ asset('js/pos/pos-split-bill.js') }}"></script>
    <script src="{{ asset('js/pos/pos-loyalty.js') }}"></script>
    <script src="{{ asset('js/pos/pos-table.js') }}"></script>
    <script src="{{ asset('js/pos/pos-init.js') }}"></script>
    
    @stack('scripts')
</body>
</html>