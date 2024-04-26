<?php

namespace App\Http\Controllers;

use App\Http\Requests\request_presensi;
use Illuminate\Http\Request;
use App\Models\model_presensi;
use App\Http\Resources\resource_presensi;

class controller_presensi extends Controller
{
    public function updatepresensi(request_presensi $request, $id)
    {
        try {
            $presensi = model_presensi::findOrFail($id);
            $validated = $request->validated();


            $presensi->update($validated);
            return new resource_presensi($presensi);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'presensi dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }

    public function getByNama(string $name)
    {
        $presensi = model_presensi::where('Nama', $name)->first();

        if (!$presensi) {
            return response()->json([
                'message' => "presensi dengan nama '$name' tidak ditemukan."
            ], 404);
        }

        return new resource_presensi($presensi);
    }
}
