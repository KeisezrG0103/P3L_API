<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class request_penitip extends FormRequest
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
            "Nama_Penitip" => ["required", "string"],
            "komisi" => ["numeric", "min:0", "gte:0"],
            "email" => ["required", "email"],
            "no_telp" => ["numeric", "required"],
        ];
    }

    public function messages(): array
    {
        return [
            "Nama_Penitip.required" => "Nama tidak boleh kosong",
            "Nama_Penitip.string" => "Nama harus berupa string",
            "komisi.numeric" => "Komisi harus berupa numeric",
            "komisi.min" => "Komisi tidak boleh negatif",
            "komisi.gte" => "Komisi tidak boleh negatif",
            "email.required" => "Email tidak boleh kosong",
            "email.email" => "Email tidak valid",
            "no_telp.numeric" => "Nomor Telepon harus numeric",
            "no_telp.required" => "Nomor Telepon tidak boleh kosong",
        ];

    }
}
