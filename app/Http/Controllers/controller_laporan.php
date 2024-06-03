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

    public function laporanProdukPerBulan($bulan, $year)
    {
        $date = Carbon::now()->format('Y-m-d');
        $penjualan = $this->service_laporan->filterSameNameAndAdd($bulan, $year);

        return response()->json([
            'status' => 'success',
            'date' => $date,
            'data' => $penjualan
        ], 200);
    }


    public function laporanPresensiKaryawan($bulan, $year)
    {

        $date = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $presensi = $this->service_laporan->laporanPresensiKaryawan($bulan, $year);

        return response()->json([
            'status' => 'success',
            'date' => $date,
            'data' => $presensi
        ], 200);
    }

    public function getLaporanPresensi($date)
    {

        $carbonDate = Carbon::parse($date);
        $bulan = $carbonDate->format('m');
        $year = $carbonDate->format('Y');


        return $this->laporanPresensiKaryawan($bulan, $year);
    }

    public function laporanKeuangan($bulan, $year)
    {
        $date = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $keuangan = $this->service_laporan->laporanKeuangan($bulan, $year);

        return response()->json([
            'status' => 'success',
            'date' => $date,
            'data' => $keuangan
        ], 200);
    }

    public function getLaporanKeuangan($date)
    {

        $carbonDate = Carbon::parse($date);
        $bulan = $carbonDate->format('m');
        $year = $carbonDate->format('Y');


        return $this->laporanKeuangan($bulan, $year);
    }

    public function getlaporanPenitip($date)
    {
        $carbonDate = Carbon::parse($date);
        $bulan = $carbonDate->month;
        $tahun = $carbonDate->year;

        $laporan = $this->service_laporan->laporanPenitip($bulan, $tahun);

        return response()->json([
            'status' => 'success',
            'date' => $carbonDate->format('Y-m-d'),
            'data' => $laporan
        ], 200);
    }

    public function LaporanPenggunaanBahanBakuByPeriode($start, $end)
    {
        try {
            $date = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $laporan = $this->service_laporan->RecapLaporanPenggunaanBahanBakuByPeriode($start, $end);

            return response()->json([
                'status' => 'success',
                'date' => $date,
                'data' => $laporan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function LaporanPenjualanBulananSecaraKeseluruhan()
    {
        try {
            $date = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $laporan = $this->service_laporan->LaporanPenjualanBulananSecaraKeseluruhan();

            return response()->json([
                'status' => 'success',
                'date' => $date,
                'data' => $laporan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
