<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\model_pesanan;
use Illuminate\Support\Facades\DB;

class service_proses_pesanan
{
    public function getDaftarPesananYangDiprosesHariIni($tanggalBesok)
    {
        $pesanan = model_pesanan::select(
            'pesanan.Id',
            'pesanan.Tanggal_Pesan',
            'pesanan.Status',
            'pesanan.Tanggal_Diambil'
        )->where('pesanan.Status_Pembayaran', 'Lunas')
            ->where('pesanan.Status', 'Diterima')
            ->whereDate('pesanan.Tanggal_Diambil', $tanggalBesok)
            ->get();

        return $pesanan;
    }
}
