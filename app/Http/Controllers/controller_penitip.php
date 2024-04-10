<?php

namespace App\Http\Controllers;

use App\Http\Resources\resource_penitip;
use Illuminate\Http\Request;
use App\Models\model_penitip;

class controller_penitip extends Controller
{
    public function ReadPenitip(){
        $penitip = model_penitip::all();
        return resource_penitip::collection($penitip);
    }
}
