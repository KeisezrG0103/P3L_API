<?php

namespace App\Services;

use App\Models\model_detail_transaksi;
use App\Models\model_pesanan;
use App\Models\model_produk;
use Exception;

class service_detail_pemesanan
{
    public function isPenitip($produkId): bool
    {
        try {
            $produk = model_produk::find($produkId);
            return $produk->Penitip_Id !== null;
        } catch (Exception $e) {
            return false;
        }
    }

    public function kurangiStok($produkId, $jumlah): void
    {
        $produk = model_produk::find($produkId);
        $produk->Stok -= $jumlah;
        $produk->save();
    }

    public function CekStokProdukBukanPenitip($produkId, $jumlah): bool
    {
        $produk = model_produk::find($produkId);
        return $produk->Stok >= $jumlah;
    }

    public function getPesananById($id)
    {
        return model_pesanan::find($id);
    }

    public function addDetailPemesananProduk($request)
    {
        $pesanan = $this->getPesananById($request['Pesanan_Id']);
        $detail_transaksi = new model_detail_transaksi();

        $detail_transaksi->SubTotal = $request['SubTotal'];
        $detail_transaksi->Total_Produk = $request['Total_Produk'];
        $detail_transaksi->Pesanan_Id = $request['Pesanan_Id'];

        if (isset($request['Produk_Id']) && $request['Produk_Id'] !== null) {
            $detail_transaksi->Produk_Id = $request['Produk_Id'];
            $detail_transaksi->Hampers_Id = null;

            if ($this->isPenitip($request['Produk_Id'])) {
                $this->kurangiStok($request['Produk_Id'], $request['Total_Produk']);
                $pesanan->IsPreOrder = false;
            } else if ($this->CekStokProdukBukanPenitip($request['Produk_Id'], $request['Total_Produk']) > 0) {
                $this->kurangiStok($request['Produk_Id'], $request['Total_Produk']);
                $pesanan->IsPreOrder = false;
            } else {
                $pesanan->IsPreOrder = true;
            }
        } else if (isset($request['Hampers_Id']) && $request['Hampers_Id'] !== null) {
            $detail_transaksi->Hampers_Id = $request['Hampers_Id'];
            $detail_transaksi->Produk_Id = null;
            $pesanan->IsPreOrder = true;
        } else {
            throw new Exception('Produk_Id atau Hampers_Id harus ada dalam permintaan');
        }

        $pesanan->update();
        $detail_transaksi->save();
    }
}
