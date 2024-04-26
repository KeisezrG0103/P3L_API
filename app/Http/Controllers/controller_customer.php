<?php

namespace App\Http\Controllers;

use App\Http\Requests\request_customer;
use Illuminate\Http\Request;
use App\Models\model_customer;
use App\Http\Resources\resource_customer;
use App\Http\Resources\resource_gaji;
use Illuminate\Support\Facades\Validator;

class controller_customer extends Controller
{
    public function createCustomer(request_customer $request)
    {
        $validated = $request->validated();

        $customer = model_customer::create($validated);
        return new resource_customer($customer);
    }

    public function updateCustomer(request_customer $request, $id)
    {
        try {
            $customer = model_customer::findOrFail($id);
            $validated = $request->validated();


            $customer->update($validated);
            return new resource_customer($customer);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'customer dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }

    public function readCustomer()
    {
        $customer = model_customer::all();
        return resource_customer::collection($customer);
    }

    public function deleteCustomer(int $id)
    {
        try {
            $customer = model_customer::findOrFail($id);

            $customer->delete();

            return response()->json([
                'message' => 'customer dengan Id ' . $id . ' berhasil dihapus.',
            ]);

            $customer = new resource_customer($customer);

            return $customer;
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'customer dengan Id ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }

    public function getByNama(string $name)
    {
        $customer = model_customer::where('Nama', $name)->first();

        if (!$customer) {
            return response()->json([
                'message' => "customer dengan nama '$name' tidak ditemukan."
            ], 404);
        }

        return new resource_customer($customer);
    }
}
