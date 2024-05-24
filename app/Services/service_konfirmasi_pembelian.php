<?php


namespace App\Services;

use App\Models\model_pesanan;
use App\Models\model_produk;
use App\Models\model_detail_transaksi;
use App\Models\model_customer;
use App\Models\model_hampers;

/**
 * Class service_konfirmasi_pembelian.
 */
class service_konfirmasi_pembelian
{
    public function getDaftarPesananToConfirm()
    {
        $pesanan = model_pesanan::select(
            'pesanan.Id',
            'pesanan.Tanggal_Pesan',
            'pesanan.Status',
            'pesanan.Tanggal_Diambil',
            'pesanan.Status_Pembayaran'
        )->where('pesanan.Status_Pembayaran', 'Lunas')
        ->get();

        return $pesanan;
    }

    public function konfirmasiPesanan($id)
    {
       
        $pesanan = model_pesanan::findOrFail($id);
        
      

        $customer = model_customer::where('Email', $pesanan->Customer_Email)->first();

    
        $customer->update(['Total_Poin' => $customer->Total_Poin + $pesanan->Poin_Didapat]);
        
       
         
        if ($pesanan->IsPreOrder == 1) {
             $pesanan->update(['Status' => 'Diterima']); 
        } else {
            if ($pesanan->Is_Deliver == 1) {
                $pesanan->update(['Status' => 'Siap dideliver']);
            } else {
                $pesanan->update(['Status' => 'Siap dipickup']);
            }
        }
        
    }

    
    
    

    public function tolakPesanan($id)
    {
       
        $pesanan = model_pesanan::findOrFail($id);

     
        $details = model_detail_transaksi::where('Pesanan_Id', $id)->get();

        
        $customer = model_customer::where('Email', $pesanan->Customer_Email)->first();

        
        $customer->update(['Total_Saldo' => $customer->Total_Saldo + $pesanan->Total]);

        
        foreach ($details as $detail) {
          
            $produk = model_produk::findOrFail($detail->Produk_Id);
            
           
            if (!$pesanan->IsPreOrder == 1) {
               
                $produk->update([
                    'Stok' => $produk->Stok + $detail->Total_Produk
                ]);
            }
        }

      
        $pesanan->update(['Status' => 'Ditolak']);
    }

}

