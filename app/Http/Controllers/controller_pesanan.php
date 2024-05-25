<?php

namespace App\Http\Controllers;

use App\Http\Requests\request_pembayaran;
use App\Http\Requests\request_pesanan;
use App\Http\Resources\resource_pesanan;
use App\Services\service_pesanan;
use App\Services\service_proses_pesanan;
use App\Models\model_pesanan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class controller_pesanan extends Controller
{

    protected service_pesanan $service;
    protected service_proses_pesanan $serviceProses;


    public function __construct(service_pesanan $service, service_proses_pesanan $serviceProses)
    {
        $this->service = $service;
        $this->serviceProses = $serviceProses;
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

    public function getPesananAndProdukDitolak($email)
    {
        $pesanan = $this->service->getPesananAndProdukDitolak($email);
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


        $file_path = $request->file('Bukti_Pembayaran')->store('Bukti_Pembayaran', 'public');


        $pesanan->update(['Bukti_Pembayaran' => url(Storage::url($file_path))]);


        $pesanan->update(['Tanggal_Pelunasan' => Carbon::now('Asia/Jakarta')]);
        $pesanan->update(['Status_Pembayaran' => 'Sudah Bayar']);
        $pesanan->update(['Status' => 'Menunggu Konfirmasi Pembayaran']);


        return new resource_pesanan($pesanan);
    }

    public function getDaftarPesananYangDiprosesHariIni($tanggal_besok)
    {
        $pesanan = $this->serviceProses->getDaftarPesananYangDiprosesHariIni($tanggal_besok);
        return resource_pesanan::collection($pesanan);
    }

    public function prosesPesanan($NoNota)
    {
        $pesanan = $this->serviceProses->prosesPesanan($NoNota);
        return response()->json($pesanan);
    }

    public function getListPesananHarianDanYangDibeli($tanggal_besok)
    {
        $pesanan = $this->serviceProses->getListPesananHarianDanYangDibeli($tanggal_besok);
        return resource_pesanan::collection($pesanan);
    }

    public function getRekapPesananHarian($tanggal_besok)
    {
        $pesanan = $this->serviceProses->getRekapPesananHarian($tanggal_besok);
        return response()->json($pesanan);
    }
}
