<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Transaksi;

class ProfileController extends Controller
{
    public function index()
    {
        $users = Auth::user();
        $wallet = $users->wallet;

        $totalMasuk = 0;
        $totalKeluar = 0;

        if ($wallet) {
            $totalMasuk = Transaksi::where('wallet_id', $wallet->id)
            ->where('jenis', 'masuk')
            ->sum('jumlah');

            $totalKeluar = Transaksi::where('wallet_id', $wallet->id)
            ->where('jenis', 'keluar')
            ->sum('jumlah');
        }
        return view('profile.index', compact('users', 'wallet', 'totalMasuk', 'totalKeluar'));
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $users */
        $users = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,png,jepg,webp', 'max:2048'],
        ], [
            'avatar.image' => 'File harus berupa gambar',
            'avatar.mimes' => 'Format Harus jpg png jepg webp',
            'avatar.max' => 'Size Foto tidak boleh melebihi 2mb',
        ]);

        $data = [
            'name' => $request->name,
            'phone' =>  $request->phone,
            'bio'  => $request->bio,
        ];

        if ($request->hasFile('avatar')) {
            if ($users->avatar && Storage::disk('public')->exists($users->avatar)) {
                Storage::disk('public')->delete($users->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $users->update($data);

        return back()->with('success', 'Profile Berhasil di Perbaruiii');
    }
}
