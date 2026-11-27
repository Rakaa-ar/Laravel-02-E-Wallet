<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Akun | Arvanta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="arvanta-body">
    <div class="container py-4" style="max-width: 1060px;">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-white mb-1">Profil Pengguna</h4>
                <p class="mb-0" style="color: var(--text-secondary); font-size: 0.88rem;">
                    Kelola foto profil, biodata, dan informasi ID transfer Arvanta.
                </p>
            </div>
            <a href="{{ url('/dompet') }}" class="btn btn-arvanta-ghost text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dompet
            </a>
        </div>

        @if (session('success'))
            <div class="alert border-0 rounded-3 mb-4 py-2 px-3 d-flex align-items-center gap-2"
                style="background-color: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3) !important; font-size: 0.88rem;">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert border-0 rounded-3 mb-4 py-2 px-3 d-flex align-items-center gap-2"
                style="background-color: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3) !important; font-size: 0.88rem;">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="row g-4">
            <!-- Kolom Kiri -->
            <div class="col-lg-4">
                <div class="card-arvanta p-4 text-center mb-3">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="avatar-wrapper">
                            @if ($users->avatar)
                                <img src="{{ asset('storage/' . $users->avatar) }}" alt="Avatar" class="avatar-circle"
                                    id="avatarPreviewImg">
                            @else
                                <div class="avatar-initial" id="avatarPreviewInitial">
                                    {{ strtoupper(substr($users->name, 0, 2)) }}
                                </div>
                                <img src="" alt="Avatar" class="avatar-circle d-none" id="avatarPreviewImg">
                            @endif
                        </div>
                    </div>

                    <h6 class="fw-bold text-white mb-1 fs-5">{{ $users->name }}</h6>
                    <p class="mb-3" style="color: var(--text-secondary); font-size: 0.84rem;">{{ $users->email }}</p>

                    @if ($users->bio)
                        <p class="small fst-italic mb-3 px-2" style="color: #cbd5e1;">"{{ $users->bio }}"</p>
                    @endif

                    <!-- Box ID Transfer -->
                    <div class="id-badge d-flex justify-content-between align-items-center">
                        <div class="text-start">
                            <span class="d-block"
                                style="font-size: 0.68rem; color: var(--text-secondary); letter-spacing: 0.05em;">ID
                                TRANSFER ARVANTA</span>
                            <strong class="text-white" style="font-size: 0.95rem;"
                                id="accountNumberText">{{ $users->account_number ?? 'Belum tersedia' }}</strong>
                        </div>
                        @if ($users->account_number)
                            <button type="button" class="btn-copy-soft" id="btnCopyId" onclick="copyAccountId()">
                                <i class="bi bi-clipboard me-1"></i> Salin
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Ringkasan Arus Kas -->
                <div class="card-arvanta p-4">
                    <h6 class="fw-semibold text-white mb-3" style="font-size: 0.95rem;">Ringkasan Arus Kas</h6>
                    <div class="p-3 mb-2 subcard-arvanta">
                        <span class="d-block mb-1" style="font-size: 0.78rem; color: var(--text-secondary);">Total Uang
                            Masuk</span>
                        <span class="fw-bold" style="font-size: 1.05rem; color: #2ecc71;">+ Rp
                            {{ number_format($totalMasuk, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-3 subcard-arvanta">
                        <span class="d-block mb-1" style="font-size: 0.78rem; color: var(--text-secondary);">Total Uang
                            Keluar</span>
                        <span class="fw-bold" style="font-size: 1.05rem; color: #ff6b6b;">- Rp
                            {{ number_format($totalKeluar, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Form Edit Biodata -->
            <div class="col-lg-8">
                <div class="card-arvanta p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-semibold text-white mb-0" style="font-size: 1rem;">Edit Biodata Diri</h6>
                        <span class="badge rounded-pill px-3 py-1"
                            style="background-color: rgba(108, 92, 231, 0.15); color: #a5b4fc; font-weight: 500; font-size: 0.75rem;">
                            Akun Terverifikasi
                        </span>
                    </div>

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="label-arvanta">Foto Profil (Maks. 2MB — JPG, PNG, WEBP)</label>
                            <input type="file" name="avatar" id="avatarInput"
                                class="form-control form-control-arvanta" accept="image/png, image/jpeg, image/webp"
                                onchange="previewAvatar(event)">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="label-arvanta">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control form-control-arvanta"
                                    value="{{ old('name', $users->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="label-arvanta">Email Terdaftar</label>
                                <input type="email" class="form-control form-control-arvanta"
                                    value="{{ $users->email }}" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="label-arvanta">Nomor Handphone / WhatsApp</label>
                            <input type="text" name="phone" class="form-control form-control-arvanta"
                                placeholder="Contoh: 081234567890" value="{{ old('phone', $users->phone) }}">
                        </div>

                        <div class="mb-4">
                            <label class="label-arvanta">Bio Singkat</label>
                            <textarea name="bio" rows="3" class="form-control form-control-arvanta"
                                placeholder="Tulis bio singkat tentang dirimu...">{{ old('bio', $users->bio) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end pt-2 border-top"
                            style="border-color: var(--border-soft) !important;">
                            <button type="submit" class="btn btn-arvanta-primary">
                                <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
