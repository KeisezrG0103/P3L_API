<?php

namespace App\Http\Controllers;

use App\Http\Requests\request_pesanan;
use App\Http\Resources\resource_pesanan;
use App\Services\service_pesanan;
use App\Services\service_proses_pesanan;

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

    public function getDaftarPesananYangDiprosesHariIni($tanggalBesok)
    {
        $pesanan = $this->serviceProses->getDaftarPesananYangDiprosesHariIni($tanggalBesok);
        return resource_pesanan::collection($pesanan);
    }
}
