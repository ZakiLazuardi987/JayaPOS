<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="staff-id" content="{{ auth()->id() ?? '' }}">
    <title>Menu POS - JAYA POS</title>
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
    <script src="{{ asset('js/pos.js') }}"></script>
    
    @stack('scripts')
</body>
</html>