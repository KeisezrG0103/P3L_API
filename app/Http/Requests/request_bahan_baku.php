<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class request_bahan_baku extends FormRequest
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
            "Qty" => ["required", "numeric"],
            "Satuan" => ["required", "string"],
        ];
    }

    public function messages(): array
    {
        return [
            "Nama.required" => "Nama tidak boleh kosong",
            "Qty.required" => "Qty tidak boleh kosong",
            "Satuan.required" => "Satuan tidak boleh kosong",
            "Nama.string" => "Nama harus berupa string",
            "Qty.numeric" => "Qty harus berupa angka",
            "Satuan.string" => "Satuan harus berupa string",
        ];

    }
}
