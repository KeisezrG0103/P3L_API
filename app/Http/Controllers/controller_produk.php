<?php

namespace App\Http\Controllers;

use App\Http\Requests\request_produk;
use App\Models\model_produk;
use App\Http\Resources\resource_produk;
use Illuminate\Http\Request;

class controller_produk extends Controller
{
    public function createProduk(request_produk $request)
    {
        $validated = $request->validated();
        $produk = model_produk::create($validated);
        return new resource_produk($produk);
    }
}
