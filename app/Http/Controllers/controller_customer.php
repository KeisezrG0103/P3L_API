<?php

namespace App\Http\Controllers;

use App\Models\model_customer;
use App\Http\Resources\resource_customer;
use App\Http\Requests\request_customer;


class controller_customer extends Controller
{
    public function readCustomer()
    {
        $customer = model_customer::all();
        return resource_customer::collection($customer);
    }

    public function getById(string $id)
    {
        $customer = model_customer::where('Email', $id)->first();

        if (!$customer) {
            return response()->json([
                'message' => "Customer dengan ID $id tidak ditemukan."
            ], 404);
        }

        return new resource_customer($customer);
    }

    public function getByNama(string $name)
    {
        $customer = model_customer::where('Nama', $name)->first();

        if (!$customer) {
            return response()->json([
                'message' => "Customer dengan nama '$name' tidak ditemukan."
            ], 404);
        }

        return new resource_customer($customer);
    }

    public function registerCustomer(request_customer $request)
    {
        $validated = $request->validated();
        try {
            $validated['Password'] = password_hash($validated['Password'], PASSWORD_DEFAULT);
            $customer = model_customer::create($validated);
            return new resource_customer($customer);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
