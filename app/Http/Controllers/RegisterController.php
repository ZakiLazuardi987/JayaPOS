<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showForm()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'phone'    => ['required', 'string', 'max:20', 'unique:staff,phone'], // Langsung validasi 'phone'
            'email'    => ['required', 'string', 'email', 'max:100', 'unique:staff,username'], 
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required'      => 'Nama wajib diisi.',
            'phone.required'     => 'Nomor HP wajib diisi.',
            'phone.unique'       => 'Nomor HP sudah terdaftar.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email sudah terdaftar.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai.',
        ]);

        $user = Staff::create([
            'name'      => $validated['name'],
            'phone'     => $validated['phone'], // Berisi data utuh dari plugin (cth: +628123456)
            'username'  => $validated['email'], 
            'password'  => Hash::make($validated['password']),
            'role'      => 'cashier', 
            'outlet_id' => null,  
            'is_active' => false, 
        ]);

        return redirect('/')
            ->with('success', 'Akun berhasil didaftarkan, menunggu validasi admin.');
    }
}