<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\service_konfirmasi_pembelian; 

class controller_konfirmasi_pembelian extends Controller
{
    /**
     * Display a listing of the resource.
     */

     
    private $service_konfirmasi;

    public function __construct()
    {
        $this->service_konfirmasi = new service_konfirmasi_pembelian();
    }
    public function getDaftarPesananToConfirm (){

        try {
            $pesanan = $this->service_konfirmasi->getDaftarPesananToConfirm();
            return response()->json($pesanan, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

    }

    public function konfirmasiPesanan($id)
    {
        try {
            $this->service_konfirmasi->konfirmasiPesanan($id);
            return response()->json(['message' => 'Pesanan berhasil dikonfirmasi'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function tolakPesanan($id)
    {
        try {
            $this->service_konfirmasi->tolakPesanan($id);
            return response()->json(['message' => 'Pesanan berhasil ditolak'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
    
}
