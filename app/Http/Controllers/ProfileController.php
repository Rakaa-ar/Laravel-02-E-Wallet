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
}
