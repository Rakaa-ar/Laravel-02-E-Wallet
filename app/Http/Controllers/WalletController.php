<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Wallet;
use App\Models\User;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $user = User::find($userId);
        // Auto-generate 6 digit ID User jika belum punya
        if (!$user->account_number) {
            do {
                $code = rand(100000, 999999);
            } while (User::where('account_number', $code)->exists());

            User::where('id', $user->id)->update([
                'account_number' => $code
            ]);

            $user->account_number = $code;
        }

        $dompet = Wallet::firstOrCreate(
            ['user_id' => Auth::id()],
            ['saldo' => 0]
        );

        $riwayat = $dompet->transaksi()->latest()->paginate(5);

        return view('wallet.index', compact('dompet', 'riwayat'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis' => 'required|in:masuk,keluar',
            'jumlah' => 'required|numeric|min:1000',
            'keterangan' => 'nullable|string'
        ], [
            'jumlah.min' => 'Nominal minimal transaksi Rp 1.000'
        ]);

        $user = User::find(Auth::id());

        if ($request->jenis === 'keluar') {
            $request->validate([
                'pin' => 'required|numeric|digits:6',
            ], [
                'pin.required' => 'PIN Keamanan Wajib di isi untuk tarik tunaik',
                'pin.digits' => 'PIN harus 6 digit angka.'
            ]);

            $errorPin = $this->verifikasiPin($user, $request->pin);
            if ($errorPin) {
                return back()->with('error', $errorPin);
            }
        }

        $dompet = Wallet::firstOrCreate(
            ['user_id' => Auth::id()],
            ['saldo' => 0]
        );

        $totalKeluarHariIni = $dompet->transaksi()
            ->where('jenis', 'keluar')
            ->whereDate('created_at', today())
            ->sum('jumlah');

        $tipeTransaksi = $request->jenis === 'masuk' ? 'topup' : 'tarik';

        try {
            $response = Http::timeout(5)->post('http://localhost:8080/api/transaksi', [
                'saldo_saat_ini' => (float) $dompet->saldo,
                'jenis' => $request->jenis,
                'tipe_transaksi' => $tipeTransaksi,
                'jumlah' => (float) $request->jumlah,
                'total_keluar_hari_ini' => (float) $totalKeluarHariIni
            ]);

            $result = $response->json();

            if (!$result['status']) {
                return back()->with('error', $result['pesan']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Koneksi ke GO gagal, pastikan server Go menyala!');
        }

        try {
            $biayaAdmin = $result['biaya_admin'] ?? 0;

            DB::transaction(function () use ($dompet, $request, $result, $biayaAdmin) {
                $catatanAdmin = $biayaAdmin > 0 ? ' (Admin Rp ' . number_format($biayaAdmin, 0, ',', '.') . ')' : '';
                //catatan riwayat nominal
                $dompet->transaksi()->create([
                    'kode_transaksi' => 'TRX-' . date('YmdHis') . '-' . rand(100, 999),
                    'jenis'          => $request->jenis,
                    'jumlah'         => $request->jumlah + $biayaAdmin,
                    'keterangan'     => ($request->keterangan ?? ($request->jenis == 'masuk' ? 'Top Up Saldo' : 'Tarik Tunai')) . $catatanAdmin
                ]);

                // Update Saldo
                $dompet->saldo = $result['sisa_saldo'];
                $dompet->save();
            });

            //Pesan Sukses Dinamis
            $tipeTransaksi = $request->jenis == 'masuk' ? 'Top-Up Saldo' : 'Tarik Tunai';
            $infoAdmin = $biayaAdmin > 0 ? ' + Biaya Admin Rp ' . number_format($biayaAdmin, 0, ',', '.') : '';
            return redirect('/dompet')->with('success', $tipeTransaksi . ' Rp ' . number_format($request->jumlah, 0, ',', '.') . $infoAdmin . ' berhasil!');
        } catch (\Exception $e) {
            return back()->with('error', 'Sistem terputus! Gagal memproses transaksi database.');
        }
    }

    public function transfer(Request $request)
    {
        // Validasi Input
        $validator = Validator::make($request->all(), [
            'account_number' => 'required|numeric|digits:6|exists:users,account_number',
            'jumlah'         => 'required|numeric|min:1000',
            'keterangan'     => 'nullable|string|max:255',
            'pin'            => 'required|numeric|digits:6', // Validasi input PIN
        ], [
            'account_number.required' => 'ID User tujuan wajib diisi.',
            'account_number.digits'   => 'ID User harus 6 digit angka.',
            'account_number.exists'   => 'ID User penerima tidak ditemukan!',
            'jumlah.min'              => 'Nominal transfer minimal Rp 1.000.',
            'pin.required'            => 'PIN Keamanan wajib dimasukkan.',
            'pin.digits'              => 'PIN harus 6 digit angka.',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        $pengirimUser = User::find(Auth::id());

        $errorPin = $this->verifikasiPin($pengirimUser, $request->pin);
        if ($errorPin) {
            return back()->with('error', $errorPin);
        }

        $pengirimWallet = Wallet::where('user_id', $pengirimUser->id)->first();

        // Cegah transfer ke ID sendiri
        if ((string)$pengirimUser->account_number === (string)$request->account_number) {
            return back()->with('error', 'Tidak bisa transfer ke ID User sendiri!');
        }

        // Cari User & Wallet Penerima
        $penerimaUser   = User::where('account_number', $request->account_number)->first();
        $penerimaWallet = Wallet::firstOrCreate(
            ['user_id' => $penerimaUser->id],
            ['saldo'   => 0]
        );

        $totalKeluarHariIni =  $pengirimWallet->transaksi()
            ->where('jenis', 'keluar')
            ->whereDate('created_at', today())
            ->sum('jumlah');

        // Cek Kecukupan Saldo ke Go Microservice
        try {
            $response = Http::timeout(5)->post('http://localhost:8080/api/transaksi', [
                'saldo_saat_ini' => (float) $pengirimWallet->saldo,
                'jenis'          => 'keluar',
                'jumlah'         => (float) $request->jumlah,
                'tipe_transaksi' => 'transfer',
                'total_keluar_hari_ini' => (float) $totalKeluarHariIni,
            ]);

            $result = $response->json();

            if (!$result['status']) {
                return back()->with('error', 'Transfer gagal: ' . $result['pesan']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Koneksi ke Go Microservice gagal!');
        }

        // Transaksi DB Atomik
        try {
            $biayaAdmin = $result['biaya_admin'] ?? 0;

            DB::transaction(function () use ($pengirimWallet, $penerimaWallet, $pengirimUser, $penerimaUser, $request, $result, $biayaAdmin) {
                $baseKode = 'TRX-' . date('YmdHis') . '-' . rand(100, 999);
                $catatanAdmin = $biayaAdmin > 0 ? ' (Admin Rp ' . number_format($biayaAdmin, 0, ',', '.') .  ')' : '';

                $pengirimWallet->saldo = $result['sisa_saldo'];
                $pengirimWallet->save();

                $pengirimWallet->transaksi()->create([
                    'kode_transaksi' => $baseKode . '-OUT',
                    'jenis'          => 'keluar',
                    'jumlah'         => $request->jumlah + $biayaAdmin,
                    'keterangan'     => 'Transfer ke ' . $penerimaUser->name . ($request->keterangan ? ' - ' . $request->keterangan : '') . $catatanAdmin
                ]);

                $penerimaWallet->saldo += $request->jumlah;
                $penerimaWallet->save();

                $penerimaWallet->transaksi()->create([
                    'kode_transaksi' => $baseKode . '-IN',
                    'jenis'          => 'masuk',
                    'jumlah'         => $request->jumlah,
                    'keterangan'     => 'Transfer dari ' . $pengirimUser->name . ($request->keterangan ? ' - ' . $request->keterangan : '')
                ]);
            });

            $infoAdmin = $biayaAdmin > 0 ? ' (Biaya Admin Rp ' . number_format($biayaAdmin, 0, ',', '.') . ')' : ' (Gratis Admin)';
            return redirect('/dompet')->with('success', 'Transfer Rp ' . number_format($request->jumlah, 0, ',', '.') . ' ke ' . $penerimaUser->name . $infoAdmin . ' berhasil!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses transaksi database: ' . $e->getMessage());
        }
    }

    public function cetakStruk($kode_transaksi)
    {
        $transaksi = Transaksi::where('kode_transaksi', $kode_transaksi)->firstOrFail();

        $pdf = Pdf::loadView('wallet.struk', compact('transaksi'));

        return $pdf->stream('Struk-Transaksi-' . $kode_transaksi . '.pdf');
    }

    public function setPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|numeric|digits:6|confirmed',
        ], [
            'pin.required' => 'PIN Transaksi Wajib di isi',
            'pin.digits' => 'PIN Harus Berupa 6 angka',
            'pin.confirmed' => 'Konfirmasi PIN tidak cocok',
        ]);

        $user = User::find(Auth::id());
        $user->pin = Hash::make($request->pin);
        $user->save();

        return back()->with('success', 'PIN transaksi berhasil dibuat! Silakan lanjutkan transaksi.');
    }

    public function cekPenerima($account_number)
    {
        if ((string) Auth::user()->account_number === (string) $account_number) {
            return response()->json([
                'status' => false,
                'pesan' => 'Tidak bisa transfer ke id kamu sendiri'
            ]);
        }

        $penerima = User::where('account_number', $account_number)->first();

        if (!$penerima) {
            return response()->json([
                'status' => false,
                'pesan' => 'Maaf ID User Tidak di temukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'nama' => $penerima->name
        ]);
    }

    public function verifikasiPin($user, $inputPin)
    {
        if (!$user->pin) {
            return 'Anda Belum Membuat 6-Digit PIN transaksi!';
        }

        // cek dlu akun kena blokir ga
        if ($user->pin_locked_until && now()->lessThan($user->pin_locked_until)) {
            $sisaMenit = ceil(now()->diffInSeconds($user->pin_locked_until) / 60);
            return "Transaksi di blokir sementara karna 3x salah PIN. COBA LAGI DALAM {$sisaMenit} Menit.";
        }

        // Cocokkan pin input sama hash di db
        if (!Hash::check($inputPin, $user->pin)) {
            $attempts = $user->pin_attempts + 1;

            if ($attempts >= 3) {
                $user->update([
                    'pin_attempts' => 0,
                    'pin_locked_until' => now()->addMinutes(15),
                ]);
                return 'PIN SALAH 3 KALI ANDA DI BLOKIR COBA 15 MENIT LAGI.';
            }

            $user->update(['pin_attempts' => $attempts]);
            $sisa = 3 - $attempts;
            return "PIN keamanan Salah! sisa percobaan: {$sisa} kali lagi";
        }

        // Jika pin benar maka false sebelumnya clear
        if ($user->pin_attempts > 0 || $user->pin_locked_until) {
            $user->update([
                'pin_attempts' => 0,
                'pin_locked_until' => null,
            ]);
        }

        return null;
    }

    public function updatePin(Request $request)
    {
        $request->validate([
            'old_pin' => 'required|digits:6',
            'new_pin' => 'required|digits:6|confirmed|different:old_pin',
        ], [
            'new_pin.different' => 'PIN baru tidak boleh sama dengan PIN lama',
            'new_pin.confirmed' => 'Konfirmasi PIN baru tidak cocok bray',
            'new_pin.digits' => 'PIN baru harus berupa 6 digit angka bray',
        ]);

        $user = User::find(Auth::id());

        $errorPin = $this->verifikasiPin($user, $request->old_pin);
        if ($errorPin) {
            return back()->with('error', $errorPin);
        }

        $user->update([
            'pin' => Hash::make($request->new_pin),
        ]);

        return back()->with('success', 'PIN KEAMANAN BERHASIL DI PERBARUI');
    }
}
