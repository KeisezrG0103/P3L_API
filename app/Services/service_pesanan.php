<?php

namespace App\Services;

use App\Models\model_pesanan;

/**
 * Class service_pesanan.
 */
class service_pesanan
{

    public function readPesanan() : object
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
