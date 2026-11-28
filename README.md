# 💳 Arvanta Digital Wallet (`laravel-02`)

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Golang](https://img.shields.io/badge/Golang-00ADD8?style=for-the-badge&logo=go&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap_5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white)

**Arvanta** adalah aplikasi web *digital wallet* (dompet digital) berbasis arsitektur **multi-service** yang menggabungkan **Laravel** sebagai *core web application* & *user interface* dengan **Golang** sebagai *transaction microservice* mandiri. Aplikasi ini dirancang untuk menangani manajemen saldo, transfer antar-pengguna berbasis ID unik, keamanan PIN transaksi, hingga pelaporan mutasi keuangan.

---

## 🏗️ Arsitektur & Tech Stack

- **Backend Core:** PHP & Laravel (MVC, Eloquent ORM, Blade Templates, DB Transactions)
- **Transaction Microservice:** Golang (REST API mandiri pada port `8080` dengan proteksi *API Key*)
- **Database:** MySQL (`wallet_db`)
- **Frontend & UI:** Bootstrap 5, Custom CSS Variables (Dark & Light Mode terintegrasi), Vanilla JS, Vite
- **Authentication:** Laravel Socialite (Google & GitHub OAuth) + 6-Digit Email OTP
- **Reporting:** DomPDF (Cetak Struk Transaksi & Laporan Rekap Mutasi PDF)

---

## ✨ Fitur Utama

### 1. Sistem Autentikasi Berlapis
- **Registrasi & Login Manual dengan OTP:** Pengguna yang mendaftar secara manual wajib memverifikasi **6-digit kode OTP** melalui `register_otp.blade.php` sebelum akun aktif dan dapat mengakses dashboard.
- **OAuth Social Login (Google & GitHub):** Mendukung login instan menggunakan akun Google maupun GitHub (*bypass* OTP karena identitas sudah terverifikasi oleh provider).
- **Auto-Generated Account ID:** Setiap akun baru otomatis memperoleh nomor ID transfer unik 6-digit sebagai identitas tujuan transfer antar-pengguna.

### 2. Dashboard Dompet & Manajemen Saldo (`/dompet`)
- **Overview Keuangan & Ringkasan Arus Kas:** Menampilkan total saldo aktif secara *real-time* beserta akumulasi **Total Dana Masuk** dan **Total Dana Keluar + Admin**.
- **Top-Up Saldo:** Pengisian saldo dengan format nominal otomatis dan pencatatan keterangan transaksi.
- **Transfer Saldo Antar-User & Tarik Tunai:** Pengiriman saldo ke sesama pengguna Arvanta berdasarkan ID Transfer tujuan maupun penarikan dana, lengkap dengan perhitungan biaya admin dan validasi kecukupan saldo.

### 3. Keamanan Transaksi & Golang Microservice
- **6-Digit PIN Transaksi:** Fitur pengaturan (*Set PIN*) dan pembaruan (*Ganti PIN*) kode keamanan 6-digit. Setiap transaksi keluar (Transfer & Tarik Tunai) wajib divalidasi dengan PIN sebelum diproses.
- **Integrasi Laravel ↔ Golang Microservice:** Pemrosesan transaksi terhubung ke *service* **Golang** pada port `8080` yang dilindungi autentikasi *API Key* pada *request header*.
- **Atomic Database Transaction:** Perpindahan saldo dibungkus dalam `DB::transaction` untuk menjaga konsistensi data antara tabel `dompet` dan `transaksi`.

### 4. Filter Mutasi & Cetak PDF
- **Riwayat Transaksi Terstruktur:** Menampilkan daftar mutasi masuk dan keluar lengkap dengan ikon indikator, keterangan, tanggal, status, dan nominal.
- **Filter Mutasi:** Pencarian riwayat berdasarkan rentang tanggal (*Dari Tanggal* – *Sampai Tanggal*) dan jenis transaksi (*Semua*, *Masuk*, *Keluar*).
- **Cetak Struk & Laporan Mutasi PDF:** Mendukung pencetakan bukti struk individual per transaksi maupun pengunduhan laporan rekap mutasi dalam format PDF.

### 5. Profil Pengguna & Dual-Theme UI (`/profile` & `/dompet`)
- **Manajemen Profil & Live Avatar Preview:** Pembaruan nama lengkap, nomor WhatsApp, bio singkat, salin ID Transfer satu klik, serta ganti foto profil dengan *live preview* sebelum disimpan.
- **Sinkronisasi Dark Mode & Light Mode:** Menggunakan CSS variables (`:root` dan `[data-theme="light"]`) yang disimpan di `localStorage`, sehingga tema otomatis tersinkronisasi di seluruh halaman.

---

## 🚀 Cara Menjalankan di Lokal

1. **Clone repositori ini**
   bash
   git clone [https://github.com/Rakapradiva/laravel-02.git](https://github.com/Rakapradiva/laravel-02.git)
   cd laravel-02