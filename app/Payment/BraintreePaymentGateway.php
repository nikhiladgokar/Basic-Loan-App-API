<?php

namespace App\Payment;

use App\Payment\PaymentGatewayContract;

class BraintreePaymentGateway implements PaymentGatewayContract
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

        //Ignoring catch in test case as added just static stucture
        // @codeCoverageIgnoreStart
       }catch (\Exception $e){

        return [
            'status' => 'failed', //:: payment status recived form api
            'response' => false,
         ];
        // @codeCoverageIgnoreEnd
       }
    }

}
