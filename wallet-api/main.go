package main

import (
	"encoding/json"
	"fmt"
	"net/http"
)

type TransaksiRequest struct {
	SaldoSaatIni       float64 `json:"saldo_saat_ini"`
	Jenis              string  `json:"jenis"`
	TipeTransaksi      string  `json:"tipe_transaksi"`
	Jumlah             float64 `json:"jumlah"`
	TotalKeluarHariIni float64 `json:"total_keluar_hari_ini"`
}

type TransaksiResponse struct {
	Status        bool    `json:"status"`
	Pesan         string  `json:"pesan"`
	BiayaAdmin    float64 `json:"biaya_admin"`
	TotalPotongan float64 `json:"total_potongan"`
	Sisa          float64 `json:"sisa_saldo"`
}

const LimitHarian = 5000000 // 5jta perhari limit

func hitungBiayaAdmin(tipe string, jumlah float64) float64 {
	switch tipe {
	case "transfer":
		if jumlah > 50000 {
			return 1500
		}
		return 0
	case "tarik":
		return 2500
	default:
		return 0
	}
}

func prosesTransaksi(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method tidak diizinkan", http.StatusMethodNotAllowed)
		return
	}

	var req TransaksiRequest
	err := json.NewDecoder(r.Body).Decode(&req)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}
	w.Header().Set("Content-Type", "application/json")
	sisaSaldo := req.SaldoSaatIni

	if req.Jenis == "masuk" {
		sisaSaldo += req.Jumlah
		json.NewEncoder(w).Encode(TransaksiResponse{
			Status:        true,
			Pesan:         "Top lu berhasil bro!",
			BiayaAdmin:    0,
			TotalPotongan: 0,
			Sisa:          sisaSaldo,
		})
		return
	}

	if req.Jenis == "keluar" {
		if (req.TotalKeluarHariIni + req.Jumlah) > LimitHarian {
			sisaLimit := LimitHarian - req.TotalKeluarHariIni
			if sisaLimit < 0 {
				sisaLimit = 0
			}
			json.NewEncoder(w).Encode(TransaksiResponse{
				Status: false,
				Pesan:  fmt.Sprintf("Melebihi limit harian 5.000.000 sisa limit hari ini: Rp %.0f", sisaLimit),
				Sisa:   req.SaldoSaatIni,
			})
			return
		}

		biayaAdmin := hitungBiayaAdmin(req.TipeTransaksi, req.Jumlah)
		totalPotongan := req.Jumlah + biayaAdmin

		if req.SaldoSaatIni < totalPotongan {
			json.NewEncoder(w).Encode(TransaksiResponse{
				Status: false,
				Pesan:  fmt.Sprintf("Saldo lu kurang bro (termasuk biaya admin Rp %.0f)!", biayaAdmin),
				Sisa:   req.SaldoSaatIni,
			})
			return
		}

		sisaSaldo -= totalPotongan
		json.NewEncoder(w).Encode(TransaksiResponse{
			Status:        true,
			Pesan:         "Transaksi Berhasil",
			BiayaAdmin:    biayaAdmin,
			TotalPotongan: totalPotongan,
			Sisa:          sisaSaldo,
		})
		return
	}

	json.NewEncoder(w).Encode(TransaksiResponse{
		Status: false,
		Pesan:  "Jenis Transaksi Tidak di kenalin oleh sistem",
		Sisa:   req.SaldoSaatIni,
	})
}

func main() {
	http.HandleFunc("/api/transaksi", prosesTransaksi)

	fmt.Println("GOO WALLET API BERJALAN DI http://localhost:8080...")
	if err := http.ListenAndServe(":8080", nil); err != nil {
		fmt.Println("Gagal Menjalankan Serve Gooo:", err)
	}
}
