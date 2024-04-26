<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class request_karyawan extends FormRequest
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
            "Nama" => ["required", "string"],
            "Password" => ["required", "string"],
            "Total_Gaji" => ["numeric"],
            "Bonus" => ["numeric"],
            "Role_id" => ["required"],
        ];
    }

    public function messages(): array
    {
        return [
            "Nama.required" => "Nama tidak boleh kosong",
            "password.string" => "Password tidak boleh kosong",
            "Total_Gaji.numeric" => "Total Gaji harus berupa numeric",
            "Bonus.numeric" => "Bonus harus berupa numeric",
            "Role_id.required" => "Role Id Karyawan harus diisi",
        ];

    }
}
