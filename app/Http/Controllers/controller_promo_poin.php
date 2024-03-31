<?php

namespace App\Http\Controllers;

use App\Http\Resources\resource_promo_poin;
use App\Models\model_promo_poin;
use App\Http\Requests\request_promo_poin;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class controller_promo_poin extends Controller
{
    public function createPromoPoin(request_promo_poin $request)
    {
        $promoPoinData = $request->validated();
        $promoPoin = model_promo_poin::create($promoPoinData);
        return new resource_promo_poin($promoPoin);
    }

    public function updatePromoPoin(request_promo_poin $request, int $id)
    {
        try {
            $promoPoin = model_promo_poin::findOrFail($id);
            $promoPoinData = $request->validated();
            $promoPoin->update($promoPoinData);
            return new resource_promo_poin($promoPoin);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Promo poin dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }

    }

    public function deletePromoPoin(int $id)
    {
        try {
            $promoPoin = model_promo_poin::findOrFail($id);
            $promoPoin->delete();
            $promoPoinResource = new resource_promo_poin($promoPoin);
            $promoPoinResource->setIsDeleted(true);
            return $promoPoinResource;
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Promo poin dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }

    public function readPromo(){
        $promoPoin = model_promo_poin::all();
        return resource_promo_poin::collection($promoPoin);
    }

    public function getById(int $id){
        try {
            $promoPoin = model_promo_poin::findOrFail($id);
            return new resource_promo_poin($promoPoin);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Promo poin dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }


    }

}
