<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class request_pembayaran extends FormRequest
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
            "Bukti_Pembayaran" => ["required", "mimes:jpg,png"]
        ];
    }

    public function messages(): array
    {
        return [
            "Bukti_Pembayaran.required" => "Bukti Pembayaran tidak boleh kosong",
            "Bukti_Pembayaran.mimes" => "Bukti Pembayaran harus berupa file dengan format JPG atau PNG",
        ];

    }
}
