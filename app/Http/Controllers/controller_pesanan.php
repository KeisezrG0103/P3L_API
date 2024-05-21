<?php

namespace App\Http\Controllers;

use App\Http\Requests\request_pembayaran;
use App\Http\Requests\request_pesanan;
use App\Http\Resources\resource_pesanan;
use App\Services\service_pesanan;
use App\Services\service_utils;
use App\Models\model_pesanan;
use Illuminate\Support\Facades\Validator;

class controller_pesanan extends Controller
{

    protected service_pesanan $service;
    protected service_utils $service_utils;
    public function __construct(service_pesanan $service, service_utils $service_utils)
    {
        $this->service = $service;
        $this->service_utils = $service_utils;
    }

    public function getHistoryByEmail(string $id)
    {
        $pesanan = $this->service->readHistoryByEmail($id);

        return  resource_pesanan::collection($pesanan);
    }

    public function getAllHistoryPesanan()
    {
        $pesanan = $this->service->getAllHistoryPesanan();
        return  resource_pesanan::collection($pesanan);
    }

    public function getLatestPesanan($month)
    {
        $pesanan = $this->service->getLatestPesananId($month);
        return response()->json([
            "no_pesanan" => $pesanan
        ]);
    }

    public function generateNoNota($month)
    {
        $nota = $this->service->generateNoNota($month);
        return response()->json([
            "no_nota" => $nota
        ]);
    }

    public function PesanProduk(request_pesanan $request)
    {
        $pesananValidated = $request->validated();
        $this->service->PesanProduk($pesananValidated);
        return response()->json([
            "message" => "Pesanan berhasil dibuat"
        ]);
    }

    public function getNotaById($NoNota)
    {
        $pesanan = $this->service->getFullNota($NoNota);
        return response()->json($pesanan);
    }

    public function getPesananAndProdukOnGoing($email)
    {
        $pesanan = $this->service->getPesananAndProdukOnGoing($email);
        return resource_pesanan::collection($pesanan);
    }

 

    public function sendBuktiPembayaran(request_pembayaran $request, $id)
    {
      
        $validator = Validator::make($request->all(), [
            'Bukti_Pembayaran' => ['required', 'mimes:jpg,png'],
        ]);
    
      
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 400); 
        }
    
    
        $pesanan = model_pesanan::find($id);
    
        if (!$pesanan) {
            return response()->json([
                "message" => "Pesanan dengan ID $id tidak ditemukan."
            ], 404);
        }
    
        
        if ($request->hasFile('Bukti_Pembayaran')) {
          
            $file_name = $this->service_utils->saveImageBayar($request->file('Bukti_Pembayaran'), 'Bukti_Pembayaran');
    
            $pesanan->update(['Bukti_Pembayaran' => $file_name]);
        }
    
       
        return new resource_pesanan($pesanan);
    }
    
    

}
