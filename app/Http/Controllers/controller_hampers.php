<?php

namespace App\Http\Controllers;

use App\Http\Requests\request_hampers;
use App\Models\model_hampers;
use App\Http\Resources\resource_hampers;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class controller_hampers extends Controller
{
    public function createHampers(request_hampers $request)
    {
        $hampersData = $request->validated();
        $hampers = model_hampers::create($hampersData);
        return new resource_hampers($hampers);
    }

    public function updateHampers(request_hampers $request_hampers, int $id)
    {
        try {
            $hampers = model_hampers::findOrFail($id);
            $hampersData = $request_hampers->validated();
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
            $hampers->delete();
            $hampersResource = new resource_hampers($hampers);
            return $hampersResource;
        } catch (\Throwable $th) {
            if($th instanceof \Illuminate\Database\QueryException){
                return response()->json([
                    'message' => 'Hampers dengan Id ' . $id . ' tidak bisa dihapus karena sedang digunakan',
                ], 400);
            }else {
                return response()->json([
                    'message' => 'Hampers dengan Id ' . $id . ' tidak ditemukan',
                ], 404);
            }
        }
    }
    public function readHampers()
    {
        $hampers = model_hampers::all();
        return resource_hampers::collection($hampers);
    }

    public function getById(int $id)
    {
        try {
            $hampers = model_hampers::findOrFail($id);
            return new resource_hampers($hampers);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Hampers dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }
}
