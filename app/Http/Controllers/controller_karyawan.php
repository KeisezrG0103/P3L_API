<?php

namespace App\Http\Controllers;

use App\Http\Requests\request_karyawan;
use Illuminate\Http\Request;
use App\Models\model_karyawan;
use App\Http\Resources\resource_karyawan;
use App\Http\Resources\resource_gaji;
use Illuminate\Support\Facades\Validator;

class controller_karyawan extends Controller
{
    public function createResep(request_karyawan $request)
    {
        $validated = $request->validated();

        $karyawan = model_karyawan::create($validated);
        return new resource_karyawan($karyawan);
    }

    public function updatekaryawan(request_karyawan $request, $id)
    {
        try {
            $karyawan = model_karyawan::findOrFail($id);
            $validated = $request->validated();


            $karyawan->update($validated);
            return new resource_karyawan($karyawan);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'karyawan dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }

    public function readkaryawan()
    {
        $karyawan = model_karyawan::all();
        return resource_karyawan::collection($karyawan);
    }

    public function deletekaryawan(int $id)
    {
        try {
            $karyawan = model_karyawan::findOrFail($id);

            $karyawan->delete();

            return response()->json([
                'message' => 'karyawan dengan Id ' . $id . ' berhasil dihapus.',
            ]);

            $karyawan = new resource_karyawan($karyawan);

            return $karyawan;
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'karyawan dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }

    public function getByNama(string $name)
    {
        $karyawan = model_karyawan::where('Nama', $name)->first();

        if (!$karyawan) {
            return response()->json([
                'message' => "karyawan dengan nama '$name' tidak ditemukan."
            ], 404);
        }

        return new resource_karyawan($karyawan);
    }

    public function updateGaji(request_karyawan $request, $id)
    {
        try {
            $karyawan = model_karyawan::findOrFail($id);

            $validated = Validator::make($request, [
                'TotalGaji' => 'required',
                'Bonus' => 'required'
            ]);

            $karyawan->update($validated);
            return new resource_karyawan($karyawan);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'karyawan dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }
}
