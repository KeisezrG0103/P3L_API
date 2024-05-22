<?php

namespace App\Http\Controllers;

use App\Http\Resources\resource_laporan;
use App\Services\service_laporan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;

class controller_laporan extends Controller
{
    private $service_laporan;

    public function __construct()
    {
        $this->service_laporan = new service_laporan();
    }

    public function getAllBahanBaku()
    {
        $date = Carbon::now()->format('Y-m-d');
        $bahan_baku = $this->service_laporan->geAllBahanBaku();

        return response()->json([
            'status' => 'success',
            'date' => $date,
            'data' => $bahan_baku
        ], 200);
    }

    public function laporanProdukPerBulan($bulan)
    {
        $date = Carbon::now()->format('Y-m-d');
        $penjualan = $this->service_laporan->laporanPenjualanPerProduk($bulan);

        return response()->json([
            'status' => 'success',
            'date' => $date,
            'data' => $penjualan
        ], 200);
    }
}