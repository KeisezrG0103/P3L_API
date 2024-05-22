<?php

namespace App\Http\Controllers;

use App\Http\Resources\resource_resep;
use Illuminate\Http\Request;
use App\Models\model_resep;
use App\Models\model_produk;
use App\Services\service_resep;

class controller_resep extends Controller
{
    private service_resep $service_resep;

    public function __construct(service_resep $service_resep)
    {
        $this->service_resep = $service_resep;
    }

    public function generateResepAllProduk()
    {
        $this->service_resep->generateResepAllProduk();
        return response()->json([
            'message' => 'Success'
        ]);
    }

    public function getResep()
    {
        $resep = $this->service_resep->readResep();
        return resource_resep::collection($resep);
    }
}
