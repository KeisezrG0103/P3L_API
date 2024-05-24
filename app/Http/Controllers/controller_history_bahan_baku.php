<?php

namespace App\Http\Controllers;

use App\Http\Resources\resource_history_bahan_baku;
use App\Services\service_history_bahan_baku;
use Illuminate\Http\Request;

class controller_history_bahan_baku extends Controller
{
    private $service_history_bahan_baku;

    public function __construct(service_history_bahan_baku $service_history_bahan_baku)
    {
        $this->service_history_bahan_baku = $service_history_bahan_baku;
    }

    public function getHistoryBahanBaku()
    {
        $historyBahanBaku = $this->service_history_bahan_baku->getHistoryBahanBaku();
        return resource_history_bahan_baku::collection($historyBahanBaku);
    }
}
