<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    // Menampilkan halaman akun
    public function index()
    {
        $user = Auth::user();

        return view('account', compact('user'));
    }

    // Mengupdate profil dan password
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'name')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'notelp' => ['nullable', 'string', 'max:20'],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => [
                'nullable',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ], [
            'username.unique' => 'Nama pengguna ini sudah diambil, silakan gunakan nama lain.',
            'email.unique' => 'Email ini sudah terdaftar.',
        ]);

        $user->name = $request->username;
        $user->email = $request->email;
        $user->phone_number = $request->notelp;

        if ($request->filled('new_password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama salah.'])->withInput();
            }
            $user->password = Hash::make($request->new_password);
        }

        /** @var User $user */
        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    // Menghapus Akun
    public function destroy(Request $request)
    {
        $user = Auth::user();

        // Logout dulu
        Auth::logout();

        // Hapus data user
        /** @var User $user */
        $user->delete();

        // Invalidasi session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Akun Anda telah dihapus.');
    }
}
