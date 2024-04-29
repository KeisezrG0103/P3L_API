<?php

namespace App\Http\Controllers;

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
}
