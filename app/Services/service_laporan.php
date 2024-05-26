<?php

namespace App\Services;

use App\Models\model_bahan_baku;
use App\Models\model_karyawan;
use App\Models\model_pengadaan_bahan_baku;
use App\Models\model_pengeluaran_lain_lain;
use App\Models\model_penitip;
use App\Models\model_pesanan;
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

    public function laporanKeuangan($bulan, $year)
    {
       
        $penjualan = model_pesanan::select(
                DB::raw('SUM(pesanan.Total) as TotalPenjualan'),
                DB::raw('SUM(pesanan.Tip) as TotalTip')
            )
            ->whereMonth('pesanan.Tanggal_Pesan', $bulan)
            ->whereYear('pesanan.Tanggal_Pesan', $year)
            ->first();
    
        
        $pengeluaranLain = model_pengeluaran_lain_lain::select(
            'Nama_Pengeluaran', 
            'Harga')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $year)
            ->get();
    
        $totalPengadaanBahanBaku = model_pengadaan_bahan_baku::select(DB::raw('SUM(Harga) as TotalPengadaanBahanBaku'))
            ->whereMonth('Tanggal_Pengadaan', $bulan)
            ->whereYear('Tanggal_Pengadaan', $year)
            ->first();
    
        
        $totalPembayaranPenitip = DB::table('pesanan')
            ->leftJoin('detail_transaksi', 'pesanan.Id', '=', 'detail_transaksi.Pesanan_Id')
            ->leftJoin('produk', 'detail_transaksi.Produk_Id', '=', 'produk.Id')
            ->leftJoin('penitip', 'produk.Penitip_Id', '=', 'penitip.Id')
            ->select(DB::raw('SUM(DISTINCT penitip.Komisi) as TotalPembayaranPenitip'))
            ->whereMonth('pesanan.Tanggal_Pesan', $bulan)
            ->whereYear('pesanan.Tanggal_Pesan', $year)
            ->first();
    
      
        return [
            'penjualan' => $penjualan,
            'pengeluaranLain' => $pengeluaranLain,
            'totalPengadaanBahanBaku' => $totalPengadaanBahanBaku,
            'totalPembayaranPenitip' => $totalPembayaranPenitip
        ];
    }
    

    public function laporanPenitip($bulan, $tahun)
    {
    
        $listPenitip = model_penitip::all();

    
        $laporan = [];

    
        foreach ($listPenitip as $penitip) {
        
            $laporanPenitip = (object)[
                'Penitip_ID' => $penitip->Id,
                'Nama_Penitip' => $penitip->Nama_Penitip,
                'Produk' => [] 
            ];

        
            $produkPenitip = $penitip->produk()->get();
            
        
            foreach ($produkPenitip as $produk) {
            
                $laporanProduk = DB::table('detail_transaksi')
                    ->select(
                        DB::raw('SUM(Total_Produk) AS Total_Produk_Terbeli'),
                        DB::raw('SUM(Total_Produk * Harga) AS Total_Harga_Produk'),
                        DB::raw('(SUM(Total_Produk * Harga) * 0.2) AS Komisi'),
                        DB::raw('(SUM(Total_Produk * Harga) * 0.8) AS Pendapatan')
                    )
                    ->leftJoin('produk', 'detail_transaksi.Produk_Id', '=', 'produk.Id')
                    ->leftJoin('pesanan', 'detail_transaksi.Pesanan_Id', '=', 'pesanan.Id')
                    ->where('produk.Penitip_ID', $penitip->Id)
                    ->where('produk.Id', $produk->Id)
                    ->whereMonth('pesanan.Tanggal_Pesan', $bulan)
                    ->whereYear('pesanan.Tanggal_Pesan', $tahun)
                    ->groupBy('produk.Id')
                    ->first();

            
                $laporanProdukObj = (object)[
                    'Nama_Produk' => $produk->Nama,
                    'Harga_Produk' => $produk->Harga,
                    'Total_Produk_Terbeli' => $laporanProduk->Total_Produk_Terbeli ?? 0,
                    'Total' => $laporanProduk->Total_Harga_Produk ?? 0,
                    'Komisi' => $laporanProduk->Komisi ?? 0,
                    'Pendapatan' => $laporanProduk->Pendapatan ?? 0
                ];
            
                
                $laporanPenitip->Produk[] = $laporanProdukObj;
            }

            
            $laporan[] = $laporanPenitip;
        }

        
        return $laporan;
    }


}
