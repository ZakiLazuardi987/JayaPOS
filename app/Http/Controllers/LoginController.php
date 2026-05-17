<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showForm()
    {
        return view('login');
    }

    public function checkAccount(Request $request)
    {
        $request->validate(['identifier' => 'required|string']);
        $identifier = $request->identifier;

        if (is_numeric($identifier) && str_starts_with($identifier, '0')) {
            $identifier = '+62' . ltrim($identifier, '0');
        }

        $user = Staff::where('username', $identifier)
                     ->orWhere('phone', $identifier)
                     ->first();

        if ($user) {
            return response()->json([
                'status' => 'success', 
                'identifier' => $user->username 
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Akun tidak ditemukan.'], 404);
    }

    // FUNGSI BARU UNTUK STEP 2
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string'
        ]);

        // 1. Cek User dan Status Aktif
        $user = Staff::where('username', $request->identifier)->first();
        
        if ($user && !$user->is_active) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Akun Anda sedang menunggu validasi Admin.'
            ], 403);
        }

        // 2. Coba Login
        if (Auth::attempt(['username' => $request->identifier, 'password' => $request->password])) {
            $user = Auth::user();
            
            // Tampilkan selalu semua cabang yang aktif
            $outlets = Outlet::where('status', 'active')->get();
            
            // LOGIN BERHASIL -> Session & Token otomatis berubah
            return response()->json([
                'status' => 'success',
                'outlets' => $outlets,
                'user_outlet_id' => $user->outlet_id,
                // TAMBAHKAN INI: Kirim token baru ke Frontend
                'new_csrf' => csrf_token() 
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Kata sandi salah.'], 401);
    }

    // FUNGSI BARU UNTUK STEP 3
    public function selectOutlet(Request $request)
    {
        $request->validate(['outlet_id' => 'required']);
        
        $user = Auth::user();
        
        // Pengecekan keamanan: cegah staff biasa memilih outlet lain
        if ($user->role !== 'admin') {
            if (!$user->outlet_id) {
                return response()->json(['status' => 'error', 'message' => 'Anda belum ditempatkan di cabang manapun.'], 403);
            }
            if ($user->outlet_id != $request->outlet_id) {
                return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses ke cabang ini.'], 403);
            }
        }
        
        // Simpan outlet ke session agar bisa dipakai di menu POS
        session(['active_outlet' => $request->outlet_id]);
        
        return response()->json(['status' => 'success', 'redirect' => url('/pos')]);
    }
}