<?php

namespace App\Services;

use App\Models\model_alamat;

class service_alamat
{
    public function getAlamat($email)
    {
        $alamat = model_alamat::where('Customer_Email', $email)->get();
        return $alamat;
    }

    public function addAlamat($request, $email)
    {
        $alamat = new model_alamat();
        $alamat->Alamat = $request->Alamat;
        $alamat->Customer_Email = $email;
        $alamat->save();
    }
}
