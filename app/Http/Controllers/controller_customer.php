<?php

namespace App\Http\Controllers;

use App\Models\model_customer;
use App\Http\Resources\resource_customer;
use App\Http\Requests\request_customer;
use App\Services\service_customer;
use App\Models\model_history_penarikan_saldo;
use App\Http\Requests\request_saldo;
use Carbon\Carbon;

class controller_customer extends Controller
{

    private service_customer $service_customer;

    public function __construct(service_customer $service_customer)
    {
        $this->service_customer = $service_customer;
    }

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


    public function getTanggalLahirPerCustomer(String $Email)
    {
        $Tanggal_Lahir_Customer = $this->service_customer->getTanggalLahirByEmail($Email);
        return response()->json([
            "Tanggal_Lahir" => $Tanggal_Lahir_Customer
        ]);
    }
    public function getCustomerByEmail(String $Email)
    {
        try {

            $customer = model_customer::where('Email', $Email)->first();

            if (!$customer) {
            
                return response()->json(['error' => 'Customer not found'], 404);
            }

            
            return response()->json([ $customer],200);
        } catch (\Exception $e) {
            
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function requestSaldo(request_saldo $request, String $Email)
    {
        try {
           
            $validatedRequest = $request->validated();

           
            $customer = model_customer::where('Email', $Email)->first();

            if (!$customer) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

           
            $customer->update([
                'IsPengajuanSaldo' => 1,
                'Nama_Bank' => $validatedRequest['Bank'],
                'Nomor_Rekening' => $validatedRequest['Nomor_Rekening']
            ]);

            
            $history = model_history_penarikan_saldo::create([
                'Jumlah_Penarikan' => $validatedRequest['Jumlah_Penarikan'],
                'Customer_Email' => $Email,
                'Tanggal_Penarikan' => Carbon::now('Asia/Jakarta'),
                'Status' => 'Menunggu'
            ]);

            
            return response()->json([
                'customer' => $customer,
                'history' => $history
            ], 200);

        } catch (\Exception $e) {
            
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
        
    public function getHistoryPenarikanSaldo($Email)
    {
        $history = model_history_penarikan_saldo::where("Customer_Email", $Email)->get();
    
       
        return response()->json($history, 200);
        
           
    }

}
