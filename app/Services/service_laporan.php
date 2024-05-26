<?php

namespace App\Services;

use App\Models\model_bahan_baku;
use App\Models\model_karyawan;
use App\Models\model_produk;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class service_laporan
{
    public function geAllBahanBaku()
    {

        $bahan_baku = model_bahan_baku::select(
            'bahan_baku.Id',
            'bahan_baku.Nama',
            'bahan_baku.Qty',
            'bahan_baku.Satuan',
        )->get();




        return $bahan_baku;
    }

    public function laporanPenjualanPerProduk($bulan)
    {
        //perbulan
        $penjualan = model_produk::select(
            'produk.Id',
            'produk.Nama',
            'produk.Harga',
            DB::raw('SUM(detail_transaksi.Total_Produk) as Kuantitas'),
            DB::raw('SUM(detail_transaksi.Total_Produk * produk.Harga) as Total_Penjualan')
        )
            ->leftJoin('detail_transaksi', 'produk.Id', '=', 'detail_transaksi.Produk_Id')
            ->leftJoin('pesanan', 'detail_transaksi.Pesanan_Id', '=', 'pesanan.Id')
            ->whereMonth('pesanan.Tanggal_Pesan', $bulan)
            ->where('pesanan.Status', 'Selesai')
            ->groupBy('produk.Id', 'produk.Nama', 'produk.Harga')
            ->get();

        return $penjualan;
    }

    public function laporanPresensiKaryawan($bulan, $year)
    {
        $presensi = model_karyawan::select(
            'karyawan.Nama',
            DB::raw('karyawan.TotalGaji - karyawan.Bonus as Honor_Harian'),
            'karyawan.Bonus',
            'karyawan.TotalGaji as Total',
            DB::raw('SUM(CASE WHEN presensi.Status = "Masuk" THEN 1 ELSE 0 END) as Jumlah_Hadir'),
            DB::raw('SUM(CASE WHEN presensi.Status != "Masuk" THEN 1 ELSE 0 END) as Jumlah_Bolos')
        )
        ->leftJoin('presensi', 'karyawan.Id', '=', 'presensi.Karyawan_Id')
        ->whereMonth('presensi.Tanggal', $bulan)
        ->whereYear('presensi.Tanggal', $year)
        ->groupBy('karyawan.Nama', 'karyawan.TotalGaji', 'karyawan.Bonus')
        ->get();

        return $presensi;
    }
}
