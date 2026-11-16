<!-- 4. Modal Ganti PIN -->
<div class="modal fade" id="modalGantiPin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white"
            style="background-color: #141724; border: 1px solid #202436; border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-key-fill me-2 text-info"></i>Ganti PIN Transaksi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form action="{{ route('update.pin') }}" method="POST" id="formGantiPin">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">PIN LAMA (6 DIGIT)</label>
                        <input type="password" name="old_pin" class="form-control text-center fw-bold fs-4"
                            maxlength="6" placeholder="******" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">PIN BARU (6 DIGIT ANGKA)</label>
                        <input type="password" name="new_pin" class="form-control text-center fw-bold fs-4"
                            maxlength="6" placeholder="******" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">KONFIRMASI PIN BARU</label>
                        <input type="password" name="new_pin_confirmation"
                            class="form-control text-center fw-bold fs-4" maxlength="6" placeholder="******"
                            required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnProsesGantiPin"
                        class="btn btn-info rounded-3 px-4 fw-semibold text-dark">Update PIN</button>
                </div>
            </form>
        </div>
    </div>
</div>