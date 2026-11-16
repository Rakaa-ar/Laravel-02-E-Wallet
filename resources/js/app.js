//// Helper tampil & hapus error inline
function tampilError(inputEl, errorEl, pesan) {
    inputEl.classList.add('is-invalid');
    errorEl.innerHTML = `<i class="bi bi-exclamation-circle-fill me-1"></i>${pesan}`;
}

function hapusError(inputEl, errorEl) {
    inputEl.classList.remove('is-invalid');
    errorEl.innerHTML = '';
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formTransaksi');
    const btn = document.getElementById('btnProses');
    const inputFormatted = document.getElementById('jumlah_formatted');
    const inputHidden = document.getElementById('jumlah');
    const errorTopup = document.getElementById('errorTopup');
    const alertBox = document.getElementById('alert-box');

    if (alertBox) {
        setTimeout(function() {
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";
            setTimeout(() => alertBox.remove(), 500);
        }, 3000);
    }

    function updateJumlah() {
        hapusError(inputFormatted, errorTopup);
        let angka = inputFormatted.value.replace(/[^,\d]/g, '');
        let split = angka.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
        inputFormatted.value = rupiah;
        inputHidden.value = split[0];
    }

    inputFormatted.addEventListener('input', updateJumlah);
    inputFormatted.addEventListener('keyup', updateJumlah);

    form.addEventListener('submit', function(e) {
        updateJumlah();

        if (!inputHidden.value || parseInt(inputHidden.value) < 1000) {
            e.preventDefault();
            tampilError(inputFormatted, errorTopup, 'Nominal minimal top-up adalah Rp 1.000');
            return false;
        }

        setTimeout(function() {
            btn.disabled = true;
            btn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
        }, 10);
    });
});

// Format Rupiah & Validasi Transfer
const inputRupiah = document.getElementById('inputRupiah');
const inputJumlahAsli = document.getElementById('inputJumlahAsli');
const errorTransfer = document.getElementById('errorTransfer');
const formTransfer = document.getElementById('formTransfer');

if (inputRupiah) {
    inputRupiah.addEventListener('input', function() {
        hapusError(inputRupiah, errorTransfer);
        let angkaMurni = this.value.replace(/[^0-9]/g, '');
        inputJumlahAsli.value = angkaMurni;
        this.value = angkaMurni !== '' ? new Intl.NumberFormat('id-ID').format(angkaMurni) : '';
    });
}

if (formTransfer) {
    formTransfer.addEventListener('submit', function(e) {
        if (!inputJumlahAsli.value || parseInt(inputJumlahAsli.value) < 1000) {
            e.preventDefault();
            tampilError(inputRupiah, errorTransfer, 'Nominal minimal transfer adalah Rp 1.000');
            return false;
        }

        const btn = document.getElementById('btnProsesTransfer');
        setTimeout(function() {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
        }, 10);
    });
}

// Format Rupiah & Validasi Tarik Tunai
const inputRupiahTarik = document.getElementById('inputRupiahTarik');
const inputJumlahAsliTarik = document.getElementById('inputJumlahAsliTarik');
const errorTarik = document.getElementById('errorTarik');
const formTarik = document.getElementById('formTarikTunai');

if (inputRupiahTarik) {
    inputRupiahTarik.addEventListener('input', function() {
        hapusError(inputRupiahTarik, errorTarik);
        let angkaMurni = this.value.replace(/[^0-9]/g, '');
        inputJumlahAsliTarik.value = angkaMurni;
        this.value = angkaMurni !== '' ? new Intl.NumberFormat('id-ID').format(angkaMurni) : '';
    });
}

if (formTarik) {
    formTarik.addEventListener('submit', function(e) {
        if (!inputJumlahAsliTarik.value || parseInt(inputJumlahAsliTarik.value) < 1000) {
            e.preventDefault();
            tampilError(inputRupiahTarik, errorTarik, 'Nominal minimal penarikan adalah Rp 1.000');
            return false;
        }

        const btn = document.getElementById('btnProsesTarik');
        setTimeout(function() {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
        }, 10);
    });
}

// Cek Nama Penerima Real-Time (Fetch API)
const inputAccountNumber = document.getElementById('inputAccountNumber');
const penerimaInfo = document.getElementById('penerimaInfo');
const btnProsesTransfer = document.getElementById('btnProsesTransfer');

if (inputAccountNumber) {
    inputAccountNumber.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        const kode = this.value;

        if (kode.length < 6) {
            penerimaInfo.innerHTML = '';
            if (btnProsesTransfer) btnProsesTransfer.disabled = true;
            return;
        }

        penerimaInfo.innerHTML =
            '<span class="text-secondary"><span class="spinner-border spinner-border-sm me-1"></span>Mengecek ID...</span>';

        fetch(`/dompet/cek-penerima/${kode}`)
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    penerimaInfo.innerHTML =
                        `<span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i>Penerima: ${data.nama}</span>`;
                    if (btnProsesTransfer) btnProsesTransfer.disabled = false;
                } else {
                    const pesanError = data.pesan || data.message || 'ID User tidak ditemukan!';
                    penerimaInfo.innerHTML =
                        `<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>${pesanError}</span>`;
                    if (btnProsesTransfer) btnProsesTransfer.disabled = true;
                }
            })
            .catch(() => {
                penerimaInfo.innerHTML = '<span class="text-danger">Gagal mengecek ID penerima.</span>';
                if (btnProsesTransfer) btnProsesTransfer.disabled = true;
            });
    });
}

// Anti Double-Click Form Ganti PIN
const formGantiPin = document.getElementById('formGantiPin');
if (formGantiPin) {
    formGantiPin.addEventListener('submit', function() {
        const btn = document.getElementById('btnProsesGantiPin');
        setTimeout(function() {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
        }, 10);
    });
}


