<?php

namespace App\Http\Controllers;

use App\Http\Resources\resource_kategori;
use Illuminate\Http\Request;
use App\Models\model_kategori;

class controller_kategori extends Controller
{
    public function ReadKategori(){
        $kategori = model_kategori::all();
        return resource_kategori::collection($kategori);
    }
}
