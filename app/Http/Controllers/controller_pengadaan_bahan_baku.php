<?php

namespace App\Http\Controllers;
use App\Models\model_pengadaan_bahan_baku;
use App\Http\Resources\resource_pengadaan_bahan_baku;
use App\Http\Requests\request_pengadaan_bahan_baku;
use App\Models\model_bahan_baku;
use App\Services\service_pengadaan_bahan_baku;

use Illuminate\Http\Request;

class controller_pengadaan_bahan_baku extends Controller
{
    private service_pengadaan_bahan_baku $service_pengadaan_bahan_baku;

    public function __construct(service_pengadaan_bahan_baku $service_pengadaan_bahan_baku)
    {
        $this->service_pengadaan_bahan_baku = $service_pengadaan_bahan_baku;
    }
    public function createPengadaanBahanBaku(request_pengadaan_bahan_baku $request)
    {
        try {
            $request_val = $request->validated();
            $bahan = model_bahan_baku::select('Nama')->where('Id', $request_val['BahanBaku_Id'])->first();
            $pengadaan_bahan_baku = model_pengadaan_bahan_baku::create($request_val);


            return new resource_pengadaan_bahan_baku($pengadaan_bahan_baku);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function readPengadaanBahanBakuByID($id)
    {
        try {
            $pengadaan_bahan_baku = $this->service_pengadaan_bahan_baku->getDetailPengadaanByID($id);
            return response()->json($pengadaan_bahan_baku, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function readPengadaanBahanBaku()
    {
        $pengadaan_bahan_baku = $this->service_pengadaan_bahan_baku->getDetailPengadaan();
        return resource_pengadaan_bahan_baku::collection($pengadaan_bahan_baku);
    }


    public function updatePengadaanBahanBaku(request_pengadaan_bahan_baku $request, $id)
    {
        try {
            $pengadaan_bahan_baku = model_pengadaan_bahan_baku::findOrFail($id);
            $request_val = $request->validated();
            $pengadaan_bahan_baku->update($request_val);
            return new resource_pengadaan_bahan_baku($pengadaan_bahan_baku);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function deletePengadaanBahanBaku($id)
    {
        try {
            $pengadaan_bahan_baku = model_pengadaan_bahan_baku::findOrFail($id);
            $pengadaan_bahan_baku->delete();
            return response()->json(['message' => 'Data Deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }



}
