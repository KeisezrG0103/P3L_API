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

    public function updateProduk(request_produk $request, int $id)
    {
        try {
            $produk = model_produk::findOrFail($id);
            $validated = $request->validated();
            $produk->update($validated);
            return new resource_produk($produk);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Produk dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }

    public function deleteProduk(int $id)
    {
        try {
            $produk = model_produk::findOrFail($id);
            $produk->delete();
            $produkResource = new resource_produk($produk);
            return $produkResource;
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Produk dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }

    public function readProduk()
    {
        $produk = model_produk::all();
        return resource_produk::collection($produk);
    }

    public function getById(int $id)
    {
        try {
            $produk = model_produk::findOrFail($id);
            return new resource_produk($produk);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Produk dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }

    public function getByNama(string $name)
    {
        $produk = model_produk::where('Nama', 'like', '%' . $name . '%')->get();
        return resource_produk::collection($produk);
    }

}
