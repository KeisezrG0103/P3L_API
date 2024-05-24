<?php

namespace App\Services;

use App\Models\model_history_bahan_baku;

class service_history_bahan_baku
{
    public function getHistoryBahanBaku()
    {
        $historyBahanBaku = model_history_bahan_baku::select(
            'history_bahan_baku.Id',
            'history_bahan_baku.Tanggal_Digunakan',
            'bahan_baku.Nama as Nama_Bahan_Baku',
            'history_bahan_baku.Jumlah_Penggunaan',
            'history_bahan_baku.Satuan',

        )->join('bahan_baku', 'history_bahan_baku.Bahan_Baku_Id', '=', 'bahan_baku.Id')
            ->get();


        return $historyBahanBaku;
    }

    public function getHistoryBahanBakuByBahanAndTanggal($Bahan_Id, $Tanggal_Digunakan)
    {
        $historyBahanBaku = model_history_bahan_baku::where('Bahan_Baku_Id', $Bahan_Id)
            ->where('Tanggal_Digunakan', $Tanggal_Digunakan)
            ->first();

        return $historyBahanBaku;
    }

    public function CatatPemakaianBahanBaku($data)
    {

        $historyBahanBaku = $this->getHistoryBahanBakuByBahanAndTanggal($data['Bahan_Baku_Id'], $data['Tanggal_Digunakan']);


        if ($historyBahanBaku) {
            $historyBahanBaku->Jumlah_Penggunaan += $data['Jumlah_Penggunaan'];
            $historyBahanBaku->update();
        } else {

            $historyBahanBaku = new model_history_bahan_baku();
            $historyBahanBaku->Bahan_Baku_Id = $data['Bahan_Baku_Id'];
            $historyBahanBaku->Tanggal_Digunakan = $data['Tanggal_Digunakan'];
            $historyBahanBaku->Jumlah_Penggunaan = $data['Jumlah_Penggunaan'];
            $historyBahanBaku->Satuan = $data['Satuan'];
            $historyBahanBaku->save();
        }
    }
}
