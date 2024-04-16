<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\model_bahan_baku;
use App\Http\Resources\resource_bahan_baku;

class controller_bahan_baku extends Controller
{
    public function readBahanBaku()
    {
        $bahan_baku = model_bahan_baku::all();
        return resource_bahan_baku::collection($bahan_baku);
    }
}
