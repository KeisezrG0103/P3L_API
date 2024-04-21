<?php

namespace App\Services;

use App\Models\model_presensi;


class service_presensi
{

    public function GetAllPresensi()
    {
        $presensi = model_presensi::select(
            'presensi.Id',
            'presensi.Tanggal',
            'karyawan.Nama as Nama_Karyawan',
            'presensi.Status',
            'karyawan.Id as Karyawan_Id'
        )->join('karyawan', 'presensi.Karyawan_Id', '=', 'karyawan.Id')
            ->get();

        return $presensi;
    }

    public function GetPresensiByDate(String $date)
    {
        $presensi = model_presensi::select(
            'presensi.Id',
            'presensi.Tanggal',
            'karyawan.Nama as Nama_Karyawan',
            'presensi.Status',
            'karyawan.Id as Karyawan_Id'
        )->join('karyawan', 'presensi.Karyawan_Id', '=', 'karyawan.Id')
            ->where('presensi.Tanggal', $date)
            ->get();

        return $presensi;
    }

    public function ChangeStatusToTidakHadir(int $id)
    {
        $presensi = model_presensi::find($id);
        $presensi->Status = 'Tidak_Masuk';
        $presensi->save();
    }
}
