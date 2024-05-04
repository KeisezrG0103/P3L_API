<?php

namespace App\Services;

use App\Models\model_customer;

class services_poin
{
    public function getPoinPerCustomer(String $Email) : int
    {
        $customer = model_customer::find($Email);
        return $customer->Total_Poin;
    }
}
