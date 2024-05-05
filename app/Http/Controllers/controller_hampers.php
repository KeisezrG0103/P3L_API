<?php

namespace App\Http\Controllers;

use App\Http\Requests\request_hampers;
use App\Models\model_hampers;
use App\Http\Resources\resource_hampers;
use App\Services\service_utils;

class controller_hampers extends Controller
{
    public service_utils $service_utils;

    public function __construct(service_utils $service_utils)
    {
        $this->service_utils = $service_utils;
    }


    public function createHampers(request_hampers $request)
    {
        $hampersData = $request->validated();
        if ($request->hasFile('Gambar')) {
            $hampersData = $this->service_utils->saveImage($hampersData, 'hampers');
        }

        $hampers = model_hampers::create($hampersData);
        return new resource_hampers($hampers);
    }

    public function updateHampers(request_hampers $request_hampers, int $id)
    {
        try {
            $hampers = model_hampers::findOrFail($id);
            $hampersData = $request_hampers->validated();
            if ($request_hampers->hasFile('Gambar')) {
                if ($hampers->Gambar != null) {
                    $this->service_utils->deleteImage('hampers', $hampers->Gambar);
                }
                $hampersData = $this->service_utils->saveImage($hampersData, 'hampers');
            }

            $hampers->update($hampersData);

            return new resource_hampers($hampers);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Hampers dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }

    public function deleteHampers(int $id)
    {
        try {
            $hampers = model_hampers::findOrFail($id);
            if ($hampers->Gambar != null) {
                $this->service_utils->deleteImage('hampers', $hampers->Gambar);
            }
            $hampers->delete();
            $hampersResource = new resource_hampers($hampers);
            return $hampersResource;
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return response()->json([
                    'message' => 'Hampers dengan Id ' . $id . ' tidak bisa dihapus karena sedang digunakan',
                ], 400);
            } else {
                return response()->json([
                    'message' => 'Hampers dengan Id ' . $id . ' tidak ditemukan',
                ], 404);
            }
        }
    }
    public function readHampers()
    {
        // Get all hampers
        $hampers = model_hampers::all();

        $hampers_with_image = $this->service_utils->transformJsonWithImage($hampers, 'hampers');

        return resource_hampers::collection($hampers_with_image);
    }

    public function getById(int $id)
    {
        try {
            $hampers = model_hampers::findOrFail($id);
            $hampers_with_image = $this->service_utils->getSingleImageUrl($hampers, 'hampers');


            return new resource_hampers($hampers_with_image);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Hampers dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }



}
