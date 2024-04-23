<?php

namespace App\Services;

use App\Models\model_pesanan;

/**
 * Class service_pesanan.
 */
class service_pesanan
{

    public function readHistoryByEmail(string $id) : object
    {
        $pesanan = model_pesanan::select(
            'pesanan.Id as Id',
            'pesanan.Ongkos_Kirim as Ongkos_Kirim',
            'pesanan.Total as Total',
            'pesanan.Status as Status',
            'pesanan.Tanggal_Diambil as Tanggal_Diambil',
            'pesanan.Tanggal_Pesan as Tanggal_Pesan',
            'customer.Email as Email', 
            'customer.Nama as Nama',
            'pesanan.Bukti_Pembayaran as Bukti_Pembayaran',
            'pesanan.Tanggal_Pelunasan as Tanggal_Pelunasan',
            'alamat.Alamat as Alamat',
            'pesanan.Status_Pembayaran as Status_Pembayaran',
            'pesanan.Tip as Tip',
        )
        ->leftJoin('customer', 'pesanan.Customer_Email', '=', 'customer.Email')
        ->leftJoin('alamat', 'pesanan.Alamat_Id', '=', 'alamat.Id')
        ->where('customer.Email', $id)
        ->get();
    
        
        foreach ($pesanan as $pes) {
            $produk = model_pesanan::select('produk.Nama as Nama_Produk')
                ->join('detail_transaksi', 'pesanan.Id', '=', 'detail_transaksi.Pesanan_id')
                ->join('produk', 'detail_transaksi.Produk_Id', '=', 'produk.Id')
                ->where('pesanan.Id', $pes->Id)
                ->get();
    
            $hampers = model_pesanan::select('hampers.Nama_Hampers as Nama_Hampers')
                ->join('detail_transaksi', 'pesanan.Id', '=', 'detail_transaksi.Pesanan_id')
                ->join('hampers', 'detail_transaksi.Hampers_Id', '=', 'hampers.Id')
                ->where('pesanan.Id', $pes->Id)
                ->get();
    
            $produkNames = [];
            foreach ($produk as $prod) {
                $produkNames[] = $prod->Nama_Produk;
            }
            $pes->Nama_Produk = implode(', ', $produkNames);
            
    
            $hampersNames = [];
            foreach ($hampers as $hamp) {
                $hampersNames[] = $hamp->Nama_Hampers;
            }
            $pes->Nama_Hampers = implode(', ', $hampersNames);
        }
    
        return $pesanan;
    }
    
    

    public function getAllHistoryPesanan() : object
    {
        return model_pesanan::select(
            'pesanan.Id as Id',
            'pesanan.Ongkos_Kirim as Ongkos_Kirim',
            'pesanan.Total as Total',
            'pesanan.Status as Status',
            'pesanan.Tanggal_Diambil as Tanggal_Diambil',
            'pesanan.Tanggal_Pesan as Tanggal_Pesan',
            'customer.Email as Email', 
            'customer.Nama as Nama',
            'pesanan.Bukti_Pembayaran as Bukti_Pembayaran',
            'pesanan.Tanggal_Pelunasan as Tanggal_Pelunasan',
            'alamat.Alamat as Alamat',
            'pesanan.Status_Pembayaran as Status_Pembayaran',
            'pesanan.Tip as Tip',
        )->leftJoin('customer', 'pesanan.Customer_Email', '=', 'customer.Email')
        ->leftJoin('alamat', 'pesanan.Alamat_Id', '=', 'alamat.Id')
        ->get();
    }
}
