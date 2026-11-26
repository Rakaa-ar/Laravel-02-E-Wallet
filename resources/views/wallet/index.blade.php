<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arvanta | Raka</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="fw-bold mb-1 text-white">Overview Keuangan</h3>
                <p class="text-secondary mb-0">Halo {{ Auth::user()->name ?? 'Raka' }}, kelola saldomu di sini.</p>
            </div>

            <!-- Wrapper buat tombol Logout & Avatar -->
            <div class="d-flex align-items-center gap-3">

                <!-- Tombol Logout -->
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit"
                        class="btn btn-outline-danger btn-sm rounded-pill px-3 d-flex align-items-center gap-2 fw-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                            <path fill-rule="evenodd"
                                d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z" />
                            <path fill-rule="evenodd"
                                d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
                        </svg>
                        Logout
                    </button>
                </form>

                <div class="bg-white p-1 rounded-circle shadow-sm d-flex">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Raka Pradiva') }}&background=6f42c1&color=fff"
                        alt="Profile" class="rounded-circle" width="48" height="48">
                </div>

            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="d-flex gap-2 mb-2">
                    <button type="button"
                        class="btn btn-outline-info w-100 mb-3 py-2 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3"
                        data-bs-toggle="modal" data-bs-target="#modalTarikTunai">
                        <i class="bi bi-cash-stack fs-5"></i> Tarik Tunai
                    </button>
                    <button type="button"
                        class="btn btn-outline-info w-100 mb-3 py-2 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3"
                        data-bs-toggle="modal" data-bs-target="#modalTransfer">
                        <i class="bi bi-arrow-left-right fs-5"></i> Transfer Saldo
                    </button>
                </div>
                <div class="card wallet-card p-4 mb-4">
                    <p class="mb-2 opacity-75 small fw-semibold">TOTAL SALDO</p>
                    <h2 class="fw-bold mb-4">Rp {{ number_format($dompet->saldo, 0, ',', '.') }}</h2>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="badge bg-white bg-opacity-25 text-white px-3 py-2 rounded-pill">
                            <i class="bi bi-person-badge me-1"></i> ID User:
                            {{ Auth::user()->account_number ?? '------' }}
                        </span>

                        @if (Auth::user()->pin)
                            <button type="button"
                                class="btn btn-sm bg-opacity-25 text-dark fw-semibold rounded-pill px-3"
                                data-bs-toggle="modal" data-bs-target="#modalGantiPin">
                                <i class="bi bi-key-fill me-1"></i> Ganti PIN
                            </button>
                        @endif
                    </div>
                </div>

                <div class="card card-custom p-4">
                    <h5 class="fw-bold mb-4">Buat Transaksi</h5>

                    <!-- ID form ditambahkan -->
                    <form action="/dompet/transaksi" method="POST" id="formTransaksi">
                        @csrf
                        <!-- Kunci jenis transaksi hanya untuk Top-Up -->
                        <input type="hidden" name="jenis" value="masuk">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">JENIS TRANSAKSI</label>
                            <input type="text" class="form-control" value="Top-Up Saldo" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="jumlah_formatted" class="form-label text-muted small fw-semibold">NOMINAL
                                (RP)</label>
                            <input type="text" id="jumlah_formatted" class="form-control" placeholder="Contoh 50.000"
                                required autocomplete="off">
                            <input type="hidden" name="jumlah" id="jumlah" required>
                            <div id="errorTopup" class="text-danger small mt-1"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">CATATAN / KETERANGAN</label>
                            <input type="text" name="keterangan" class="form-control"
                                placeholder="Contoh: Bayar jajan siomay">
                        </div>

                        <!-- ID tombol ditambahkan -->
                        <button type="submit" id="btnProses" class="btn btn-primary w-100">
                            <i class="bi bi-send-fill me-2"></i>Proses Sekarang
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card card-custom p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Riwayat Transaksi</h5>
                    </div>
                    <!-- Ringkasan Arus Kas -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 wallet-dark-box">
                                <small class="text-secondary d-block mb-1">Total Dana Masuk</small>
                                <strong class="text-success fs-5">+ Rp
                                    {{ number_format($totalMasuk, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 wallet-dark-box">
                                <small class="text-secondary d-block mb-1">Total Dana Keluar + Admin
                                    </small>
                                <strong class="text-danger fs-5">- Rp
                                    {{ number_format($totalKeluar, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Form Filter Mutasi & Cetak PDF -->
                    <form method="GET" action="{{ url('/dompet') }}" class="row g-2 align-items-end mb-4">
                        <div class="col-md-3">
                            <label class="form-label small text-secondary mb-1">Dari Tanggal</label>
                            <input type="date" name="dari_tanggal"
                                class="form-control form-control-sm wallet-dark-input"
                                value="{{ request('dari_tanggal') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary mb-1">Sampai Tanggal</label>
                            <input type="date" name="sampai_tanggal"
                                class="form-control form-control-sm wallet-dark-input"
                                value="{{ request('sampai_tanggal') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-secondary mb-1">Jenis</label>
                            <select name="jenis" class="form-select form-select-sm wallet-dark-input">
                                <option value="">Semua</option>
                                <option value="masuk" {{ request('jenis') == 'masuk' ? 'selected' : '' }}>Masuk
                                </option>
                                <option value="keluar" {{ request('jenis') == 'keluar' ? 'selected' : '' }}>Keluar
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-indigo fw-semibold px-3">Filter</button>
                            <a href="{{ url('/dompet') }}" class="btn btn-sm btn-outline-light px-3">Reset</a>
                            <a href="{{ route('wallet.laporan.pdf', request()->query()) }}" target="_blank"
                                class="btn btn-sm btn-danger fw-semibold px-1 text-nowrap">
                                Cetak PDF
                            </a>
                        </div>
                    </form>

                    @if (session('success'))
                        <div id="alert-box" class="alert alert-success alert-dismissible fade show border-0 bg-masuk"
                            role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div id="alert-box" class="alert alert-danger alert-dismissible fade show border-0 bg-keluar"
                            role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead>
                                <tr class="text-uppercase">
                                    <th>Detail Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th class="text-end">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riwayat as $item)
                                    <tr class="border-bottom">
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="icon-box {{ $item->jenis == 'masuk' ? 'bg-masuk' : 'bg-keluar' }} d-flex justify-content-center align-items-center rounded-2"
                                                    style="width: 42px; height: 42px; flex-shrink: 0;">
                                                    <i
                                                        class="bi {{ $item->jenis == 'masuk' ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }} fs-5"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold text-dark">
                                                        {{ $item->keterangan ?? 'Transaksi Sistem' }}</h6>
                                                    <small class="text-muted">{{ ucfirst($item->jenis) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted small fw-medium">
                                            {{ $item->created_at->format('d M Y, H:i') }}</td>
                                        <td>
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill mb-1 d-block"
                                                style="width: fit-content;">Selesai</span>

                                            @if ($item->kode_transaksi)
                                                <a href="{{ route('cetak.struk', $item->kode_transaksi) }}"
                                                    target="_blank"
                                                    class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 text-decoration-none d-inline-block mt-1">
                                                    <i class="bi bi-printer-fill me-1"></i> Struk
                                                </a>
                                            @endif
                                        </td>
                                        <td
                                            class="text-end fw-bold {{ $item->jenis == 'masuk' ? 'text-success' : 'text-dark' }}">
                                            {{ $item->jenis == 'masuk' ? '+' : '-' }} Rp
                                            {{ number_format($item->jumlah, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="bi bi-inbox fs-1 d-block mb-2 text-light"></i>
                                            Belum ada riwayat transaksi.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-4 w-100">
                            {{ $riwayat->links() }}
                        </div>
                    </div>

                    @if (!Auth::user()->pin)
                        <div
                            class="alert alert-warning border-0 rounded-4 mt-4 mb-0 p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-shield-lock-fill me-2 fs-5"></i>
                                <strong>Keamanan Akun:</strong> Anda belum membuat 6-Digit PIN Transaksi.
                            </div>
                            <button type="button" class="btn btn-dark btn-sm rounded-pill px-3"
                                data-bs-toggle="modal" data-bs-target="#modalSetPin">
                                Buat PIN Sekarang
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('wallet.partials.modal-transfer')
    @include('wallet.partials.modal-set-pin')
    @include('wallet.partials.modal-tarik')
    @include('wallet.partials.modal-ganti-pin')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
</body>

</html>
