<!-- 3. Modal Tarik Tunai -->
<div class="modal fade" id="modalTarikTunai" tabindex="-1" aria-labelledby="modalTarikTunaiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white"
            style="background-color: #141724; border: 1px solid #202436; border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTarikTunaiLabel">
                    <i class="bi bi-cash-stack me-2 text-warning"></i>Tarik Tunai Saldo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form action="/dompet/transaksi" method="POST" id="formTarikTunai">
                @csrf
                <input type="hidden" name="jenis" value="keluar">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">NOMINAL PENARIKAN (RP)</label>
                        <input type="text" id="inputRupiahTarik" class="form-control"
                            placeholder="Contoh: 50.000" required autocomplete="off">
                        <input type="hidden" name="jumlah" id="inputJumlahAsliTarik" min="1000" required>
                        <div id="errorTarik" class="text-danger small mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">CATATAN / KETERANGAN</label>
                        <input type="text" name="keterangan" class="form-control"
                            placeholder="Contoh: Buat ongkos bensin">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">PIN KEAMANAN (6 DIGIT)</label>
                        <input type="password" name="pin" class="form-control" maxlength="6"
                            placeholder="******" required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnProsesTarik"
                        class="btn btn-warning rounded-3 px-4 text-dark fw-bold">Tarik Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>