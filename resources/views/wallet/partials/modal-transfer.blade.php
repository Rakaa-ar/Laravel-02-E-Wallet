<!-- 1. Modal Transfer 6 Digit ID -->
<div class="modal fade" id="modalTransfer" tabindex="-1" aria-labelledby="modalTransferLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white"
            style="background-color: #141724; border: 1px solid #202436; border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTransferLabel">
                    <i class="bi bi-send-fill me-2 text-primary"></i>Transfer Saldo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form action="{{ route('transfer') }}" method="POST" id="formTransfer">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">ID USER PENERIMA (6 DIGIT)</label>
                        <input type="text" name="account_number" id="inputAccountNumber"
                            class="form-control text-center fw-bold fs-4 letter-spacing-2" maxlength="6"
                            placeholder="Contoh: 345678" required autocomplete="off">
                        <div id="penerimaInfo" class="mt-2 text-center small fw-semibold"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">NOMINAL TRANSFER (RP)</label>
                        <input type="text" id="inputRupiah" class="form-control" min="1000"
                            placeholder="Minimal 1.000" required autocomplete="off">
                        <input type="hidden" name="jumlah" id="inputJumlahAsli" min="1000" required>
                        <div id="errorTransfer" class="text-danger small mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">CATATAN / KETERANGAN</label>
                        <input type="text" name="keterangan" class="form-control"
                            placeholder="Contoh: Bayar jajan siomay">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">PIN KEAMANAN (6 DIGIT)</label>
                        <input type="password" name="pin" class="form-control" maxlength="6"
                            placeholder="******" required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnProsesTransfer" class="btn btn-primary rounded-3 px-4"
                        disabled>Kirim Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>