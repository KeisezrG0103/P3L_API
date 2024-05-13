<?php

namespace App\Http\Controllers;

use App\Services\service_pesanan;
use Illuminate\Http\Request;

class controller_pesanan_selesai extends Controller

{

    protected $service;

    public function __construct(service_pesanan $service)
    {
        $this->service = $service;
    }

    public function getPesananSelesaiWithDetailPesananAndTanggal($Email)
    {
        $pesanan = $this->service->getPesananSelesaiWithDetailPesananAndTanggal($Email);
        return response()->json([
            "data" => $pesanan
        ]);
    }
}
