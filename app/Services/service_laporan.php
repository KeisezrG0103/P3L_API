<?php

namespace App\Services;

use App\Models\model_bahan_baku;
use App\Models\model_detail_hampers;
use App\Models\model_detail_transaksi;
use App\Models\model_hampers;
use App\Models\model_history_bahan_baku;
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

    public function getHampers($hampersIds)
    {
        return model_hampers::select('Id', 'Nama_Hampers', 'Harga')
            ->whereIn('Id', $hampersIds)
            ->get()
            ->keyBy('Id');
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


    public function laporanPenjualanProdukV2($bulan, $year)
    {
        $pesanan = $this->laporanPenjualanProdukPerBulan($bulan, $year);
    
        $penjualanProduk = [];
    
        // Mengumpulkan semua ID pesanan untuk query eager loading
        $pesananIds = $pesanan->pluck('Id')->toArray();
    
        // Mengambil semua produk dalam pesanan dengan eager loading
        $produkDalamPesanan = model_detail_transaksi::select(
            'detail_transaksi.Total_Produk as Kuantitas',
            'detail_transaksi.Hampers_Id',
            'detail_transaksi.Produk_Id'
        )
            ->leftJoin('produk', 'detail_transaksi.Produk_Id', '=', 'produk.Id')
            ->whereIn('detail_transaksi.Pesanan_Id', $pesananIds)
            ->where('produk.Penitip_Id', null)
            ->get();
    
        // Membuat dictionary untuk harga hampers
        $hampersIds = $produkDalamPesanan->pluck('Hampers_Id')->toArray();
        $hargaHampers = $this->getHampers($hampersIds)->keyBy('Id');
    
        // Membuat dictionary untuk harga produk
        $produkIds = $produkDalamPesanan->pluck('Produk_Id')->toArray();
        $hargaProduk = $this->getHargaProduk($produkIds)->keyBy('Id');
    
        // Memproses produk dalam pesanan
        foreach ($produkDalamPesanan as $produk) {
            if ($produk->Hampers_Id && isset($hargaHampers[$produk->Hampers_Id])) {
                $harga = $hargaHampers[$produk->Hampers_Id]->Harga;
                $namaProduk = $hargaHampers[$produk->Hampers_Id]->Nama_Hampers;
            } elseif ($produk->Produk_Id && isset($hargaProduk[$produk->Produk_Id])) {
                $harga = $hargaProduk[$produk->Produk_Id]->Harga;
                $namaProduk = $hargaProduk[$produk->Produk_Id]->Nama;
            } else {
                // Handle case where neither Hampers_Id nor Produk_Id exist in their respective dictionaries
                continue; // Skip this product if its price or name can't be found
            }
    
            $penjualanProduk[] = (object)[
                'Nama_Produk' => $namaProduk,
                'Kuantitas' => $produk->Kuantitas,
                'Harga' => $harga,
                'Total' => $harga * $produk->Kuantitas
            ];
        }
    
        return $penjualanProduk;
    }
    



    public function sameNameAndAdd($bulan, $year)
    {
        $penjualan = $this->laporanPenjualanProdukV2($bulan, $year);

        $penjualanProduk = [];

        foreach ($penjualan as $p) {
            $index = array_search($p->Nama_Produk, array_column($penjualanProduk, 'Nama_Produk'));

            if ($index === false) {
                $penjualanProduk[] = $p;
            } else {
                $penjualanProduk[$index]->Kuantitas += $p->Kuantitas;
                $penjualanProduk[$index]->Total += $p->Total;
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
        // Langkah pertama: Dapatkan data dasar
        $presensi = model_karyawan::select(
            'karyawan.Nama',
            'role.Gaji as Gaji',
            'karyawan.Bonus',
            DB::raw('SUM(CASE WHEN presensi.Status = "Masuk" THEN 1 ELSE 0 END) as Jumlah_Hadir'),
            DB::raw('SUM(CASE WHEN presensi.Status != "Masuk" THEN 1 ELSE 0 END) as Jumlah_Bolos')
        )
        ->join('presensi', 'karyawan.Id', '=', 'presensi.Karyawan_Id')
        ->join('role', 'karyawan.Role_Id', '=', 'role.Id')
        ->whereMonth('presensi.Tanggal', $bulan)
        ->whereYear('presensi.Tanggal', $year)
        ->groupBy('karyawan.Nama', 'role.Gaji', 'karyawan.TotalGaji', 'karyawan.Bonus')
        ->get();
    
        $presensi->transform(function ($item) {
            if ($item->Jumlah_Bolos >= 4) {
                $item->Bonus = 0;
            }
    
            if ($item->Jumlah_Hadir > 0) {
                $item->Honor_Harian = $item->Gaji / $item->Jumlah_Hadir;
                $item->Total = $item->Gaji + $item->Bonus;
            
                return $item;
            } else {
                $item->Honor_Harian = 0;
                $item->Total = 0;
                return $item;
            }
            
          
        });
    
        // Hitung total gaji karyawan
        $totalGajiKaryawan = $presensi->sum('Total');
    
        return [
            'presensi' => $presensi,
            'totalGajiKaryawan' => $totalGajiKaryawan
        ];
    }
    
    public function laporanKeuangan($bulan, $year)
    {
        $penjualan = model_pesanan::select(
            DB::raw('COALESCE(SUM(pesanan.Total), 0) as TotalPenjualan'),
            DB::raw('COALESCE(SUM(pesanan.Tip), 0) as TotalTip')
        )
        ->whereMonth('pesanan.Tanggal_Pesan', $bulan)
        ->whereYear('pesanan.Tanggal_Pesan', $year)
        ->where('pesanan.Status', 'Selesai')
        ->first();
    
        $pengeluaranLain = model_pengeluaran_lain_lain::select(
            'Nama_Pengeluaran',
            'Harga'
        )
        ->whereMonth('tanggal', $bulan)
        ->whereYear('tanggal', $year)
        ->get();
    
        $totalPengadaanBahanBaku = model_pengadaan_bahan_baku::select(DB::raw('COALESCE(SUM(Harga), 0) as TotalPengadaanBahanBaku'))
        ->whereMonth('Tanggal_Pengadaan', $bulan)
        ->whereYear('Tanggal_Pengadaan', $year)
        ->first();
    
        $totalPembayaranPenitip = DB::table('detail_transaksi')
        ->join('produk', 'detail_transaksi.Produk_Id', '=', 'produk.Id')
        ->join('penitip', 'produk.Penitip_Id', '=', 'penitip.Id')
        ->join('pesanan', 'detail_transaksi.Pesanan_Id', '=', 'pesanan.Id')
        ->select(
            DB::raw('COALESCE(SUM((detail_transaksi.Total_Produk * produk.Harga) - (detail_transaksi.Total_Produk * produk.Harga * 0.2)), 0) AS TotalPembayaranPenitip')
        )
        ->whereMonth('pesanan.Tanggal_Pesan', $bulan)
        ->whereYear('pesanan.Tanggal_Pesan', $year)
        ->where('pesanan.Status', 'Selesai')
        ->first();
    
        // Panggil fungsi laporanPresensiKaryawan untuk mendapatkan total gaji karyawan
        $laporanPresensi = $this->laporanPresensiKaryawan($bulan, $year);
        $totalGajiKaryawan = $laporanPresensi['totalGajiKaryawan'];
    
        return [
            'penjualan' => $penjualan,
            'pengeluaranLain' => $pengeluaranLain,
            'totalPengadaanBahanBaku' => $totalPengadaanBahanBaku,
            'totalPembayaranPenitip' => $totalPembayaranPenitip,
            'totalGajiKaryawan' => $totalGajiKaryawan
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
                    ->where('pesanan.Status', 'Selesai')
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


    public function LaporanPenggunaanBahanBakuByPeriode(String $dateDari, String $dateHingga)
    {
        $penggunaanBahan = model_history_bahan_baku::select(
            'history_bahan_baku.Id',
            'bahan_baku.Nama',
            'history_bahan_baku.Jumlah_Penggunaan',
            'history_bahan_baku.Tanggal_Digunakan',
            'history_bahan_baku.Satuan'
        )
            ->join('bahan_baku', 'history_bahan_baku.Bahan_Baku_Id', '=', 'bahan_baku.Id')
            ->whereBetween('history_bahan_baku.Tanggal_Digunakan', [$dateDari, $dateHingga])
            ->get();
        return $penggunaanBahan;
    }

    public function RecapLaporanPenggunaanBahanBakuByPeriode(String $dateDari, String $dateHingga)
    {
        $penggunaan = $this->LaporanPenggunaanBahanBakuByPeriode($dateDari, $dateHingga);

        $recap = [];

        foreach ($penggunaan as $item) {
            //if bahan baku sama dijumlahkan

            $index = array_search($item->Nama, array_column($recap, 'Nama'));

            if ($index === false) {
                $recap[] = [
                    'Nama' => $item->Nama,
                    'Jumlah_Penggunaan' => $item->Jumlah_Penggunaan,
                    'Satuan' => $item->Satuan
                ];
            } else {
                $recap[$index]['Jumlah_Penggunaan'] += $item->Jumlah_Penggunaan;
            }
        }

        return $recap;
    }

    public function LaporanPenjualanBulananSecaraKeseluruhan()
    {
        $laporan = model_pesanan::select(
            DB::raw('MONTHNAME(Tanggal_Pesan) as Bulan'),
            DB::raw('YEAR(Tanggal_Pesan) as Tahun'),
            DB::raw('SUM(Total) as Total_Penjualan'),
            DB::raw('SUM(Tip) as Total_Tip')
        )->where('Status', 'Selesai')->where('Status_Pembayaran', 'Lunas')->groupBy('Bulan', 'Tahun')->get();

        return $laporan;
    }
}
