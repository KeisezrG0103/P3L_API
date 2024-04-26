<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class request_customer extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "Email" => ["required","string", "unique:users,email"],
            "Nama" => ["required", "string"],
            "Password" => ["required", "string"],
            "Total_Poin" => ["numeric"],
            "Total_Saldo" => ["numeric"],
            "IsPengajuanSaldo" => ["numeric"],
            "Nomor_Rekening" => ["numeric"],
            "Tanggal_Lahir" => ["required"],
        ];
    }

    public function messages(): array
    {
        return [
            "Email.required" => "Email tidak boleh kosong",
            "Nama.required" => "Nama tidak boleh kosong",
            "Password.string" => "Password tidak boleh kosong",
            "Total_Poin.numeric" => "Total Poin harus berupa numeric",
            "Total_Saldo.numeric" => "Total Saldo harus berupa numeric",
            "Nomor_Rekening.numeric" => "No Rek harus berupa numeric",
            "Tanggal_Lahir.required" => "Tanggal Lahir harus diisi",
        ];

    }
}
