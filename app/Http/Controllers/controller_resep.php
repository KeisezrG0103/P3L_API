<?php

namespace App\Http\Controllers;

use App\Http\Resources\resource_resep;
use App\Models\model_pesanan;
use Illuminate\Http\Request;
use App\Models\model_resep;
use App\Models\model_produk;
use App\Services\service_history_bahan_baku;
use App\Services\service_proses_pesanan;
use App\Services\service_resep;

class controller_resep extends Controller
{
    private service_resep $service_resep;
    private service_proses_pesanan $service_proses_pesanan;
    private service_history_bahan_baku $service_history_bahan_baku;

    public function __construct(service_resep $service_resep, service_proses_pesanan $service_proses_pesanan, service_history_bahan_baku $service_history_bahan_baku)
    {
        $this->service_resep = $service_resep;
        $this->service_proses_pesanan = $service_proses_pesanan;
        $this->service_history_bahan_baku = $service_history_bahan_baku;
    }


    public function generateResepAllProduk()
    {
        $this->service_resep->generateResepAllProduk();
        return response()->json([
            'message' => 'Success'
        ]);
    }

    public function getResep()
    {
        $resep = $this->service_resep->readResep();
        return resource_resep::collection($resep);
    }

    public function getResepFromDetailPesanan($noNota)
    {
        $resep = $this->service_proses_pesanan->getResepFromDetailPesanan($noNota);
        return response()->json([
            'resep' => $resep
        ]);
    }

    public function getDetailResepByPesanan($nota)
    {
        try {
            $deyail_pesanan = $this->service_proses_pesanan->getDetailResepByPesanan($nota);
            return response()->json([
                'detail_pesanan' => $deyail_pesanan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function GetKekuranganBahanBaku($noNota)
    {
        try {
            $compare = $this->service_proses_pesanan->compareStokBahanBakuDanKebutuhan($noNota);
            if (count($compare) == 0) {
                return response()->json([
                    'message' => 'Semua bahan baku cukup'
                ]);
            }

            return response()->json([
                'message' => 'Ada bahan baku yang kurang',
                'Kekurangan' => $compare
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }



    public function changeStatusToProses($noNota)
    {
        $pesanan = model_pesanan::where('pesanan.Id', $noNota)->first();
        $compare = $this->service_proses_pesanan->compareStokBahanBakuDanKebutuhan($noNota);

        if (!$pesanan) {
            return response()->json([
                'message' => 'Pesanan dengan ID $noNota tidak ditemukan'
            ]);
        } else {
            if (count($compare) == 0) {
                $this->service_proses_pesanan->CatatPemakaianBahanBaku($noNota);
                $this->service_proses_pesanan->KurangiStokBahanBaku($noNota);

                $pesanan->Status = 'Diproses';
                $pesanan->save();
                return response()->json([
                    'message' => 'Pesanan berhasil diubah menjadi proses'
                ]);
            } else {
                return response()->json([
                    'message' => 'Ada bahan baku yang kurang'
                ]);
            }
        }
    }

    public function getHistoryBahanBakuByBahanAndTanggal($Bahan_Id, $Tanggal_Digunakan)
    {
        $historyBahanBaku = $this->service_history_bahan_baku->getHistoryBahanBakuByBahanAndTanggal($Bahan_Id, $Tanggal_Digunakan);
        return response()->json([
            'history_bahan_baku' => $historyBahanBaku
        ]);
    }
}
