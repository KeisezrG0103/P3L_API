<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class service_produk
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function get_produk() : object
    {
        return DB::table('produk')->select(
            'produk.Id as Id',
            'produk.Nama as Nama_Produk',
            'produk.Harga as Harga_Produk',
            'produk.Satuan as Satuan_Produk',
            'produk.Stok as Stok_Produk',
            'penitip.Nama_Penitip as Penitip',
            'kategori.Kategori as Kategori'
        )->leftJoin('penitip', 'produk.Penitip_Id', '=', 'penitip.Id')
        ->leftJoin('kategori', 'produk.Kategori_Id', '=', 'kategori.Id')
        ->get();
    }

    public function getProdukById(int $id) : object
    {
        return DB::table('produk')->select(
            'produk.Id as Id',
            'produk.Nama as Nama_Produk',
            'produk.Harga as Harga_Produk',
            'produk.Satuan as Satuan_Produk',
            'produk.Stok as Stok_Produk',
            'penitip.Nama_Penitip as Penitip',
            'kategori.Kategori as Kategori'
        )->leftJoin('penitip', 'produk.Penitip_Id', '=', 'penitip.Id')
        ->leftJoin('kategori', 'produk.Kategori_Id', '=', 'kategori.Id')
        ->where('produk.Id', '=', $id)
        ->get();
    }
}
