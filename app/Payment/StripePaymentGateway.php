<?php

namespace App\Payment;

use App\Payment\PaymentGatewayContract;

class StripePaymentGateway implements PaymentGatewayContract
{
    private $currency;

    public function __construct($currency){
        $this->currency = $currency;
    }

    public function charge($amount,$sender,$reciver){
       try{
           //:: charge user using api with the given amount

       return [
           'amount' => $amount,
           'currency' =>$this->currency,
           'transaction_number' => 'TXN_xxxx', //:: number we got from api
           'status' => 'success', //:: payment status recived form api
           'response' => true,
        ];

       }catch (\Exception $e){

        return [
            'status' => 'failed', //:: payment status recived form api
            'response' => false,
         ];

       }
    }

}
