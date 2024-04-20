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

    public function readPesanan()
    {
        $pesanan = $this->service->readPesanan();

        return new resource_pesanan($pesanan);
    }

}
