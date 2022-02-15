<?php

namespace App\Payment;

interface PaymentGatewayContract
{
    public function charge($amount,$sender,$reciver);
}
