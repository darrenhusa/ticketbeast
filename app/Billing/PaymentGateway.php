<?php

namespace App\Billing;

// use Illuminate\Http\Request;

interface PaymentGateway 
{
    public function charge($amount, $token);

}