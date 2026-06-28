<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - JayaPOS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pos-global.css') }}">
    <style>
        :root {
            --primary: #9e1b1b;
            --primary-hover: #7f1616;
            --coral: #f87171;
            --bg-sidebar: #ffffff;
            --bg-content: #f3f4f6;
            --text-dark: #1f2937;
            --text-muted: #6b7280;
            --border: #e5e7eb;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            display: flex;
            flex-direction: row; /* FIX: override flex-direction: column from pos-global.css */
            height: 100vh;
            overflow: hidden;
            background: var(--bg-content);
        }

        /* Sidebar Styles */
        .settings-sidebar {
            width: 300px;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 10;
        }

        .sidebar-header {
            padding: 24px 20px;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            border-bottom: 1px solid var(--border);
        }

        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 20px 0;
        }

        .menu-category {
            padding: 0 20px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 12px;
            margin-top: 24px;
            letter-spacing: 0.05em;
        }

        .menu-category:first-child {
            margin-top: 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            color: var(--text-dark);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .menu-item:hover {
            background: #f9fafb;
        }

        .menu-item.active {
            background: var(--primary);
            color: white;
        }

        .badge-aktif {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid var(--border);
        }

        .btn-keluar {
            display: block;
            width: 100%;
            padding: 14px;
            background: var(--coral);
            color: white;
            text-align: center;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-keluar:hover {
            background: #ef4444;
        }

        /* Content Area Styles */
        .settings-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .content-header {
            padding: 24px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .content-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .btn-simpan {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-simpan:hover {
            background: var(--primary-hover);
        }

        .content-body {
            flex: 1;
            padding: 0 32px 100px; /* space for bottom bar */
            overflow-y: auto;
        }

        /* Cards & Forms */
        .settings-card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .card-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        .card-row:last-child {
            border-bottom: none;
        }

        .form-group {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        .form-group:last-child {
            border-bottom: none;
        }

        .form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 8px;
            letter-spacing: 0.05em;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-family: inherit;
            font-size: 0.95rem;
            color: var(--text-dark);
            box-sizing: border-box;
            background: #f9fafb;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
        }

        /* Custom Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #cbd5e1;
            transition: .4s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: var(--primary);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(20px);
        }

        /* Bottom Footer Bar */
        .bottom-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: var(--primary);
            color: white;
            padding: 16px 0;
            text-align: center;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 -4px 10px rgba(0,0,0,0.1);
        }

        /* Tab Management */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="settings-sidebar">
        <div class="sidebar-header">
            Pengaturan
        </div>
        <div class="sidebar-menu">
            <div class="menu-category">PEMBAYARAN</div>
            <div class="menu-item" onclick="switchTab('pembayaran')">Pembayaran</div>
            <div class="menu-item" id="nav-pajak" onclick="switchTab('pajak')">Pajak <span class="badge-aktif" id="badge-pajak" style="display:none;">Aktif</span></div>
            
            <div class="menu-category">AKUN</div>
            <div class="menu-item" id="nav-bahasa" onclick="switchTab('bahasa')">Bahasa</div>
            <div class="menu-item active" id="nav-profil" onclick="switchTab('profil')">Profil</div>
        </div>
        <div class="sidebar-footer">
            <a href="{{ route('pos.favorit') }}" class="btn-keluar">KELUAR</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="settings-content">
        
        <!-- TAB: PAJAK -->
        <div id="tab-pajak" class="tab-content">
            <div class="content-header">
                <h1 class="content-title">Pajak</h1>
            </div>
            <div class="content-body">
                <div class="settings-card">
                    <div class="card-row">
                        <span style="font-weight: 500; color: var(--text-dark);">Pajak</span>
                        <label class="toggle-switch">
                            <input type="checkbox" checked onchange="document.getElementById('badge-pajak').style.display = this.checked ? 'inline-block' : 'none';">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="card-row" style="background: #f9fafb;">
                        <span style="color: var(--text-muted); font-size: 0.95rem;">pb resto</span>
                        <span style="font-weight: 700; color: var(--text-dark);">10 %</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: BAHASA -->
        <div id="tab-bahasa" class="tab-content">
            <div class="content-header">
                <h1 class="content-title">Bahasa</h1>
            </div>
            <div class="content-body">
                <div class="settings-card">
                    <div class="card-row">
                        <span style="font-weight: 500; color: var(--text-dark);">Bahasa</span>
                        <select class="form-control" style="width: auto; min-width: 200px; background: white;">
                            <option value="id">Bahasa Indonesia (ID)</option>
                            <option value="en">English (EN)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: PROFIL -->
        <div id="tab-profil" class="tab-content active">
            <form action="{{ route('pos.updatePengaturanOutlet') }}" method="POST">
                @csrf
                <div class="content-header">
                    <h1 class="content-title">Profil</h1>
                    <button type="submit" class="btn-simpan">Simpan</button>
                </div>
                <div class="content-body">
                    
                    @if(session('success'))
                        <div style="background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #bbf7d0;">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="settings-card">
                        <div class="form-group">
                            <label class="form-label">NAMA BISNIS</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $outlet->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">NOMOR TELEPON</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $outlet->phone) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">ALAMAT BISNIS</label>
                            <textarea name="address" class="form-control" rows="3" required>{{ old('address', $outlet->address) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">PROVINSI</label>
                            <select class="form-control">
                                <option>Jawa Barat</option>
                                <option>Jawa Timur</option>
                                <option>DKI Jakarta</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">KOTA / KABUPATEN</label>
                            <select class="form-control">
                                <option>Kota Malang</option>
                                <option>Kota Bandung</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">KECAMATAN</label>
                            <select class="form-control">
                                <option>Lowokwaru</option>
                                <option>Sukun</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">KODE POS</label>
                            <select class="form-control">
                                <option>65141</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- TAB: PEMBAYARAN -->
        <div id="tab-pembayaran" class="tab-content">
            <div class="content-header">
                <h1 class="content-title">Metode Pembayaran</h1>
            </div>
            <div class="content-body">
                <div class="settings-card">
                    <div class="card-row">
                        <span style="font-weight: 500;">Tunai (Cash)</span>
                        <span class="badge-aktif" style="background: var(--primary); display: inline-block;">Aktif</span>
                    </div>
                    <div class="card-row">
                        <span style="font-weight: 500;">QRIS Dinamis</span>
                        <span class="badge-aktif" style="background: var(--primary); display: inline-block;">Aktif</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom-bar">
            {{ $outlet->name ?? 'Toko Kopi Jaya' }}
        </div>
    </div>

    <script>
        // Init tax badge
        document.getElementById('badge-pajak').style.display = 'inline-block';

        function switchTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            // Remove active class from all nav items
            document.querySelectorAll('.menu-item').forEach(nav => {
                nav.classList.remove('active');
            });

            // Show selected tab
            const targetTab = document.getElementById('tab-' + tabId);
            if (targetTab) {
                targetTab.classList.add('active');
            }
            
            // Set nav active
            const targetNav = document.getElementById('nav-' + tabId);
            if (targetNav) {
                targetNav.classList.add('active');
            } else {
                // For 'pembayaran' which might not have an id, let's just find it by text if needed, 
                // but we can just use event.currentTarget if we passed 'this'.
                // Using a simple event handler override:
                event.currentTarget.classList.add('active');
            }
        }
    </script>
</body>
</html>
