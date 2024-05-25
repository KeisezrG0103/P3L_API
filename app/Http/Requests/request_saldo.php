<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class request_saldo extends FormRequest
{
    public function rules(): array
    {
        return [
            'Jumlah_Penarikan' => 'required|numeric| gt:0',
            'Bank' => 'required',
            'Nomor_Rekening' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'Jumlah_Penarikan.required' => 'Jumlah Penarikan is required', 
            'Jumlah_Penarikan.numeric' => 'Jumlah Penarikan harus berupa angka', 
            'Jumlah_Penarikan.gt' => 'Jumlah Penarikan tidak boleh atau lebih kecil dari 0', 
            'Bank.required' => 'Nama Bank is required',
            'Nomor_Rekening.required' => 'Nomor Rekening is required',
            'Nomor_Rekening.numeric' => 'Nomor Rekening harus berupa angka',
        ];
    }
}
