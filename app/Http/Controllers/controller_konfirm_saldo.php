<?php

namespace App\Http\Controllers;

use App\Models\model_history_penarikan_saldo;
use App\Models\model_customer;


class controller_konfirm_saldo extends Controller
{

  
    public function getSaldoToConfirm()
    {
        try {

            $confirm = model_history_penarikan_saldo::where('Status', 'Menunggu')->get();
            return response()->json( $confirm, 200);

        } catch (\Exception $e) {
            
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function confirmRequestSaldo($id)
    {
        try {
           
        
           
            $history = model_history_penarikan_saldo::where('Id', $id)->first();

            if(!$history){
                return response()->json([ 'message' => 'History Not Found!'], 404);
            }

            $customer = model_customer::where('Email', $history->Customer_Email)->first();
 
            $history->update([
                'Status' => 'Berhasil'
            ]);

            $customer->update([
                'Total_Saldo' => $customer->Total_Saldo - $history->Jumlah_Penarikan,
                'IsPengajuanSaldo' => 0 
            ]);
            
            return response()->json([
                'history' => $history,
                'customer' => $customer
            ], 200);

        } catch (\Exception $e) {
            
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
        
   
    public function rejectRequestSaldo($id)
    {
        try {
           
        
           
            $history = model_history_penarikan_saldo::where('Id', $id)->first();

            $customer = model_customer::where('Email', $history->Customer_Email)->first();
 
 
            $history->update([
                'Status' => 'Ditolak'
            ]);

            $customer->update([
                'IsPengajuanSaldo' => 0 
            ]);

            
            return response()->json([
                'history' => $history,
                'customer' => $customer
            ], 200);

        } catch (\Exception $e) {
            
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
