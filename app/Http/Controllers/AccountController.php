<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    // Mengunduh riwayat transaksi dalam format CSV atau JSON
    public function export(Request $request)
    {
        $rows = Payment::where('user_id', Auth::id())->latest()->get();

        if ($request->query('format') === 'json') {
            return response()->json($rows);
        }

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['order_id', 'tipe', 'status', 'nominal', 'tanggal']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->order_id,
                    $r->payment_type,
                    $r->status,
                    $r->gross_amount,
                    $r->created_at ? $r->created_at->toDateTimeString() : '',
                ]);
            }
            fclose($out);
        }, 'riwayat-transaksi.csv', [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
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
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,webp',
                'max:2048',
                'dimensions:max_width=2000,max_height=2000',
            ],
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

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

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
