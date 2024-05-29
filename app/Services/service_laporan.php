<?php

namespace App\Services;

use App\Models\model_bahan_baku;
use App\Models\model_detail_hampers;
use App\Models\model_detail_transaksi;
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



    public function laporanPenjualanProdukPerBulan($bulan, $year)
    {
        return model_pesanan::select('Id', 'Tanggal_Diambil', 'Tanggal_Pesan')
            ->whereMonth('Tanggal_Pesan', $bulan)
            ->whereYear('Tanggal_Pesan', $year)
            ->where('Status', 'Selesai')
            ->get();
    }


    public function ProdukDalamPesanan($pesananId)
    {
        return model_detail_transaksi::select(
            'detail_transaksi.Total_Produk as Kuantitas',
            'detail_transaksi.Hampers_Id',
            'detail_transaksi.Produk_Id'
        )
            ->leftJoin('produk', 'detail_transaksi.Produk_Id', '=', 'produk.Id')
            ->where('detail_transaksi.Pesanan_Id', $pesananId)
            ->where('produk.Penitip_Id', null)
            ->get();
    }

    public function getDetailHampersAndKuantitas($hampersIds)
    {
        return model_detail_hampers::select(
            'detail_hampers.Hampers_Id',
            'detail_hampers.Jumlah as Jumlah',
            'produk.Harga as Harga',
            'produk.Nama as Nama',
            'produk.Id as Produk_Id'
        )
            ->leftJoin('produk', 'detail_hampers.Produk_Id', '=', 'produk.Id')
            ->whereIn('detail_hampers.Hampers_Id', $hampersIds)
            ->get();
    }

    public function getHargaProduk($produkIds)
    {
        return model_produk::select('Id', 'Harga', 'Nama')
            ->whereIn('Id', $produkIds)
            ->get()
            ->keyBy('Id');
    }

    public function laporanPenjualanProduk($bulan, $year)
    {
        $penjualan = $this->laporanPenjualanProdukPerBulan($bulan, $year);
        $pesananIds = $penjualan->pluck('Id');

        $produkDalamPesanan = model_detail_transaksi::select(
            'detail_transaksi.Pesanan_Id',
            'detail_transaksi.Total_Produk as Kuantitas',
            'detail_transaksi.Hampers_Id',
            'detail_transaksi.Produk_Id'
        )
            ->leftJoin('produk', 'detail_transaksi.Produk_Id', '=', 'produk.Id')
            ->whereIn('detail_transaksi.Pesanan_Id', $pesananIds)
            ->where('produk.Penitip_Id', null)
            ->get();

        $hampersIds = $produkDalamPesanan->pluck('Hampers_Id')->filter();
        $detailHampers = $this->getDetailHampersAndKuantitas($hampersIds);
        $produkIds = $produkDalamPesanan->pluck('Produk_Id')->merge($detailHampers->pluck('Produk_Id'));
        $hargaProduk = $this->getHargaProduk($produkIds);

        $penjualanProduk = [];

        foreach ($produkDalamPesanan as $produk) {
            if ($produk->Hampers_Id != null) {
                foreach ($detailHampers->where('Hampers_Id', $produk->Hampers_Id) as $detail) {
                    if (preg_match('/(exclusive)/i', $detail->Nama)) {
                        continue;
                    }

                    $harga = $hargaProduk[$detail->Produk_Id];
                    $penjualanProduk[] = (object)[
                        'Nama_Produk' => $detail->Nama,
                        'Kuantitas' => $detail->Jumlah * $produk->Kuantitas,
                        'Harga' => $harga->Harga,
                        'Total' => $harga->Harga * $detail->Jumlah * $produk->Kuantitas
                    ];
                }
            } else {
                $harga = $hargaProduk[$produk->Produk_Id];
                $penjualanProduk[] = (object)[
                    'Nama_Produk' => $harga->Nama,
                    'Kuantitas' => $produk->Kuantitas,
                    'Harga' => $harga->Harga,
                    'Total' => $harga->Harga * $produk->Kuantitas
                ];
            }
        }

        return $penjualanProduk;
    }

    public function filterSameNameAndAdd($bulan, $year)
    {
        $penjualan = $this->laporanPenjualanProduk($bulan, $year);
        $penjualanProduk = [];
        $penjualanProduk = collect($penjualan)->groupBy('Nama_Produk')->map(function ($item) {
            return [
                'Nama_Produk' => $item[0]->Nama_Produk,
                'Kuantitas' => $item->sum('Kuantitas'),
                'Harga' => $item[0]->Harga,
                'Total' => $item->sum('Total')
            ];
        })->values()->all();

        return $penjualanProduk;
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
            'Harga'
        )
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
