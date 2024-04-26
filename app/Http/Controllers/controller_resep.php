<?php

namespace App\Http\Controllers;

use App\Http\Requests\request_resep;
use Illuminate\Http\Request;
use App\Models\model_resep;
use App\Http\Resources\resource_resep;
use Illuminate\Support\Facades\DB;

class controller_resep extends Controller
{
    public function createResep(request_resep $request)
    {
        $validated = $request->validated();

        $resep = model_resep::create($validated);
        return new resource_resep($resep);
    }
    
    public function updateResep(request_resep $request, $id)
    {
        try {
            $resep = model_resep::findOrFail($id);
            $validated = $request->validated();


            $resep->update($validated);
            return new resource_resep($resep);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Resep dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }
    
    public function readResep()
    {
        $resep = model_resep::all();
        return resource_resep::collection($resep);
       
    }

    public function getDetail($nama)
    {
        $resep = DB::table('detail_resep')
        ->join('resep', 'detail_resep.Resep_Id', '=', 'resep.Id')
        ->join('bahan_baku', 'detail_resep.Bahan_Baku_Id', '=', 'bahan_baku.Id')
        ->select('detail_resep.*', 'bahan_baku.nama')
        ->where('resep.nama', '=', $nama)
        ->get();

        return response([
            'data' => $resep
        ], 200);
    }



    public function deleteResep(int $id)
    {
        try {
            $resep = model_resep::findOrFail($id);

            $resep->delete();

            return response()->json([
                'message' => 'Resep dengan Id ' . $id . ' berhasil dihapus.',
            ]);

            $resep = new resource_resep($resep);
            
            return $resep;
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Resep dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }


    
    public function getByNama(string $name)
    {
        $resep = model_Resep::where('Nama', $name)->first();
        
        if (!$resep) {
            return response()->json([
                'message' => "Resep dengan nama '$name' tidak ditemukan."
            ], 404);
        }
    
        return new resource_resep($resep);
    }
}
