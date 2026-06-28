@extends('layouts.pos')

@section('title', 'Pengaturan - JayaPOS')

@section('content')
<div class="pos-container" style="background: #f3f4f6; min-height: 100vh;">
    <div class="header-section" style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb; background: #fff;">
        <h2 style="margin: 0; font-size: 1.5rem; color: #111827;">Pengaturan</h2>
        <p style="margin: 4px 0 0; color: #6b7280; font-size: 0.9rem;">Kelola profil akun dan konfigurasi outlet/toko</p>
    </div>

    <div style="padding: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; max-width: 1200px; margin: 0 auto;">
        
        <!-- Profil Akun -->
        <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); align-self: start;">
            <h3 style="margin-top: 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 12px; margin-bottom: 20px; color: #111827;">Profil Akun (Staf)</h3>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">Nama Staf</label>
                <input type="text" value="{{ $staff->name ?? 'Administrator' }}" readonly style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; background: #f9fafb; color: #4b5563; font-family: inherit;">
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">Username / Kontak</label>
                <input type="text" value="{{ $staff->username ?? ($staff->phone ?? '-') }}" readonly style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; background: #f9fafb; color: #4b5563; font-family: inherit;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">Role Akses</label>
                <input type="text" value="{{ ucfirst($staff->role ?? 'Kasir') }}" readonly style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; background: #f9fafb; color: #4b5563; font-family: inherit;">
            </div>
            
            <div style="margin-top: 24px; padding: 12px; background: #eff6ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
                <p style="font-size: 0.85rem; color: #1e3a8a; margin: 0;"><em>* Data profil hanya dapat diubah melalui Backoffice Administrator.</em></p>
            </div>
        </div>

        <!-- Konfigurasi Toko -->
        <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); align-self: start;">
            <h3 style="margin-top: 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 12px; margin-bottom: 20px; color: #111827;">Konfigurasi Outlet</h3>
            
            @if(session('success'))
                <div style="background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #bbf7d0;">
                    <strong style="margin-right: 4px;">Berhasil!</strong> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #fecaca;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('pos.updatePengaturanOutlet') }}" method="POST">
                @csrf
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">Nama Outlet</label>
                    <input type="text" name="name" value="{{ old('name', $outlet->name) }}" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; transition: border-color 0.2s;">
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">Nomor Telepon Outlet</label>
                    <input type="text" name="phone" value="{{ old('phone', $outlet->phone) }}" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; transition: border-color 0.2s;">
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #374151;">Alamat Lengkap</label>
                    <textarea name="address" rows="4" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; resize: vertical; transition: border-color 0.2s;">{{ old('address', $outlet->address) }}</textarea>
                    <small style="color: #6b7280; display: block; margin-top: 4px;">Alamat dan info outlet akan dicetak pada bagian atas struk/bill.</small>
                </div>
                
                <button type="submit" style="background: var(--primary, #b91c1c); color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; width: 100%; font-size: 1rem; transition: background-color 0.2s;">
                    Simpan Konfigurasi
                </button>
            </form>
        </div>

    </div>
</div>

<style>
    input:focus, textarea:focus {
        outline: none;
        border-color: var(--primary, #b91c1c) !important;
        box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.1);
    }
</style>
@endsection
