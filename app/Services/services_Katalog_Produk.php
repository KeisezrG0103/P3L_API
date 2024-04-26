<?php

namespace App\Services;

use App\Models\model_pesanan;
use App\Models\model_produk;
use App\Models\model_detail_transaksi;
use Illuminate\Support\Facades\DB;

class services_Katalog_Produk
{
    public function cekQuotaProdukPerTanggal(String $date, int $id_produk, int $limit): int
    {
        $pesanan = model_pesanan::select(
            'pesanan.Id',
            'detail_transaksi.Produk_Id',
            'detail_transaksi.Total_Produk',
        )->where('pesanan.Tanggal_Pesan', $date)->where('detail_transaksi.Produk_Id', $id_produk)
            ->where('pesanan.Status', '!=', 'Ditolak')->where('pesanan.Status', '!=', 'Dibatalkan')
            ->join('detail_transaksi', 'pesanan.Id', '=', 'detail_transaksi.Pesanan_Id')
            ->get();

        $total = 0;

        foreach ($pesanan as $p) {
            $total += $p->Total_Produk;
        }

        $limit = $limit - $total;

        $limit = $limit < 0 ? 0 : $limit;

        return $limit;
    }

    public function cekQuotaProdukDalamHamper(String $date, int $id_produk, int $limit): int
    {
        $pesanan = model_pesanan::select(
            'pesanan.Id',
            'detail_transaksi.Hampers_Id',
            'detail_transaksi.Total_Produk',
            'detail_hampers.Produk_Id',
            'detail_hampers.Jumlah',
            DB::raw('SUM(detail_hampers.Jumlah * detail_transaksi.Total_Produk) as Total_Terpakai')
        )
            ->join('detail_transaksi', 'pesanan.Id', '=', 'detail_transaksi.Pesanan_Id')
            ->join('detail_hampers', 'detail_transaksi.Hampers_Id', '=', 'detail_hampers.Hampers_Id')
            ->where('pesanan.Tanggal_Pesan', $date)
            ->where('detail_hampers.Produk_Id', $id_produk)
            ->where('pesanan.Status', '!=' ,'Ditolak')->where('pesanan.Status', '!=' ,'Dibatalkan')
            ->groupBy('pesanan.Id', 'detail_transaksi.Hampers_Id', 'detail_hampers.Produk_Id', 'detail_transaksi.Total_Produk', 'detail_hampers.Jumlah')
            ->get();

        $total = 0;

        foreach ($pesanan as $p) {
            $total += $p->Total_Terpakai;
        }

        $limit -= $total;

        $limit = $limit < 0 ? 0 : $limit;

        return $limit;
    }


    public function getProdukNonPenitip(): object
    {
        $produkNonPenitip = model_produk::select(
            'produk.Id',
            'produk.Nama',
            'produk.Harga',
            'produk.Satuan',
            'produk.Stok',
            'produk.Gambar',
            'produk.Kategori_Id',
            'kategori.Kategori as Nama_Kategori',

        )->join('kategori', 'produk.Kategori_Id', '=', 'kategori.Id')->where('produk.Penitip_Id', null)
            ->get();

        return $produkNonPenitip;
    }

    public function getProdukWithKuota(String $date): object
    {
        $produk = $this->getProdukNonPenitip();


        foreach ($produk as $p) {
            $p->Kuota = $this->cekQuotaProdukPerTanggal($date, $p->Id, 10);
            $p->Kuota = $this->cekQuotaProdukDalamHamper($date, $p->Id, $p->Kuota);

            $p->Kuota = $p->Kuota < 0 ? 0 : $p->Kuota;
        }
        return $produk;
    }

    public function GetKuotaProduk(String $date, int $id_produk): int
    {
        $kuota = $this->cekQuotaProdukPerTanggal($date, $id_produk, 10);
        $kuota = $this->cekQuotaProdukDalamHamper($date, $id_produk, $kuota);

        $kuota = $kuota < 0 ? 0 : $kuota;

        return $kuota;
    }

    public function getProdukWithKuotaNoBoxAndCard(String $date): object
    {
        $produk = $this->getProdukWithKuota($date);
        // how to make regex a variable?
        $produk = $produk->filter(function ($p) {
            return !preg_match('/box|card|tas/i', $p->Nama);
        });

        return $produk;
    }
}
