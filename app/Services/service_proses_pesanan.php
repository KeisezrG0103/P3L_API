<?php

namespace App\Services;

use App\Models\model_detail_hampers;
use Carbon\Carbon;
use App\Models\model_pesanan;
use Illuminate\Support\Facades\DB;
use App\Models\model_detail_transaksi;
use App\Models\model_hampers;
use App\Models\model_resep;

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

    public function getDetailPesanan($noNota)
    {
        $detailPesanan = model_detail_transaksi::select(
            DB::raw('IFNULL(produk.Nama, NULL) as Nama_Produk'),
            DB::raw('IFNULL(hampers.Nama_Hampers, NULL) as Nama_Hampers'),
            'detail_transaksi.Produk_Id',
            'detail_transaksi.Hampers_Id',
            'detail_transaksi.Total_Produk',
            'detail_transaksi.Pesanan_Id',
        )->leftJoin('produk', 'detail_transaksi.Produk_Id', '=', 'produk.Id')
            ->leftJoin('hampers', 'detail_transaksi.Hampers_Id', '=', 'hampers.Id')
            ->where('detail_transaksi.Pesanan_id', $noNota)
            ->get();

        return $detailPesanan;
    }

    public function getProdukFromHampers($idHampers)
    {
        $produk = model_detail_hampers::select(
            'produk.Nama',
            'produk.Id',
            'detail_hampers.Jumlah'
        )->where('detail_hampers.Hampers_Id', $idHampers)
            ->leftJoin('produk', 'detail_hampers.Produk_Id', '=', 'produk.Id')
            ->get();

        return $produk;
    }

    public function filterAndDeleteTrailingSpace($string)
    {
        $string = preg_replace('/[^a-zA-Z ]/', '', $string);
        $string = str_replace('Loyang', '', $string);
        $string = rtrim($string);

        return $string;
    }


    public function getResepFromDetailPesanan($noNota)
    {
        $pesanan = $this->getDetailPesanan($noNota);

        $isiResep = [];

        foreach ($pesanan as $p) {
            if ($p->Nama_Hampers != null) {
                $produk = $this->getProdukFromHampers($p->Hampers_Id);
                foreach ($produk as $pr) {

                    $cleanedProductName = $this->filterAndDeleteTrailingSpace($pr->Nama);


                    $resep = model_resep::select(
                        'resep.Id',
                        'resep.Nama as Nama_Resep',

                    )->where('Nama', 'like', '%' . $cleanedProductName . '%')

                        ->get();

                    foreach ($resep as $r) {
                        $r->Nama_Produk_asli = $pr->Nama;
                        $r->Jumlah = $pr->Jumlah;
                        $isiResep[] = $r;
                    }
                }
            }
            if ($p->Nama_Produk != null) {
                $cleanedProductName = $this->filterAndDeleteTrailingSpace($p->Nama_Produk);
                $resep = model_resep::select(
                    'resep.Id',
                    'resep.Nama as Nama_Resep'
                )->where('Nama', 'like', '%' . $cleanedProductName . '%')
                    ->get();

                foreach ($resep as $r) {
                    $r->Nama_Produk_asli = $p->Nama_Produk;
                    $r->Jumlah = $p->Total_Produk;
                    $isiResep[] = $r;
                }
            }
        }

        return $isiResep;
    }

    public function cekProdukSetengahLoyang($NamaProduk)
    {
        $pattern = '/1\/2\s*loyang/i';

        // Perform the regex match
        if (preg_match($pattern, $NamaProduk)) {
            return true;
        } else {
            return false;
        }
    }

    public function convertSetengahLoyangKeSatuLoyang($jumlah)
    {
        $jumlah = $jumlah / 2;

        return $jumlah;
    }

    public function prosesPesanan($noNota)
    {
        $resep = $this->getResepFromDetailPesanan($noNota);


        $ProsesPesanan = [];

        foreach ($resep as $r) {

            if ($this->cekProdukSetengahLoyang($r->Nama_Produk_asli) && $r->Jumlah > 1) {

                $r->Jumlah_Proses = $this->convertSetengahLoyangKeSatuLoyang($r->Jumlah);

                $ProsesPesanan[] = $r;
            } else {
                $r->Jumlah_Proses = $r->Jumlah;
                $r->sisa = true;
                $ProsesPesanan[] = $r;
            }
        }

        return $ProsesPesanan;
    }
}
