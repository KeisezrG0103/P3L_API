<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class resource_produk extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'Id' => $this->Id,
            'Nama' => $this->Nama,
            'Harga' => $this->Harga,
            'Stok' => $this->Stok,
            'Satuan' => $this->Satuan,
            'Penitip_Id' => $this->Penitip_Id,
        ];
    }
}
