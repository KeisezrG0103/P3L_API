<?php

namespace App\Http\Controllers;

use App\Http\Resources\resource_pesanan;
use App\Services\service_pesanan;

class controller_pesanan extends Controller
{

    private service_pesanan $service;
    public function __construct(service_pesanan $service)
    {
        $this->service = $service;
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

}
