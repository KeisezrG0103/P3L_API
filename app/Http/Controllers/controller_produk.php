<?php

namespace App\Http\Controllers;

use App\Http\Requests\request_produk;
use App\Models\model_produk;
use App\Http\Resources\resource_produk;
use App\Services\service_produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\service_utils;

class controller_produk extends Controller
{

    private service_produk $service;
    private service_utils $service_utils;


    public function __construct(service_produk $service, service_utils $service_utils)
    {
        $this->service = $service;
        $this->service_utils = $service_utils;
    }

    public function createProduk(request_produk $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('Gambar')) {
            $validated = $this->service_utils->saveImage($validated, 'produk');
        }

        $produk = model_produk::create($validated);
        return new resource_produk($produk);
    }

    public function updateProduk(request_produk $request, $id)
    {
        try {
            $produk = model_produk::findOrFail($id);
            $validated = $request->validated();

            if ($request->hasFile('Gambar')) {
                if ($produk->Gambar != null) {
                    $this->service_utils->deleteImage('produk', $produk->Gambar);
                }
                $validated = $this->service_utils->saveImage($validated, 'produk');
            }


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

            if ($produk->Gambar != null) {
                $this->service_utils->deleteImage('produk', $produk->Gambar);
            }
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
        $produk = $this->service->get_produk();

        $produk_with_image = $this->service_utils->transformJsonWithImage($produk, 'produk');



        return resource_produk::collection($produk_with_image);
    }

    public function getById(int $id)
    {
        try {
            $produk = $this->service->getProdukById($id);
            // $produk_with_image = $this->service_utils->getSingleImageUrl($produk, 'produk');
            $produk_with_image = $this->service_utils->transformJsonWithImage($produk, 'produk');
            return new resource_produk($produk_with_image);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 404);
        }
    }

    public function getByNama(string $name)
    {
        $produk = model_produk::where('Nama', 'like', '%' . $name . '%')->get();
        return new resource_produk($produk);
    }
}
