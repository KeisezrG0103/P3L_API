<?php

namespace App\Http\Controllers;

use App\Http\Requests\request_alamat;
use App\Http\Resources\resource_alamat;
use App\Services\service_alamat;
use Illuminate\Http\Request;

class controller_alamat extends Controller
{
    private service_alamat $service;

    public function __construct(service_alamat $service)
    {
        $this->service = $service;
    }

    public function getAlamat($email)
    {
        try {
            $alamat = $this->service->getAlamat($email);

            return resource_alamat::collection($alamat);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function addAlamat(request_alamat $request, $email)
    {
        try {
            $this->service->addAlamat($request, $email);

            return response()->json([
                'message' => 'Alamat berhasil ditambahkan'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
