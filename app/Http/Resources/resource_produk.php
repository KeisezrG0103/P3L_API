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
    public function __construct($resource)
    {
        parent::__construct($resource);
    }
    public function toArray(Request $request): array
    {
        return [
            'Id' => $this->Id,
            'Nama_Produk' => $this->Nama_Produk,
            'Harga' => $this->Harga_Produk,
            'Stok' => $this->Stok_Produk,
            'Kategori' => $this->Kategori,
            'Penitip' => $this->Penitip,
        ];
    }
}
